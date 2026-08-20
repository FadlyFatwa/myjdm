<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ap_invoice extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2, 3]);
        $this->load->model('Ap_invoice_m');
        $this->load->library('fungsi');
    }

    public function index()
    {
        $data['title'] = 'Hutang (AP)';
        $this->template->load('template', 'finance/ap_invoice/ap_invoice_data', $data);
    }

    public function get_json()
    {
        echo json_encode($this->Ap_invoice_m->get_json());
    }

    public function detail($id)
    {
        $ap = $this->Ap_invoice_m->get($id);
        if (!$ap) show_404();

        $this->load->model('Ap_payment_m');
        $data['title']    = 'Detail Hutang ' . $ap->ap_no;
        $data['ap']       = $ap;
        $data['payments'] = $this->Ap_payment_m->get_by_invoice($id);
        $this->template->load('template', 'finance/ap_invoice/ap_invoice_detail', $data);
    }

    public function void($id)
    {
        check_allowed_levels([1, 2]);
        if ($this->input->method() !== 'post') show_404();

        try {
            $this->Ap_invoice_m->void($id, $this->input->post('void_reason', TRUE), $this->fungsi->user_login()->user_id);
            $this->session->set_flashdata('success', 'Invoice hutang berhasil dibatalkan.');
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('ap-invoice/detail/' . $id);
    }

    public function refresh_all_due_dates()
    {
        check_allowed_levels([1, 2]);
        if ($this->input->method() !== 'post') show_404();

        $updated = $this->Ap_invoice_m->recalc_all_due_dates();
        $this->session->set_flashdata('success', $updated > 0
            ? $updated . ' jatuh tempo invoice berhasil diperbarui mengikuti termin supplier terkini.'
            : 'Semua jatuh tempo sudah sesuai, tidak ada yang perlu diperbarui.');
        redirect('ap-invoice');
    }
}
