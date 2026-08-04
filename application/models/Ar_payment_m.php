<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ar_payment_m extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Journal_m');
        $this->load->model('Coa_m');
        $this->load->model('Ar_invoice_m');
    }

    public function payment_no()
    {
        $sql = "SELECT
                    DATE_FORMAT(CURDATE(), '%y%m') AS today,
                    MAX(MID(payment_no, 7, 4)) AS last_no
                FROM ar_payment
                WHERE MID(payment_no, 3, 4) = DATE_FORMAT(CURDATE(), '%y%m')";
        $row = $this->db->query($sql)->row();

        $today = $row->today;
        if ($row->last_no !== null) {
            $n  = ((int) $row->last_no) + 1;
            $no = sprintf("%'.04d", $n);
        } else {
            $no = '0001';
        }
        return 'RC' . $today . $no;
    }

    public function get_by_invoice($ar_invoice_id)
    {
        $this->db->select('ar_payment.*, user.nama AS received_by_name');
        $this->db->from('ar_payment');
        $this->db->join('user', 'user.user_id = ar_payment.received_by');
        $this->db->where('ar_payment.ar_invoice_id', $ar_invoice_id);
        $this->db->order_by('ar_payment.ar_payment_id', 'DESC');
        return $this->db->get()->result();
    }

    public function get($id)
    {
        return $this->db->where('ar_payment_id', $id)->get('ar_payment')->row();
    }

    /**
     * $post: ar_invoice_id, payment_date, amount, payment_method, notes
     */
    public function pay($post, $user_id)
    {
        $ar = $this->Ar_invoice_m->get($post['ar_invoice_id']);
        if (!$ar) throw new Exception('Invoice piutang tidak ditemukan');
        if (!in_array($ar->status, ['outstanding', 'partial'])) {
            throw new Exception('Invoice ini tidak bisa menerima pembayaran (status: ' . $ar->status . ')');
        }

        $amount = (int) $post['amount'];
        if ($amount <= 0) throw new Exception('Jumlah pembayaran harus lebih dari 0');
        if ($amount > $ar->outstanding_amount) {
            throw new Exception('Jumlah pembayaran melebihi sisa piutang (Rp ' . number_format($ar->outstanding_amount, 0, ',', '.') . ')');
        }

        $this->db->trans_start();

        $payment_no = $this->payment_no();

        $kas_akun = $post['payment_method'] === 'cash'
            ? $this->Coa_m->get_by_subtype('kas')
            : $this->Coa_m->get_by_subtype('bank');
        $piutang = $this->Coa_m->get_by_subtype('piutang_usaha');

        $journal_id = $this->Journal_m->post([
            'journal_date' => $post['payment_date'],
            'source_type'  => 'ar_payment',
            'source_id'    => null, // diisi setelah insert ar_payment (butuh ID)
            'description'  => 'Penerimaan piutang ' . $ar->ar_no,
            'created_by'   => $user_id,
        ], [
            ['coa_id' => $kas_akun->coa_id, 'debit' => $amount, 'kredit' => 0, 'notes' => $payment_no],
            ['coa_id' => $piutang->coa_id, 'debit' => 0, 'kredit' => $amount, 'notes' => $payment_no],
        ]);

        $this->db->insert('ar_payment', [
            'payment_no'     => $payment_no,
            'ar_invoice_id'  => $post['ar_invoice_id'],
            'payment_date'   => $post['payment_date'],
            'amount'         => $amount,
            'payment_method' => $post['payment_method'],
            'notes'          => $post['notes'] ?? null,
            'received_by'    => $user_id,
            'journal_id'     => $journal_id,
        ]);
        $ar_payment_id = $this->db->insert_id();

        // Update source_id jurnal supaya polymorphic ref lengkap
        $this->db->where('journal_id', $journal_id)->update('finance_journal', ['source_id' => $ar_payment_id]);

        $this->Ar_invoice_m->update_paid_amount($post['ar_invoice_id'], $amount);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Gagal mencatat pembayaran piutang');
        }

        return $ar_payment_id;
    }

    public function void_payment($id, $reason, $user_id)
    {
        $payment = $this->get($id);
        if (!$payment) throw new Exception('Pembayaran tidak ditemukan');
        if ($payment->is_void) throw new Exception('Pembayaran sudah di-void sebelumnya');

        $this->db->trans_start();

        $this->db->where('ar_payment_id', $id)->update('ar_payment', [
            'is_void'     => 1,
            'void_reason' => $reason,
            'voided_by'   => $user_id,
            'voided_at'   => date('Y-m-d H:i:s'),
        ]);

        // Reverse: kurangi kembali paid_amount di invoice (delta negatif)
        $this->Ar_invoice_m->update_paid_amount($payment->ar_invoice_id, -$payment->amount);

        if ($payment->journal_id) {
            $this->Journal_m->void($payment->journal_id, 'Pembayaran ' . $payment->payment_no . ' di-void: ' . $reason, $user_id);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
