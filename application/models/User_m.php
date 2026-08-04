<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_m extends CI_Model {

    public function login($post)
    {
        $this->db->select('*');
        $this->db->from('user');
        $this->db->where('username', $post['username']);
        $query = $this->db->get();

        if ($query->num_rows() === 0) return false;

        $row = $query->row();

        // Migrasi otomatis: SHA1 (40 char) → bcrypt
        if (strlen($row->password) === 40) {
            if ($row->password !== sha1($post['password'])) return false;
            // Upgrade ke bcrypt saat login berhasil
            $this->db->where('user_id', $row->user_id);
            $this->db->update('user', ['password' => password_hash($post['password'], PASSWORD_BCRYPT)]);
        } else {
            if (!password_verify($post['password'], $row->password)) return false;
        }

        return $row;
    }

    public function get($id = null)
    {
        $this->db->from('user');
        if($id != null) {
            $this->db->where('user_id', $id);
        }
        $query = $this->db->get();
        return $query;
    }

    public function add($post)
    {
        $params['nama'] = $post['fullname'];
        $params['username'] = $post['username'];
        $params['password'] = password_hash($post['password'], PASSWORD_BCRYPT);
        $params['level'] = $post['level'];
        $this->db->insert('user', $params);
    }

    public function edit($post)
    {
        $params['nama'] = $post['fullname'];
        $params['username'] = $post['username'];
        if(!empty($post['password'])) {
            $params['password'] = password_hash($post['password'], PASSWORD_BCRYPT);
        }
        $params['level'] = $post['level'];
        $this->db->where('user_id', $post['user_id']);
        $this->db->update('user', $params);
    }

    public function del($id)
	{
        $this->db->where('user_id', $id);
        $this->db->delete('user');
    }

}