<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ap_kontra_bon_m extends CI_Model {

    public function kontra_bon_no()
    {
        $sql = "SELECT
                    DATE_FORMAT(CURDATE(), '%y%m') AS today,
                    MAX(MID(kontra_bon_no, 8, 4)) AS last_no
                FROM ap_kontra_bon
                WHERE MID(kontra_bon_no, 4, 4) = DATE_FORMAT(CURDATE(), '%y%m')";
        $row = $this->db->query($sql)->row();

        $today = $row->today;
        if ($row->last_no !== null) {
            $n  = ((int) $row->last_no) + 1;
            $no = sprintf("%'.04d", $n);
        } else {
            $no = '0001';
        }
        return 'KBH' . $today . $no;
    }

    /**
     * Preview nota yang akan tergabung — dipanggil AJAX sebelum submit form create().
     */
    public function get_outstanding_invoices($supplier_id, $from, $to)
    {
        $this->db->select('ap_invoice.*, supplier.nama_supplier, po_receipt.supplier_invoice_no, po_header.po_number', false);
        $this->db->from('ap_invoice');
        $this->db->join('supplier', 'supplier.supplier_id = ap_invoice.supplier_id');
        $this->db->join('po_receipt', 'po_receipt.receipt_id = ap_invoice.receipt_id', 'left');
        $this->db->join('po_header', 'po_header.po_id = po_receipt.po_id', 'left');
        $this->db->where('ap_invoice.supplier_id', $supplier_id);
        $this->db->where('ap_invoice.invoice_date >=', $from);
        $this->db->where('ap_invoice.invoice_date <=', $to);
        $this->db->where_in('ap_invoice.status', ['outstanding', 'partial']);
        $this->db->where('ap_invoice.kontra_bon_id', null);
        $this->db->order_by('ap_invoice.invoice_date', 'ASC');
        return $this->db->get()->result();
    }

    public function get($id)
    {
        $this->db->select('ap_kontra_bon.*, supplier.nama_supplier, supplier.phone');
        $this->db->from('ap_kontra_bon');
        $this->db->join('supplier', 'supplier.supplier_id = ap_kontra_bon.supplier_id');
        $this->db->where('ap_kontra_bon.kontra_bon_id', $id);
        return $this->db->get()->row();
    }

    public function get_invoices($kontra_bon_id)
    {
        $this->db->select('ap_invoice.*, po_receipt.supplier_invoice_no, po_header.po_number', false);
        $this->db->from('ap_invoice');
        $this->db->join('po_receipt', 'po_receipt.receipt_id = ap_invoice.receipt_id', 'left');
        $this->db->join('po_header', 'po_header.po_id = po_receipt.po_id', 'left');
        $this->db->where('ap_invoice.kontra_bon_id', $kontra_bon_id);
        $this->db->order_by('ap_invoice.invoice_date', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * $post: supplier_id, period_start, period_end, due_date (opsional override)
     */
    public function create($post, $user_id)
    {
        $supplier = $this->db->where('supplier_id', $post['supplier_id'])->get('supplier')->row();
        if (!$supplier) throw new Exception('Supplier tidak ditemukan');

        $invoices = $this->get_outstanding_invoices($post['supplier_id'], $post['period_start'], $post['period_end']);
        if (empty($invoices)) {
            throw new Exception('Tidak ada nota outstanding pada rentang tanggal tersebut.');
        }

        $due_date = !empty($post['due_date'])
            ? $post['due_date']
            : date('Y-m-d', strtotime($post['period_end'] . ' +' . (int) $supplier->payment_term_days . ' days'));

        $total_amount = 0;
        $paid_amount = 0;
        $outstanding_amount = 0;
        foreach ($invoices as $inv) {
            $total_amount += (int) $inv->amount;
            $paid_amount += (int) $inv->paid_amount;
            $outstanding_amount += (int) $inv->outstanding_amount;
        }

        $this->db->trans_start();

        $kontra_bon_no = $this->kontra_bon_no();
        $this->db->insert('ap_kontra_bon', [
            'kontra_bon_no'      => $kontra_bon_no,
            'supplier_id'        => $post['supplier_id'],
            'period_start'       => $post['period_start'],
            'period_end'         => $post['period_end'],
            'due_date'           => $due_date,
            'total_amount'       => $total_amount,
            'paid_amount'        => $paid_amount,
            'outstanding_amount' => $outstanding_amount,
            'status'             => $outstanding_amount <= 0 ? 'paid' : ($paid_amount > 0 ? 'partial' : 'outstanding'),
            'created_by'         => $user_id,
        ]);
        $kontra_bon_id = $this->db->insert_id();

        $invoice_ids = array_column($invoices, 'ap_invoice_id');
        $this->db->where_in('ap_invoice_id', $invoice_ids)->update('ap_invoice', [
            'kontra_bon_id' => $kontra_bon_id,
            'due_date'      => $due_date,
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Gagal membuat kontra bon');
        }

        return $kontra_bon_id;
    }

    /**
     * Dipanggil oleh Ap_kontra_bon_payment_m setelah distribusi pembayaran ke nota-nota.
     */
    public function update_paid_amount($kontra_bon_id, $paid_delta)
    {
        $kb = $this->db->where('kontra_bon_id', $kontra_bon_id)->get('ap_kontra_bon')->row();
        if (!$kb) return false;

        $new_paid = $kb->paid_amount + $paid_delta;
        $new_outstanding = $kb->total_amount - $new_paid;
        $status = $new_outstanding <= 0 ? 'paid' : ($new_paid > 0 ? 'partial' : 'outstanding');

        $this->db->where('kontra_bon_id', $kontra_bon_id)->update('ap_kontra_bon', [
            'paid_amount'        => $new_paid,
            'outstanding_amount' => $new_outstanding,
            'status'             => $status,
        ]);

        return true;
    }

    public function void($id, $reason, $user_id)
    {
        $kb = $this->db->where('kontra_bon_id', $id)->get('ap_kontra_bon')->row();
        if (!$kb) throw new Exception('Kontra bon tidak ditemukan');
        if ($kb->paid_amount > 0) {
            throw new Exception('Kontra bon sudah ada pembayaran, tidak bisa di-void.');
        }

        $this->db->trans_start();

        $invoices = $this->get_invoices($id);
        $supplier = $this->db->where('supplier_id', $kb->supplier_id)->get('supplier')->row();
        foreach ($invoices as $inv) {
            $original_due = date('Y-m-d', strtotime($inv->invoice_date . ' +' . (int) $supplier->payment_term_days . ' days'));
            $this->db->where('ap_invoice_id', $inv->ap_invoice_id)->update('ap_invoice', [
                'kontra_bon_id' => null,
                'due_date'      => $original_due,
            ]);
        }

        $this->db->where('kontra_bon_id', $id)->update('ap_kontra_bon', [
            'status'      => 'void',
            'void_reason' => $reason,
            'voided_by'   => $user_id,
            'voided_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_json()
    {
        $draw   = intval($this->input->post('draw'));
        $search = $this->input->post('search')['value'] ?? '';

        $base = function () {
            $this->db->select('ap_kontra_bon.*, supplier.nama_supplier');
            $this->db->from('ap_kontra_bon');
            $this->db->join('supplier', 'supplier.supplier_id = ap_kontra_bon.supplier_id');
        };

        $base();
        $total = $this->db->count_all_results();

        $base();
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ap_kontra_bon.kontra_bon_no', $search);
            $this->db->or_like('supplier.nama_supplier', $search);
            $this->db->group_end();
        }
        $filtered = $this->db->count_all_results('', false);

        $start  = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->order_by('ap_kontra_bon.kontra_bon_id', 'DESC')->get()->result();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ];
    }
}
