<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_m extends CI_Model {

    public function get($id = null){
        $this->db->from('customer');
        if($id != null){
            $this->db->where('customer_id', $id);
        }
        $query = $this->db->get();
        return $query;
    }

    public function add($post)
    {
        $params = [
            'nama_customer' => $post['nama_customer'],
            'phone' => $post['telp'],
            'alamat' => $post['alamat'],
            'credit_limit' => $post['credit_limit'] ?? 0,
            'payment_term_days' => $post['payment_term_days'] ?? 0,
            'gross_discount_percent' => $post['gross_discount_percent'] ?: null,
        ];
        $this->db->insert('customer',$params);
    }

    public function edit($post)
    {
        $params = [
            'nama_customer' => $post['nama_customer'],
            'phone' => $post['telp'],
            'alamat' => $post['alamat'],
            'credit_limit' => $post['credit_limit'] ?? 0,
            'payment_term_days' => $post['payment_term_days'] ?? 0,
            'gross_discount_percent' => $post['gross_discount_percent'] ?: null,
        ];
        $this->db->where('customer_id',$post['id']);
        $this->db->update('customer',$params);
    }

    public function del($id)
    {
        $this->db->where('customer_id', $id);
        $this->db->delete('customer');
    }
}