<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Beban_m extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Journal_m');
        $this->load->model('Coa_m');
    }

    public function expense_no()
    {
        $sql = "SELECT
                    DATE_FORMAT(CURDATE(), '%y%m') AS today,
                    MAX(MID(expense_no, 7, 4)) AS last_no
                FROM beban_operasional
                WHERE MID(expense_no, 3, 4) = DATE_FORMAT(CURDATE(), '%y%m')";
        $row = $this->db->query($sql)->row();

        $today = $row->today;
        if ($row->last_no !== null) {
            $n  = ((int) $row->last_no) + 1;
            $no = sprintf("%'.04d", $n);
        } else {
            $no = '0001';
        }
        return 'BB' . $today . $no;
    }

    public function get($id)
    {
        $this->db->select('beban_operasional.*, finance_coa.coa_code, finance_coa.coa_name, user.nama AS created_by_name');
        $this->db->from('beban_operasional');
        $this->db->join('finance_coa', 'finance_coa.coa_id = beban_operasional.coa_id');
        $this->db->join('user', 'user.user_id = beban_operasional.created_by');
        $this->db->where('beban_operasional.expense_id', $id);
        return $this->db->get()->row();
    }

    /**
     * $post: expense_date, coa_id, amount, payment_method, description
     */
    public function create($post, $user_id)
    {
        $coa = $this->Coa_m->get($post['coa_id']);
        if (!$coa) throw new Exception('Kategori beban tidak ditemukan');

        $amount = (int) $post['amount'];
        if ($amount <= 0) throw new Exception('Jumlah beban harus lebih dari 0');

        $this->db->trans_start();

        $expense_no = $this->expense_no();

        $kas_akun = $post['payment_method'] === 'cash'
            ? $this->Coa_m->get_by_subtype('kas')
            : $this->Coa_m->get_by_subtype('bank');

        $journal_id = $this->Journal_m->post([
            'journal_date' => $post['expense_date'],
            'source_type'  => 'beban_operasional',
            'source_id'    => null,
            'description'  => 'Beban ' . $coa->coa_name . ': ' . $post['description'],
            'created_by'   => $user_id,
        ], [
            ['coa_id' => $coa->coa_id, 'debit' => $amount, 'kredit' => 0, 'notes' => $expense_no],
            ['coa_id' => $kas_akun->coa_id, 'debit' => 0, 'kredit' => $amount, 'notes' => $expense_no],
        ]);

        $this->db->insert('beban_operasional', [
            'expense_no'     => $expense_no,
            'expense_date'   => $post['expense_date'],
            'coa_id'         => $post['coa_id'],
            'amount'         => $amount,
            'payment_method' => $post['payment_method'],
            'description'    => $post['description'],
            'journal_id'     => $journal_id,
            'created_by'     => $user_id,
        ]);
        $expense_id = $this->db->insert_id();

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Gagal mencatat beban operasional');
        }

        return $expense_id;
    }

    public function void($id, $reason, $user_id)
    {
        $expense = $this->db->where('expense_id', $id)->get('beban_operasional')->row();
        if (!$expense) throw new Exception('Data beban tidak ditemukan');
        if ($expense->is_void) throw new Exception('Beban ini sudah di-void sebelumnya');

        $this->db->trans_start();

        $this->db->where('expense_id', $id)->update('beban_operasional', [
            'is_void'     => 1,
            'void_reason' => $reason,
            'voided_by'   => $user_id,
            'voided_at'   => date('Y-m-d H:i:s'),
        ]);

        if ($expense->journal_id) {
            $this->Journal_m->void($expense->journal_id, 'Beban ' . $expense->expense_no . ' di-void: ' . $reason, $user_id);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_json()
    {
        $draw   = intval($this->input->post('draw'));
        $search = $this->input->post('search')['value'] ?? '';

        $base = function () {
            $this->db->select('beban_operasional.*, finance_coa.coa_name');
            $this->db->from('beban_operasional');
            $this->db->join('finance_coa', 'finance_coa.coa_id = beban_operasional.coa_id');
        };

        $base();
        $total = $this->db->count_all_results();

        $base();
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('beban_operasional.expense_no', $search);
            $this->db->or_like('beban_operasional.description', $search);
            $this->db->or_like('finance_coa.coa_name', $search);
            $this->db->group_end();
        }
        $filtered = $this->db->count_all_results('', false);

        $start  = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->order_by('beban_operasional.expense_id', 'DESC')->get()->result();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ];
    }
}
