<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ar_kontra_bon_payment_m extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Journal_m');
        $this->load->model('Coa_m');
        $this->load->model('Ar_invoice_m');
        $this->load->model('Ar_kontra_bon_m');
    }

    public function payment_no()
    {
        $sql = "SELECT
                    DATE_FORMAT(CURDATE(), '%y%m') AS today,
                    MAX(MID(payment_no, 7, 4)) AS last_no
                FROM ar_kontra_bon_payment
                WHERE MID(payment_no, 3, 4) = DATE_FORMAT(CURDATE(), '%y%m')";
        $row = $this->db->query($sql)->row();

        $today = $row->today;
        if ($row->last_no !== null) {
            $n  = ((int) $row->last_no) + 1;
            $no = sprintf("%'.04d", $n);
        } else {
            $no = '0001';
        }
        return 'KP' . $today . $no;
    }

    public function get_by_kontra_bon($kontra_bon_id)
    {
        $this->db->select('ar_kontra_bon_payment.*, user.nama AS received_by_name');
        $this->db->from('ar_kontra_bon_payment');
        $this->db->join('user', 'user.user_id = ar_kontra_bon_payment.received_by');
        $this->db->where('ar_kontra_bon_payment.kontra_bon_id', $kontra_bon_id);
        $this->db->order_by('ar_kontra_bon_payment.kontra_bon_payment_id', 'DESC');
        return $this->db->get()->result();
    }

    public function get($id)
    {
        return $this->db->where('kontra_bon_payment_id', $id)->get('ar_kontra_bon_payment')->row();
    }

    public function get_detail($kontra_bon_payment_id)
    {
        $this->db->select('ar_kontra_bon_payment_detail.*, ar_invoice.ar_no');
        $this->db->from('ar_kontra_bon_payment_detail');
        $this->db->join('ar_invoice', 'ar_invoice.ar_invoice_id = ar_kontra_bon_payment_detail.ar_invoice_id');
        $this->db->where('ar_kontra_bon_payment_detail.kontra_bon_payment_id', $kontra_bon_payment_id);
        return $this->db->get()->result();
    }

    /**
     * $post: kontra_bon_id, payment_date, amount, payment_method, notes
     * Distribusi FIFO: nota dengan invoice_date paling lama dilunasi duluan.
     */
    public function pay($post, $user_id)
    {
        $kb = $this->Ar_kontra_bon_m->get($post['kontra_bon_id']);
        if (!$kb) throw new Exception('Kontra bon tidak ditemukan');
        if (!in_array($kb->status, ['outstanding', 'partial'])) {
            throw new Exception('Kontra bon ini tidak bisa menerima pembayaran (status: ' . $kb->status . ')');
        }

        $amount = (int) $post['amount'];
        if ($amount <= 0) throw new Exception('Jumlah pembayaran harus lebih dari 0');
        if ($amount > $kb->outstanding_amount) {
            throw new Exception('Jumlah pembayaran melebihi sisa kontra bon (Rp ' . number_format($kb->outstanding_amount, 0, ',', '.') . ')');
        }

        $this->db->trans_start();

        // Kunci baris kontra bon supaya dua pembayaran bersamaan tidak sama-sama lolos cek saldo di atas
        $locked = $this->db->query(
            "SELECT outstanding_amount, status FROM ar_kontra_bon WHERE kontra_bon_id = ? FOR UPDATE",
            [$post['kontra_bon_id']]
        )->row();

        if (!$locked || !in_array($locked->status, ['outstanding', 'partial']) || $amount > $locked->outstanding_amount) {
            $this->db->trans_rollback();
            throw new Exception('Jumlah pembayaran melebihi sisa kontra bon atau kontra bon sudah tidak aktif.');
        }

        $payment_no = $this->payment_no();

        $kas_akun = $post['payment_method'] === 'cash'
            ? $this->Coa_m->get_by_subtype('kas')
            : $this->Coa_m->get_by_subtype('bank');
        $piutang = $this->Coa_m->get_by_subtype('piutang_usaha');

        $journal_id = $this->Journal_m->post([
            'journal_date' => $post['payment_date'],
            'source_type'  => 'ar_kontra_bon_payment',
            'source_id'    => null,
            'description'  => 'Penerimaan kontra bon ' . $kb->kontra_bon_no,
            'created_by'   => $user_id,
        ], [
            ['coa_id' => $kas_akun->coa_id, 'debit' => $amount, 'kredit' => 0, 'notes' => $payment_no],
            ['coa_id' => $piutang->coa_id, 'debit' => 0, 'kredit' => $amount, 'notes' => $payment_no],
        ]);

        $this->db->insert('ar_kontra_bon_payment', [
            'payment_no'     => $payment_no,
            'kontra_bon_id'  => $post['kontra_bon_id'],
            'payment_date'   => $post['payment_date'],
            'amount'         => $amount,
            'payment_method' => $post['payment_method'],
            'notes'          => $post['notes'] ?? null,
            'received_by'    => $user_id,
            'journal_id'     => $journal_id,
        ]);
        $kontra_bon_payment_id = $this->db->insert_id();

        $this->db->where('journal_id', $journal_id)->update('finance_journal', ['source_id' => $kontra_bon_payment_id]);

        // Distribusi FIFO ke nota-nota dalam kontra bon ini
        $invoices = $this->db
            ->where('kontra_bon_id', $post['kontra_bon_id'])
            ->where_in('status', ['outstanding', 'partial'])
            ->order_by('invoice_date', 'ASC')
            ->get('ar_invoice')->result();

        $remaining = $amount;
        foreach ($invoices as $inv) {
            if ($remaining <= 0) break;
            $alloc = min($remaining, (int) $inv->outstanding_amount);
            if ($alloc <= 0) continue;

            $this->db->insert('ar_kontra_bon_payment_detail', [
                'kontra_bon_payment_id' => $kontra_bon_payment_id,
                'ar_invoice_id'         => $inv->ar_invoice_id,
                'amount_allocated'      => $alloc,
            ]);

            $this->Ar_invoice_m->update_paid_amount($inv->ar_invoice_id, $alloc);
            $remaining -= $alloc;
        }

        $this->Ar_kontra_bon_m->update_paid_amount($post['kontra_bon_id'], $amount);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Gagal mencatat pembayaran kontra bon');
        }

        return $kontra_bon_payment_id;
    }

    public function void_payment($id, $reason, $user_id)
    {
        $payment = $this->get($id);
        if (!$payment) throw new Exception('Pembayaran tidak ditemukan');
        if ($payment->is_void) throw new Exception('Pembayaran sudah di-void sebelumnya');

        $this->db->trans_start();

        $details = $this->get_detail($id);
        foreach ($details as $d) {
            $this->Ar_invoice_m->update_paid_amount($d->ar_invoice_id, -$d->amount_allocated);
        }

        $this->Ar_kontra_bon_m->update_paid_amount($payment->kontra_bon_id, -$payment->amount);

        $this->db->where('kontra_bon_payment_id', $id)->update('ar_kontra_bon_payment', [
            'is_void'     => 1,
            'void_reason' => $reason,
            'voided_by'   => $user_id,
            'voided_at'   => date('Y-m-d H:i:s'),
        ]);

        if ($payment->journal_id) {
            $this->Journal_m->void($payment->journal_id, 'Pembayaran ' . $payment->payment_no . ' di-void: ' . $reason, $user_id);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
