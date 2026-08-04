<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ar_kontra_bon_payment extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2, 3]);
        $this->load->model('Ar_kontra_bon_payment_m');
        $this->load->model('Ar_kontra_bon_m');
        $this->load->library('fungsi');
    }

    public function add($kontra_bon_id)
    {
        $kb = $this->Ar_kontra_bon_m->get($kontra_bon_id);
        if (!$kb) show_404();
        if (!in_array($kb->status, ['outstanding', 'partial'])) {
            $this->session->set_flashdata('error', 'Kontra bon ini tidak menerima pembayaran lagi.');
            redirect('kontra-bon/detail/' . $kontra_bon_id);
        }

        $data['title'] = 'Bayar Kontra Bon ' . $kb->kontra_bon_no;
        $data['kb']    = $kb;
        $this->template->load('template', 'finance/ar_kontra_bon_payment/ar_kontra_bon_payment_form', $data);
    }

    public function process()
    {
        $post = [
            'kontra_bon_id'  => (int) $this->input->post('kontra_bon_id'),
            'payment_date'   => $this->input->post('payment_date'),
            'amount'         => (int) str_replace('.', '', $this->input->post('amount')),
            'payment_method' => $this->input->post('payment_method'),
            'notes'          => $this->input->post('notes', TRUE),
        ];

        try {
            $this->Ar_kontra_bon_payment_m->pay($post, $this->fungsi->user_login()->user_id);
            $this->session->set_flashdata('success', 'Pembayaran kontra bon berhasil dicatat.');
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
            redirect('kontra-bon-payment/add/' . $post['kontra_bon_id']);
        }
        redirect('kontra-bon/detail/' . $post['kontra_bon_id']);
    }

    public function void($id)
    {
        check_allowed_levels([1, 2]);
        if ($this->input->method() !== 'post') show_404();

        $payment = $this->Ar_kontra_bon_payment_m->get($id);
        if (!$payment) show_404();

        try {
            $this->Ar_kontra_bon_payment_m->void_payment($id, $this->input->post('void_reason', TRUE), $this->fungsi->user_login()->user_id);
            $this->session->set_flashdata('success', 'Pembayaran berhasil di-void.');
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('kontra-bon/detail/' . $payment->kontra_bon_id);
    }
}
