<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ap_payment extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2]);
        $this->load->model('Ap_payment_m');
        $this->load->model('Ap_invoice_m');
        $this->load->library('fungsi');
    }

    public function add($ap_invoice_id)
    {
        $ap = $this->Ap_invoice_m->get($ap_invoice_id);
        if (!$ap) show_404();
        if (!in_array($ap->status, ['outstanding', 'partial'])) {
            $this->session->set_flashdata('error', 'Invoice ini tidak menerima pembayaran lagi.');
            redirect('ap-invoice/detail/' . $ap_invoice_id);
        }

        $data['title'] = 'Catat Pembayaran ' . $ap->ap_no;
        $data['ap']    = $ap;
        $this->template->load('template', 'finance/ap_payment/ap_payment_form', $data);
    }

    public function process()
    {
        check_allowed_levels([1, 2]);

        $post = [
            'ap_invoice_id'  => (int) $this->input->post('ap_invoice_id'),
            'payment_date'   => $this->input->post('payment_date'),
            'amount'         => (int) str_replace('.', '', $this->input->post('amount')),
            'payment_method' => $this->input->post('payment_method'),
            'notes'          => $this->input->post('notes', TRUE),
        ];

        try {
            $this->Ap_payment_m->pay($post, $this->fungsi->user_login()->user_id);
            $this->session->set_flashdata('success', 'Pembayaran hutang berhasil dicatat.');
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
            redirect('ap-payment/add/' . $post['ap_invoice_id']);
        }
        redirect('ap-invoice/detail/' . $post['ap_invoice_id']);
    }

    public function void($id)
    {
        check_allowed_levels([1, 2]);
        if ($this->input->method() !== 'post') show_404();

        $payment = $this->Ap_payment_m->get($id);
        if (!$payment) show_404();

        try {
            $this->Ap_payment_m->void_payment($id, $this->input->post('void_reason', TRUE), $this->fungsi->user_login()->user_id);
            $this->session->set_flashdata('success', 'Pembayaran berhasil di-void.');
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('ap-invoice/detail/' . $payment->ap_invoice_id);
    }
}
