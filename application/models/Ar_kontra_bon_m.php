<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ar_kontra_bon_m extends CI_Model {

    public function kontra_bon_no()
    {
        $sql = "SELECT
                    DATE_FORMAT(CURDATE(), '%y%m') AS today,
                    MAX(MID(kontra_bon_no, 7, 4)) AS last_no
                FROM ar_kontra_bon
                WHERE MID(kontra_bon_no, 3, 4) = DATE_FORMAT(CURDATE(), '%y%m')";
        $row = $this->db->query($sql)->row();

        $today = $row->today;
        if ($row->last_no !== null) {
            $n  = ((int) $row->last_no) + 1;
            $no = sprintf("%'.04d", $n);
        } else {
            $no = '0001';
        }
        return 'KB' . $today . $no;
    }

    /**
     * Preview nota yang akan tergabung — dipanggil AJAX sebelum submit form create().
     */
    public function get_outstanding_invoices($customer_id, $from, $to)
    {
        $this->db->select('ar_invoice.*, customer.nama_customer, t_sale.invoice AS sale_invoice');
        $this->db->from('ar_invoice');
        $this->db->join('customer', 'customer.customer_id = ar_invoice.customer_id');
        $this->db->join('t_sale', 't_sale.sale_id = ar_invoice.sale_id', 'left');
        $this->db->where('ar_invoice.customer_id', $customer_id);
        $this->db->where('ar_invoice.invoice_date >=', $from);
        $this->db->where('ar_invoice.invoice_date <=', $to);
        $this->db->where_in('ar_invoice.status', ['outstanding', 'partial']);
        $this->db->where('ar_invoice.kontra_bon_id', null);
        $this->db->order_by('ar_invoice.invoice_date', 'ASC');
        return $this->db->get()->result();
    }

    public function get($id)
    {
        $this->db->select('ar_kontra_bon.*, customer.nama_customer, customer.phone');
        $this->db->from('ar_kontra_bon');
        $this->db->join('customer', 'customer.customer_id = ar_kontra_bon.customer_id');
        $this->db->where('ar_kontra_bon.kontra_bon_id', $id);
        return $this->db->get()->row();
    }

    public function get_invoices($kontra_bon_id)
    {
        $this->db->select('ar_invoice.*, t_sale.invoice AS sale_invoice');
        $this->db->from('ar_invoice');
        $this->db->join('t_sale', 't_sale.sale_id = ar_invoice.sale_id', 'left');
        $this->db->where('ar_invoice.kontra_bon_id', $kontra_bon_id);
        $this->db->order_by('ar_invoice.invoice_date', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * $post: customer_id, period_start, period_end, due_date (opsional override)
     */
    public function create($post, $user_id)
    {
        $customer = $this->db->where('customer_id', $post['customer_id'])->get('customer')->row();
        if (!$customer) throw new Exception('Customer tidak ditemukan');

        $invoices = $this->get_outstanding_invoices($post['customer_id'], $post['period_start'], $post['period_end']);
        if (empty($invoices)) {
            throw new Exception('Tidak ada nota outstanding pada rentang tanggal tersebut.');
        }

        $due_date = !empty($post['due_date'])
            ? $post['due_date']
            : date('Y-m-d', strtotime($post['period_end'] . ' +' . (int) $customer->payment_term_days . ' days'));

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
        $this->db->insert('ar_kontra_bon', [
            'kontra_bon_no'      => $kontra_bon_no,
            'customer_id'        => $post['customer_id'],
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

        $invoice_ids = array_column($invoices, 'ar_invoice_id');
        $this->db->where_in('ar_invoice_id', $invoice_ids)->update('ar_invoice', [
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
     * Dipanggil oleh Ar_kontra_bon_payment_m setelah distribusi pembayaran ke nota-nota.
     */
    public function update_paid_amount($kontra_bon_id, $paid_delta)
    {
        $kb = $this->db->where('kontra_bon_id', $kontra_bon_id)->get('ar_kontra_bon')->row();
        if (!$kb) return false;

        $new_paid = $kb->paid_amount + $paid_delta;
        $new_outstanding = $kb->total_amount - $new_paid;
        $status = $new_outstanding <= 0 ? 'paid' : ($new_paid > 0 ? 'partial' : 'outstanding');

        $this->db->where('kontra_bon_id', $kontra_bon_id)->update('ar_kontra_bon', [
            'paid_amount'        => $new_paid,
            'outstanding_amount' => $new_outstanding,
            'status'             => $status,
        ]);

        return true;
    }

    public function void($id, $reason, $user_id)
    {
        $kb = $this->db->where('kontra_bon_id', $id)->get('ar_kontra_bon')->row();
        if (!$kb) throw new Exception('Kontra bon tidak ditemukan');
        if ($kb->paid_amount > 0) {
            throw new Exception('Kontra bon sudah ada pembayaran, tidak bisa di-void.');
        }

        $this->db->trans_start();

        $invoices = $this->get_invoices($id);
        $customer = $this->db->where('customer_id', $kb->customer_id)->get('customer')->row();
        foreach ($invoices as $inv) {
            $original_due = date('Y-m-d', strtotime($inv->invoice_date . ' +' . (int) $customer->payment_term_days . ' days'));
            $this->db->where('ar_invoice_id', $inv->ar_invoice_id)->update('ar_invoice', [
                'kontra_bon_id' => null,
                'due_date'      => $original_due,
            ]);
        }

        $this->db->where('kontra_bon_id', $id)->update('ar_kontra_bon', [
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
            $this->db->select('ar_kontra_bon.*, customer.nama_customer');
            $this->db->from('ar_kontra_bon');
            $this->db->join('customer', 'customer.customer_id = ar_kontra_bon.customer_id');
        };

        $base();
        $total = $this->db->count_all_results();

        $base();
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ar_kontra_bon.kontra_bon_no', $search);
            $this->db->or_like('customer.nama_customer', $search);
            $this->db->group_end();
        }
        $filtered = $this->db->count_all_results('', false);

        $start  = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->order_by('ar_kontra_bon.kontra_bon_id', 'DESC')->get()->result();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ];
    }
}
