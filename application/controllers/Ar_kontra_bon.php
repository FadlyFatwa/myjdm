<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ar_kontra_bon extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2]);
        $this->load->model('Ar_kontra_bon_m');
        $this->load->model('customer_m');
        $this->load->library('fungsi');
    }

    public function index()
    {
        $data['title'] = 'Kontra Bon';
        $this->template->load('template', 'finance/ar_kontra_bon/ar_kontra_bon_data', $data);
    }

    public function get_json()
    {
        echo json_encode($this->Ar_kontra_bon_m->get_json());
    }

    public function preview()
    {
        $customer_id = $this->input->post('customer_id');
        $from = $this->input->post('period_start');
        $to   = $this->input->post('period_end');

        if (empty($customer_id) || empty($from) || empty($to)) {
            echo json_encode(['success' => false, 'message' => 'Lengkapi customer dan rentang tanggal.']);
            return;
        }

        $invoices = $this->Ar_kontra_bon_m->get_outstanding_invoices($customer_id, $from, $to);
        $total = 0;
        foreach ($invoices as $inv) $total += (int) $inv->outstanding_amount;

        echo json_encode([
            'success'  => true,
            'invoices' => $invoices,
            'total'    => $total,
        ]);
    }

    public function add()
    {
        check_allowed_levels([1, 2]);

        if ($this->input->method() === 'post') {
            $post = [
                'customer_id'  => (int) $this->input->post('customer_id'),
                'period_start' => $this->input->post('period_start'),
                'period_end'   => $this->input->post('period_end'),
                'due_date'     => $this->input->post('due_date') ?: null,
            ];

            if (empty($post['customer_id']) || empty($post['period_start']) || empty($post['period_end'])) {
                $this->session->set_flashdata('error', 'Customer dan rentang tanggal wajib diisi.');
                redirect('kontra-bon/add');
            }

            try {
                $kontra_bon_id = $this->Ar_kontra_bon_m->create($post, $this->fungsi->user_login()->user_id);
                $this->session->set_flashdata('success', 'Kontra bon berhasil dibuat.');
                redirect('kontra-bon/detail/' . $kontra_bon_id);
            } catch (Exception $e) {
                $this->session->set_flashdata('error', $e->getMessage());
                redirect('kontra-bon/add');
            }
        }

        $data['title']     = 'Buat Kontra Bon';
        $data['customers'] = $this->customer_m->get()->result();
        $this->template->load('template', 'finance/ar_kontra_bon/ar_kontra_bon_form', $data);
    }

    public function detail($id)
    {
        $kb = $this->Ar_kontra_bon_m->get($id);
        if (!$kb) show_404();

        $this->load->model('Ar_kontra_bon_payment_m');
        $data['title']    = 'Detail Kontra Bon ' . $kb->kontra_bon_no;
        $data['kb']       = $kb;
        $data['invoices'] = $this->Ar_kontra_bon_m->get_invoices($id);
        $data['payments'] = $this->Ar_kontra_bon_payment_m->get_by_kontra_bon($id);
        $this->template->load('template', 'finance/ar_kontra_bon/ar_kontra_bon_detail', $data);
    }

    public function void($id)
    {
        check_allowed_levels([1, 2]);
        if ($this->input->method() !== 'post') show_404();

        try {
            $this->Ar_kontra_bon_m->void($id, $this->input->post('void_reason', TRUE), $this->fungsi->user_login()->user_id);
            $this->session->set_flashdata('success', 'Kontra bon berhasil dibatalkan.');
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('kontra-bon/detail/' . $id);
    }

    public function cetak($id)
    {
        $kb = $this->Ar_kontra_bon_m->get($id);
        if (!$kb) show_404();

        $invoices = $this->Ar_kontra_bon_m->get_invoices($id);
        $subtotal_brutto = array_sum(array_map(function ($inv) {
            return $inv->gross_amount ?: $inv->amount;
        }, $invoices));

        $data = [
            'kb'               => $kb,
            'invoices'         => $invoices,
            'subtotal_brutto'  => $subtotal_brutto,
        ];

        $html = $this->load->view('finance/ar_kontra_bon/ar_kontra_bon_print', $data, true);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('kontra_bon_' . $kb->kontra_bon_no . '.pdf', ['Attachment' => 0]);
    }
}
