<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ar_invoice extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2, 3]);
        $this->load->model('Ar_invoice_m');
        $this->load->model('Coa_m');
        $this->load->model('customer_m');
        $this->load->library('fungsi');
    }

    public function index()
    {
        $data['title'] = 'Piutang (AR)';
        $this->template->load('template', 'finance/ar_invoice/ar_invoice_data', $data);
    }

    public function get_json()
    {
        echo json_encode($this->Ar_invoice_m->get_json());
    }

    public function add()
    {
        check_allowed_levels([1, 2]);

        if ($this->input->method() === 'post') {
            $post = [
                'customer_id'  => (int) $this->input->post('customer_id'),
                'invoice_date' => $this->input->post('invoice_date'),
                'due_date'     => $this->input->post('due_date') ?: null,
                'amount'       => (int) str_replace('.', '', $this->input->post('amount')),
                'description'  => $this->input->post('description', TRUE),
                'lawan_coa_id' => $this->input->post('lawan_coa_id'),
            ];

            if (empty($post['customer_id']) || $post['amount'] <= 0 || empty($post['lawan_coa_id'])) {
                $this->session->set_flashdata('error', 'Customer, jumlah, dan akun lawan wajib diisi.');
                redirect('ar-invoice/add');
            }

            try {
                $this->Ar_invoice_m->create_manual($post, $this->fungsi->user_login()->user_id);
                $this->session->set_flashdata('success', 'Piutang manual berhasil dibuat.');
            } catch (Exception $e) {
                $this->session->set_flashdata('error', $e->getMessage());
                redirect('ar-invoice/add');
            }
            redirect('ar-invoice');
        }

        $data['title']     = 'Tambah Piutang Manual';
        $data['customers'] = $this->customer_m->get()->result();
        $data['coa_list']  = $this->Coa_m->get_all_postable();
        $this->template->load('template', 'finance/ar_invoice/ar_invoice_form', $data);
    }

    public function detail($id)
    {
        $ar = $this->Ar_invoice_m->get($id);
        if (!$ar) show_404();

        $this->load->model('Ar_payment_m');
        $data['title']    = 'Detail Piutang ' . $ar->ar_no;
        $data['ar']       = $ar;
        $data['payments'] = $this->Ar_payment_m->get_by_invoice($id);
        $this->template->load('template', 'finance/ar_invoice/ar_invoice_detail', $data);
    }

    public function void($id)
    {
        check_allowed_levels([1]);
        if ($this->input->method() !== 'post') show_404();

        try {
            $this->Ar_invoice_m->void($id, $this->input->post('void_reason', TRUE), $this->fungsi->user_login()->user_id);
            $this->session->set_flashdata('success', 'Invoice piutang berhasil dibatalkan.');
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('ar-invoice/detail/' . $id);
    }

    public function refresh_all_due_dates()
    {
        check_allowed_levels([1, 2]);
        if ($this->input->method() !== 'post') show_404();

        $updated = $this->Ar_invoice_m->recalc_all_due_dates();
        $this->session->set_flashdata('success', $updated > 0
            ? $updated . ' jatuh tempo invoice berhasil diperbarui mengikuti termin customer terkini.'
            : 'Semua jatuh tempo sudah sesuai, tidak ada yang perlu diperbarui.');
        redirect('ar-invoice');
    }
}
