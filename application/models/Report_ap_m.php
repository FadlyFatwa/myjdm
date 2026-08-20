<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_ap_m extends CI_Model {

    /**
     * Kartu hutang per supplier: gabungan invoice (kredit) + pembayaran (debit),
     * diurutkan tanggal, dengan saldo berjalan. Query langsung dari ap_invoice/ap_payment
     * (bukan v_general_ledger) karena baris jurnal tidak menyimpan supplier_id.
     */
    public function get_kartu_hutang($supplier_id, $from, $to)
    {
        $invoices = $this->db
            ->select("ap_invoice_id, ap_no AS ref_no, invoice_date AS trx_date, description,
                      0 AS debit, amount AS kredit, 'invoice' AS trx_type", false)
            ->from('ap_invoice')
            ->where('supplier_id', $supplier_id)
            ->where('status !=', 'void')
            ->where('invoice_date >=', $from)
            ->where('invoice_date <=', $to)
            ->get()->result();

        $payments = $this->db
            ->select("ap_payment.ap_payment_id, ap_payment.payment_no AS ref_no, ap_payment.payment_date AS trx_date,
                      CONCAT('Pembayaran ', ap_invoice.ap_no) AS description,
                      ap_payment.amount AS debit, 0 AS kredit, 'payment' AS trx_type", false)
            ->from('ap_payment')
            ->join('ap_invoice', 'ap_invoice.ap_invoice_id = ap_payment.ap_invoice_id')
            ->where('ap_invoice.supplier_id', $supplier_id)
            ->where('ap_payment.is_void', 0)
            ->where('ap_payment.payment_date >=', $from)
            ->where('ap_payment.payment_date <=', $to)
            ->get()->result();

        $rows = array_merge($invoices, $payments);
        usort($rows, function ($a, $b) {
            return strcmp($a->trx_date, $b->trx_date);
        });

        $balance = 0;
        foreach ($rows as $r) {
            $balance += $r->kredit - $r->debit;
            $r->balance = $balance;
        }

        return $rows;
    }

    /**
     * Aging hutang per invoice, bucket 0-30/31-60/61-90/>90 hari dari due_date.
     */
    public function get_aging($as_of_date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $as_of_date)) {
            $as_of_date = date('Y-m-d');
        }
        $this->db->select("ap_invoice.*, supplier.nama_supplier, po_receipt.supplier_invoice_no,
            GREATEST(DATEDIFF(" . $this->db->escape($as_of_date) . ", ap_invoice.due_date), 0) AS days_overdue", false);
        $this->db->from('ap_invoice');
        $this->db->join('supplier', 'supplier.supplier_id = ap_invoice.supplier_id');
        $this->db->join('po_receipt', 'po_receipt.receipt_id = ap_invoice.receipt_id', 'left');
        $this->db->where_in('ap_invoice.status', ['outstanding', 'partial']);
        $this->db->order_by('ap_invoice.due_date', 'ASC');
        $rows = $this->db->get()->result();

        foreach ($rows as $r) {
            $days = (int) $r->days_overdue;
            if ($days <= 30) {
                $r->bucket = '0-30';
            } elseif ($days <= 60) {
                $r->bucket = '31-60';
            } elseif ($days <= 90) {
                $r->bucket = '61-90';
            } else {
                $r->bucket = '>90';
            }
        }

        return $rows;
    }

    public function get_aging_summary_by_supplier($as_of_date)
    {
        $rows = $this->get_aging($as_of_date);
        $summary = [];

        foreach ($rows as $r) {
            $sid = $r->supplier_id;
            if (!isset($summary[$sid])) {
                $summary[$sid] = [
                    'supplier_id'   => $sid,
                    'nama_supplier' => $r->nama_supplier,
                    '0-30'          => 0,
                    '31-60'         => 0,
                    '61-90'         => 0,
                    '>90'           => 0,
                    'total'         => 0,
                ];
            }
            $summary[$sid][$r->bucket] += (int) $r->outstanding_amount;
            $summary[$sid]['total']    += (int) $r->outstanding_amount;
        }

        return array_values($summary);
    }

    /**
     * Daftar hutang per periode (invoice_date), semua status kecuali difilter,
     * opsional dibatasi ke satu supplier.
     */
    public function get_period_list($from, $to, $status = '', $supplier_id = '')
    {
        $this->db->select('ap_invoice.*, supplier.nama_supplier, po_receipt.supplier_invoice_no');
        $this->db->from('ap_invoice');
        $this->db->join('supplier', 'supplier.supplier_id = ap_invoice.supplier_id');
        $this->db->join('po_receipt', 'po_receipt.receipt_id = ap_invoice.receipt_id', 'left');
        $this->db->where('ap_invoice.invoice_date >=', $from);
        $this->db->where('ap_invoice.invoice_date <=', $to);
        if ($status === 'lunas') {
            $this->db->where('ap_invoice.status', 'paid');
        } elseif ($status === 'belum_lunas') {
            $this->db->where_in('ap_invoice.status', ['outstanding', 'partial']);
        } elseif ($status === 'void') {
            $this->db->where('ap_invoice.status', 'void');
        }
        if (!empty($supplier_id)) {
            $this->db->where('ap_invoice.supplier_id', $supplier_id);
        }
        $this->db->order_by('ap_invoice.invoice_date', 'ASC');
        return $this->db->get()->result();
    }
}
