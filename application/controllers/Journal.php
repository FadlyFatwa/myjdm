<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Journal extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2]);
        $this->load->model('Journal_m');
        $this->load->model('Coa_m');
        $this->load->library('fungsi');
    }

    public function index()
    {
        $data['title'] = 'Jurnal Umum';
        $this->template->load('template', 'finance/journal/journal_data', $data);
    }

    public function get_json()
    {
        echo json_encode($this->Journal_m->get_json());
    }

    public function detail($id)
    {
        $journal = $this->Journal_m->get($id);
        if (!$journal) show_404();

        $data = [
            'journal' => $journal,
            'lines'   => $this->Journal_m->get_detail($id),
        ];
        $this->load->view('finance/journal/journal_detail_modal', $data);
    }

    public function add()
    {
        check_allowed_levels([1]);

        if ($this->input->method() === 'post') {
            $coa_ids = $this->input->post('coa_id');
            $debits  = $this->input->post('debit');
            $kredits = $this->input->post('kredit');
            $notes   = $this->input->post('notes');

            $lines = [];
            foreach ($coa_ids as $i => $coa_id) {
                if (empty($coa_id)) continue;
                $debit  = (int) str_replace('.', '', $debits[$i] ?? 0);
                $kredit = (int) str_replace('.', '', $kredits[$i] ?? 0);
                if ($debit <= 0 && $kredit <= 0) continue;
                $lines[] = [
                    'coa_id' => (int) $coa_id,
                    'debit'  => $debit,
                    'kredit' => $kredit,
                    'notes'  => $notes[$i] ?? null,
                ];
            }

            if (count($lines) < 2) {
                $this->session->set_flashdata('error', 'Jurnal manual butuh minimal 2 baris (debit & kredit).');
                redirect('journal/add');
            }

            try {
                $this->Journal_m->post([
                    'journal_date' => $this->input->post('journal_date'),
                    'source_type'  => 'manual_adjustment',
                    'source_id'    => null,
                    'description'  => $this->input->post('description', TRUE),
                    'created_by'   => $this->fungsi->user_login()->user_id,
                ], $lines);
                $this->session->set_flashdata('success', 'Jurnal manual berhasil diposting.');
            } catch (Exception $e) {
                $this->session->set_flashdata('error', $e->getMessage());
                redirect('journal/add');
            }
            redirect('journal');
        }

        $data['title'] = 'Jurnal Manual';
        $data['coa_list'] = $this->Coa_m->get_all_postable();
        $this->template->load('template', 'finance/journal/journal_form', $data);
    }

    public function void($id)
    {
        check_allowed_levels([1]);
        if ($this->input->method() !== 'post') show_404();

        $journal = $this->Journal_m->get($id);
        if (!$journal) show_404();

        $this->Journal_m->void($id, $this->input->post('void_reason', TRUE), $this->fungsi->user_login()->user_id);
        $this->session->set_flashdata('success', 'Jurnal berhasil dibatalkan.');
        redirect('journal');
    }
}
