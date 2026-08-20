<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Karyawan_m extends CI_Model {

    public function get($id = null)
    {
        $this->db->select('karyawan.*, user.nama AS user_nama');
        $this->db->from('karyawan');
        $this->db->join('user', 'user.user_id = karyawan.user_id', 'left');
        if ($id !== null) {
            $this->db->where('karyawan.karyawan_id', $id);
            return $this->db->get();
        }
        $this->db->order_by('karyawan.nama', 'ASC');
        return $this->db->get();
    }

    public function get_list()
    {
        $this->db->select('karyawan.*, user.nama AS user_nama');
        $this->db->from('karyawan');
        $this->db->join('user', 'user.user_id = karyawan.user_id', 'left');
        $this->db->where('karyawan.is_active', 1);
        $this->db->order_by('karyawan.nama', 'ASC');
        return $this->db->get();
    }

    public function get_active()
    {
        $this->db->where('is_active', 1);
        $this->db->order_by('nama', 'ASC');
        return $this->db->get('karyawan')->result();
    }

    public function add($post)
    {
        $params = [
            'nama'    => $post['nama'],
            'user_id' => $post['user_id'] === '' ? null : $post['user_id'],
        ];
        $this->db->insert('karyawan', $params);
    }

    public function edit($post)
    {
        $params = [
            'nama'    => $post['nama'],
            'user_id' => $post['user_id'] === '' ? null : $post['user_id'],
        ];
        $this->db->where('karyawan_id', $post['id']);
        $this->db->update('karyawan', $params);
    }

    public function del($id)
    {
        $this->db->where('karyawan_id', $id);
        $this->db->update('karyawan', ['is_active' => 0]);
    }
}
