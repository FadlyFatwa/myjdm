<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migrasi SEKALI PAKAI: bawa transaksi t_sale lama (kredit, belum lunas, customer
 * terdaftar) ke modul AR (ar_invoice + jurnal), untuk transaksi yang terjadi
 * sebelum modul Finance aktif. Hapus controller ini + view terkait + route +
 * tombol di ar_invoice_data.php setelah selesai dipakai.
 */
class Migrate_ar extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        check_allowed_levels([1]);
        $this->load->model('Ar_invoice_m');
        $this->load->library('fungsi');
    }

    public function index()
    {
        $data['title'] = 'Migrasi Data Piutang Lama';
        $data['from']  = $this->input->get('from') ?: date('Y-m-01');
        $data['to']    = $this->input->get('to') ?: date('Y-m-d');
        $data['preview'] = $this->_preview($data['from'], $data['to']);
        $this->template->load('template', 'finance/migrate_ar/migrate_ar_form', $data);
    }

    private function _preview($from, $to)
    {
        $eligible = $this->db
            ->select('t_sale.sale_id, t_sale.invoice, t_sale.date, t_sale.customer_id, customer.nama_customer, t_sale.final_price, t_sale.user_id')
            ->from('t_sale')
            ->join('customer', 'customer.customer_id = t_sale.customer_id')
            ->where('t_sale.date >=', $from)
            ->where('t_sale.date <=', $to)
            ->where('t_sale.payment_status', 'belum lunas')
            ->where('t_sale.customer_id IS NOT NULL', null, false)
            ->get()->result();

        // Buang yang sudah pernah dimigrasi (idempotent, aman dijalankan ulang)
        $eligible = array_values(array_filter($eligible, function ($s) {
            return !$this->Ar_invoice_m->get_by_sale($s->sale_id);
        }));

        $skipped_walkin = $this->db
            ->select('t_sale.sale_id, t_sale.invoice, t_sale.date, t_sale.customer_name, t_sale.final_price')
            ->from('t_sale')
            ->where('t_sale.date >=', $from)
            ->where('t_sale.date <=', $to)
            ->where('t_sale.payment_status', 'belum lunas')
            ->where('t_sale.customer_id IS NULL', null, false)
            ->get()->result();

        return [
            'eligible' => $eligible,
            'skipped_walkin' => $skipped_walkin,
        ];
    }

    public function run()
    {
        if ($this->input->method() !== 'post') show_404();

        $from = $this->input->post('from');
        $to   = $this->input->post('to');

        $preview = $this->_preview($from, $to);
        $migrated = 0;
        $failed = [];

        foreach ($preview['eligible'] as $sale) {
            try {
                $this->Ar_invoice_m->create_from_sale($sale);
                $migrated++;
            } catch (Exception $e) {
                $failed[] = $sale->invoice . ': ' . $e->getMessage();
            }
        }

        $msg = $migrated . ' transaksi berhasil dimigrasi jadi piutang (AR).';
        if (!empty($failed)) {
            $msg .= ' Gagal: ' . implode('; ', $failed);
        }
        $this->session->set_flashdata($failed ? 'error' : 'success', $msg);
        redirect('migrate-ar?from=' . $from . '&to=' . $to);
    }
}
