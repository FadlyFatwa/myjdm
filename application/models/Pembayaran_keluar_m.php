<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pembayaran_keluar_m extends CI_Model {

    /**
     * Riwayat pembayaran keluar (hutang): gabungan pembayaran invoice AP langsung
     * (cash/kredit) dan pembayaran kontra bon hutang.
     */
    public function get_period_list($from, $to, $supplier_id = '', $payment_method = '', $status = '')
    {
        $sql = "SELECT * FROM (
                    SELECT
                        'invoice' AS jenis,
                        ap.ap_payment_id AS payment_id,
                        ap.payment_no,
                        ap.payment_date,
                        inv.ap_no AS reference_no,
                        inv.supplier_id,
                        s.nama_supplier,
                        ap.amount,
                        ap.payment_method,
                        ap.is_void,
                        ap.notes,
                        u.nama AS paid_by_name
                    FROM ap_payment ap
                    JOIN ap_invoice inv ON inv.ap_invoice_id = ap.ap_invoice_id
                    JOIN supplier s ON s.supplier_id = inv.supplier_id
                    JOIN user u ON u.user_id = ap.paid_by

                    UNION ALL

                    SELECT
                        'kontra_bon' AS jenis,
                        kp.kontra_bon_payment_id AS payment_id,
                        kp.payment_no,
                        kp.payment_date,
                        kb.kontra_bon_no AS reference_no,
                        kb.supplier_id,
                        s.nama_supplier,
                        kp.amount,
                        kp.payment_method,
                        kp.is_void,
                        kp.notes,
                        u.nama AS paid_by_name
                    FROM ap_kontra_bon_payment kp
                    JOIN ap_kontra_bon kb ON kb.kontra_bon_id = kp.kontra_bon_id
                    JOIN supplier s ON s.supplier_id = kb.supplier_id
                    JOIN user u ON u.user_id = kp.paid_by
                ) t
                WHERE t.payment_date BETWEEN ? AND ?";
        $params = [$from, $to];

        if ($supplier_id !== '') {
            $sql .= " AND t.supplier_id = ?";
            $params[] = $supplier_id;
        }
        if ($payment_method !== '') {
            $sql .= " AND t.payment_method = ?";
            $params[] = $payment_method;
        }
        if ($status === 'void') {
            $sql .= " AND t.is_void = 1";
        } elseif ($status === 'aktif') {
            $sql .= " AND t.is_void = 0";
        }

        $sql .= " ORDER BY t.payment_date DESC, t.payment_no DESC";

        return $this->db->query($sql, $params)->result();
    }
}
