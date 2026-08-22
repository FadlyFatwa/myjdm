<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Penerimaan extends CI_Controller {

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

    private function _update_item_price($item_id, $supplier_id, $new_price, $source = 'po_receipt', $source_id = null, $harga_list = null, $manual_pk = null)
    {
        $item = $this->db->where('item_id', $item_id)->get('p_item')->row();
        if (!$item || $new_price <= 0) return;

        $new_pk = $this->fungsi->build_pk($new_price, $harga_list, $manual_pk);
        if ((int) $item->modal === $new_price && $new_pk === $item->pk) return; // tidak ada yang berubah

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

        // Harga pembanding untuk log: default-nya harga saat ini (item->modal). Tapi kalau
        // ini KOREKSI dari PO/resi yang sama (source_id sama) yang sebelumnya sudah pernah
        // ubah harga item ini (misal salah input, dikoreksi lagi), pakai harga SEBELUM PO ini
        // pernah menyentuh item ini sama sekali (dari log paling awal) -- supaya naik/turun
        // yang tercatat mencerminkan efek bersih dari seluruh proses penerimaan ini, bukan
        // dibandingkan ke input yang salah dan baru saja dikoreksi.
        $harga_lama_log = (int) $item->modal;
        if ($source_id) {
            $earliest = $this->db
                ->where('item_id', $item_id)
                ->where('sumber', $source)
                ->where('sumber_id', $source_id)
                ->order_by('id', 'ASC')
                ->limit(1)
                ->get('harga_log')->row();
            if ($earliest) {
                $harga_lama_log = (int) $earliest->harga_lama;
            }
        }

        // Log perubahan harga
        $this->po_m->log_price_change([
            'item_id'     => $item_id,
            'supplier_id' => $supplier_id,
            'harga_lama'  => $harga_lama_log,
            'harga_baru'  => $new_price,
            'sumber'      => $source,
            'sumber_id'   => $source_id,
            'catatan'     => 'Koreksi GR',
            'changed_by'  => (int) $this->session->userdata('userid'),
        ]);
    }

    /**
     * Hitung ulang distribusi PPN ke actual_price SEMUA baris resi ini, HANYA
     * kalau ppn_mode='add_distribute'. Dipanggil ulang tiap ada perubahan yang
     * menggeser subtotal (edit baris, tambah ekstra, hapus baris) supaya porsi
     * PPN tiap baris tetap proporsional terhadap subtotal terkini. Basisnya
     * actual_price yang SUDAH tersimpan (bisa jadi sudah termasuk distribusi
     * PPN dari perubahan sebelumnya) -- diterima sebagai trade-off karena staff
     * selalu cross-check "Total Utang" vs nota fisik tiap simpan (1 nota
     * supplier = 1 resi, jadi perbandingannya selalu jelas & langsung).
     * Juga menulis ulang po_receipt.ppn_nominal/total_amount supaya konsisten
     * dengan actual_price baru, tanpa perlu klik "Simpan Pengaturan Invoice"
     * terpisah. $manual_pk_by_detail_id (opsional): [po_detail.id => PK manual]
     * untuk baris yang PK-nya di-override manual oleh user — tetap dihormati
     * meski harganya ikut bergeser karena redistribusi.
     */
    private function _redistribute_ppn($receipt_id, $manual_pk_by_detail_id = [])
    {
        $receipt = $this->db->where('receipt_id', $receipt_id)->get('po_receipt')->row();
        if (!$receipt || $receipt->ppn_mode !== 'add_distribute') return;

        $rows = $this->db->where('receipt_id', $receipt_id)->where('qty_received >', 0)->get('po_detail')->result();
        if (empty($rows)) return;

        $subtotal = 0;
        foreach ($rows as $r) {
            $subtotal += (int) $r->qty_received * (float) $r->actual_price;
        }
        if ($subtotal <= 0) return;

        $subtotal_setelah_diskon = $subtotal - (int) $receipt->diskon_invoice;
        $ppn_nominal = $this->fungsi->hitung_ppn_tambah($subtotal_setelah_diskon);

        $po = $this->db->where('po_id', $receipt->po_id)->get('po_header')->row();
        $new_subtotal = $subtotal;

        if ($ppn_nominal > 0) {
            $count         = count($rows);
            $remaining_ppn = $ppn_nominal;
            $new_subtotal  = 0;
            foreach ($rows as $i => $r) {
                $row_subtotal = (int) $r->qty_received * (float) $r->actual_price;
                if ($i === $count - 1) {
                    $row_ppn_share = $remaining_ppn;
                } else {
                    $row_ppn_share = (int) round($ppn_nominal * ($row_subtotal / $subtotal));
                    $remaining_ppn -= $row_ppn_share;
                }
                $new_price = (int) round($r->actual_price + ($r->qty_received > 0 ? $row_ppn_share / $r->qty_received : 0));
                $manual_pk = $manual_pk_by_detail_id[$r->id] ?? null;

                if ($new_price !== (int) $r->actual_price) {
                    $this->db->where('id', $r->id)->update('po_detail', ['actual_price' => $new_price]);
                }
                if ($r->item_id && $po && ($new_price !== (int) $r->actual_price || $manual_pk)) {
                    $this->_update_item_price($r->item_id, $po->supplier_id, $new_price, 'po_receipt', $receipt->po_id, $r->harga_list, $manual_pk);
                }
                $new_subtotal += (int) $r->qty_received * $new_price;
            }
        }

        // total_amount dari subtotal PASCA redistribusi (actual_price di tiap baris SUDAH
        // termasuk porsi PPN-nya) dikurangi diskon invoice — BUKAN subtotal_setelah_diskon +
        // ppn_nominal, karena itu akan menghitung PPN dua kali (sudah menempel di actual_price).
        $this->db->where('receipt_id', $receipt_id)->update('po_receipt', [
            'ppn_persen'   => 11.00,
            'ppn_nominal'  => $ppn_nominal,
            'total_amount' => (int) round($new_subtotal - (int) $receipt->diskon_invoice),
        ]);
    }

    public function receiving_list()
    {
        $data['suppliers'] = $this->supplier_m->get()->result();
        $this->template->load('template', 'penerimaan/receiving_list', $data);
    }

    public function receiving_json()
    {
        $draw   = (int) $this->input->post('draw');
        $start  = (int) $this->input->post('start');
        $length = (int) $this->input->post('length');
        $search = $_POST['search']['value'] ?? '';

        // qty_ordered=0 menandai baris "ekstra" (di luar rencana PO) — dikeluarkan dari
        // total_ordered/total_received supaya progress bar mencerminkan progress terhadap
        // rencana PO aslinya, bukan ikut membengkak/lebih dari 100% gara-gara barang ekstra.
        $this->db->select('po_header.po_id, po_header.po_number, po_header.po_date, po_header.expected_date,
                           po_header.status, supplier.nama_supplier,
                           COUNT(po_detail.id) AS total_lines,
                           SUM(CASE WHEN po_detail.qty_ordered > 0 THEN po_detail.qty_ordered ELSE 0 END) AS total_ordered,
                           SUM(CASE WHEN po_detail.qty_ordered > 0 THEN po_detail.qty_received ELSE 0 END) AS total_received', false);
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

    public function receive_form($po_id)
    {
        $po = $this->po_m->get_po((int) $po_id);
        if (!$po || !in_array($po->status, ['sent', 'partial'])) {
            $this->session->set_flashdata('error', 'PO tidak tersedia untuk penerimaan.');
            redirect('purchase-order/' . $po_id);
            return;
        }
        $details      = $this->po_m->get_po_detail((int) $po_id);
        $categories   = $this->db->order_by('nama_category')->get('p_category')->result();
        $units        = $this->db->order_by('nama_unit')->get('p_unit')->result();
        $max_barcode  = $this->item_m->get_max_barcode();
        $next_barcode = str_pad((int) $max_barcode + 1, 5, '0', STR_PAD_LEFT);
        $this->template->load('template', 'penerimaan/po_receive', [
            'po'           => $po,
            'details'      => $details,
            'categories'   => $categories,
            'units'        => $units,
            'next_barcode' => $next_barcode,
        ]);
    }

    /**
     * Tambah baris po_detail ad-hoc saat penerimaan (di luar rencana PO awal).
     * qty_ordered=0 menandai baris ini "ekstra" — beda dari add_detail_draft()
     * yang cuma boleh jalan selagi status PO masih draft, endpoint ini boleh
     * jalan selama status PO sent/partial (yaitu, sedang proses diterima).
     */
    public function receive_add_item()
    {
        $po_id  = (int) $this->input->post('po_id');
        $source = $this->input->post('source');

        $po = $this->po_m->get_po($po_id);
        if (!$po || !in_array($po->status, ['sent', 'partial'])) {
            echo json_encode(['status' => 'error', 'message' => 'PO tidak dalam status penerimaan.']);
            exit();
        }

        if ($source === 'existing') {
            $item_id    = (int) $this->input->post('item_id');
            $unit_price = (float) $this->input->post('unit_price');
            $item       = $item_id ? $this->db->where('item_id', $item_id)->get('p_item')->row() : null;

            if (!$item) {
                echo json_encode(['status' => 'error', 'message' => 'Item tidak ditemukan.']);
                exit();
            }

            $this->db->insert('po_detail', [
                'po_id'       => $po_id,
                'item_id'     => $item_id,
                'qty_ordered' => 0,
                'unit_price'  => $unit_price,
            ]);
            $detail_id  = $this->db->insert_id();
            $item_name  = $item->nama_item;
        } elseif ($source === 'new') {
            // Sejalan dengan pola "belum terdaftar" yang sudah ada (po_detail.php +
            // register_temp_item()) — item BELUM dibuat di p_item di sini. Baris ini
            // cuma placeholder (item_id NULL + item_name_temp), sama seperti item
            // yang ditambahkan ke PO draft tanpa item_id. Nanti didaftarkan lewat
            // tombol "Daftarkan" (register_temp_item) setelah qty diterima — satu
            // mekanisme registrasi buat semua item belum terdaftar, apapun asalnya.
            $nama_item = trim($this->input->post('nama_item'));
            $modal     = (int) $this->input->post('modal');

            if (!$nama_item || $modal <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Nama dan harga wajib diisi.']);
                exit();
            }

            $this->db->insert('po_detail', [
                'po_id'          => $po_id,
                'item_id'        => null,
                'item_name_temp' => $nama_item,
                'qty_ordered'    => 0,
                'unit_price'     => $modal,
            ]);
            $detail_id = $this->db->insert_id();
            $item_id   = null;
            $item_name = $nama_item;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sumber item tidak valid.']);
            exit();
        }

        echo json_encode([
            'status'    => 'success',
            'detail_id' => $detail_id,
            'item_id'   => $item_id,
            'item_name' => $item_name,
            'is_temp'   => $item_id === null,
        ]);
        exit();
    }

    /**
     * Mulai penerimaan tanpa PO formal (WA-order dkk). Bikin po_header
     * "shell" dengan status langsung 'sent' (skip draft — secara alami
     * memang sudah terkirim & diterima bersamaan) supaya receive_form()/
     * receive() yang sudah ada bisa langsung dipakai tanpa perubahan guard.
     */
    public function receive_direct_start()
    {
        $supplier_id = (int) $this->input->post('supplier_id');
        $supplier    = $supplier_id ? $this->db->where('supplier_id', $supplier_id)->get('supplier')->row() : null;

        if (!$supplier) {
            $this->session->set_flashdata('error', 'Pilih supplier terlebih dahulu.');
            redirect('purchase-order');
            return;
        }

        $user_id = (int) $this->session->userdata('userid');

        $this->db->insert('po_header', [
            'po_number'    => $this->po_m->po_number(),
            'supplier_id'  => $supplier_id,
            'po_date'      => date('Y-m-d'),
            'status'       => 'sent',
            'is_direct'    => 1,
            'created_by'   => $user_id,
        ]);
        $po_id = $this->db->insert_id();

        if (!$po_id) {
            $this->session->set_flashdata('error', 'Gagal memulai penerimaan langsung.');
            redirect('purchase-order');
            return;
        }

        redirect('purchase-order/receive/' . $po_id);
    }

    public function receive()
    {
        $po_id       = (int) $this->input->post('po_id');
        $po          = $this->po_m->get_po($po_id);
        $user_id     = (int) $this->session->userdata('userid');
        $detail_ids  = $this->input->post('detail_id');
        $qty_arr     = $this->input->post('qty_received');
        $price_arr   = $this->input->post('actual_price');
        $harga_list_arr = (array) ($this->input->post('harga_list') ?: []);
        $diskon_pct_arr = (array) ($this->input->post('diskon_persen') ?: []);

        if (!$po || !in_array($po->status, ['sent', 'partial'])) {
            $this->session->set_flashdata('error', 'PO tidak dapat diproses penerimaan.');
            redirect('purchase-order/' . $po_id);
            return;
        }

        // Cara Bayar & PPN wajib dipilih manual (tidak ada default) supaya user benar-benar
        // mengecek, bukan cuma lolos lewat nilai bawaan yang belum tentu sesuai transaksi ini.
        $payment_type = $this->input->post('payment_type');
        if (!in_array($payment_type, ['cash', 'credit'], true)) {
            $this->session->set_flashdata('error', 'Cara Bayar Barang wajib dipilih (Kredit/Cash).');
            redirect('purchase-order/receive/' . $po_id);
            return;
        }
        $ppn_mode = $this->input->post('ppn_mode');
        if (!in_array($ppn_mode, ['none', 'add_distribute', 'inclusive'], true)) {
            $this->session->set_flashdata('error', 'PPN wajib dipilih salah satu opsinya.');
            redirect('purchase-order/receive/' . $po_id);
            return;
        }

        // Tahap 1 — hitung harga bersih per baris (harga_list-diskon, belum termasuk PPN).
        // Kalau harga_list diisi, actual_price yang dikirim client diabaikan & dihitung ulang
        // di server supaya tidak bisa dimanipulasi dari luar.
        $net_prices       = [];
        $harga_list_by_idx = [];
        $diskon_by_idx     = [];
        $subtotal          = 0;
        foreach ((array) $detail_ids as $idx => $detail_id) {
            $qty_received = (int) ($qty_arr[$idx] ?? 0);
            if ($qty_received <= 0) continue;

            $harga_list = isset($harga_list_arr[$idx]) && $harga_list_arr[$idx] !== ''
                ? (float) str_replace('.', '', $harga_list_arr[$idx]) : null;
            $diskon_pct = isset($diskon_pct_arr[$idx]) && $diskon_pct_arr[$idx] !== ''
                ? (float) $diskon_pct_arr[$idx] : null;

            if ($harga_list !== null && $harga_list > 0) {
                $net_price = $harga_list * (1 - ($diskon_pct ?: 0) / 100);
            } else {
                $net_price = (float) ($price_arr[$idx] ?? 0);
            }

            $net_prices[$idx]        = $net_price;
            $harga_list_by_idx[$idx] = $harga_list;
            $diskon_by_idx[$idx]     = $diskon_pct;
            $subtotal += $qty_received * $net_price;
        }

        // Tahap 2 — hitung invoice: diskon nominal + PPN opsional, 3 mode saling eksklusif
        // (kebijakan tiap supplier beda-beda soal PPN):
        //   'none'           — tidak ada PPN sama sekali.
        //   'add_distribute' — harga yang diketik BELUM termasuk PPN; PPN dihitung dari
        //                      subtotal lalu ditambahkan ke total DAN didistribusi naik ke
        //                      harga beli tiap item (Tahap 3).
        //   'inclusive'      — harga yang diketik SUDAH termasuk PPN; PPN diekstrak dari
        //                      subtotal cuma buat catatan, harga beli & total TIDAK berubah.
        $diskon_invoice = (int) str_replace('.', '', $this->input->post('diskon_invoice'));

        $subtotal_setelah_diskon = $subtotal - $diskon_invoice;

        if ($ppn_mode === 'add_distribute') {
            $ppn_nominal  = $this->fungsi->hitung_ppn_tambah($subtotal_setelah_diskon);
            $ppn_persen   = 11.00;
            $total_amount = (int) round($subtotal_setelah_diskon + $ppn_nominal);
        } elseif ($ppn_mode === 'inclusive') {
            $ppn_nominal  = $this->fungsi->hitung_ppn_ekstrak($subtotal_setelah_diskon);
            $ppn_persen   = 11.00;
            $total_amount = (int) round($subtotal_setelah_diskon); // sudah termasuk, tidak nambah
        } else {
            $ppn_nominal  = 0;
            $ppn_persen   = null;
            $total_amount = (int) round($subtotal_setelah_diskon);
        }

        // Tahap 3 — distribusi PPN ke harga beli tiap baris HANYA untuk mode add_distribute.
        // Mode 'inclusive': harga beli memang sudah termasuk PPN sejak diketik, tidak diubah.
        // Mode 'none': tidak ada PPN untuk didistribusi.
        $actual_prices_final = $net_prices;
        if ($ppn_mode === 'add_distribute' && $subtotal > 0 && $ppn_nominal > 0) {
            $idx_list      = array_keys($net_prices);
            $count         = count($idx_list);
            $remaining_ppn = $ppn_nominal;
            foreach ($idx_list as $i => $idx) {
                $qty_received = (int) ($qty_arr[$idx] ?? 0);
                $row_subtotal = $qty_received * $net_prices[$idx];
                if ($i === $count - 1) {
                    $row_ppn_share = $remaining_ppn; // baris terakhir serap sisa pembulatan
                } else {
                    $row_ppn_share = (int) round($ppn_nominal * ($row_subtotal / $subtotal));
                    $remaining_ppn -= $row_ppn_share;
                }
                $actual_prices_final[$idx] = $net_prices[$idx] + ($qty_received > 0 ? $row_ppn_share / $qty_received : 0);
            }
        }

        // Simpan data penerimaan ke po_receipt
        $supplier_invoice_no  = $this->input->post('supplier_invoice_no') ?: null;
        $invoice_date         = $this->input->post('invoice_date') ?: null;
        $receive_date         = $this->input->post('receive_date') ?: date('Y-m-d');
        $ongkir = (int) str_replace('.', '', $this->input->post('ongkir'));

        $this->db->trans_start();

        $this->db->insert('po_receipt', [
            'po_id'                 => $po_id,
            'supplier_invoice_no'   => $supplier_invoice_no,
            'invoice_date'          => $invoice_date,
            'receive_date'          => $receive_date,
            'received_by'           => $user_id,
            'ongkir'                => $ongkir,
            'ongkir_payment_method' => $ongkir > 0 ? 'cash' : null,
            'diskon_invoice'        => $diskon_invoice,
            'ppn_mode'              => $ppn_mode,
            'ppn_persen'            => $ppn_persen,
            'ppn_nominal'           => $ppn_nominal,
            'total_amount'          => $total_amount,
        ]);
        $receipt_id = $this->db->insert_id();

        if (!$receipt_id) {
            // Jangan lanjut catat ongkir/stok kalau po_receipt sendiri gagal
            // disimpan -- nanti stok & jurnal ongkir jalan tanpa riwayat penerimaannya.
            $this->db->trans_complete();
            $this->session->set_flashdata('error', 'Penerimaan barang gagal disimpan, silakan coba lagi.');
            redirect('purchase-order/' . $po_id);
            return;
        }

        // Hutang ke supplier — dicatat untuk SEMUA penerimaan (kredit maupun cash) supaya
        // konsisten muncul di Kartu Hutang; kalau cash, langsung dilunasi penuh saat itu
        // juga lewat Ap_invoice_m::create_from_receipt() (lihat method tsb). $payment_type
        // sudah divalidasi & pasti 'cash'/'credit' di awal method.
        if ($total_amount > 0) {
            $this->load->model('Ap_invoice_m');
            $receipt_row = (object) [
                'receipt_id'   => $receipt_id,
                'po_id'        => $po_id,
                'receive_date' => $receive_date,
                'total_amount' => $total_amount,
            ];
            $this->Ap_invoice_m->create_from_receipt($receipt_row, $payment_type, $user_id);
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
            if ($qty_received <= 0) continue;

            $actual_price = (int) round($actual_prices_final[$idx] ?? 0);
            $new_pk       = strtoupper(trim($pk_arr[$idx] ?? ''));

            $detail = $this->db->where('id', (int) $detail_id)->get('po_detail')->row();
            if (!$detail) continue;

            if ($harga_list_by_idx[$idx] !== null) {
                $this->db->where('id', (int) $detail_id)->update('po_detail', [
                    'harga_list'    => $harga_list_by_idx[$idx],
                    'diskon_persen' => $diskon_by_idx[$idx],
                ]);
            }

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
                        // JS di frontend selalu auto-isi field PK sebagai preview (dari harga
                        // SEBELUM distribusi PPN) begitu halaman dimuat / harga diketik — jadi
                        // $new_pk hampir selalu terisi meski user tidak sengaja override manual.
                        // Bandingkan dengan preview yang sama (dihitung dari net_price, pre-PPN):
                        // kalau sama persis, berarti itu cuma auto-fill JS, BUKAN override asli —
                        // abaikan & hitung ulang PK dari actual_price final (pasca distribusi PPN).
                        $preview_pk  = $this->fungsi->build_pk($net_prices[$idx] ?? 0, $harga_list_by_idx[$idx], null);
                        $is_override = $new_pk !== '' && $new_pk !== $preview_pk;

                        $pk_final = $this->fungsi->build_pk($actual_price, $harga_list_by_idx[$idx], $is_override ? $new_pk : null);
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

        // Catatan: notifikasi WA ke grup TIDAK dikirim di sini. Pesan baru dikirim
        // setelah staff menyelesaikan cetak & tempel label (lihat mark_labeled()),
        // supaya isi pesan sesuai kondisi nyata barang (siap disimpan).

        // Pengaman: baris ekstra yang item-nya baru didaftarkan lewat receive_add_item()
        // (source=new) tidak lewat receive_detail() untuk pembuatan itemnya, jadi status
        // PO dihitung ulang sekali lagi di sini supaya tetap akurat.
        $this->po_m->recalc_status($po_id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Penerimaan barang gagal disimpan, silakan coba lagi.');
            redirect('purchase-order/receive/' . $po_id);
            return;
        }

        $this->session->set_flashdata('success', 'Penerimaan barang berhasil dicatat.');
        redirect('purchase-order/history/' . $receipt_id);
    }

    /**
     * Format pesan notifikasi WA untuk penerimaan barang (dikirim setelah selesai dilabeli)
     */
    private function _build_receive_wa_message(string $po_number, string $nama_supplier, string $nama_penerima, array $item_lines, $invoice_date, $receive_date): string
    {
        $lines = [
            "✅ *PENERIMAAN BARANG*",
            "━━━━━━━━━━━━━━",
            "📋 No. PO       : {$po_number}",
            "🏭 Supplier     : {$nama_supplier}",
            "🧾 Tgl Invoice  : " . ($invoice_date ? indo_date($invoice_date) : '-'),
            "📅 Tgl Terima   : " . indo_date($receive_date),
            "👤 Diterima     : {$nama_penerima}",
            "━━━━━━━━━━━━━━",
            "📦 *Detail Item*",
        ];

        foreach ($item_lines as $i => $line) {
            $lines[] = ($i + 1) . '. ' . $line;
        }

        $lines[] = "━━━━━━━━━━━━━━";
        $lines[] = "_Harap Segera disimpan Terima kasih_";
        $lines[] = "_Notifikasi otomatis dari sistem myjdm_";

        return implode("\n", $lines);
    }

    /**
     * Ambil po_number & nama_supplier untuk keperluan pesan WA
     */
    private function _po_supplier_info($po_id)
    {
        return $this->db->select('po_header.po_number, supplier.nama_supplier', false)
            ->from('po_header')
            ->join('supplier', 'po_header.supplier_id = supplier.supplier_id', 'left')
            ->where('po_header.po_id', (int) $po_id)
            ->get()->row();
    }

    /**
     * Kirim pesan WA ke grup, non-blocking (gagal hanya dicatat di log)
     */
    private function _send_wa_notif(string $message, string $log_context): void
    {
        $wa_result = $this->whatsapp->send_to_group($message);
        if (!$wa_result['success']) {
            log_message('error', "[Penerimaan] WA notifikasi gagal terkirim ({$log_context}).");
        }
    }

    public function receiving_history()
    {
        $this->template->load('template', 'penerimaan/receiving_history');
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
                               po_receipt.created_at, po_receipt.label_status,
                               po_header.po_id, po_header.po_number, po_header.is_direct,
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
            $is_empty = ((int) $row->total_qty === 0);

            if ($is_empty) {
                $status_label = '<span class="label label-default"><i class="fa fa-ban"></i> Kosong</span>';
            } elseif ($row->label_status === 'labeled') {
                $status_label = '<span class="label label-success"><i class="fa fa-check-circle"></i> Sudah Dilabeli</span>';
            } else {
                $status_label = '<span class="label label-warning"><i class="fa fa-clock-o"></i> Belum Dilabeli</span>';
            }

            $result[] = [
                'no'                  => $start + $i + 1,
                'receive_date'        => indo_date($row->receive_date),
                'invoice_date'        => $row->invoice_date ? indo_date($row->invoice_date) : '<span class="text-muted">—</span>',
                'supplier_invoice_no' => $row->supplier_invoice_no
                    ? '<span class="label label-default" style="font-size:11px">' . htmlspecialchars($row->supplier_invoice_no) . '</span>'
                    : '<span class="text-muted">—</span>',
                'po_number'           => '<a href="' . site_url('purchase-order/' . $row->po_id) . '">' . htmlspecialchars($row->po_number) . '</a>'
                                       . ((int) $row->is_direct ? ' <span class="label label-success" style="font-size:9px" title="Diterima langsung tanpa PO formal">Langsung</span>' : ''),
                'nama_supplier'       => htmlspecialchars($row->nama_supplier),
                'total_lines'         => (int) $row->total_lines . ' item',
                'total_qty'           => (int) $row->total_qty . ' pcs',
                'received_by_name'    => htmlspecialchars($row->received_by_name ?? '—'),
                'status_label'        => $status_label,
                'is_empty'            => $is_empty,
                'receipt_id'          => $row->receipt_id,
                'action'              => '<a href="' . site_url('purchase-order/history/' . $row->receipt_id) . '" class="btn btn-primary btn-xs" title="Lihat detail">'
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
            ->select('po_receipt.*, po_header.po_number, po_header.po_id, po_header.is_direct,
                      supplier.supplier_id, supplier.nama_supplier, user.nama AS received_by_name,
                      labeler.nama AS labeled_by_name', false)
            ->from('po_receipt')
            ->join('po_header', 'po_receipt.po_id = po_header.po_id')
            ->join('supplier', 'po_header.supplier_id = supplier.supplier_id')
            ->join('user', 'po_receipt.received_by = user.user_id', 'left')
            ->join('user AS labeler', 'po_receipt.labeled_by = labeler.user_id', 'left')
            ->get()->row();

        if (!$receipt) {
            $this->session->set_flashdata('error', 'Data penerimaan tidak ditemukan.');
            redirect('purchase-order/history');
            return;
        }

        // stock_id (t_stock) untuk item receipt ini — dipakai untuk shortcut print barcode
        $stock_rows = $this->db->select('t_stock.stock_id', false)
            ->from('t_stock')
            ->join('po_detail', 't_stock.po_detail_id = po_detail.id')
            ->where('po_detail.receipt_id', (int) $receipt_id)
            ->where('t_stock.type', 'in')
            ->get()->result();
        $stock_ids = array_column($stock_rows, 'stock_id');

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

        $categories   = $this->db->order_by('nama_category')->get('p_category')->result();
        $units        = $this->db->order_by('nama_unit')->get('p_unit')->result();
        $max_barcode  = $this->item_m->get_max_barcode();
        $next_barcode = str_pad((int) $max_barcode + 1, 5, '0', STR_PAD_LEFT);

        $this->template->load('template', 'penerimaan/receiving_history_detail', [
            'receipt'         => $receipt,
            'items'           => $items,
            'available_items' => $available_items,
            'stock_ids'       => $stock_ids,
            'categories'      => $categories,
            'units'           => $units,
            'next_barcode'    => $next_barcode,
        ]);
    }

    /**
     * Form "Edit Penerimaan" — po_receive.php dalam mode edit. Query identik dengan
     * receiving_history_detail() (sama-sama menampilkan isi resi ini), cuma dirender
     * lewat view yang sama dengan mode terima baru (penerimaan/po_receive) supaya
     * seluruh aksi koreksi (simpan baris, barang ekstra, invoice, ongkir, daftarkan)
     * dapat ruang penuh alih-alih ditempel inline di halaman ringkasan.
     */
    public function edit_receipt_form($receipt_id)
    {
        $receipt = $this->db->where('po_receipt.receipt_id', (int) $receipt_id)
            ->select('po_receipt.*, po_header.po_number, po_header.po_id, po_header.is_direct,
                      supplier.supplier_id, supplier.nama_supplier, user.nama AS received_by_name,
                      labeler.nama AS labeled_by_name', false)
            ->from('po_receipt')
            ->join('po_header', 'po_receipt.po_id = po_header.po_id')
            ->join('supplier', 'po_header.supplier_id = supplier.supplier_id')
            ->join('user', 'po_receipt.received_by = user.user_id', 'left')
            ->join('user AS labeler', 'po_receipt.labeled_by = labeler.user_id', 'left')
            ->get()->row();

        if (!$receipt) {
            $this->session->set_flashdata('error', 'Data penerimaan tidak ditemukan.');
            redirect('purchase-order/history');
            return;
        }

        $po = $this->po_m->get_po((int) $receipt->po_id);

        $stock_rows = $this->db->select('t_stock.stock_id', false)
            ->from('t_stock')
            ->join('po_detail', 't_stock.po_detail_id = po_detail.id')
            ->where('po_detail.receipt_id', (int) $receipt_id)
            ->where('t_stock.type', 'in')
            ->get()->result();
        $stock_ids = array_column($stock_rows, 'stock_id');

        $items = $this->db->select('po_detail.*, p_item.nama_item, p_item.barcode, p_item.pk AS item_pk, p_unit.nama_unit', false)
            ->from('po_detail')
            ->join('p_item', 'po_detail.item_id = p_item.item_id', 'left')
            ->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left')
            ->where('po_detail.receipt_id', (int) $receipt_id)
            ->get()->result();

        // Kalau mode PPN resi ini saat ini "ditambah & didistribusi", actual_price yang
        // tersimpan SUDAH termasuk porsi PPN dari redistribusi terakhir. Supaya form Edit
        // tidak membingungkan (dan supaya edit berulang tidak menumpuk PPN di atas PPN),
        // tampilkan & jadikan titik-mula edit adalah harga BERSIH sebelum PPN — diekstrak
        // pakai formula yang sama dengan mode 'inclusive' (11/12 × 12%) — bukan harga
        // mentah yang tersimpan. PK awal ikut dihitung ulang dari harga bersih ini supaya
        // konsisten dengan harga yang ditampilkan.
        if ($receipt->ppn_mode === 'add_distribute') {
            foreach ($items as $it) {
                $price = (int) $it->actual_price;
                if ($price > 0) {
                    $net_price = $price - $this->fungsi->hitung_ppn_ekstrak($price);
                    $it->actual_price = $net_price;
                    if ($it->item_id) {
                        $it->item_pk = $this->fungsi->build_pk($net_price, $it->harga_list);
                    }
                }
            }
        }

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

        $categories   = $this->db->order_by('nama_category')->get('p_category')->result();
        $units        = $this->db->order_by('nama_unit')->get('p_unit')->result();
        $max_barcode  = $this->item_m->get_max_barcode();
        $next_barcode = str_pad((int) $max_barcode + 1, 5, '0', STR_PAD_LEFT);

        $this->template->load('template', 'penerimaan/po_receive', [
            'po'              => $po,
            'receipt'         => $receipt,
            'items'           => $items,
            'available_items' => $available_items,
            'stock_ids'       => $stock_ids,
            'categories'      => $categories,
            'units'           => $units,
            'next_barcode'    => $next_barcode,
        ]);
    }

    public function mark_labeled($receipt_id)
    {
        if ($this->input->method() !== 'post') show_404();

        $receipt_id = (int) $receipt_id;
        $user_id    = (int) $this->session->userdata('userid');

        $receipt = $this->db->where('po_receipt.receipt_id', $receipt_id)
            ->select('po_receipt.*, po_header.po_number, supplier.nama_supplier', false)
            ->from('po_receipt')
            ->join('po_header', 'po_receipt.po_id = po_header.po_id')
            ->join('supplier', 'po_header.supplier_id = supplier.supplier_id')
            ->get()->row();

        if (!$receipt) {
            echo json_encode(['status' => 'error', 'message' => 'Data penerimaan tidak ditemukan.']);
            return;
        }

        if ($receipt->label_status === 'labeled') {
            echo json_encode(['status' => 'success', 'already_labeled' => true]);
            return;
        }

        $items = $this->db->select('po_detail.qty_received, p_item.nama_item, p_item.barcode, po_detail.item_name_temp, p_unit.nama_unit', false)
            ->from('po_detail')
            ->join('p_item', 'po_detail.item_id = p_item.item_id', 'left')
            ->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left')
            ->where('po_detail.receipt_id', $receipt_id)
            ->where('po_detail.qty_received >', 0)
            ->get()->result();

        $item_lines = [];
        foreach ($items as $it) {
            $item_lines[] = ($it->nama_item ?? $it->item_name_temp ?? 'Item')
                    . ' (' . ($it->barcode ?: '-') . ')'
                . ' - ' . $it->qty_received . ' ' . ($it->nama_unit ?? '');
        }

        $receiver = $this->db->where('user_id', $user_id)->get('user')->row();

        $pesan = $this->_build_receive_wa_message(
            $receipt->po_number,
            $receipt->nama_supplier,
            $receiver->nama ?? '-',
            $item_lines,
            $receipt->invoice_date,
            $receipt->receive_date
        );
        $this->_send_wa_notif($pesan, 'selesai dilabeli receipt_id ' . $receipt_id);

        $this->db->where('receipt_id', $receipt_id)->update('po_receipt', [
            'label_status' => 'labeled',
            'labeled_at'   => date('Y-m-d H:i:s'),
            'labeled_by'   => $user_id,
        ]);

        echo json_encode(['status' => 'success', 'already_labeled' => false]);
    }

    /**
     * Simpan qty per baris — TIDAK menyentuh harga (harga_list/diskon_persen/
     * actual_price/PK ditangani terpisah, ditahan dulu di browser sampai
     * "Simpan Semua Perubahan Harga" diklik, lihat update_receipt_prices()).
     * Qty sengaja tetap langsung tersimpan per baris (real-time) karena
     * langsung mempengaruhi stok — beda dari harga yang tidak butuh real-time.
     */
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

        // Ambil semua detail receipt ini (join nama item untuk pesan WA)
        $details = $this->db->select('po_detail.*, p_item.nama_item, p_unit.nama_unit', false)
            ->from('po_detail')
            ->join('p_item', 'po_detail.item_id = p_item.item_id', 'left')
            ->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left')
            ->where('po_detail.receipt_id', $receipt_id)
            ->get()->result();

        $wa_item_lines = [];
        foreach ($details as $d) {
            if ($d->qty_received > 0) {
                $wa_item_lines[] = ($d->nama_item ?? $d->item_name_temp ?? 'Item')
                    . ' - ' . $d->qty_received . ' ' . ($d->nama_unit ?? '');
            }
        }

        $this->db->trans_start();

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

        // Batalkan hutangnya kalau belum ada pembayaran — HARUS sebelum po_receipt
        // dihapus (FK ap_invoice.receipt_id ON DELETE SET NULL berarti receipt_id
        // sudah tidak bisa dipakai mencarinya lagi setelah baris ini dihapus). Kalau
        // sudah ada pembayaran, void() akan menolak (Exception) dan sengaja dibiarkan
        // (harus diselesaikan manual lewat menu Hutang, resi fisiknya tetap terhapus).
        $this->load->model('Ap_invoice_m');
        $ap = $this->Ap_invoice_m->get_by_receipt($receipt_id);
        if ($ap && $ap->status !== 'void') {
            try {
                $this->Ap_invoice_m->void($ap->ap_invoice_id, 'Penerimaan dibatalkan', $this->fungsi->user_login()->user_id);
            } catch (Exception $e) {
                log_message('error', '[Penerimaan] Gagal void ap_invoice saat delete_receipt: ' . $e->getMessage());
            }
        }

        // Hapus receipt header
        $this->db->where('receipt_id', $receipt_id)->delete('po_receipt');

        // Recalculate po_header status
        $remaining = $this->db->select('qty_ordered, qty_received')->where('po_id', $po_id)->get('po_detail')->result();
        $total_received = array_sum(array_column((array) $remaining, 'qty_received'));

        $new_status = $total_received > 0 ? 'partial' : 'sent';
        $this->db->where('po_id', $po_id)->update('po_header', ['status' => $new_status]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal membatalkan penerimaan, silakan coba lagi.']);
            return;
        }

        $po_info = $this->_po_supplier_info($po_id);
        $actor   = $this->fungsi->user_login()->nama ?? '-';

        $lines = [
            "❌ *PEMBATALAN PENERIMAAN*",
            "━━━━━━━━━━━━━━",
            "📋 No. PO      : " . ($po_info->po_number ?? '-'),
            "🏭 Supplier    : " . ($po_info->nama_supplier ?? '-'),
            "🧾 No. Invoice : " . ($receipt->supplier_invoice_no ?: '-'),
            "━━━━━━━━━━━━━━",
            "📦 *Item yang Dibatalkan*",
        ];
        foreach ($wa_item_lines as $i => $line) {
            $lines[] = ($i + 1) . '. ' . $line;
        }
        $lines[] = "━━━━━━━━━━━━━━";
        $lines[] = "👤 Dibatalkan : {$actor}";
        $lines[] = "🕐 Waktu      : " . date('d/m/Y H:i');
        $lines[] = "_Notifikasi otomatis dari sistem myjdm_";

        $this->_send_wa_notif(implode("\n", $lines), 'pembatalan receipt_id ' . $receipt_id);

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

        $this->db->trans_start();

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

        $this->_redistribute_ppn($receipt_id);
        $this->load->model('Ap_invoice_m');
        $ap_synced = $this->Ap_invoice_m->sync_amount_from_receipt($receipt_id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambah item, silakan coba lagi.']);
            return;
        }

        $item_row = $detail->item_id ? $this->db->where('item_id', $detail->item_id)->get('p_item')->row() : null;
        $unit_row = $item_row && $item_row->unit_id ? $this->db->where('unit_id', $item_row->unit_id)->get('p_unit')->row() : null;
        $item_nm  = ($item_row->nama_item ?? null) ?? $detail->item_name_temp ?? 'Item';
        $actor    = $this->fungsi->user_login()->nama ?? '-';
        $po_info  = $this->_po_supplier_info($detail->po_id);

        $lines = [
            "➕ *TAMBAH ITEM PENERIMAAN*",
            "━━━━━━━━━━━━━━",
            "📋 No. PO   : " . ($po_info->po_number ?? '-'),
            "🏭 Supplier : " . ($po_info->nama_supplier ?? '-'),
            "📦 Item     : {$item_nm} - {$qty} " . ($unit_row->nama_unit ?? ''),
            "👤 Ditambah : {$actor}",
            "🕐 Waktu    : " . date('d/m/Y H:i'),
            "━━━━━━━━━━━━━━",
            "_Notifikasi otomatis dari sistem myjdm_",
        ];
        $this->_send_wa_notif(implode("\n", $lines), 'tambah item receipt_id ' . $receipt_id);

        echo json_encode(['status' => 'success', 'ap_synced' => $ap_synced]);
    }

    /**
     * "Barang Ekstra" versi Koreksi Data — resi sudah final, jadi beda dari
     * receive_add_item() (yang cuma staging buat form penerimaan): di sini
     * langsung efektif (stok kekredit / baris belum-terdaftar langsung
     * ke-link ke resi ini), reuse Po_m::receive_detail() yang sudah ada.
     */
    public function add_receipt_extra_item()
    {
        if ($this->input->method() !== 'post') show_404();

        $receipt_id = (int) $this->input->post('receipt_id');
        $source     = $this->input->post('source');
        $qty        = (int) $this->input->post('qty');

        $receipt = $this->db->where('receipt_id', $receipt_id)->get('po_receipt')->row();
        if (!$receipt || $qty < 1) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak valid.']);
            return;
        }
        $po = $this->db->where('po_id', $receipt->po_id)->get('po_header')->row();
        if (!$po) {
            echo json_encode(['status' => 'error', 'message' => 'PO tidak ditemukan.']);
            return;
        }
        if (!in_array($source, ['existing', 'new'], true)) {
            echo json_encode(['status' => 'error', 'message' => 'Sumber item tidak valid.']);
            return;
        }

        $harga_list_in = $this->input->post('harga_list');
        $diskon_in     = $this->input->post('diskon_persen');
        $harga_list    = ($harga_list_in !== null && $harga_list_in !== '') ? (float) str_replace('.', '', $harga_list_in) : null;
        $diskon_persen = ($diskon_in !== null && $diskon_in !== '') ? (float) $diskon_in : null;

        $this->db->trans_start();

        if ($source === 'existing') {
            $item_id = (int) $this->input->post('item_id');
            $item    = $item_id ? $this->db->where('item_id', $item_id)->get('p_item')->row() : null;
            if (!$item) {
                echo json_encode(['status' => 'error', 'message' => 'Item tidak ditemukan.']);
                return;
            }

            $manual_price = (int) str_replace('.', '', $this->input->post('actual_price'));
            $net_price    = ($harga_list !== null && $harga_list > 0)
                ? (int) round($harga_list * (1 - ($diskon_persen ?: 0) / 100))
                : $manual_price;
            if ($net_price <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Harga harus lebih dari 0.']);
                return;
            }

            $this->db->insert('po_detail', [
                'po_id'         => $po->po_id,
                'item_id'       => $item_id,
                'qty_ordered'   => 0,
                'unit_price'    => $net_price,
                'harga_list'    => $harga_list,
                'diskon_persen' => $diskon_persen,
            ]);
            $detail_id = $this->db->insert_id();

            // Reuse penuh: stok kekredit, receipt_id ke-link, status PO recalc.
            $this->po_m->receive_detail(
                $po->po_id, $detail_id, $qty, $net_price,
                $po->supplier_id, $po->po_number, $receipt->invoice_date, $receipt_id
            );

            $this->_update_item_price($item_id, $po->supplier_id, $net_price, 'po_receipt', $po->po_id, $harga_list);

            $item_nm  = $item->nama_item;
            $unit_row = $item->unit_id ? $this->db->where('unit_id', $item->unit_id)->get('p_unit')->row() : null;
            $unit_nm  = $unit_row->nama_unit ?? '';
        } elseif ($source === 'new') {
            $nama_item = trim($this->input->post('nama_item'));
            $modal     = (int) str_replace('.', '', $this->input->post('modal'));
            $net_price = ($harga_list !== null && $harga_list > 0)
                ? (int) round($harga_list * (1 - ($diskon_persen ?: 0) / 100))
                : $modal;

            if (!$nama_item || $net_price <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Nama dan harga wajib diisi.']);
                return;
            }

            // Belum terdaftar — sejalan dengan pola yang sudah ada (item_name_temp),
            // langsung di-link ke resi ini. Stok baru kekredit setelah didaftarkan
            // lewat tombol "Daftarkan" yang sudah ada di halaman ini.
            $this->db->insert('po_detail', [
                'po_id'          => $po->po_id,
                'item_id'        => null,
                'item_name_temp' => $nama_item,
                'qty_ordered'    => 0,
                'qty_received'   => $qty,
                'actual_price'   => $net_price,
                'unit_price'     => $net_price,
                'harga_list'     => $harga_list,
                'diskon_persen'  => $diskon_persen,
                'receipt_id'     => $receipt_id,
            ]);

            $item_nm = $nama_item;
            $unit_nm = '';
        }

        $this->_redistribute_ppn($receipt_id);
        $this->load->model('Ap_invoice_m');
        $ap_synced = $this->Ap_invoice_m->sync_amount_from_receipt($receipt_id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambah barang, silakan coba lagi.']);
            return;
        }

        $actor   = $this->fungsi->user_login()->nama ?? '-';
        $po_info = $this->_po_supplier_info($po->po_id);
        $lines = [
            "➕ *TAMBAH BARANG EKSTRA (KOREKSI)*",
            "━━━━━━━━━━━━━━",
            "📋 No. PO   : " . ($po_info->po_number ?? '-'),
            "🏭 Supplier : " . ($po_info->nama_supplier ?? '-'),
            "📦 Item     : {$item_nm} - {$qty} {$unit_nm}",
            "👤 Ditambah : {$actor}",
            "🕐 Waktu    : " . date('d/m/Y H:i'),
            "━━━━━━━━━━━━━━",
            "_Notifikasi otomatis dari sistem myjdm_",
        ];
        $this->_send_wa_notif(implode("\n", $lines), 'tambah barang ekstra receipt_id ' . $receipt_id);

        echo json_encode(['status' => 'success', 'ap_synced' => $ap_synced]);
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

        $old_qty  = (int) $detail->qty_received;
        $item_row = $detail->item_id ? $this->db->where('item_id', $detail->item_id)->get('p_item')->row() : null;
        $unit_row = $item_row && $item_row->unit_id ? $this->db->where('unit_id', $item_row->unit_id)->get('p_unit')->row() : null;
        $item_nm  = ($item_row->nama_item ?? null) ?? $detail->item_name_temp ?? 'Item';
        $actor    = $this->fungsi->user_login()->nama ?? '-';
        $po_info  = $this->_po_supplier_info($detail->po_id);

        $this->db->trans_start();

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
            // Batalkan hutangnya kalau belum ada pembayaran — HARUS sebelum po_receipt
            // dihapus (FK ap_invoice.receipt_id ON DELETE SET NULL berarti receipt_id
            // sudah tidak bisa dipakai mencarinya lagi setelah baris ini dihapus).
            $this->load->model('Ap_invoice_m');
            $ap_to_void = $this->Ap_invoice_m->get_by_receipt($receipt_id);
            if ($ap_to_void && $ap_to_void->status !== 'void') {
                try {
                    $this->Ap_invoice_m->void($ap_to_void->ap_invoice_id, 'Semua item penerimaan dihapus, resi kosong dibatalkan', $this->fungsi->user_login()->user_id);
                } catch (Exception $e) {
                    log_message('error', '[Penerimaan] Gagal void ap_invoice saat delete_receipt_detail: ' . $e->getMessage());
                }
            }

            // Tidak ada item yang diterima → hapus receipt header
            $this->db->where('receipt_id', $receipt_id)->delete('po_receipt');

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus item, silakan coba lagi.']);
                return;
            }

            $lines = [
                "❌ *PEMBATALAN PENERIMAAN*",
                "━━━━━━━━━━━━━━",
                "📋 No. PO   : " . ($po_info->po_number ?? '-'),
                "🏭 Supplier : " . ($po_info->nama_supplier ?? '-'),
                "📦 Item     : {$item_nm} - {$old_qty} " . ($unit_row->nama_unit ?? ''),
                "ℹ️ Penerimaan ini kosong setelah item terakhir dihapus, seluruh receipt dibatalkan.",
                "👤 Dibatalkan : {$actor}",
                "🕐 Waktu      : " . date('d/m/Y H:i'),
                "━━━━━━━━━━━━━━",
                "_Notifikasi otomatis dari sistem myjdm_",
            ];
            $this->_send_wa_notif(implode("\n", $lines), 'auto-cancel receipt_id ' . $receipt_id);

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

        $this->_redistribute_ppn($receipt_id);
        $this->load->model('Ap_invoice_m');
        $ap_synced = $this->Ap_invoice_m->sync_amount_from_receipt($receipt_id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus item, silakan coba lagi.']);
            return;
        }

        $lines = [
            "🗑️ *HAPUS ITEM PENERIMAAN*",
            "━━━━━━━━━━━━━━",
            "📋 No. PO   : " . ($po_info->po_number ?? '-'),
            "🏭 Supplier : " . ($po_info->nama_supplier ?? '-'),
            "📦 Item     : {$item_nm} - {$old_qty} " . ($unit_row->nama_unit ?? ''),
            "👤 Dihapus  : {$actor}",
            "🕐 Waktu    : " . date('d/m/Y H:i'),
            "━━━━━━━━━━━━━━",
            "_Notifikasi otomatis dari sistem myjdm_",
        ];
        $this->_send_wa_notif(implode("\n", $lines), 'hapus item detail_id ' . $detail_id);

        echo json_encode(['status' => 'success', 'receipt_deleted' => false, 'ap_synced' => $ap_synced]);
    }

    /**
     * Edit Diskon Invoice & mode PPN level resi (Koreksi Data) — level 1 saja
     * karena menyentuh angka tagihan ke supplier. Sengaja TIDAK menulis ulang
     * actual_price/p_item.modal item manapun (lihat catatan di rencana):
     * mendistribusi ulang PPN ke harga beli tiap kali dikoreksi berisiko
     * dobel hitung karena tidak semua baris py harga_list sebagai acuan bersih.
     */
    /**
     * Diskon Invoice, Ongkir, dan Mode PPN disimpan bareng dalam SATU transaksi —
     * level 1 saja. Kalau salah satu bagian gagal (mis. sync ap_invoice), semuanya
     * di-rollback, tidak ada yang tersimpan sebagian. Ongkir lama di-void (bukan
     * diedit langsung) lalu dibuat entry Beban baru, konsisten dengan pola
     * void+recreate yang sudah dipakai di tempat lain (Journal, harga_log).
     */
    /**
     * Simpan SEMUA perubahan harga sekaligus — harga per baris (+ harga list/
     * diskon%/PK manual), Diskon Invoice, Ongkir, dan Mode PPN — level 1 saja.
     * Semuanya ditahan di browser sampai tombol "Simpan Semua Perubahan Harga"
     * diklik, baru diproses bareng di sini dalam SATU transaksi supaya
     * redistribusi PPN dihitung sekali dari seluruh perubahan, bukan
     * bertahap per baris (lihat diskusi soal staging harga vs qty).
     */
    public function update_receipt_prices()
    {
        if ($this->input->method() !== 'post') show_404();

        $level = (int) $this->fungsi->user_login()->level;
        if (!in_array($level, [1, 2], true)) {
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
            return;
        }
        $is_level1 = $level === 1;

        $receipt_id = (int) $this->input->post('receipt_id');
        $user_id    = (int) $this->session->userdata('userid');

        $detail_ids     = (array) $this->input->post('detail_id');
        $qty_arr        = (array) $this->input->post('qty_received');
        $price_arr      = (array) $this->input->post('actual_price');
        $harga_list_arr = (array) $this->input->post('harga_list');
        $diskon_arr     = (array) $this->input->post('diskon_persen');
        $pk_arr         = (array) $this->input->post('pk_new');

        $receipt = $this->db->where('receipt_id', $receipt_id)->get('po_receipt')->row();
        if (!$receipt) {
            echo json_encode(['status' => 'error', 'message' => 'Data penerimaan tidak ditemukan.']);
            return;
        }

        $po      = $this->db->where('po_id', $receipt->po_id)->get('po_header')->row();
        $po_info = $this->_po_supplier_info($receipt->po_id);

        $diskon_invoice      = $is_level1 ? (int) str_replace('.', '', $this->input->post('diskon_invoice')) : (int) $receipt->diskon_invoice;
        $new_ongkir          = $is_level1 ? (int) str_replace('.', '', $this->input->post('ongkir')) : (int) $receipt->ongkir;
        $ppn_mode            = $is_level1 ? $this->input->post('ppn_mode') : $receipt->ppn_mode;
        $supplier_invoice_no = $is_level1 ? ($this->input->post('supplier_invoice_no') ?: null) : $receipt->supplier_invoice_no;
        $invoice_date        = $is_level1 ? ($this->input->post('invoice_date') ?: null) : $receipt->invoice_date;
        if (!in_array($ppn_mode, ['none', 'add_distribute', 'inclusive'], true)) $ppn_mode = 'none';
        $ongkir_changed = $is_level1 && $new_ongkir !== (int) $receipt->ongkir;

        // ── Validasi qty SEMUA baris dulu, sebelum menyentuh DB sama sekali —
        // full staging: kalau ada satu baris saja yang tidak valid, batalkan
        // seluruh penyimpanan (tidak ada partial save). ──
        $qty_plan = [];
        foreach ($detail_ids as $idx => $detail_id) {
            $detail_id = (int) $detail_id;
            $detail = $this->db->where('id', $detail_id)->where('receipt_id', $receipt_id)->get('po_detail')->row();
            if (!$detail) continue;

            $qty_in  = $qty_arr[$idx] ?? null;
            $new_qty = ($qty_in !== null && $qty_in !== '') ? (int) $qty_in : (int) $detail->qty_received;
            if ($new_qty < 0) {
                echo json_encode(['status' => 'error', 'message' => 'Qty tidak boleh negatif.']);
                return;
            }
            // qty_ordered=0 menandai baris "ekstra" (di luar rencana PO) — tidak ada
            // batas atas order buat baris ini, jadi lewati pengecekan batas.
            if ((int) $detail->qty_ordered > 0 && $new_qty > (int) $detail->qty_ordered) {
                $nama = $detail->item_id
                    ? ($this->db->where('item_id', $detail->item_id)->get('p_item')->row()->nama_item ?? 'item')
                    : ($detail->item_name_temp ?? 'item');
                echo json_encode(['status' => 'error', 'message' => 'Qty "' . $nama . '" tidak boleh melebihi qty order (' . $detail->qty_ordered . ').']);
                return;
            }

            $qty_plan[$detail_id] = [
                'detail' => $detail,
                'old'    => (int) $detail->qty_received,
                'new'    => $new_qty,
                'diff'   => $new_qty - (int) $detail->qty_received,
            ];
        }

        $this->db->trans_start();

        // ── Tahap 0: qty (boleh level 1 & 2) ──
        $qty_changes  = [];
        $any_qty_diff = false;
        foreach ($qty_plan as $detail_id => $p) {
            if ($p['diff'] === 0) continue;
            $any_qty_diff = true;
            $detail = $p['detail'];

            if ($detail->item_id) {
                $this->db->set('stock', "stock + ({$p['diff']})", false)
                         ->where('item_id', $detail->item_id)
                         ->update('p_item');

                $this->db->where('po_detail_id', $detail_id)->delete('t_stock');

                if ($p['new'] > 0 && $po) {
                    $this->db->insert('t_stock', [
                        'item_id'      => (int) $detail->item_id,
                        'type'         => 'in',
                        'supplier_id'  => (int) $po->supplier_id,
                        'po_detail_id' => $detail_id,
                        'qty'          => $p['new'],
                        'date'         => $receipt->invoice_date ?: date('Y-m-d'),
                        'detail'       => 'Goods Receipt ' . ($po->po_number ?? ''),
                        'created_at'   => date('Y-m-d H:i:s'),
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $this->db->where('id', $detail_id)->update('po_detail', ['qty_received' => $p['new']]);

            $item_nm = $detail->item_id
                ? ($this->db->where('item_id', $detail->item_id)->get('p_item')->row()->nama_item ?? null)
                : null;
            $qty_changes[] = [
                'nama' => $item_nm ?? $detail->item_name_temp ?? 'Item',
                'lama' => $p['old'],
                'baru' => $p['new'],
            ];
        }

        if ($any_qty_diff) {
            $details_now    = $this->db->select('qty_ordered, qty_received')->where('po_id', $receipt->po_id)->get('po_detail')->result();
            $total_ordered  = array_sum(array_column((array) $details_now, 'qty_ordered'));
            $total_received = array_sum(array_column((array) $details_now, 'qty_received'));
            $new_po_status  = $total_received <= 0 ? 'sent' : ($total_received >= $total_ordered ? 'received' : 'partial');
            $this->db->where('po_id', $receipt->po_id)->update('po_header', ['status' => $new_po_status]);
        }

        // ── Tahap 1: simpan harga per baris apa adanya (belum kena redistribusi PPN) — level 1 saja ──
        $manual_pk_by_detail_id = [];
        $changed_items = [];
        if ($is_level1) {
            foreach ($qty_plan as $detail_id => $p) {
                $detail = $p['detail'];
                $idx    = array_search((string) $detail_id, array_map('strval', $detail_ids), true);
                if ($idx === false) continue;

                $new_price     = (int) str_replace('.', '', $price_arr[$idx] ?? '0');
                $harga_list_in = $harga_list_arr[$idx] ?? '';
                $diskon_in     = $diskon_arr[$idx] ?? '';
                $harga_list    = $harga_list_in !== '' ? (float) str_replace('.', '', $harga_list_in) : null;
                $diskon_persen = $diskon_in !== '' ? (float) $diskon_in : null;
                $manual_pk     = trim($pk_arr[$idx] ?? '');

                if ($harga_list !== null && $harga_list > 0) {
                    $new_price = (int) round($harga_list * (1 - ($diskon_persen ?: 0) / 100));
                }
                if ($new_price <= 0) continue;

                $this->db->where('id', $detail_id)->update('po_detail', [
                    'actual_price'  => $new_price,
                    'harga_list'    => $harga_list,
                    'diskon_persen' => $diskon_persen,
                ]);

                if ($manual_pk !== '') $manual_pk_by_detail_id[$detail_id] = $manual_pk;

                if ($detail->item_id && $po && ($new_price !== (int) $detail->actual_price || $manual_pk !== '')) {
                    $item_before = $this->db->where('item_id', $detail->item_id)->get('p_item')->row();
                    $this->_update_item_price($detail->item_id, $po->supplier_id, $new_price, 'po_receipt', $receipt->po_id, $harga_list, $manual_pk ?: null);
                    if ($item_before && $new_price !== (int) $detail->actual_price) {
                        $changed_items[] = [
                            'nama' => $item_before->nama_item,
                            'lama' => (int) $detail->actual_price,
                            'baru' => $new_price,
                        ];
                    }
                }
            }
        }

        // ── Tahap 2: No. Invoice, Tanggal Invoice, Diskon Invoice, Ongkir, Mode PPN — level 1 saja ──
        if ($is_level1) {
            $update = [
                'supplier_invoice_no' => $supplier_invoice_no,
                'invoice_date'        => $invoice_date,
                'diskon_invoice'      => $diskon_invoice,
                'ppn_mode'            => $ppn_mode,
            ];

            if ($ongkir_changed) {
                $this->load->model('Beban_m');
                $this->load->model('Coa_m');

                if ($receipt->ongkir_expense_id) {
                    $this->Beban_m->void($receipt->ongkir_expense_id, 'Koreksi ongkir dari Detail Penerimaan #' . $receipt_id, $user_id);
                }

                $new_expense_id = null;
                $new_journal_id = null;
                if ($new_ongkir > 0) {
                    $beban_angkut   = $this->Coa_m->get_by_subtype('beban_angkut_pembelian');
                    $new_expense_id = $this->Beban_m->create([
                        'expense_date'   => $receipt->receive_date,
                        'coa_id'         => $beban_angkut->coa_id,
                        'amount'         => $new_ongkir,
                        'payment_method' => 'cash',
                        'description'    => 'Ongkir penerimaan PO ' . ($po_info->po_number ?? '') . ' (koreksi)',
                    ], $user_id);
                    $new_journal_id = $this->Beban_m->get($new_expense_id)->journal_id;
                }

                $update['ongkir']                = $new_ongkir;
                $update['ongkir_payment_method'] = $new_ongkir > 0 ? 'cash' : null;
                $update['ongkir_expense_id']     = $new_expense_id;
                $update['ongkir_journal_id']     = $new_journal_id;
            }

            $this->db->where('receipt_id', $receipt_id)->update('po_receipt', $update);
        }

        // ── Tahap 3: hitung ulang total dari mode PPN yang TERSIMPAN saat ini
        // (bukan dari nilai yang di-POST — untuk level 2, diskon_invoice/ppn_mode
        // tidak ikut diubah, jadi harus baca ulang dari DB supaya konsisten) ──
        $receipt_now          = $this->db->where('receipt_id', $receipt_id)->get('po_receipt')->row();
        $effective_ppn_mode   = $receipt_now->ppn_mode;
        $effective_diskon_inv = (int) $receipt_now->diskon_invoice;

        if ($effective_ppn_mode === 'add_distribute') {
            $this->_redistribute_ppn($receipt_id, $manual_pk_by_detail_id);
            $receipt_after = $this->db->where('receipt_id', $receipt_id)->get('po_receipt')->row();
            $ppn_nominal   = (int) $receipt_after->ppn_nominal;
            $total_amount  = (int) $receipt_after->total_amount;
            $subtotal      = (float) ($total_amount + $effective_diskon_inv);
        } else {
            $subtotal_row = $this->db->query(
                "SELECT COALESCE(SUM(qty_received * actual_price), 0) AS subtotal FROM po_detail WHERE receipt_id = ?",
                [$receipt_id]
            )->row();
            $subtotal = (float) $subtotal_row->subtotal;
            $subtotal_setelah_diskon = $subtotal - $effective_diskon_inv;

            if ($effective_ppn_mode === 'inclusive') {
                $ppn_nominal = $this->fungsi->hitung_ppn_ekstrak($subtotal_setelah_diskon);
                $ppn_persen  = 11.00;
            } else {
                $ppn_nominal = 0;
                $ppn_persen  = null;
            }
            $total_amount = (int) round($subtotal_setelah_diskon);

            $this->db->where('receipt_id', $receipt_id)->update('po_receipt', [
                'ppn_persen'   => $ppn_persen,
                'ppn_nominal'  => $ppn_nominal,
                'total_amount' => $total_amount,
            ]);
        }

        $this->load->model('Ap_invoice_m');
        $ap_synced = $this->Ap_invoice_m->sync_amount_from_receipt($receipt_id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan perubahan, silakan coba lagi.']);
            return;
        }

        $actor = $this->fungsi->user_login()->nama ?? '-';
        $lines = [
            "✏️ *KOREKSI PENERIMAAN*",
            "━━━━━━━━━━━━━━",
            "📋 No. PO      : " . ($po_info->po_number ?? '-'),
            "🏭 Supplier    : " . ($po_info->nama_supplier ?? '-'),
        ];
        foreach ($qty_changes as $qc) {
            $lines[] = "🔢 {$qc['nama']} : qty {$qc['lama']} → {$qc['baru']}";
        }
        foreach ($changed_items as $ci) {
            $lines[] = "📦 {$ci['nama']} : Rp " . number_format($ci['lama'], 0, ',', '.') . " → Rp " . number_format($ci['baru'], 0, ',', '.');
        }
        if ($ongkir_changed) {
            $lines[] = "🚚 Ongkir      : Rp " . number_format((int) $receipt->ongkir, 0, ',', '.') . " → Rp " . number_format($new_ongkir, 0, ',', '.');
        }
        $lines[] = "💵 Total Utang : Rp " . number_format($total_amount, 0, ',', '.');
        $lines[] = "👤 Diubah      : {$actor}";
        $lines[] = "🕐 Waktu       : " . date('d/m/Y H:i');
        $lines[] = "━━━━━━━━━━━━━━";
        $lines[] = "_Notifikasi otomatis dari sistem myjdm_";
        if (!empty($qty_changes) || !empty($changed_items) || $ongkir_changed) {
            $this->_send_wa_notif(implode("\n", $lines), 'koreksi receipt_id ' . $receipt_id);
        }

        echo json_encode([
            'status'         => 'success',
            'diskon_invoice' => $effective_diskon_inv,
            'ppn_mode'       => $effective_ppn_mode,
            'ppn_nominal'    => $ppn_nominal,
            'total_amount'   => $total_amount,
            'subtotal'       => (int) round($subtotal),
            'ongkir'         => $new_ongkir,
            'ap_synced'      => $ap_synced,
        ]);
    }

    public function receiving_supplier()
    {
        $this->template->load('template', 'penerimaan/receiving_supplier');
    }

    public function receiving_supplier_data()
    {
        // Fetch sent/partial POs grouped by supplier. qty_ordered=0 menandai baris
        // "ekstra" (di luar rencana PO) — dikeluarkan dari total_ordered/total_received
        // supaya progress tidak lebih dari 100% gara-gara barang ekstra.
        $rows = $this->db->select('po_header.po_id, po_header.po_number, po_header.po_date,
                                   po_header.expected_date, po_header.status,
                                   supplier.supplier_id, supplier.nama_supplier,
                                   COUNT(po_detail.id) AS total_lines,
                                   SUM(CASE WHEN po_detail.qty_ordered > 0 THEN po_detail.qty_ordered ELSE 0 END) AS total_ordered,
                                   SUM(CASE WHEN po_detail.qty_ordered > 0 THEN po_detail.qty_received ELSE 0 END) AS total_received,
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
}
