<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Journal_m extends CI_Model {

    public function journal_no()
    {
        $sql = "SELECT
                    DATE_FORMAT(CURDATE(), '%y%m') AS today,
                    MAX(MID(journal_no, 7, 4)) AS last_no
                FROM finance_journal
                WHERE MID(journal_no, 3, 4) = DATE_FORMAT(CURDATE(), '%y%m')";
        $row = $this->db->query($sql)->row();

        $today = $row->today;
        if ($row->last_no !== null) {
            $n  = ((int) $row->last_no) + 1;
            $no = sprintf("%'.04d", $n);
        } else {
            $no = '0001';
        }
        return 'JU' . $today . $no;
    }

    /**
     * Posting jurnal double-entry. $lines = [['coa_id'=>, 'debit'=>, 'kredit'=>, 'notes'=>], ...]
     * Melempar Exception jika total debit != total kredit (validasi balance wajib di sini,
     * bukan di DB constraint, konsisten dengan gaya validasi project ini).
     */
    public function post(array $header, array $lines)
    {
        $total_debit  = array_sum(array_column($lines, 'debit'));
        $total_kredit = array_sum(array_column($lines, 'kredit'));

        if ($total_debit !== $total_kredit) {
            throw new Exception('Jurnal tidak balance: debit ' . $total_debit . ' != kredit ' . $total_kredit);
        }
        if ($total_debit <= 0) {
            throw new Exception('Jumlah jurnal harus lebih dari 0');
        }

        $this->db->trans_start();

        $this->db->insert('finance_journal', [
            'journal_no'   => $this->journal_no(),
            'journal_date' => $header['journal_date'],
            'source_type'  => $header['source_type'],
            'source_id'    => $header['source_id'] ?? null,
            'description'  => $header['description'],
            'total_debit'  => $total_debit,
            'total_kredit' => $total_kredit,
            'status'       => 'posted',
            'created_by'   => $header['created_by'],
        ]);
        $journal_id = $this->db->insert_id();

        foreach ($lines as $line) {
            $this->db->insert('finance_journal_detail', [
                'journal_id' => $journal_id,
                'coa_id'     => $line['coa_id'],
                'debit'      => $line['debit'] ?? 0,
                'kredit'     => $line['kredit'] ?? 0,
                'notes'      => $line['notes'] ?? null,
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Gagal posting jurnal ' . $header['source_type']);
        }

        return $journal_id;
    }

    public function void($journal_id, $reason, $user_id)
    {
        return $this->db->where('journal_id', $journal_id)->update('finance_journal', [
            'status'      => 'void',
            'void_reason' => $reason,
            'voided_by'   => $user_id,
            'voided_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function get($id)
    {
        return $this->db->where('journal_id', $id)->get('finance_journal')->row();
    }

    public function get_by_source($source_type, $source_id)
    {
        return $this->db
            ->where('source_type', $source_type)
            ->where('source_id', $source_id)
            ->where('status', 'posted')
            ->get('finance_journal')
            ->row();
    }

    public function get_detail($journal_id)
    {
        $this->db->select('finance_journal_detail.*, finance_coa.coa_code, finance_coa.coa_name');
        $this->db->from('finance_journal_detail');
        $this->db->join('finance_coa', 'finance_coa.coa_id = finance_journal_detail.coa_id');
        $this->db->where('finance_journal_detail.journal_id', $journal_id);
        return $this->db->get()->result();
    }

    public function get_json()
    {
        $draw   = intval($this->input->post('draw'));
        $search = $this->input->post('search')['value'] ?? '';

        $this->db->from('finance_journal');
        $this->db->join('user', 'user.user_id = finance_journal.created_by');
        $total = $this->db->count_all_results();

        $this->db->select('finance_journal.*, user.nama AS created_by_name');
        $this->db->from('finance_journal');
        $this->db->join('user', 'user.user_id = finance_journal.created_by');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('finance_journal.journal_no', $search);
            $this->db->or_like('finance_journal.description', $search);
            $this->db->group_end();
        }
        $filtered = $this->db->count_all_results('', false);

        $start  = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->order_by('finance_journal.journal_id', 'DESC')->get()->result();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ];
    }

    public function get_ledger($coa_id, $from, $to)
    {
        $this->db->from('v_general_ledger');
        $this->db->where('coa_id', $coa_id);
        $this->db->where('journal_date >=', $from);
        $this->db->where('journal_date <=', $to);
        $this->db->order_by('journal_date', 'ASC');
        return $this->db->get()->result();
    }
}
