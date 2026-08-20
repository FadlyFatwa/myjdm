<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_order extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        check_not_login();
        if (!in_array($this->fungsi->user_login()->level, [1, 2])) {
            redirect('dashboard');
        }
        $this->load->model(['po_m', 'item_m', 'stock_m', 'supplier_m']);
        $this->load->library('whatsapp');
    }

    public function index()
    {
        $data['suppliers'] = $this->supplier_m->get()->result();
        $this->template->load('template', 'purchasing/po_list', $data);
    }

    public function get_json()
    {
        $draw         = (int) $this->input->post('draw');
        $start        = (int) $this->input->post('start');
        $length       = (int) $this->input->post('length');

        $filter        = $this->input->post('filter') ?: 'all';
        $totalFiltered = $this->po_m->count_filtered($filter);
        $rows          = $this->po_m->get_po_datatables($filter);
        $totalRecords  = $this->po_m->count_all();

        $status_map = [
            'draft'     => '<span class="label label-default">Draft</span>',
            'sent'      => '<span class="label label-info">Terkirim</span>',
            'partial'   => '<span class="label label-warning">Sebagian Diterima</span>',
            'received'  => '<span class="label label-success">Diterima</span>',
            'cancelled' => '<span class="label label-danger">Dibatalkan</span>',
            'closed'    => '<span class="label label-default"><i class="fa fa-lock"></i> Ditutup</span>',
        ];

        $is_superadmin = ((int) $this->fungsi->user_login()->level === 1);

        $today = time();

        $result = [];
        foreach ($rows as $i => $row) {
            $status_badge = $status_map[$row->status] ?? $row->status;
            $can_cancel   = $is_superadmin && in_array($row->status, ['draft', 'sent']);

            // Hitung overdue: pakai last_receipt_date jika ada, fallback ke po_date
            $is_active    = in_array($row->status, ['sent', 'partial']);
            $base_date    = !empty($row->last_receipt_date) ? $row->last_receipt_date : $row->po_date;
            $days_waiting = (int) floor(($today - strtotime($base_date)) / 86400);
            $is_overdue   = $is_active && $days_waiting > 7;

            $overdue_badge = '';
            if ($is_overdue) {
                $label = !empty($row->last_receipt_date) ? 'sejak GR terakhir' : 'sejak dibuat';
                $overdue_badge = '<span class="label label-danger">'
                    . '<i class="fa fa-clock-o"></i> ' . $days_waiting . ' hr ' . $label
                    . '</span>';
            }

            $po_number_cell = htmlspecialchars($row->po_number);
            if ((int) $row->is_direct) {
                $po_number_cell .= ' <span class="label label-success" style="font-size:9px" title="Diterima langsung tanpa PO formal">Langsung</span>';
            }

            $result[] = [
                'no'            => $start + $i + 1,
                'po_number'     => $po_number_cell,
                'nama_supplier' => $row->nama_supplier,
                'po_date'       => indo_date($row->po_date),
                'expected_date' => $row->expected_date ? indo_date($row->expected_date) : '-',
                'status'        => '<div class="po-status">' . $status_badge . $overdue_badge . '</div>',
                'is_overdue'    => $is_overdue,
                'action'        => '<div class="po-actions">'
                    . '<a href="' . site_url('purchase-order/' . $row->po_id) . '" class="btn btn-primary btn-xs" title="Lihat detail"><i class="fa fa-eye"></i> Detail</a>'
                    . '<a href="' . site_url('purchase-order/print/' . $row->po_id) . '" target="_blank" class="btn btn-default btn-xs" title="Cetak PO"><i class="fa fa-print"></i></a>'
                    . ($can_cancel
                        ? '<button class="btn btn-danger btn-xs btn-cancel-po" data-id="' . $row->po_id . '" title="Batalkan PO"><i class="fa fa-times"></i></button>'
                        : '<span class="btn btn-xs po-action-placeholder" aria-hidden="true"><i class="fa fa-times"></i></span>')
                    . '</div>',
            ];
        }

        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data'            => $result,
        ]);
        exit();
    }

    public function overdue_count()
    {
        echo json_encode(['count' => $this->po_m->count_overdue()]);
    }

    public function detail($po_id)
    {
        $po      = $this->po_m->get_po((int) $po_id);
        if (!$po) {
            $this->session->set_flashdata('error', 'Purchase Order tidak ditemukan.');
            redirect('purchase-order');
            return;
        }
        $details    = $this->po_m->get_po_detail((int) $po_id);
        $categories = $this->db->order_by('nama_category')->get('p_category')->result();
        $units      = $this->db->order_by('nama_unit')->get('p_unit')->result();
        $max_barcode  = $this->item_m->get_max_barcode();
        $next_barcode = str_pad((int) $max_barcode + 1, 5, '0', STR_PAD_LEFT);
        $data    = [
            'po'           => $po,
            'details'      => $details,
            'categories'   => $categories,
            'units'        => $units,
            'next_barcode' => $next_barcode,
        ];
        $this->template->load('template', 'purchasing/po_detail', $data);
    }

    public function register_temp_item()
    {
        $detail_id   = (int) $this->input->post('detail_id');
        $po_id       = (int) $this->input->post('po_id');
        $nama_item   = trim($this->input->post('nama_item'));
        $barcode     = $this->input->post('barcode') ?: null;
        $category_id = (int) $this->input->post('category_id');
        $unit_id     = (int) $this->input->post('unit_id');
        $modal       = (int) $this->input->post('modal');

        if (!$nama_item || !$category_id || !$unit_id || !$modal) {
            echo json_encode(['status' => 'error', 'message' => 'Nama, kategori, satuan, dan harga wajib diisi.']);
            exit();
        }

        // PK otomatis dari modal; override jika user mengisi manual
        $pk_input = trim($this->input->post('pk'));
        $pk = $pk_input ?: $this->fungsi->price_to_pk($modal);

        $detail = $this->db->where('id', $detail_id)->where('po_id', $po_id)->get('po_detail')->row();
        if (!$detail || $detail->item_id) {
            echo json_encode(['status' => 'error', 'message' => 'Detail tidak valid atau sudah terdaftar.']);
            exit();
        }

        $po = $this->po_m->get_po($po_id);

        // Transaksi: p_item, po_detail.item_id, t_stock, dan supplier_barang harus
        // konsisten sebagai satu unit -> kalau insert p_item gagal, jangan sampai
        // po_detail ke-link ke item_id=0 atau t_stock ke-insert dengan item_id ngawur.
        $this->db->trans_start();

        $item_id = $this->po_m->create_item_from_temp($nama_item, $barcode, $pk, $category_id, $unit_id, $modal, $po->supplier_id);

        if (!$item_id) {
            $this->db->trans_complete();
            echo json_encode(['status' => 'error', 'message' => 'Barang baru gagal disimpan.']);
            exit();
        }

        $this->db->where('id', $detail_id)->update('po_detail', ['item_id' => $item_id]);

        if ((int) $detail->qty_received > 0) {
            $this->db->query(
                "UPDATE p_item SET stock = stock + ? WHERE item_id = ?",
                [(int) $detail->qty_received, $item_id]
            );
            $this->db->insert('t_stock', [
                'item_id'     => $item_id,
                'type'        => 'in',
                'supplier_id' => $po->supplier_id,
                'qty'         => (int) $detail->qty_received,
                'date'        => date('Y-m-d'),
                'detail'      => 'GR ' . $po->po_number . ' (Registrasi Baru)',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['status' => 'error', 'message' => 'Barang baru gagal disimpan, silakan coba lagi.']);
            exit();
        }

        echo json_encode(['status' => 'success', 'item_id' => $item_id]);
        exit();
    }

    public function confirm_price_update()
    {
        $item_id     = (int) $this->input->post('item_id');
        $supplier_id = (int) $this->input->post('supplier_id');
        $harga_baru  = (int) $this->input->post('harga_baru');
        $po_id       = (int) $this->input->post('po_id');
        $user_id     = (int) $this->session->userdata('userid');

        // Ambil harga lama
        $item = $this->db->where('item_id', $item_id)->get('p_item')->row();
        if (!$item) {
            echo json_encode(['status' => 'error', 'message' => 'Item tidak ditemukan.']);
            exit();
        }

        $harga_lama = (int) $item->modal;

        // Update p_item.modal
        $this->db->where('item_id', $item_id)->update('p_item', ['modal' => $harga_baru]);

        // Update supplier_barang.harga_beli (INSERT if not exists)
        $existing = $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)->get('supplier_barang')->row();
        if ($existing) {
            $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)
                ->update('supplier_barang', ['harga_beli' => $harga_baru]);
        } else {
            $this->db->insert('supplier_barang', [
                'item_id'    => $item_id,
                'supplier_id'=> $supplier_id,
                'harga_beli' => $harga_baru,
            ]);
        }

        // Log
        $this->po_m->log_price_change([
            'item_id'     => $item_id,
            'supplier_id' => $supplier_id,
            'harga_lama'  => $harga_lama,
            'harga_baru'  => $harga_baru,
            'sumber'      => 'po_receipt',
            'sumber_id'   => $po_id,
            'catatan'     => 'Update dari Goods Receipt PO #' . $po_id,
            'changed_by'  => $user_id,
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Harga master berhasil diupdate.']);
        exit();
    }

    public function search_item_draft()
    {
        $kw          = trim($this->input->post('keyword'));
        $supplier_id = (int) $this->input->post('supplier_id');

        $this->db->select('p_item.item_id, p_item.barcode, p_item.nama_item, p_item.stock, p_item.modal,
                           COALESCE(sb.harga_beli, p_item.modal, 0) AS ref_price,
                           supplier.nama_supplier', false);
        $this->db->from('p_item');
        $this->db->join('supplier', 'p_item.supplier_id = supplier.supplier_id', 'left');
        $this->db->join('supplier_barang sb', 'sb.item_id = p_item.item_id AND sb.supplier_id = ' . $supplier_id, 'left');
        $this->db->where('p_item.status', 'active');
        if ($supplier_id) {
            $this->db->group_start();
            $this->db->where('p_item.supplier_id', $supplier_id);
            $this->db->or_where('sb.supplier_id', $supplier_id);
            $this->db->group_end();
        }
        if (!empty($kw)) {
            $words = array_filter(array_map('trim', explode(' ', $kw)));
            foreach ($words as $word) {
                $this->db->group_start();
                $this->db->like('p_item.nama_item', $word);
                $this->db->or_like('p_item.barcode', $word);
                $this->db->group_end();
            }
        }
        $this->db->limit(15);
        $rows = $this->db->get()->result();
        echo json_encode(['items' => $rows]);
        exit();
    }

    public function add_detail_draft()
    {
        if ((int) $this->fungsi->user_login()->level !== 1) {
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
            exit();
        }
        $po_id      = (int) $this->input->post('po_id');
        $item_id    = $this->input->post('item_id') ? (int) $this->input->post('item_id') : null;
        $qty        = max(1, (int) $this->input->post('qty'));
        $unit_price = (float) $this->input->post('unit_price');
        $notes      = $this->input->post('notes');

        $po = $this->po_m->get_po($po_id);
        if (!$po || $po->status !== 'draft') {
            echo json_encode(['status' => 'error', 'message' => 'PO bukan status draft.']);
            exit();
        }

        $row = [
            'po_id'          => $po_id,
            'item_id'        => $item_id,
            'item_name_temp' => $item_id ? null : $this->input->post('item_name_temp'),
            'qty_ordered'    => $qty,
            'qty_received'   => 0,
            'unit_price'     => $unit_price,
            'notes'          => $notes ?: null,
        ];
        $this->db->insert('po_detail', $row);
        $new_id = $this->db->insert_id();

        // Fetch the inserted row with display_name for response
        $detail = $this->po_m->get_po_detail($po_id);
        $inserted = null;
        foreach ($detail as $d) {
            if ((int) $d->id === (int) $new_id) { $inserted = $d; break; }
        }

        echo json_encode(['status' => 'success', 'detail' => $inserted]);
        exit();
    }

    public function remove_detail_draft()
    {
        if ((int) $this->fungsi->user_login()->level !== 1) {
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
            exit();
        }
        $detail_id = (int) $this->input->post('detail_id');
        $po_id     = (int) $this->input->post('po_id');

        $po = $this->po_m->get_po($po_id);
        if (!$po || $po->status !== 'draft') {
            echo json_encode(['status' => 'error', 'message' => 'PO bukan status draft.']);
            exit();
        }

        $detail = $this->db->where('id', $detail_id)->where('po_id', $po_id)->get('po_detail')->row();
        if (!$detail) {
            echo json_encode(['status' => 'error', 'message' => 'Item tidak ditemukan.']);
            exit();
        }

        $returned_to_cart = false;

        if ($detail->item_id) {
            $this->load->model('po_cart_m');
            $this->po_cart_m->add([
                'item_id'     => $detail->item_id,
                'supplier_id' => $po->supplier_id,
                'qty'         => (int) $detail->qty_ordered,
                'ref_price'   => (int) $detail->unit_price,
                'notes'       => 'Dikembalikan dari ' . $po->po_number,
                'added_by'    => (int) $this->session->userdata('userid'),
            ]);
            $returned_to_cart = true;
        } elseif (!empty($detail->item_name_temp)) {
            $this->load->model('po_cart_m');
            $this->po_cart_m->add([
                'item_name_temp' => $detail->item_name_temp,
                'supplier_id'    => $po->supplier_id,
                'qty'            => (int) $detail->qty_ordered,
                'ref_price'      => (int) $detail->unit_price,
                'notes'          => 'Dikembalikan dari ' . $po->po_number,
                'added_by'       => (int) $this->session->userdata('userid'),
            ]);
            $returned_to_cart = true;
        }

        $this->db->where('id', $detail_id)->where('po_id', $po_id)->delete('po_detail');

        echo json_encode([
            'status'           => 'success',
            'returned_to_cart' => $returned_to_cart,
        ]);
        exit();
    }

    public function update_detail_draft()
    {
        if ((int) $this->fungsi->user_login()->level !== 1) {
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
            exit();
        }
        $po_id      = (int) $this->input->post('po_id');
        $detail_ids = (array) $this->input->post('detail_id');
        $qtys       = (array) $this->input->post('qty');
        $prices     = (array) $this->input->post('unit_price');

        $po = $this->po_m->get_po($po_id);
        if (!$po || $po->status !== 'draft') {
            echo json_encode(['status' => 'error', 'message' => 'PO bukan status draft.']);
            exit();
        }

        foreach ($detail_ids as $i => $detail_id) {
            $qty   = max(1, (int) ($qtys[$i] ?? 1));
            $price = (int) ($prices[$i] ?? 0);
            $this->db->where('id', (int) $detail_id)
                ->where('po_id', $po_id)
                ->update('po_detail', ['qty_ordered' => $qty, 'unit_price' => $price]);
        }

        echo json_encode(['status' => 'success']);
        exit();
    }

    public function close_po()
    {
        $po_id      = (int) $this->input->post('po_id');
        $close_note = $this->input->post('close_note');

        $po = $this->po_m->get_po($po_id);
        if (!$po || !in_array($po->status, ['sent', 'partial'])) {
            $this->session->set_flashdata('error', 'PO tidak bisa ditutup pada status ini.');
            redirect('purchase-order/' . $po_id);
            return;
        }

        $details  = $this->po_m->get_po_detail($po_id);
        $returned = $this->return_unreceived_to_cart($po, $details, 'Kekurangan dari ');

        $this->po_m->update_status($po_id, 'closed');
        $this->db->where('po_id', $po_id)->update('po_header', ['close_note' => $close_note]);

        $msg = 'PO ditutup.';
        if ($returned > 0) $msg .= " {$returned} item dikembalikan ke Keranjang PO.";
        $this->session->set_flashdata('success', $msg);
        redirect('purchase-order/' . $po_id);
    }

    /**
     * Kembalikan item PO yang belum diterima ke Keranjang PO — untuk supplier
     * PO itu sendiri, dan juga ke supplier lain (dari supplier_barang) yang
     * juga menyuplai item tersebut, agar item multi-supplier kembali utuh
     * seperti sebelum PO dibuat.
     */
    private function return_unreceived_to_cart($po, $details, $note_prefix)
    {
        $this->load->model('po_cart_m');
        $user_id  = (int) $this->session->userdata('userid');
        $returned = 0;

        foreach ($details as $d) {
            $unreceived = $d->qty_ordered - $d->qty_received;
            if ($unreceived <= 0) continue;

            if (!$d->item_id) {
                // Belum terdaftar — po_cart sudah dukung item_name_temp (pola yang sama
                // dipakai di po_detail & po_receive), jadi tetap dikembalikan ke keranjang
                // sebagai item temp, bukan dibuang begitu saja.
                $this->po_cart_m->add([
                    'item_name_temp' => $d->item_name_temp,
                    'supplier_id'    => $po->supplier_id,
                    'qty'            => $unreceived,
                    'ref_price'      => (int) $d->unit_price,
                    'notes'          => $note_prefix . $po->po_number,
                    'added_by'       => $user_id,
                ]);
                $returned++;
                continue;
            }

            $this->po_cart_m->add([
                'item_id'     => $d->item_id,
                'supplier_id' => $po->supplier_id,
                'qty'         => $unreceived,
                'ref_price'   => (int) $d->unit_price,
                'notes'       => $note_prefix . $po->po_number,
                'added_by'    => $user_id,
            ]);
            $returned++;

            $other_suppliers = $this->db->select('supplier_id')
                ->where('item_id', $d->item_id)
                ->where('supplier_id !=', $po->supplier_id)
                ->get('supplier_barang')->result();

            foreach ($other_suppliers as $s) {
                $this->po_cart_m->add([
                    'item_id'     => $d->item_id,
                    'supplier_id' => $s->supplier_id,
                    'qty'         => $unreceived,
                    'ref_price'   => $this->po_cart_m->get_ref_price($d->item_id, $s->supplier_id),
                    'notes'       => $note_prefix . $po->po_number,
                    'added_by'    => $user_id,
                ]);
            }
        }

        return $returned;
    }

    public function print_po($po_id)
    {
        $po      = $this->po_m->get_po((int) $po_id);
        if (!$po) {
            show_404();
            return;
        }
        $details = $this->po_m->get_po_detail((int) $po_id);
        $data    = ['po' => $po, 'details' => $details];
        $this->load->view('purchasing/po_print', $data);
    }

    public function update_status()
    {
        $po_id  = (int) $this->input->post('po_id');
        $status = $this->input->post('status');
        $allowed = ['draft', 'sent', 'partial', 'received', 'cancelled', 'closed'];
        if (!in_array($status, $allowed)) {
            echo json_encode(['status' => 'error', 'message' => 'Status tidak valid.']);
            exit();
        }
        if (in_array($status, ['sent', 'cancelled']) && (int) $this->fungsi->user_login()->level !== 1) {
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya Super Admin yang dapat melakukan aksi ini.']);
            exit();
        }

        $po = $this->db->where('po_id', $po_id)->get('po_header')->row();

        $this->po_m->update_status($po_id, $status);

        if ($status === 'sent') {
            $sup = $po ? $this->db->where('supplier_id', $po->supplier_id)->get('supplier')->row() : null;
            $this->db->insert('notifications', [
                'type'      => 'po_sent',
                'title'     => 'PO Dikirim — ' . ($po->po_number ?? '#'.$po_id),
                'message'   => 'PO ke ' . ($sup->nama_supplier ?? 'supplier') . ' telah dikirim, menunggu penerimaan.',
                'for_level' => 2,
                'ref_id'    => $po_id,
            ]);
        }

        if ($status === 'cancelled' && $po) {
            $details = $this->po_m->get_po_detail($po_id);
            $this->return_unreceived_to_cart($po, $details, 'Dibatalkan dari ');
        }

        echo json_encode(['status' => 'success']);
        exit();
    }
}
