<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Absensi extends CI_Controller {

    function __construct(){
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2]);
        $this->load->model('Absensi_m');
        $this->load->library('fungsi');
    }

    public function index($tahun = null, $bulan = null, $tanggal_hari = null)
    {
        $tanggal = ($tahun && $bulan && $tanggal_hari)
            ? sprintf('%04d-%02d-%02d', $tahun, $bulan, $tanggal_hari)
            : date('Y-m-d');

        $data['title']         = 'Absensi & Uang Makan';
        $data['tanggal']       = $tanggal;
        $data['karyawan_list'] = $this->Absensi_m->get_karyawan_aktif();
        $data['hadir_ids']     = $this->Absensi_m->get_absensi($tanggal);
        $data['tarif']         = $this->Absensi_m->get_tarif();
        $data['is_processed']  = $this->Absensi_m->is_processed($tanggal);
        $data['level']         = $this->fungsi->user_login()->level;

        $this->template->load('template', 'karyawan/absensi_form', $data);
    }

    public function save()
    {
        if ($this->input->method() !== 'post') show_404();

        $tanggal      = $this->input->post('tanggal');
        $karyawan_ids = (array) $this->input->post('karyawan_id');

        $this->Absensi_m->save_absensi($tanggal, $karyawan_ids, $this->fungsi->user_login()->user_id);
        $this->session->set_flashdata('success', 'Kehadiran tanggal ' . $tanggal . ' berhasil disimpan.');
        redirect('absensi/' . $tanggal);
    }

    public function process()
    {
        if ($this->input->method() !== 'post') show_404();

        $tanggal      = $this->input->post('tanggal');
        $karyawan_ids = (array) $this->input->post('karyawan_id');

        try {
            $this->Absensi_m->save_absensi($tanggal, $karyawan_ids, $this->fungsi->user_login()->user_id);
            $this->Absensi_m->process($tanggal, $this->fungsi->user_login()->user_id);
            $this->session->set_flashdata('success', 'Uang makan tanggal ' . $tanggal . ' berhasil diproses.');
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('absensi/' . $tanggal);
    }

    public function update_tarif()
    {
        check_allowed_levels([1]);
        if ($this->input->method() !== 'post') show_404();

        $tarif   = (int) str_replace('.', '', $this->input->post('tarif'));
        $tanggal = $this->input->post('tanggal');

        if ($tarif <= 0) {
            $this->session->set_flashdata('error', 'Tarif harus lebih dari 0.');
        } else {
            $this->Absensi_m->update_tarif($tarif, $this->fungsi->user_login()->user_id);
            $this->session->set_flashdata('success', 'Tarif uang makan berhasil diperbarui.');
        }
        redirect('absensi/' . $tanggal);
    }

    public function history()
    {
        $data['title'] = 'Riwayat Uang Makan';
        $this->template->load('template', 'karyawan/uang_makan_data', $data);
    }

    public function history_json()
    {
        echo json_encode($this->Absensi_m->get_history_json());
    }

    public function void($id)
    {
        check_allowed_levels([1]);
        if ($this->input->method() !== 'post') show_404();

        try {
            $this->Absensi_m->void($id, $this->input->post('void_reason', TRUE), $this->fungsi->user_login()->user_id);
            $this->session->set_flashdata('success', 'Uang makan berhasil dibatalkan.');
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('absensi/history');
    }
}
