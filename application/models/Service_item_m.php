<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Service_item_m extends CI_Model {

    public function get_all()
    {
        return $this->db
            ->where('status', 'active')
            ->order_by('nama_jasa', 'ASC')
            ->get('p_jasa')
            ->result();
    }

    public function get($id)
    {
        return $this->db
            ->where('jasa_id', $id)
            ->get('p_jasa')
            ->row();
    }

    public function get_json()
    {
        $draw   = intval($this->input->post('draw'));
        $search = $this->input->post('search')['value'] ?? '';
        $start  = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));

        $total = $this->db
            ->where('status', 'active')
            ->count_all_results('p_jasa');

        $this->db->from('p_jasa');
        $this->db->where('status', 'active');

        if (!empty($search)) {
            $this->db->like('nama_jasa', $search);
        }

        $filtered = $this->db->count_all_results('', false);

        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->order_by('nama_jasa', 'ASC')->get()->result();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ];
    }

    public function insert($data)
    {
        $data['status'] = 'active';
        $this->db->insert('p_jasa', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('jasa_id', $id)->update('p_jasa', $data);
    }

    public function delete($id)
    {
        $this->db->where('jasa_id', $id)->update('p_jasa', ['status' => 'inactive']);
    }
}
