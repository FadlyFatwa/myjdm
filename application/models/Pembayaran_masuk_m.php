<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pembayaran_masuk_m extends CI_Model {

    /**
     * Riwayat pembayaran masuk (piutang): penerimaan pembayaran kontra bon
     * piutang kredit dari penjualan.
     */
    public function get_period_list($from, $to, $customer_id = '', $payment_method = '', $status = '')
    {
        $sql = "SELECT
                    kp.kontra_bon_payment_id AS payment_id,
                    kp.payment_no,
                    kp.payment_date,
                    kb.kontra_bon_no AS reference_no,
                    kb.customer_id,
                    c.nama_customer,
                    kp.amount,
                    kp.payment_method,
                    kp.is_void,
                    kp.notes,
                    u.nama AS received_by_name
                FROM ar_kontra_bon_payment kp
                JOIN ar_kontra_bon kb ON kb.kontra_bon_id = kp.kontra_bon_id
                JOIN customer c ON c.customer_id = kb.customer_id
                JOIN user u ON u.user_id = kp.received_by
                WHERE kp.payment_date BETWEEN ? AND ?";
        $params = [$from, $to];

        if ($customer_id !== '') {
            $sql .= " AND kb.customer_id = ?";
            $params[] = $customer_id;
        }
        if ($payment_method !== '') {
            $sql .= " AND kp.payment_method = ?";
            $params[] = $payment_method;
        }
        if ($status === 'void') {
            $sql .= " AND kp.is_void = 1";
        } elseif ($status === 'aktif') {
            $sql .= " AND kp.is_void = 0";
        }

        $sql .= " ORDER BY kp.payment_date DESC, kp.payment_no DESC";

        return $this->db->query($sql, $params)->result();
    }
}
