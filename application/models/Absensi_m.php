<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Absensi_m extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Beban_m');
        $this->load->model('Coa_m');
    }

    public function get_karyawan_aktif()
    {
        return $this->db->where('is_active', 1)->order_by('nama', 'ASC')->get('karyawan')->result();
    }

    public function get_absensi($tanggal)
    {
        $rows = $this->db->where('tanggal', $tanggal)->get('absensi_harian')->result();
        return array_map(function ($r) { return (int) $r->karyawan_id; }, $rows);
    }

    public function save_absensi($tanggal, $karyawan_ids, $user_id)
    {
        $this->db->trans_start();

        $this->db->where('tanggal', $tanggal)->delete('absensi_harian');

        foreach ($karyawan_ids as $karyawan_id) {
            $this->db->insert('absensi_harian', [
                'tanggal'     => $tanggal,
                'karyawan_id' => (int) $karyawan_id,
                'created_by'  => $user_id,
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_tarif()
    {
        $row = $this->db->where('id', 1)->get('uang_makan_setting')->row();
        return $row ? (int) $row->tarif : 0;
    }

    public function update_tarif($tarif, $user_id)
    {
        $this->db->where('id', 1)->update('uang_makan_setting', [
            'tarif'      => (int) $tarif,
            'updated_by' => $user_id,
        ]);
    }

    public function is_processed($tanggal)
    {
        return $this->db->where('tanggal', $tanggal)->where('is_void', 0)->get('uang_makan')->num_rows() > 0;
    }

    public function get($id)
    {
        $this->db->select('uang_makan.*, user.nama AS created_by_name');
        $this->db->from('uang_makan');
        $this->db->join('user', 'user.user_id = uang_makan.created_by');
        $this->db->where('uang_makan.uang_makan_id', $id);
        return $this->db->get()->row();
    }

    /**
     * Hitung kehadiran hari itu x tarif, catat sebagai Beban Operasional (reuse Beban_m->create
     * supaya otomatis muncul di listing Beban & posting jurnal, persis pola ongkir di Purchase_order).
     */
    public function process($tanggal, $user_id)
    {
        if ($this->is_processed($tanggal)) {
            throw new Exception('Uang makan tanggal ' . $tanggal . ' sudah diproses sebelumnya');
        }

        $jumlah_karyawan = count($this->get_absensi($tanggal));
        if ($jumlah_karyawan === 0) {
            throw new Exception('Belum ada karyawan yang dicentang hadir pada tanggal ' . $tanggal);
        }

        $tarif = $this->get_tarif();
        if ($tarif <= 0) {
            throw new Exception('Tarif uang makan belum diatur');
        }

        $total_amount = $jumlah_karyawan * $tarif;

        $this->db->trans_start();

        $coa = $this->Coa_m->get_by_subtype('beban_uang_makan');
        if (!$coa) throw new Exception('Kategori COA Beban Uang Makan Karyawan tidak ditemukan');

        $expense_id = $this->Beban_m->create([
            'expense_date'   => $tanggal,
            'coa_id'         => $coa->coa_id,
            'amount'         => $total_amount,
            'payment_method' => 'cash',
            'description'    => 'Uang makan karyawan ' . $tanggal . ' - ' . $jumlah_karyawan . ' karyawan x Rp' . number_format($tarif, 0, ',', '.'),
        ], $user_id);

        $expense = $this->Beban_m->get($expense_id);

        $this->db->insert('uang_makan', [
            'tanggal'         => $tanggal,
            'jumlah_karyawan' => $jumlah_karyawan,
            'tarif'           => $tarif,
            'total_amount'    => $total_amount,
            'expense_id'      => $expense_id,
            'journal_id'      => $expense->journal_id,
            'created_by'      => $user_id,
        ]);
        $uang_makan_id = $this->db->insert_id();

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Gagal memproses uang makan');
        }

        return $uang_makan_id;
    }

    public function void($id, $reason, $user_id)
    {
        $um = $this->get($id);
        if (!$um) throw new Exception('Data uang makan tidak ditemukan');
        if ($um->is_void) throw new Exception('Uang makan ini sudah di-void sebelumnya');

        $this->db->trans_start();

        $this->db->where('uang_makan_id', $id)->update('uang_makan', [
            'is_void'     => 1,
            'void_reason' => $reason,
            'voided_by'   => $user_id,
            'voided_at'   => date('Y-m-d H:i:s'),
        ]);

        if ($um->expense_id) {
            $this->Beban_m->void($um->expense_id, 'Uang makan ' . $um->tanggal . ' di-void: ' . $reason, $user_id);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_history_json()
    {
        $draw   = intval($this->input->post('draw'));
        $search = $this->input->post('search')['value'] ?? '';

        $base = function () {
            $this->db->select('uang_makan.*, user.nama AS created_by_name');
            $this->db->from('uang_makan');
            $this->db->join('user', 'user.user_id = uang_makan.created_by');
        };

        $base();
        $total = $this->db->count_all_results();

        $base();
        if (!empty($search)) {
            $this->db->like('uang_makan.tanggal', $search);
        }
        $filtered = $this->db->count_all_results('', false);

        $start  = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->order_by('uang_makan.tanggal', 'DESC')->get()->result();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ];
    }
}
