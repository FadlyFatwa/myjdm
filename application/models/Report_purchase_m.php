<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_purchase_m extends CI_Model {

    /**
     * Daftar pembelian per periode (receive_date), satu baris = satu po_receipt.
     * ap_invoice di-LEFT JOIN karena penerimaan Rp 0 tidak membuat ap_invoice.
     */
    public function get_period_list($from, $to, $status = '', $supplier_id = '')
    {
        $this->db->select('po_receipt.*, po_header.po_number, supplier.nama_supplier,
            ap_invoice.payment_type, ap_invoice.status AS ap_status', false);
        $this->db->from('po_receipt');
        $this->db->join('po_header', 'po_header.po_id = po_receipt.po_id');
        $this->db->join('supplier', 'supplier.supplier_id = po_header.supplier_id');
        $this->db->join('ap_invoice', 'ap_invoice.receipt_id = po_receipt.receipt_id', 'left');
        $this->db->where('po_receipt.receive_date >=', $from);
        $this->db->where('po_receipt.receive_date <=', $to);
        if ($status === 'lunas') {
            $this->db->where('ap_invoice.status', 'paid');
        } elseif ($status === 'belum_lunas') {
            $this->db->where_in('ap_invoice.status', ['outstanding', 'partial']);
        } elseif ($status === 'void') {
            $this->db->where('ap_invoice.status', 'void');
        }
        if (!empty($supplier_id)) {
            $this->db->where('po_header.supplier_id', $supplier_id);
        }
        $this->db->order_by('po_receipt.receive_date', 'ASC');
        return $this->db->get()->result();
    }
}
