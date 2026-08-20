<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Supplier_m extends CI_Model {

    public function get($id = null){
        $this->db->from('supplier');
        if($id != null){
            $this->db->where('supplier_id', $id);
        }
        $query = $this->db->get();
        return $query;
    }

    public function add($post)
    {
        $params = [
            'nama_supplier' => $post['nama_supplier'],
            'phone' => $post['telp'],
            'alamat' => $post['alamat'],
            'keterangan' => empty($post['keterangan']) ? null : $post['keterangan'],
            'payment_term_days' => (int) ($post['payment_term_days'] ?? 0),
        ];
        $this->db->insert('supplier',$params);
    }

    public function edit($post)
    {
        $params = [
            'nama_supplier' => $post['nama_supplier'],
            'phone' => $post['telp'],
            'alamat' => $post['alamat'],
            'keterangan' => empty($post['keterangan']) ? null : $post['keterangan'],
            'payment_term_days' => (int) ($post['payment_term_days'] ?? 0),
        ];
        $this->db->where('supplier_id',$post['id']);
        $this->db->update('supplier',$params);
    }

    public function del($id)
    {
        $this->db->where('supplier_id', $id);
        $this->db->delete('supplier');
    }
}