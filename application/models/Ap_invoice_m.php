<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ap_invoice_m extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Journal_m');
        $this->load->model('Coa_m');
    }

    public function ap_no()
    {
        $sql = "SELECT
                    DATE_FORMAT(CURDATE(), '%y%m') AS today,
                    MAX(MID(ap_no, 7, 4)) AS last_no
                FROM ap_invoice
                WHERE MID(ap_no, 3, 4) = DATE_FORMAT(CURDATE(), '%y%m')";
        $row = $this->db->query($sql)->row();

        $today = $row->today;
        if ($row->last_no !== null) {
            $n  = ((int) $row->last_no) + 1;
            $no = sprintf("%'.04d", $n);
        } else {
            $no = '0001';
        }
        return 'AP' . $today . $no;
    }

    /**
     * Hitung ulang due_date SEMUA invoice outstanding/partial berdasarkan
     * payment_term_days supplier TERKINI. Dipakai saat termin supplier diubah
     * setelah invoice terlanjur dibuat (due_date dihitung sekali saat create
     * dan disimpan statis, tidak otomatis ikut berubah).
     */
    public function recalc_all_due_dates()
    {
        $this->db->select('ap_invoice.ap_invoice_id, ap_invoice.invoice_date, ap_invoice.due_date, supplier.payment_term_days');
        $this->db->from('ap_invoice');
        $this->db->join('supplier', 'supplier.supplier_id = ap_invoice.supplier_id');
        $this->db->where_in('ap_invoice.status', ['outstanding', 'partial']);
        // Nota yang sudah digabung ke kontra bon ikut jatuh tempo kontra bon, bukan termin supplier langsung
        $this->db->where('ap_invoice.kontra_bon_id', null);
        $rows = $this->db->get()->result();

        $updated = 0;
        foreach ($rows as $r) {
            $new_due_date = date('Y-m-d', strtotime($r->invoice_date . ' +' . (int) $r->payment_term_days . ' days'));
            if ($new_due_date !== $r->due_date) {
                $this->db->where('ap_invoice_id', $r->ap_invoice_id)->update('ap_invoice', ['due_date' => $new_due_date]);
                $updated++;
            }
        }

        return $updated;
    }

    public function get($id)
    {
        $this->db->select('ap_invoice.*, supplier.nama_supplier, supplier.phone, kb.kontra_bon_no, po_receipt.po_id, po_receipt.supplier_invoice_no, po_header.po_number', false);
        $this->db->from('ap_invoice');
        $this->db->join('supplier', 'supplier.supplier_id = ap_invoice.supplier_id');
        $this->db->join('ap_kontra_bon AS kb', 'kb.kontra_bon_id = ap_invoice.kontra_bon_id', 'left');
        $this->db->join('po_receipt', 'po_receipt.receipt_id = ap_invoice.receipt_id', 'left');
        $this->db->join('po_header', 'po_header.po_id = po_receipt.po_id', 'left');
        $this->db->where('ap_invoice.ap_invoice_id', $id);
        return $this->db->get()->row();
    }

    public function get_by_receipt($receipt_id)
    {
        return $this->db->where('receipt_id', $receipt_id)->get('ap_invoice')->row();
    }

    public function get_by_supplier($supplier_id)
    {
        $this->db->where('supplier_id', $supplier_id);
        $this->db->where('status !=', 'void');
        $this->db->order_by('invoice_date', 'DESC');
        return $this->db->get('ap_invoice')->result();
    }

    /**
     * Buat AP invoice otomatis dari resi penerimaan barang.
     * $receipt = row po_receipt (harus sudah punya total_amount final).
     * $payment_type = 'credit' (jadi hutang, jatuh tempo termin supplier)
     *                 atau 'cash' (langsung dilunasi penuh saat itu juga).
     */
    public function create_from_receipt($receipt, $payment_type, $user_id)
    {
        $po = $this->db->where('po_id', $receipt->po_id)->get('po_header')->row();
        $supplier = $this->db->where('supplier_id', $po->supplier_id)->get('supplier')->row();

        $invoice_date = $receipt->receive_date;
        $due_date = date('Y-m-d', strtotime($invoice_date . ' +' . (int) $supplier->payment_term_days . ' days'));
        $amount = (int) $receipt->total_amount;

        $this->db->trans_start();

        $ap_no = $this->ap_no();

        $this->db->insert('ap_invoice', [
            'ap_no'              => $ap_no,
            'receipt_id'         => $receipt->receipt_id,
            'supplier_id'        => $po->supplier_id,
            'invoice_date'       => $invoice_date,
            'due_date'           => $due_date,
            'description'        => 'Hutang dari penerimaan barang ' . $po->po_number,
            'amount'             => $amount,
            'paid_amount'        => 0,
            'outstanding_amount' => $amount,
            'status'             => 'outstanding',
            'payment_type'       => $payment_type,
            'created_by'         => $user_id,
        ]);
        $ap_invoice_id = $this->db->insert_id();

        $persediaan = $this->Coa_m->get_by_subtype('persediaan');
        $hutang = $this->Coa_m->get_by_subtype('hutang_usaha');

        $journal_id = $this->Journal_m->post([
            'journal_date' => $invoice_date,
            'source_type'  => 'ap_invoice',
            'source_id'    => $ap_invoice_id,
            'description'  => 'Hutang penerimaan barang ' . $po->po_number,
            'created_by'   => $user_id,
        ], [
            ['coa_id' => $persediaan->coa_id, 'debit' => $amount, 'kredit' => 0, 'notes' => $ap_no],
            ['coa_id' => $hutang->coa_id, 'debit' => 0, 'kredit' => $amount, 'notes' => $po->po_number],
        ]);

        $this->db->query("UPDATE supplier SET ap_balance = ap_balance + ? WHERE supplier_id = ?", [$amount, $po->supplier_id]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Gagal mencatat hutang penerimaan barang.');
        }

        if ($payment_type === 'cash' && $amount > 0) {
            $this->load->model('Ap_payment_m');
            $this->Ap_payment_m->pay([
                'ap_invoice_id'  => $ap_invoice_id,
                'payment_date'   => $invoice_date,
                'amount'         => $amount,
                'payment_method' => 'cash',
                'notes'          => 'Dibayar cash saat penerimaan barang',
            ], $user_id);
        }

        return $ap_invoice_id;
    }

    /**
     * Dipanggil setelah pembayaran dicatat/void — recompute paid_amount/outstanding/status,
     * sinkronkan supplier.ap_balance.
     */
    public function update_paid_amount($ap_invoice_id, $paid_delta)
    {
        $ap = $this->db->where('ap_invoice_id', $ap_invoice_id)->get('ap_invoice')->row();
        if (!$ap) return false;

        $new_paid = $ap->paid_amount + $paid_delta;
        $new_outstanding = $ap->amount - $new_paid;
        $status = $new_outstanding <= 0 ? 'paid' : ($new_paid > 0 ? 'partial' : 'outstanding');

        $this->db->where('ap_invoice_id', $ap_invoice_id)->update('ap_invoice', [
            'paid_amount'        => $new_paid,
            'outstanding_amount' => $new_outstanding,
            'status'             => $status,
        ]);

        $this->db->query("UPDATE supplier SET ap_balance = ap_balance - ? WHERE supplier_id = ?", [$paid_delta, $ap->supplier_id]);

        return true;
    }

    /**
     * Resync amount/outstanding_amount kalau total resi berubah lewat Edit Penerimaan
     * (harga list/diskon per baris, Barang Ekstra, diskon invoice, PPN). Cuma jalan kalau
     * invoice masih murni outstanding (belum ada pembayaran sama sekali) & belum masuk
     * kontra bon — begitu ada pembayaran, disesuaikan otomatis dianggap terlalu berisiko
     * (sama seperti Ar_invoice_m::sync_from_sale_edit()), harus diselesaikan manual lewat
     * menu Hutang. Mengembalikan true kalau disesuaikan, false kalau di-skip/tidak ada.
     */
    public function sync_amount_from_receipt($receipt_id)
    {
        $ap = $this->get_by_receipt($receipt_id);
        if (!$ap || $ap->status === 'void') return false;
        if ($ap->paid_amount > 0 || $ap->kontra_bon_id) return false;

        $receipt = $this->db->where('receipt_id', $receipt_id)->get('po_receipt')->row();
        if (!$receipt) return false;

        // Hitung ulang live dari po_detail saat ini (Penerimaan::_redistribute_ppn() sudah
        // menulis ulang po_receipt.ppn_nominal/total_amount & actual_price tiap ada koreksi
        // baris, tapi disini dihitung ulang independen dari sumbernya langsung supaya tetap
        // benar walau dipanggil dari alur lain). actual_price SUDAH termasuk porsi PPN kalau
        // ppn_mode='add_distribute' (didistribusi oleh receive()/Penerimaan::_redistribute_ppn()),
        // jadi jangan tambah ppn_nominal lagi di sini — itu akan menghitung PPN dua kali.
        $subtotal_row = $this->db->query(
            "SELECT COALESCE(SUM(qty_received * actual_price), 0) AS subtotal FROM po_detail WHERE receipt_id = ?",
            [$receipt_id]
        )->row();
        $subtotal = (int) $subtotal_row->subtotal;
        $new_amount = $subtotal - (int) $receipt->diskon_invoice;

        if ($new_amount === (int) $ap->amount) return false;

        $this->db->trans_start();

        $old_journal = $this->Journal_m->get_by_source('ap_invoice', $ap->ap_invoice_id);
        if ($old_journal) {
            $this->Journal_m->void($old_journal->journal_id, 'Disesuaikan: total resi diedit', $ap->created_by);
        }
        $this->db->query("UPDATE supplier SET ap_balance = ap_balance - ? WHERE supplier_id = ?", [$ap->outstanding_amount, $ap->supplier_id]);

        $this->db->where('ap_invoice_id', $ap->ap_invoice_id)->update('ap_invoice', [
            'amount'             => $new_amount,
            'outstanding_amount' => $new_amount,
        ]);

        if ($new_amount > 0) {
            $persediaan = $this->Coa_m->get_by_subtype('persediaan');
            $hutang = $this->Coa_m->get_by_subtype('hutang_usaha');
            $this->Journal_m->post([
                'journal_date' => $ap->invoice_date,
                'source_type'  => 'ap_invoice',
                'source_id'    => $ap->ap_invoice_id,
                'description'  => 'Hutang penerimaan barang (disesuaikan): ' . $ap->ap_no,
                'created_by'   => $ap->created_by,
            ], [
                ['coa_id' => $persediaan->coa_id, 'debit' => $new_amount, 'kredit' => 0, 'notes' => $ap->ap_no],
                ['coa_id' => $hutang->coa_id, 'debit' => 0, 'kredit' => $new_amount, 'notes' => $ap->ap_no],
            ]);
        }

        $this->db->query("UPDATE supplier SET ap_balance = ap_balance + ? WHERE supplier_id = ?", [$new_amount, $ap->supplier_id]);

        $this->db->trans_complete();
        return $this->db->trans_status() !== FALSE;
    }

    public function void($id, $reason, $user_id)
    {
        $ap = $this->db->where('ap_invoice_id', $id)->get('ap_invoice')->row();
        if (!$ap) return false;
        if ($ap->paid_amount > 0) {
            throw new Exception('Invoice sudah ada pembayaran, tidak bisa di-void.');
        }
        if ($ap->kontra_bon_id) {
            throw new Exception('Nota ini sudah tergabung dalam kontra bon — batalkan kontra bon-nya, bukan nota individual.');
        }

        $this->db->trans_start();

        $this->db->where('ap_invoice_id', $id)->update('ap_invoice', [
            'status'      => 'void',
            'void_reason' => $reason,
            'voided_by'   => $user_id,
            'voided_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->db->query("UPDATE supplier SET ap_balance = ap_balance - ? WHERE supplier_id = ?", [$ap->outstanding_amount, $ap->supplier_id]);

        $journal = $this->Journal_m->get_by_source('ap_invoice', $id);
        if ($journal) {
            $this->Journal_m->void($journal->journal_id, 'AP invoice ' . $ap->ap_no . ' di-void: ' . $reason, $user_id);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_json()
    {
        $draw   = intval($this->input->post('draw'));
        $search = $this->input->post('search')['value'] ?? '';

        $base = function () {
            $this->db->select('ap_invoice.*, supplier.nama_supplier, po_receipt.supplier_invoice_no, po_header.po_number', false);
            $this->db->from('ap_invoice');
            $this->db->join('supplier', 'supplier.supplier_id = ap_invoice.supplier_id');
            $this->db->join('po_receipt', 'po_receipt.receipt_id = ap_invoice.receipt_id', 'left');
            $this->db->join('po_header', 'po_header.po_id = po_receipt.po_id', 'left');
        };

        $base();
        $total = $this->db->count_all_results();

        $base();
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ap_invoice.ap_no', $search);
            $this->db->or_like('supplier.nama_supplier', $search);
            $this->db->or_like('po_header.po_number', $search);
            $this->db->or_like('po_receipt.supplier_invoice_no', $search);
            $this->db->group_end();
        }
        $filtered = $this->db->count_all_results('', false);

        $start  = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->order_by('ap_invoice.ap_invoice_id', 'DESC')->get()->result();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ];
    }

    public function get_aging_data($as_of_date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $as_of_date)) {
            $as_of_date = date('Y-m-d');
        }
        $this->db->select("ap_invoice.*, supplier.nama_supplier,
            DATEDIFF(" . $this->db->escape($as_of_date) . ", ap_invoice.due_date) AS days_overdue", false);
        $this->db->from('ap_invoice');
        $this->db->join('supplier', 'supplier.supplier_id = ap_invoice.supplier_id');
        $this->db->where_in('ap_invoice.status', ['outstanding', 'partial']);
        $this->db->order_by('ap_invoice.due_date', 'ASC');
        return $this->db->get()->result();
    }
}
