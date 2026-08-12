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
    }

    private function _price_to_pk($price)
    {
        $map   = ['0'=>'Y','1'=>'S','2'=>'I','3'=>'T','4'=>'O','5'=>'M','6'=>'P','7'=>'U','8'=>'L','9'=>'X'];
        $s     = preg_replace('/[^0-9]/', '', (string) $price);
        $out   = '';
        $zeros = 0;
        for ($i = 0; $i < strlen($s); $i++) {
            if ($s[$i] === '0') {
                $zeros++;
            } else {
                if ($zeros > 1) $out .= 'Y' . $zeros;
                elseif ($zeros === 1) $out .= 'Y';
                $zeros = 0;
                $out .= $map[$s[$i]] ?? $s[$i];
            }
        }
        if ($zeros > 1) $out .= 'Y' . $zeros;
        elseif ($zeros === 1) $out .= 'Y';
        return strtoupper($out);
    }

    private function _update_item_price($item_id, $supplier_id, $new_price, $source = 'po_receipt', $source_id = null)
    {
        $item = $this->db->where('item_id', $item_id)->get('p_item')->row();
        if (!$item || $new_price <= 0) return;
        if ((int) $item->modal === $new_price) return; // tidak berubah

        $new_pk = $this->_price_to_pk($new_price);

        // Update p_item
        $this->db->where('item_id', $item_id)->update('p_item', [
            'modal' => $new_price,
            'pk'    => $new_pk,
        ]);

        // Update supplier_barang
        $existing = $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)->get('supplier_barang')->row();
        if ($existing) {
            $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)
                     ->update('supplier_barang', ['harga_beli' => $new_price]);
        } else {
            $this->db->insert('supplier_barang', [
                'item_id'     => $item_id,
                'supplier_id' => $supplier_id,
                'harga_beli'  => $new_price,
            ]);
        }

        // Log perubahan harga
        $this->po_m->log_price_change([
            'item_id'     => $item_id,
            'supplier_id' => $supplier_id,
            'harga_lama'  => (int) $item->modal,
            'harga_baru'  => $new_price,
            'sumber'      => $source,
            'sumber_id'   => $source_id,
            'catatan'     => 'Koreksi GR',
            'changed_by'  => (int) $this->session->userdata('userid'),
        ]);
    }

    public function index()
    {
        $this->template->load('template', 'purchasing/po_list');
    }

    public function receiving_list()
    {
        $this->template->load('template', 'purchasing/receiving_list');
    }

    public function receiving_json()
    {
        $draw   = (int) $this->input->post('draw');
        $start  = (int) $this->input->post('start');
        $length = (int) $this->input->post('length');
        $search = $_POST['search']['value'] ?? '';

        $this->db->select('po_header.po_id, po_header.po_number, po_header.po_date, po_header.expected_date,
                           po_header.status, supplier.nama_supplier,
                           COUNT(po_detail.id) AS total_lines,
                           SUM(po_detail.qty_ordered) AS total_ordered,
                           SUM(po_detail.qty_received) AS total_received', false);
        $this->db->from('po_header');
        $this->db->join('supplier', 'po_header.supplier_id = supplier.supplier_id');
        $this->db->join('po_detail', 'po_detail.po_id = po_header.po_id', 'left');
        $this->db->where_in('po_header.status', ['sent', 'partial']);
        $this->db->group_by('po_header.po_id');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('po_header.po_number', $search);
            $this->db->or_like('supplier.nama_supplier', $search);
            $this->db->group_end();
        }

        $total_filtered = $this->db->count_all_results('', false);
        $this->db->order_by('po_header.po_id', 'DESC');
        $this->db->limit($length, $start);
        $rows = $this->db->get()->result();

        $total_records = $this->db->where_in('status', ['sent', 'partial'])->count_all_results('po_header');

        $status_map = [
            'sent'    => '<span class="label label-info">Terkirim</span>',
            'partial' => '<span class="label label-warning">Sebagian Diterima</span>',
        ];

        $result = [];
        foreach ($rows as $i => $row) {
            $ordered  = (int) $row->total_ordered;
            $received = (int) $row->total_received;
            $pct      = $ordered > 0 ? round($received / $ordered * 100) : 0;
            $bar_color = $pct > 0 ? '#f39c12' : '#ddd';
            $progress = '<div style="font-size:11px;margin-bottom:3px;font-weight:600;color:' . ($pct > 0 ? '#e08e0b' : '#999') . '">'
                      . $received . '/' . $ordered . ' (' . $pct . '%)</div>'
                      . '<div style="height:5px;background:#e9ecef;border-radius:3px">'
                      . '<div style="width:' . $pct . '%;height:5px;background:' . $bar_color . ';border-radius:3px"></div></div>';
            $result[] = [
                'no'            => $start + $i + 1,
                'po_number'     => htmlspecialchars($row->po_number),
                'nama_supplier' => htmlspecialchars($row->nama_supplier),
                'po_date'       => indo_date($row->po_date),
                'expected_date' => $row->expected_date ? indo_date($row->expected_date) : '<span class="text-muted">—</span>',
                'status'        => $status_map[$row->status] ?? $row->status,
                'progress'      => $progress,
                'action'        => '<a href="' . site_url('purchase-order/' . $row->po_id) . '" class="btn btn-warning btn-xs">'
                                 . '<i class="fa fa-inbox"></i> Terima</a>',
            ];
        }

        echo json_encode(['draw' => $draw, 'recordsTotal' => $total_records, 'recordsFiltered' => $total_filtered, 'data' => $result]);
        exit();
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

            if ($is_overdue) {
                $label = !empty($row->last_receipt_date) ? 'sejak GR terakhir' : 'sejak dibuat';
                $status_badge .= ' <span class="label label-danger" style="margin-left:4px">'
                    . '<i class="fa fa-clock-o"></i> ' . $days_waiting . ' hr ' . $label
                    . '</span>';
            }

            $result[] = [
                'no'            => $start + $i + 1,
                'po_number'     => $row->po_number,
                'nama_supplier' => $row->nama_supplier,
                'po_date'       => indo_date($row->po_date),
                'expected_date' => $row->expected_date ? indo_date($row->expected_date) : '-',
                'status'        => $status_badge,
                'is_overdue'    => $is_overdue,
                'action'        => '
                    <a href="' . site_url('purchase-order/' . $row->po_id) . '" class="btn btn-primary btn-xs"><i class="fa fa-eye"></i> Detail</a>
                    <a href="' . site_url('purchase-order/print/' . $row->po_id) . '" target="_blank" class="btn btn-default btn-xs"><i class="fa fa-print"></i></a>
                    ' . ($can_cancel ? '<button class="btn btn-danger btn-xs btn-cancel-po" data-id="' . $row->po_id . '"><i class="fa fa-times"></i></button>' : ''),
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
        $pk = $pk_input ?: $this->_price_to_pk($modal);

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

        $this->db->insert('p_item', [
            'nama_item'   => $nama_item,
            'barcode'     => $barcode,
            'pk'          => $pk,
            'category_id' => $category_id,
            'unit_id'     => $unit_id,
            'modal'       => $modal,
            'price'       => 1,
            'stock'       => 0,
            'supplier_id' => $po->supplier_id,
            'status'      => 'active',
        ]);
        $item_id = $this->db->insert_id();

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

        $existing = $this->db->where('item_id', $item_id)->where('supplier_id', $po->supplier_id)->get('supplier_barang')->row();
        if (!$existing) {
            $this->db->insert('supplier_barang', [
                'item_id'     => $item_id,
                'supplier_id' => $po->supplier_id,
                'harga_beli'  => $modal,
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

    public function receive_form($po_id)
    {
        $po = $this->po_m->get_po((int) $po_id);
        if (!$po || !in_array($po->status, ['sent', 'partial'])) {
            $this->session->set_flashdata('error', 'PO tidak tersedia untuk penerimaan.');
            redirect('purchase-order/' . $po_id);
            return;
        }
        $details = $this->po_m->get_po_detail((int) $po_id);
        $this->template->load('template', 'purchasing/po_receive', [
            'po'      => $po,
            'details' => $details,
        ]);
    }

    public function receive()
    {
        $po_id       = (int) $this->input->post('po_id');
        $po          = $this->po_m->get_po($po_id);
        $user_id     = (int) $this->session->userdata('userid');
        $detail_ids  = $this->input->post('detail_id');
        $qty_arr     = $this->input->post('qty_received');
        $price_arr   = $this->input->post('actual_price');

        if (!$po || !in_array($po->status, ['sent', 'partial'])) {
            $this->session->set_flashdata('error', 'PO tidak dapat diproses penerimaan.');
            redirect('purchase-order/' . $po_id);
            return;
        }

        // Simpan data penerimaan ke po_receipt
        $supplier_invoice_no  = $this->input->post('supplier_invoice_no') ?: null;
        $invoice_date         = $this->input->post('invoice_date') ?: null;
        $receive_date         = $this->input->post('receive_date') ?: date('Y-m-d');
        $ongkir = (int) str_replace('.', '', $this->input->post('ongkir'));

        $this->db->insert('po_receipt', [
            'po_id'                 => $po_id,
            'supplier_invoice_no'   => $supplier_invoice_no,
            'invoice_date'          => $invoice_date,
            'receive_date'          => $receive_date,
            'received_by'           => $user_id,
            'ongkir'                => $ongkir,
            'ongkir_payment_method' => $ongkir > 0 ? 'cash' : null,
        ]);
        $receipt_id = $this->db->insert_id();

        if (!$receipt_id) {
            // Jangan lanjut catat ongkir/stok kalau po_receipt sendiri gagal
            // disimpan -- nanti stok & jurnal ongkir jalan tanpa riwayat penerimaannya.
            $this->session->set_flashdata('error', 'Penerimaan barang gagal disimpan, silakan coba lagi.');
            redirect('purchase-order/' . $po_id);
            return;
        }

        // Ongkir selalu dibayar tunai dari Kas — catat lewat Beban_m supaya muncul juga
        // di listing "Beban Operasional" (bukan cuma jurnal), bukan cuma dikait ke PO.
        if ($ongkir > 0) {
            $this->load->model('Beban_m');
            $this->load->model('Coa_m');

            $beban_angkut = $this->Coa_m->get_by_subtype('beban_angkut_pembelian');

            $expense_id = $this->Beban_m->create([
                'expense_date'   => $receive_date,
                'coa_id'         => $beban_angkut->coa_id,
                'amount'         => $ongkir,
                'payment_method' => 'cash',
                'description'    => 'Ongkir penerimaan PO ' . $po->po_number,
            ], $user_id);

            $expense = $this->Beban_m->get($expense_id);

            $this->db->where('receipt_id', $receipt_id)->update('po_receipt', [
                'ongkir_expense_id' => $expense_id,
                'ongkir_journal_id' => $expense->journal_id,
            ]);
        }

        $pk_arr = (array) ($this->input->post('pk_new') ?: []);

        foreach ((array) $detail_ids as $idx => $detail_id) {
            $qty_received = (int) ($qty_arr[$idx] ?? 0);
            $actual_price = (int) ($price_arr[$idx] ?? 0);
            $new_pk       = strtoupper(trim($pk_arr[$idx] ?? ''));

            if ($qty_received <= 0) continue;

            $detail = $this->db->where('id', (int) $detail_id)->get('po_detail')->row();
            if (!$detail) continue;

            $ok = $this->po_m->receive_detail(
                $po_id,
                (int) $detail_id,
                $qty_received,
                $actual_price,
                $po->supplier_id,
                $po->po_number,
                $invoice_date,
                $receipt_id
            );

            // Update harga & supplier saat penerimaan
            if ($ok && $detail->item_id && $actual_price > 0) {
                $item = $this->db->where('item_id', $detail->item_id)->get('p_item')->row();
                $sb   = $this->db->where('item_id', $detail->item_id)
                                  ->where('supplier_id', $po->supplier_id)
                                  ->get('supplier_barang')->row();

                // Selalu update supplier_id di p_item (siapa yang terakhir kirim)
                if ($item) {
                    $p_item_update = ['supplier_id' => $po->supplier_id];

                    $harga_lama_sb = $sb ? (int) $sb->harga_beli : null;
                    $harga_berubah = ($harga_lama_sb === null || $harga_lama_sb !== $actual_price);

                    if ($harga_berubah) {
                        $pk_final = $new_pk ?: $this->_price_to_pk($actual_price);
                        $p_item_update['modal'] = $actual_price;
                        $p_item_update['pk']    = $pk_final;
                    }

                    $this->db->where('item_id', $detail->item_id)->update('p_item', $p_item_update);
                }

                $harga_lama_sb = $sb ? (int) $sb->harga_beli : null;
                $harga_berubah = ($harga_lama_sb === null || $harga_lama_sb !== $actual_price);

                if ($harga_berubah && $item) {
                    // Update supplier_barang untuk supplier ini
                    if ($sb) {
                        $this->db->where('item_id', $detail->item_id)
                                 ->where('supplier_id', $po->supplier_id)
                                 ->update('supplier_barang', ['harga_beli' => $actual_price]);
                    } else {
                        $this->db->insert('supplier_barang', [
                            'item_id'     => $detail->item_id,
                            'supplier_id' => $po->supplier_id,
                            'harga_beli'  => $actual_price,
                        ]);
                    }

                    // Log perubahan harga
                    $this->po_m->log_price_change([
                        'item_id'     => $detail->item_id,
                        'supplier_id' => $po->supplier_id,
                        'harga_lama'  => $harga_lama_sb ?? (int) $item->modal,
                        'harga_baru'  => $actual_price,
                        'sumber'      => 'po_receipt',
                        'sumber_id'   => $po_id,
                        'catatan'     => 'GR ' . $po->po_number . ' (' . $po->nama_supplier . ')',
                        'changed_by'  => $user_id,
                    ]);
                }
            }
        }

        $sup_name = $this->db->where('supplier_id', $po->supplier_id)->get('supplier')->row();
        $receiver = $this->db->where('user_id', $user_id)->get('user')->row();
        $this->db->insert('notifications', [
            'type'      => 'po_received',
            'title'     => 'Penerimaan Barang — ' . $po->po_number,
            'message'   => 'Diterima dari ' . ($sup_name->nama_supplier ?? 'supplier') . ' oleh ' . ($receiver->nama ?? 'gudang') . '.',
            'for_level' => 1,
            'ref_id'    => $receipt_id,
        ]);

        $this->session->set_flashdata('success', 'Penerimaan barang berhasil dicatat.');
        redirect('purchase-order/' . $po_id);
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

    public function receiving_history()
    {
        $this->template->load('template', 'purchasing/receiving_history');
    }

    public function receiving_history_data()
    {
        $draw   = (int) $this->input->post('draw');
        $start  = (int) $this->input->post('start');
        $length = (int) $this->input->post('length');
        $search = $_POST['search']['value'] ?? '';

        $base = function () use ($search) {
            $this->db->select('po_receipt.receipt_id, po_receipt.supplier_invoice_no,
                               po_receipt.invoice_date, po_receipt.receive_date,
                               po_receipt.created_at,
                               po_header.po_id, po_header.po_number,
                               supplier.nama_supplier,
                               user.nama AS received_by_name,
                               COUNT(po_detail.id) AS total_lines,
                               SUM(po_detail.qty_received) AS total_qty', false);
            $this->db->from('po_receipt');
            $this->db->join('po_header', 'po_receipt.po_id = po_header.po_id');
            $this->db->join('supplier', 'po_header.supplier_id = supplier.supplier_id');
            $this->db->join('user', 'po_receipt.received_by = user.user_id', 'left');
            $this->db->join('po_detail', 'po_detail.receipt_id = po_receipt.receipt_id', 'left');
            $this->db->group_by('po_receipt.receipt_id');
            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('po_header.po_number', $search);
                $this->db->or_like('supplier.nama_supplier', $search);
                $this->db->or_like('po_receipt.supplier_invoice_no', $search);
                $this->db->or_like('user.nama', $search);
                $this->db->group_end();
            }
        };

        $base();
        $total_filtered = $this->db->count_all_results('', false);
        $this->db->order_by('po_receipt.receipt_id', 'DESC');
        $this->db->limit($length, $start);
        $rows = $this->db->get()->result();

        $base();
        $total_records = $this->db->count_all_results('');

        $result = [];
        foreach ($rows as $i => $row) {
            $result[] = [
                'no'                  => $start + $i + 1,
                'receive_date'        => indo_date($row->receive_date),
                'invoice_date'        => $row->invoice_date ? indo_date($row->invoice_date) : '<span class="text-muted">—</span>',
                'supplier_invoice_no' => $row->supplier_invoice_no
                    ? '<span class="label label-default" style="font-size:11px">' . htmlspecialchars($row->supplier_invoice_no) . '</span>'
                    : '<span class="text-muted">—</span>',
                'po_number'           => '<a href="' . site_url('purchase-order/' . $row->po_id) . '">' . htmlspecialchars($row->po_number) . '</a>',
                'nama_supplier'       => htmlspecialchars($row->nama_supplier),
                'total_lines'         => (int) $row->total_lines . ' item',
                'total_qty'           => (int) $row->total_qty . ' pcs',
                'received_by_name'    => htmlspecialchars($row->received_by_name ?? '—'),
                'is_empty'            => ((int) $row->total_qty === 0),
                'receipt_id'          => $row->receipt_id,
                'action'              => '<a href="' . site_url('purchase-order/history/' . $row->receipt_id) . '" class="btn btn-info btn-xs">'
                                       . '<i class="fa fa-eye"></i> Detail</a>'
                                       . ' <button class="btn btn-danger btn-xs btn-del-receipt" data-id="' . $row->receipt_id . '" title="Hapus penerimaan ini">'
                                       . '<i class="fa fa-trash"></i></button>',
            ];
        }

        echo json_encode(['draw' => $draw, 'recordsTotal' => $total_records, 'recordsFiltered' => $total_filtered, 'data' => $result]);
        exit();
    }

    public function receiving_history_detail($receipt_id)
    {
        $receipt = $this->db->where('po_receipt.receipt_id', (int) $receipt_id)
            ->select('po_receipt.*, po_header.po_number, po_header.po_id,
                      supplier.nama_supplier, user.nama AS received_by_name', false)
            ->from('po_receipt')
            ->join('po_header', 'po_receipt.po_id = po_header.po_id')
            ->join('supplier', 'po_header.supplier_id = supplier.supplier_id')
            ->join('user', 'po_receipt.received_by = user.user_id', 'left')
            ->get()->row();

        if (!$receipt) {
            $this->session->set_flashdata('error', 'Data penerimaan tidak ditemukan.');
            redirect('purchase-order/history');
            return;
        }

        $items = $this->db->select('po_detail.*, p_item.nama_item, p_item.barcode, p_item.pk AS item_pk, p_unit.nama_unit', false)
            ->from('po_detail')
            ->join('p_item', 'po_detail.item_id = p_item.item_id', 'left')
            ->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left')
            ->where('po_detail.receipt_id', (int) $receipt_id)
            ->get()->result();

        // Item dari PO yang sama, belum masuk receipt ini, masih ada sisa qty
        $available_items = $this->db->query(
            "SELECT pd.id, pd.qty_ordered, pd.qty_received, pd.unit_price,
                    COALESCE(pi.nama_item, pd.item_name_temp) AS display_name,
                    pi.barcode, pu.nama_unit
             FROM po_detail pd
             LEFT JOIN p_item pi ON pd.item_id = pi.item_id
             LEFT JOIN p_unit pu ON pi.unit_id = pu.unit_id
             WHERE pd.po_id = ?
               AND (pd.receipt_id IS NULL OR pd.receipt_id != ?)
               AND pd.qty_ordered > pd.qty_received",
            [$receipt->po_id, (int) $receipt_id]
        )->result();

        $this->template->load('template', 'purchasing/receiving_history_detail', [
            'receipt'         => $receipt,
            'items'           => $items,
            'available_items' => $available_items,
        ]);
    }

    public function update_receipt_detail()
    {
        if ($this->input->method() !== 'post') show_404();

        $detail_id    = (int) $this->input->post('detail_id');
        $new_qty      = (int) $this->input->post('qty_received');
        $new_price    = (int) str_replace('.', '', $this->input->post('actual_price'));

        if ($detail_id < 1 || $new_qty < 0) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak valid.']);
            return;
        }

        $detail = $this->db->where('id', $detail_id)->get('po_detail')->row();
        if (!$detail) {
            echo json_encode(['status' => 'error', 'message' => 'Detail tidak ditemukan.']);
            return;
        }

        if ($new_qty > (int) $detail->qty_ordered) {
            echo json_encode(['status' => 'error', 'message' => 'Qty tidak boleh melebihi qty order (' . $detail->qty_ordered . ').']);
            return;
        }

        $old_qty  = (int) $detail->qty_received;
        $qty_diff = $new_qty - $old_qty;

        if ($detail->item_id && $qty_diff !== 0) {
            // Koreksi p_item.stock
            $this->db->set('stock', "stock + ($qty_diff)", false)
                     ->where('item_id', $detail->item_id)
                     ->update('p_item');

            // Hapus entry t_stock lama pakai po_detail_id, ganti dengan qty baru
            $po = $this->db->where('po_id', $detail->po_id)->get('po_header')->row();
            $this->db->where('po_detail_id', $detail_id)->delete('t_stock');

            if ($new_qty > 0 && $po) {
                $this->db->insert('t_stock', [
                    'item_id'      => (int) $detail->item_id,
                    'type'         => 'in',
                    'supplier_id'  => (int) $po->supplier_id,
                    'po_detail_id' => $detail_id,
                    'qty'          => $new_qty,
                    'date'         => date('Y-m-d'),
                    'detail'       => 'Goods Receipt ' . ($po->po_number ?? ''),
                    'created_at'   => date('Y-m-d H:i:s'),
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Update po_detail
        $update = ['qty_received' => $new_qty];
        if ($new_price > 0) $update['actual_price'] = $new_price;
        $this->db->where('id', $detail_id)->update('po_detail', $update);

        // Update harga beli item jika berubah
        if ($detail->item_id && $new_price > 0 && $new_price !== (int) $detail->actual_price) {
            $po = $this->db->where('po_id', $detail->po_id)->get('po_header')->row();
            if ($po) $this->_update_item_price($detail->item_id, $po->supplier_id, $new_price, 'po_receipt', $detail->po_id);
        }

        // Recalculate po_header status
        $po_id   = $detail->po_id;
        $details = $this->db->select('qty_ordered, qty_received')->where('po_id', $po_id)->get('po_detail')->result();
        $total_ordered  = array_sum(array_column((array)$details, 'qty_ordered'));
        $total_received = array_sum(array_column((array)$details, 'qty_received'));

        if ($total_received <= 0) {
            $new_status = 'sent';
        } elseif ($total_received >= $total_ordered) {
            $new_status = 'received';
        } else {
            $new_status = 'partial';
        }
        $this->db->where('po_id', $po_id)->update('po_header', ['status' => $new_status]);

        echo json_encode([
            'status'      => 'success',
            'new_qty'     => $new_qty,
            'qty_diff'    => $qty_diff,
            'po_status'   => $new_status,
        ]);
    }

    public function delete_receipt($receipt_id)
    {
        if ($this->input->method() !== 'post') show_404();

        $receipt_id = (int) $receipt_id;
        $receipt    = $this->db->where('receipt_id', $receipt_id)->get('po_receipt')->row();

        if (!$receipt) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
            return;
        }

        $po_id = $receipt->po_id;

        // Ambil semua detail receipt ini
        $details = $this->db->where('receipt_id', $receipt_id)->get('po_detail')->result();

        // Kembalikan stok untuk item yang qty_received > 0
        foreach ($details as $d) {
            if ($d->qty_received > 0 && $d->item_id) {
                $this->db->query(
                    "UPDATE p_item SET stock = stock - ? WHERE item_id = ?",
                    [(int) $d->qty_received, (int) $d->item_id]
                );
                // Hapus t_stock pakai po_detail_id — presisi, tidak ambigu
                $this->db->where('po_detail_id', (int) $d->id)->delete('t_stock');
            }
            // Reset qty_received di po_detail
            $this->db->where('id', $d->id)->update('po_detail', [
                'qty_received' => 0,
                'actual_price' => 0,
                'receipt_id'   => null,
            ]);
        }

        // Hapus receipt header
        $this->db->where('receipt_id', $receipt_id)->delete('po_receipt');

        // Recalculate po_header status
        $remaining = $this->db->select('qty_ordered, qty_received')->where('po_id', $po_id)->get('po_detail')->result();
        $total_received = array_sum(array_column((array) $remaining, 'qty_received'));

        $new_status = $total_received > 0 ? 'partial' : 'sent';
        $this->db->where('po_id', $po_id)->update('po_header', ['status' => $new_status]);

        echo json_encode(['status' => 'success']);
    }

    public function add_receipt_detail()
    {
        if ($this->input->method() !== 'post') show_404();

        $receipt_id    = (int) $this->input->post('receipt_id');
        $po_detail_id  = (int) $this->input->post('po_detail_id');
        $qty           = (int) $this->input->post('qty');
        $actual_price  = (int) str_replace('.', '', $this->input->post('actual_price'));

        if ($qty < 1) {
            echo json_encode(['status' => 'error', 'message' => 'Qty minimal 1.']);
            return;
        }

        $detail  = $this->db->where('id', $po_detail_id)->get('po_detail')->row();
        $receipt = $this->db->where('receipt_id', $receipt_id)->get('po_receipt')->row();

        if (!$detail || !$receipt) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
            return;
        }

        $remaining = $detail->qty_ordered - $detail->qty_received;
        if ($qty > $remaining) {
            echo json_encode(['status' => 'error', 'message' => "Melebihi sisa qty ($remaining)."]);
            return;
        }

        $po = $this->db->where('po_id', $detail->po_id)->get('po_header')->row();

        $final_price = $actual_price ?: (int) $detail->unit_price;

        // Update po_detail
        $this->db->where('id', $po_detail_id)->update('po_detail', [
            'qty_received' => $detail->qty_received + $qty,
            'actual_price' => $final_price,
            'receipt_id'   => $receipt_id,
        ]);

        // Update harga beli item jika berbeda dari modal saat ini
        if ($detail->item_id && $actual_price > 0) {
            if ($po) $this->_update_item_price($detail->item_id, $po->supplier_id, $actual_price, 'po_receipt', $detail->po_id);
        }

        // Update p_item.stock
        if ($detail->item_id) {
            $this->db->query("UPDATE p_item SET stock = stock + ? WHERE item_id = ?",
                [$qty, (int) $detail->item_id]);

            // Insert t_stock
            $this->db->insert('t_stock', [
                'item_id'      => (int) $detail->item_id,
                'type'         => 'in',
                'supplier_id'  => $po ? (int) $po->supplier_id : null,
                'po_detail_id' => $po_detail_id,
                'qty'          => $qty,
                'date'         => $receipt->invoice_date ?: date('Y-m-d'),
                'detail'       => 'Goods Receipt ' . ($po ? $po->po_number : ''),
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        // Recalculate po_header status
        $all = $this->db->select('qty_ordered, qty_received')->where('po_id', $detail->po_id)->get('po_detail')->result();
        $total_ordered  = array_sum(array_column((array) $all, 'qty_ordered'));
        $total_received = array_sum(array_column((array) $all, 'qty_received'));
        $new_status = $total_received >= $total_ordered ? 'received' : 'partial';
        $this->db->where('po_id', $detail->po_id)->update('po_header', ['status' => $new_status]);

        echo json_encode(['status' => 'success']);
    }

    public function delete_receipt_detail()
    {
        if ($this->input->method() !== 'post') show_404();

        $detail_id  = (int) $this->input->post('detail_id');
        $receipt_id = (int) $this->input->post('receipt_id');

        $detail = $this->db->where('id', $detail_id)->get('po_detail')->row();
        if (!$detail) {
            echo json_encode(['status' => 'error', 'message' => 'Detail tidak ditemukan.']);
            return;
        }

        $old_qty = (int) $detail->qty_received;

        // Jika ada qty yang sudah diterima → reverse stok + hapus entry t_stock pakai po_detail_id
        if ($old_qty > 0 && $detail->item_id) {
            $this->db->query(
                "UPDATE p_item SET stock = stock - ? WHERE item_id = ?",
                [$old_qty, (int) $detail->item_id]
            );
            $this->db->where('po_detail_id', $detail_id)->delete('t_stock');
        }

        // Reset po_detail (lepas dari receipt, qty kembali 0)
        $this->db->where('id', $detail_id)->update('po_detail', [
            'qty_received' => 0,
            'actual_price' => 0,
            'receipt_id'   => null,
        ]);

        // Cek apakah receipt masih punya item dengan qty > 0
        $remaining_qty = $this->db
            ->where('receipt_id', $receipt_id)
            ->where('qty_received >', 0)
            ->count_all_results('po_detail');

        // Juga lepas item dengan qty=0 yang masih linked ke receipt ini
        $this->db->where('receipt_id', $receipt_id)->where('qty_received', 0)
                 ->update('po_detail', ['receipt_id' => null]);

        if ($remaining_qty === 0) {
            // Tidak ada item yang diterima → hapus receipt header
            $this->db->where('receipt_id', $receipt_id)->delete('po_receipt');
            echo json_encode(['status' => 'success', 'receipt_deleted' => true]);
            return;
        }

        // Update status po_header
        $po_id   = $detail->po_id;
        $details = $this->db->select('qty_ordered, qty_received')->where('po_id', $po_id)->get('po_detail')->result();
        $total_ordered  = array_sum(array_column((array) $details, 'qty_ordered'));
        $total_received = array_sum(array_column((array) $details, 'qty_received'));

        if ($total_received <= 0) {
            $new_status = 'sent';
        } elseif ($total_received >= $total_ordered) {
            $new_status = 'received';
        } else {
            $new_status = 'partial';
        }
        $this->db->where('po_id', $po_id)->update('po_header', ['status' => $new_status]);

        echo json_encode(['status' => 'success', 'receipt_deleted' => false]);
    }

    public function receiving_supplier()
    {
        $this->template->load('template', 'purchasing/receiving_supplier');
    }

    public function receiving_supplier_data()
    {
        // Fetch sent/partial POs grouped by supplier
        $rows = $this->db->select('po_header.po_id, po_header.po_number, po_header.po_date,
                                   po_header.expected_date, po_header.status,
                                   supplier.supplier_id, supplier.nama_supplier,
                                   COUNT(po_detail.id) AS total_lines,
                                   SUM(po_detail.qty_ordered) AS total_ordered,
                                   SUM(po_detail.qty_received) AS total_received,
                                   (SELECT MAX(receive_date) FROM po_receipt WHERE po_id = po_header.po_id) AS last_receipt_date', false)
            ->from('po_header')
            ->join('supplier', 'po_header.supplier_id = supplier.supplier_id')
            ->join('po_detail', 'po_detail.po_id = po_header.po_id', 'left')
            ->where_in('po_header.status', ['sent', 'partial'])
            ->group_by('po_header.po_id')
            ->order_by('supplier.nama_supplier', 'ASC')
            ->order_by('po_header.po_id', 'ASC')
            ->get()->result();

        // Group by supplier
        $grouped = [];
        foreach ($rows as $r) {
            $sid = $r->supplier_id;
            if (!isset($grouped[$sid])) {
                $grouped[$sid] = ['nama_supplier' => $r->nama_supplier, 'pos' => []];
            }
            $grouped[$sid]['pos'][] = $r;
        }

        echo json_encode(['grouped' => array_values($grouped)]);
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
            if ($unreceived <= 0 || !$d->item_id) continue;

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
