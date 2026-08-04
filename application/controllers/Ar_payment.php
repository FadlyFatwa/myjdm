<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ar_payment extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2, 3]);
        $this->load->model('Ar_payment_m');
        $this->load->model('Ar_invoice_m');
        $this->load->library('fungsi');
    }

    public function add($ar_invoice_id)
    {
        $ar = $this->Ar_invoice_m->get($ar_invoice_id);
        if (!$ar) show_404();
        if (!in_array($ar->status, ['outstanding', 'partial'])) {
            $this->session->set_flashdata('error', 'Invoice ini tidak menerima pembayaran lagi.');
            redirect('ar-invoice/detail/' . $ar_invoice_id);
        }

        $data['title'] = 'Catat Pembayaran ' . $ar->ar_no;
        $data['ar']    = $ar;
        $this->template->load('template', 'finance/ar_payment/ar_payment_form', $data);
    }

    public function process()
    {
        check_allowed_levels([1, 2, 3]);

        $post = [
            'ar_invoice_id'  => (int) $this->input->post('ar_invoice_id'),
            'payment_date'   => $this->input->post('payment_date'),
            'amount'         => (int) str_replace('.', '', $this->input->post('amount')),
            'payment_method' => $this->input->post('payment_method'),
            'notes'          => $this->input->post('notes', TRUE),
        ];

        try {
            $this->Ar_payment_m->pay($post, $this->fungsi->user_login()->user_id);
            $this->session->set_flashdata('success', 'Pembayaran piutang berhasil dicatat.');
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
            redirect('ar-payment/add/' . $post['ar_invoice_id']);
        }
        redirect('ar-invoice/detail/' . $post['ar_invoice_id']);
    }

    public function void($id)
    {
        check_allowed_levels([1, 2]);
        if ($this->input->method() !== 'post') show_404();

        $payment = $this->Ar_payment_m->get($id);
        if (!$payment) show_404();

        try {
            $this->Ar_payment_m->void_payment($id, $this->input->post('void_reason', TRUE), $this->fungsi->user_login()->user_id);
            $this->session->set_flashdata('success', 'Pembayaran berhasil di-void.');
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('ar-invoice/detail/' . $payment->ar_invoice_id);
    }
}
