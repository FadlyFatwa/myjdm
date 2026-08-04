<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Service_item extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        $this->load->model('Service_item_m');
    }

    public function index()
    {
        $data['title'] = 'Master Jasa';
        $this->template->load('template', 'product/service_item/service_item_data', $data);
    }

    public function get_json()
    {
        echo json_encode($this->Service_item_m->get_json());
    }

    public function add()
    {
        if ($this->input->method() === 'post') {
            $data = [
                'nama_jasa' => $this->input->post('nama_jasa', TRUE),
                'tarif'     => (int) str_replace('.', '', $this->input->post('tarif')),
            ];

            if (empty($data['nama_jasa']) || $data['tarif'] <= 0) {
                $this->session->set_flashdata('error', 'Nama jasa dan tarif wajib diisi.');
                redirect('service-item/add');
            }

            $this->Service_item_m->insert($data);
            $this->session->set_flashdata('success', 'Item jasa berhasil ditambahkan.');
            redirect('service-item');
        }

        $data['title'] = 'Tambah Jasa';
        $data['row']   = (object) ['jasa_id' => '', 'nama_jasa' => '', 'tarif' => ''];
        $data['page']  = 'Tambah';
        $this->template->load('template', 'product/service_item/service_item_form', $data);
    }

    public function edit($id)
    {
        $row = $this->Service_item_m->get($id);
        if (!$row) show_404();

        if ($this->input->method() === 'post') {
            $data = [
                'nama_jasa' => $this->input->post('nama_jasa', TRUE),
                'tarif'     => (int) str_replace('.', '', $this->input->post('tarif')),
            ];

            $this->Service_item_m->update($id, $data);
            $this->session->set_flashdata('success', 'Item jasa berhasil diperbarui.');
            redirect('service-item');
        }

        $data['title'] = 'Edit Jasa';
        $data['row']   = $row;
        $data['page']  = 'Edit';
        $this->template->load('template', 'product/service_item/service_item_form', $data);
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') show_404();

        $this->Service_item_m->delete($id);
        $this->session->set_flashdata('success', 'Item jasa berhasil dihapus.');
        redirect('service-item');
    }
}
