<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_beban_m extends CI_Model {

    /**
     * Detail beban operasional per periode (expense_date). Menampilkan SEMUA
     * baris termasuk yang sudah di-void (ditandai beda di tampilan).
     */
    public function get_period_list($from, $to, $coa_id = '')
    {
        $this->db->select('beban_operasional.*, finance_coa.coa_name');
        $this->db->from('beban_operasional');
        $this->db->join('finance_coa', 'finance_coa.coa_id = beban_operasional.coa_id');
        $this->db->where('beban_operasional.expense_date >=', $from);
        $this->db->where('beban_operasional.expense_date <=', $to);
        if (!empty($coa_id)) {
            $this->db->where('beban_operasional.coa_id', $coa_id);
        }
        $this->db->order_by('beban_operasional.expense_date', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Total per kategori COA, exclude yang void. Dipakai untuk ringkasan di atas
     * tabel detail, mirror pola Report_ar_m::get_aging_summary_by_customer().
     */
    public function get_summary_by_category($from, $to)
    {
        $this->db->select('beban_operasional.coa_id, finance_coa.coa_name, SUM(beban_operasional.amount) AS total', false);
        $this->db->from('beban_operasional');
        $this->db->join('finance_coa', 'finance_coa.coa_id = beban_operasional.coa_id');
        $this->db->where('beban_operasional.expense_date >=', $from);
        $this->db->where('beban_operasional.expense_date <=', $to);
        $this->db->where('beban_operasional.is_void', 0);
        $this->db->group_by('beban_operasional.coa_id, finance_coa.coa_name');
        $this->db->order_by('total', 'DESC');
        return $this->db->get()->result();
    }
}
