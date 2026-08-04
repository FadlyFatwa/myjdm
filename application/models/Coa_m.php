<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coa_m extends CI_Model {

    public function get($id)
    {
        return $this->db->where('coa_id', $id)->get('finance_coa')->row();
    }

    public function get_by_subtype($subtype)
    {
        return $this->db->where('coa_subtype', $subtype)->where('is_active', 1)->get('finance_coa')->row();
    }

    public function get_all_postable()
    {
        return $this->db
            ->where('is_postable', 1)
            ->where('is_active', 1)
            ->order_by('coa_code', 'ASC')
            ->get('finance_coa')
            ->result();
    }

    public function get_parent_options()
    {
        return $this->db
            ->where('is_postable', 0)
            ->where('is_active', 1)
            ->order_by('coa_code', 'ASC')
            ->get('finance_coa')
            ->result();
    }

    public function get_json()
    {
        $draw   = intval($this->input->post('draw'));
        $search = $this->input->post('search')['value'] ?? '';

        $this->db->from('finance_coa');
        $this->db->join('finance_coa AS parent', 'finance_coa.parent_id = parent.coa_id', 'left');
        $this->db->where('finance_coa.is_active', 1);

        $total = $this->db->count_all_results();

        $this->db->select('finance_coa.*, parent.coa_name AS parent_name');
        $this->db->from('finance_coa');
        $this->db->join('finance_coa AS parent', 'finance_coa.parent_id = parent.coa_id', 'left');
        $this->db->where('finance_coa.is_active', 1);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('finance_coa.coa_code', $search);
            $this->db->or_like('finance_coa.coa_name', $search);
            $this->db->group_end();
        }

        $filtered = $this->db->count_all_results('', false);

        $start  = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->order_by('finance_coa.coa_code', 'ASC')->get()->result();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ];
    }

    public function insert($data)
    {
        $data['is_system'] = 0;
        $this->db->insert('finance_coa', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        return $this->db->where('coa_id', $id)->update('finance_coa', $data);
    }

    public function has_journal_entries($id)
    {
        return $this->db->where('coa_id', $id)->count_all_results('finance_journal_detail') > 0;
    }

    public function delete($id)
    {
        // Soft delete saja — akun bisa direferensikan histori jurnal
        return $this->db->where('coa_id', $id)->update('finance_coa', ['is_active' => 0]);
    }
}
