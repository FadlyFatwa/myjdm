<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Beban extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2]);
        $this->load->model('Beban_m');
        $this->load->model('Coa_m');
        $this->load->library('fungsi');
    }

    public function index()
    {
        $data['title'] = 'Beban Operasional';
        $this->template->load('template', 'finance/beban/beban_data', $data);
    }

    public function get_json()
    {
        echo json_encode($this->Beban_m->get_json());
    }

    public function add()
    {
        if ($this->input->method() === 'post') {
            $post = [
                'expense_date'   => $this->input->post('expense_date'),
                'coa_id'         => (int) $this->input->post('coa_id'),
                'amount'         => (int) str_replace('.', '', $this->input->post('amount')),
                'payment_method' => $this->input->post('payment_method'),
                'description'    => $this->input->post('description', TRUE),
            ];

            if (empty($post['coa_id']) || $post['amount'] <= 0 || empty($post['description'])) {
                $this->session->set_flashdata('error', 'Kategori beban, jumlah, dan keterangan wajib diisi.');
                redirect('beban/add');
            }

            try {
                $this->Beban_m->create($post, $this->fungsi->user_login()->user_id);
                $this->session->set_flashdata('success', 'Beban operasional berhasil dicatat.');
            } catch (Exception $e) {
                $this->session->set_flashdata('error', $e->getMessage());
                redirect('beban/add');
            }
            redirect('beban');
        }

        $data['title']    = 'Catat Beban Operasional';
        $data['coa_list'] = $this->db
            ->where('coa_type', 'beban')
            ->where('is_postable', 1)
            ->where('is_active', 1)
            ->order_by('coa_code', 'ASC')
            ->get('finance_coa')->result();
        $this->template->load('template', 'finance/beban/beban_form', $data);
    }

    public function void($id)
    {
        check_allowed_levels([1]);
        if ($this->input->method() !== 'post') show_404();

        try {
            $this->Beban_m->void($id, $this->input->post('void_reason', TRUE), $this->fungsi->user_login()->user_id);
            $this->session->set_flashdata('success', 'Beban operasional berhasil dibatalkan.');
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('beban');
    }
}
