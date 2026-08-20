<?php
defined('BASEPATH') OR exit('No direct script access allowed');
defined('BASEPATH') OR exit('No direct script access allowed');

class Retur extends CI_Controller {

    function __construct(){
        parent::__construct();
        check_not_login();
        $this->load->model('return_m');
        $this->load->model('sale_m');
        $this->load->model('item_m');
        
    }


    public function get_detail($return_id)
    {
        $return = $this->return_m->get_return($return_id)->row();
        $return_detail = $this->return_m->get_return_detail($return_id)->result();
        
        $data = [
            'return' => $return,
            'return_detail' => $return_detail
        ];

        echo json_encode($data);
    }

    public function index()
    {
        $data['returns'] = $this->return_m->get_return()->result();
        $this->template->load('template', 'retur/return_data', $data);
    }

    public function add($sale_id, $item_id = null)
    {
        // Validasi sale_id
        $sale = $this->sale_m->get_sale($sale_id)->row();
        if(!$sale) {
            $this->session->set_flashdata('error', 'Data penjualan tidak ditemukan');
            redirect('report/detail');
        }
        if ($sale->is_cancelled) {
            $this->session->set_flashdata('error', 'Transaksi ini sudah dibatalkan, stok sudah otomatis dikembalikan. Tidak bisa diretur lagi.');
            redirect('report/sale');
        }

        // Ambil detail penjualan
        $sale_detail = $this->sale_m->get_sale_detail($sale_id)->result();
        
        $data = [
            'sale_id' => $sale_id,
            'invoice' => $sale->invoice,
            'sale_detail' => $sale_detail,
            'customer_id' => $sale->customer_id,
            'nama_customer' => $sale->nama_customer ?? 'Pelanggan Umum',
            'selected_item_id' => $item_id
        ];
        
        $this->template->load('template', 'retur/return_form', $data);
    }

    public function process()
    {
        check_allowed_levels([1, 2, 3]);
        $data = $this->input->post(null, TRUE);

        $sale = $this->sale_m->get_sale($data['sale_id'])->row();
        if (!$sale || $sale->is_cancelled) {
            $this->session->set_flashdata('error', 'Transaksi ini sudah dibatalkan, tidak bisa diretur.');
            redirect('report/sale');
        }

        // Piutang (AR) sudah ada pembayaran/masuk kontra bon -> retur akan mengurangi
        // total tagihan, terlalu berisiko disinkron otomatis. Tolak dulu di sini,
        // SEBELUM t_return/t_return_detail ter-insert, sama seperti pola di Sale::update().
        $this->load->model('Ar_invoice_m');
        $existing_ar = $this->Ar_invoice_m->get_by_sale($data['sale_id']);
        if ($existing_ar && $existing_ar->status !== 'void' && ($existing_ar->paid_amount > 0 || $existing_ar->kontra_bon_id)) {
            $this->session->set_flashdata('error', 'Piutang ' . $existing_ar->ar_no . ' untuk transaksi ini sudah ada pembayaran dan/atau sudah masuk kontra bon. Selesaikan/batalkan piutangnya dulu di menu Piutang (AR) sebelum meretur barang dari transaksi ini.');
            redirect('retur/add/' . $data['sale_id']);
        }

        // Batas qty retur tidak boleh melebihi sisa qty yang masih ada di transaksi ini —
        // t_sale_detail.qty sudah otomatis berkurang tiap ada retur sebelumnya (trigger
        // retur_itemstok), jadi nilainya saat ini SUDAH mencerminkan sisa yang benar.
        $remaining_map = [];
        foreach ($this->sale_m->get_sale_detail($data['sale_id'])->result() as $d) {
            $remaining_map[$d->item_id] = (int) $d->qty;
        }
        foreach ($data['items'] as $item) {
            $qty = (int) $item['qty'];
            if ($qty <= 0) continue;
            $sisa = $remaining_map[$item['item_id']] ?? 0;
            if ($qty > $sisa) {
                $this->session->set_flashdata('error', 'Qty retur untuk salah satu barang melebihi sisa yang masih ada di transaksi ini (sisa: ' . $sisa . ').');
                redirect('retur/add/' . $data['sale_id']);
            }
        }

        $return_id = $this->return_m->add_return($data);

        // Insert t_return gagal (mis. constraint/tipe data) -> jangan lanjut,
        // supaya tidak membuat t_return_detail yatim (return_id/sale_id ngawur)
        // yang bikin trigger stok jalan tapi data retur & penjualan tidak ke-update.
        if (!$return_id) {
            $this->session->set_flashdata('error', 'Data Retur gagal disimpan');
            redirect('retur');
        }

        $return_details = [];
        foreach ($data['items'] as $item) {
            if ($item['qty'] > 0) {
                array_push($return_details, [
                    'return_id' => $return_id,
                    'sale_id' => $data['sale_id'],
                    'item_id' => $item['item_id'],
                    'qty' => $item['qty'],
                    'price_retur' => $item['price'],
                    'total' => $item['qty'] * $item['price']
                ]);
            }
        }

        $this->return_m->add_return_detail($return_details);

        if ($this->db->affected_rows() > 0) {
            // Sinkronkan piutang (AR) kalau transaksi ini kredit — total tagihan sudah
            // berkurang lewat trigger DB ("kurangi total") begitu t_return ter-insert di atas.
            try {
                $updated_sale = $this->sale_m->get_sale($data['sale_id'])->row();
                if ($updated_sale) {
                    $this->Ar_invoice_m->sync_from_sale_edit($updated_sale, (int) $this->session->userdata('userid'));
                }
                $this->session->set_flashdata('success', 'Data Retur berhasil disimpan');
            } catch (Exception $e) {
                $this->session->set_flashdata('error', 'Data Retur tersimpan, tapi sinkronisasi piutang gagal: ' . $e->getMessage());
            }
        } else {
            $this->session->set_flashdata('error', 'Data Retur gagal disimpan');
        }
        redirect('retur');
    }

    public function sale_product($sale_id = null)
    {
        $detail = $this->return_m->get_return_detail($sale_id)->result();
        echo json_encode($detail);
    }

    public function del($return_id)
    {
        check_allowed_levels([1, 2]);
        if ($this->input->method() !== 'post') show_404();
        $this->return_m->delete_return($return_id);
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Data Retur berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Data Retur gagal dihapus');
        }
        redirect('retur');
    }
}
