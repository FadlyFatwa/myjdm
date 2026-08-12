<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Return_m extends CI_Model {

    public function add_return($data)
    {
        $params = [
            'sale_id' => $data['sale_id'],
            'invoice' => $data['invoice'],
            'date' => $data['date'],
            'total_return_amount' => $data['total_return'],
            'note' => $data['note'],
            'customer_id' => $data['customer_id'],
            'user_id' => $this->session->userdata('userid'),
            'create' => date('Y-m-d H:i:s') // Menyimpan tanggal saat ini
        ];
        $this->db->insert('t_return', $params);
        return $this->db->insert_id();
    }

    public function add_return_detail($params)
    {
        // sale_id sudah disertakan langsung oleh controller (dari data form),
        // bukan di-query ulang lewat return_id -> menghindari sale_id NULL
        // jika insert t_return sebelumnya gagal/tertunda.
        $this->db->insert_batch('t_return_detail', $params);
    }

    public function get_return($return_id = null)
    {
        $this->db->select('*');
        $this->db->from('t_return');
        $this->db->join('user', 't_return.user_id = user.user_id','left');
        $this->db->join('customer', 't_return.customer_id = customer.customer_id','left');
        if ($return_id != null) {
            $this->db->where('return_id', $return_id);
        }
        $this->db->order_by('create', 'desc'); // Mengurutkan berdasarkan tanggal create
        $query = $this->db->get();
        return $query;
    }

    public function get_return_detail($return_id = null)
    {
        $this->db->from('t_return_detail');
        $this->db->join('p_item', 't_return_detail.item_id = p_item.item_id');
        
        if ($return_id != null) {
            $this->db->where('return_id', $return_id);
        }
        $query = $this->db->get();
        return $query;
    }

    // public function get_sale_with_return($sale_id = null)
    // {
    //     $this->db->select('*, customer.nama_customer, user.username as user_name,
    //                         t_sale.create as sale_create, t_return.total_return_amount');
    //     $this->db->from('t_sale');
    //     $this->db->join('customer', 't_sale.customer_id = customer.customer_id', 'left');
    //     $this->db->join('user', 't_sale.user_id = user.user_id');
    //     $this->db->join('t_return', 't_sale.sale_id = t_return.sale_id', 'left');
    //     if ($sale_id != null) {
    //         $this->db->where('t_sale.sale_id', $sale_id);
    //     }
    //     $this->db->order_by('date', 'desc');
    //     $query = $this->db->get();
    //     return $query;
    // }

    public function delete_return($return_id)
    {
        $this->db->where('return_id', $return_id);
        $this->db->delete('t_return');
    }

    public function get_returned_items($sale_id) {
        $this->db->select('item_id, SUM(qty) as returned_qty');
        $this->db->where('sale_id', $sale_id);
        $this->db->group_by('item_id');
        $query = $this->db->get('t_return_detail');
        return $query->result();
    }
}