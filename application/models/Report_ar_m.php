<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_ar_m extends CI_Model {

    /**
     * Kartu piutang per customer: gabungan invoice (debit) + pembayaran (kredit),
     * diurutkan tanggal, dengan saldo berjalan. Query langsung dari ar_invoice/ar_payment
     * (bukan v_general_ledger) karena baris jurnal tidak menyimpan customer_id.
     */
    public function get_kartu_piutang($customer_id, $from, $to)
    {
        $invoices = $this->db
            ->select("ar_invoice_id, ar_no AS ref_no, invoice_date AS trx_date, description,
                      amount AS debit, 0 AS kredit, 'invoice' AS trx_type", false)
            ->from('ar_invoice')
            ->where('customer_id', $customer_id)
            ->where('status !=', 'void')
            ->where('invoice_date >=', $from)
            ->where('invoice_date <=', $to)
            ->get()->result();

        $payments = $this->db
            ->select("ar_payment.ar_payment_id, ar_payment.payment_no AS ref_no, ar_payment.payment_date AS trx_date,
                      CONCAT('Pembayaran ', ar_invoice.ar_no) AS description,
                      0 AS debit, ar_payment.amount AS kredit, 'payment' AS trx_type", false)
            ->from('ar_payment')
            ->join('ar_invoice', 'ar_invoice.ar_invoice_id = ar_payment.ar_invoice_id')
            ->where('ar_invoice.customer_id', $customer_id)
            ->where('ar_payment.is_void', 0)
            ->where('ar_payment.payment_date >=', $from)
            ->where('ar_payment.payment_date <=', $to)
            ->get()->result();

        $rows = array_merge($invoices, $payments);
        usort($rows, function ($a, $b) {
            return strcmp($a->trx_date, $b->trx_date);
        });

        $balance = 0;
        foreach ($rows as $r) {
            $balance += $r->debit - $r->kredit;
            $r->balance = $balance;
        }

        return $rows;
    }

    /**
     * Aging piutang per invoice, bucket 0-30/31-60/61-90/>90 hari dari due_date.
     */
    public function get_aging($as_of_date)
    {
        $this->db->select("ar_invoice.*, customer.nama_customer,
            GREATEST(DATEDIFF('$as_of_date', ar_invoice.due_date), 0) AS days_overdue", false);
        $this->db->from('ar_invoice');
        $this->db->join('customer', 'customer.customer_id = ar_invoice.customer_id');
        $this->db->where_in('ar_invoice.status', ['outstanding', 'partial']);
        $this->db->order_by('ar_invoice.due_date', 'ASC');
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

    public function get_aging_summary_by_customer($as_of_date)
    {
        $rows = $this->get_aging($as_of_date);
        $summary = [];

        foreach ($rows as $r) {
            $cid = $r->customer_id;
            if (!isset($summary[$cid])) {
                $summary[$cid] = [
                    'customer_id'   => $cid,
                    'nama_customer' => $r->nama_customer,
                    '0-30'          => 0,
                    '31-60'         => 0,
                    '61-90'         => 0,
                    '>90'           => 0,
                    'total'         => 0,
                ];
            }
            $summary[$cid][$r->bucket] += (int) $r->outstanding_amount;
            $summary[$cid]['total']    += (int) $r->outstanding_amount;
        }

        return array_values($summary);
    }
}
