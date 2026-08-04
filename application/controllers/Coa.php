<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coa extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2]);
        $this->load->model('Coa_m');
    }

    public function index()
    {
        $data['title'] = 'Chart of Accounts';
        $this->template->load('template', 'finance/coa/coa_data', $data);
    }

    public function get_json()
    {
        echo json_encode($this->Coa_m->get_json());
    }

    public function add()
    {
        check_allowed_levels([1]);

        if ($this->input->method() === 'post') {
            $data = [
                'coa_code'       => $this->input->post('coa_code', TRUE),
                'coa_name'       => $this->input->post('coa_name', TRUE),
                'coa_type'       => $this->input->post('coa_type', TRUE),
                'coa_subtype'    => $this->input->post('coa_subtype', TRUE) ?: null,
                'parent_id'      => $this->input->post('parent_id') ?: null,
                'normal_balance' => $this->input->post('normal_balance', TRUE),
                'is_postable'    => $this->input->post('is_postable') ? 1 : 0,
                'description'    => $this->input->post('description', TRUE) ?: null,
            ];

            if (empty($data['coa_code']) || empty($data['coa_name'])) {
                $this->session->set_flashdata('error', 'Kode dan nama akun wajib diisi.');
                redirect('coa/add');
            }

            $this->Coa_m->insert($data);
            $this->session->set_flashdata('success', 'Akun berhasil ditambahkan.');
            redirect('coa');
        }

        $data['title']   = 'Tambah Akun';
        $data['page']    = 'Tambah';
        $data['row']     = (object) [
            'coa_id' => '', 'coa_code' => '', 'coa_name' => '', 'coa_type' => '',
            'coa_subtype' => '', 'parent_id' => '', 'normal_balance' => '',
            'is_postable' => 1, 'description' => '',
        ];
        $data['parents'] = $this->Coa_m->get_parent_options();
        $this->template->load('template', 'finance/coa/coa_form', $data);
    }

    public function edit($id)
    {
        check_allowed_levels([1]);

        $row = $this->Coa_m->get($id);
        if (!$row) show_404();

        if ($this->input->method() === 'post') {
            $data = [
                'coa_name'       => $this->input->post('coa_name', TRUE),
                'coa_type'       => $this->input->post('coa_type', TRUE),
                'coa_subtype'    => $this->input->post('coa_subtype', TRUE) ?: null,
                'parent_id'      => $this->input->post('parent_id') ?: null,
                'normal_balance' => $this->input->post('normal_balance', TRUE),
                'is_postable'    => $this->input->post('is_postable') ? 1 : 0,
                'description'    => $this->input->post('description', TRUE) ?: null,
            ];

            $this->Coa_m->update($id, $data);
            $this->session->set_flashdata('success', 'Akun berhasil diperbarui.');
            redirect('coa');
        }

        $data['title']   = 'Edit Akun';
        $data['page']    = 'Edit';
        $data['row']     = $row;
        $data['parents'] = $this->Coa_m->get_parent_options();
        $this->template->load('template', 'finance/coa/coa_form', $data);
    }

    public function delete($id)
    {
        check_allowed_levels([1]);
        if ($this->input->method() !== 'post') show_404();

        $row = $this->Coa_m->get($id);
        if (!$row) show_404();

        if ($row->is_system) {
            $this->session->set_flashdata('error', 'Akun sistem tidak boleh dihapus.');
            redirect('coa');
        }
        if ($this->Coa_m->has_journal_entries($id)) {
            $this->session->set_flashdata('error', 'Akun sudah punya histori jurnal, tidak bisa dihapus.');
            redirect('coa');
        }

        $this->Coa_m->delete($id);
        $this->session->set_flashdata('success', 'Akun berhasil dihapus.');
        redirect('coa');
    }
}
