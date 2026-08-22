<?php
require_once FCPATH . 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_ar extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2]);
        $this->load->model('Report_ar_m');
        $this->load->model('customer_m');
    }

    public function index()
    {
        $this->kartu_piutang();
    }

    public function kartu_piutang($customer_id = null)
    {
        $customer_id = $customer_id ?: $this->input->get('customer_id');
        $from = $this->input->get('from') ?: date('Y-m-01');
        $to   = $this->input->get('to') ?: date('Y-m-d');

        $data['title']     = 'Kartu Piutang';
        $data['customers'] = $this->customer_m->get()->result();
        $data['from']      = $from;
        $data['to']        = $to;
        $data['customer_id'] = $customer_id;

        if ($customer_id) {
            $data['customer'] = $this->customer_m->get($customer_id)->row();
            $data['rows']     = $this->Report_ar_m->get_kartu_piutang($customer_id, $from, $to);
        } else {
            $data['customer'] = null;
            $data['rows']     = [];
        }

        $this->template->load('template', 'report/ar/kartu_piutang', $data);
    }

    public function aging()
    {
        $as_of_date = $this->input->get('as_of') ?: date('Y-m-d');

        $data['title']   = 'Aging Piutang';
        $data['as_of']   = $as_of_date;
        $data['rows']    = $this->Report_ar_m->get_aging($as_of_date);
        $data['summary'] = $this->Report_ar_m->get_aging_summary_by_customer($as_of_date);

        $this->template->load('template', 'report/ar/aging_piutang', $data);
    }

    public function daftar()
    {
        $from        = $this->input->get('from') ?: date('Y-m-01');
        $to          = $this->input->get('to') ?: date('Y-m-d');
        $status      = $this->input->get('status') ?: '';
        $customer_id = $this->input->get('customer_id') ?: '';

        $rows = $this->Report_ar_m->get_period_list($from, $to, $status, $customer_id);

        $totals = ['amount' => 0, 'paid' => 0, 'outstanding' => 0];
        foreach ($rows as $r) {
            $totals['amount']      += (int) $r->amount;
            $totals['paid']        += (int) $r->paid_amount;
            $totals['outstanding'] += (int) $r->outstanding_amount;
        }

        $data['title']       = 'Daftar Piutang';
        $data['customers']   = $this->customer_m->get()->result();
        $data['from']        = $from;
        $data['to']          = $to;
        $data['status']      = $status;
        $data['customer_id'] = $customer_id;
        $data['rows']        = $rows;
        $data['totals']      = $totals;

        $this->template->load('template', 'report/ar/daftar_piutang', $data);
    }

    public function cetak_daftar()
    {
        $from        = $this->input->get('from') ?: date('Y-m-01');
        $to          = $this->input->get('to') ?: date('Y-m-d');
        $status      = $this->input->get('status') ?: '';
        $customer_id = $this->input->get('customer_id') ?: '';

        $rows = $this->Report_ar_m->get_period_list($from, $to, $status, $customer_id);

        $totals = ['amount' => 0, 'paid' => 0, 'outstanding' => 0];
        foreach ($rows as $r) {
            $totals['amount']      += (int) $r->amount;
            $totals['paid']        += (int) $r->paid_amount;
            $totals['outstanding'] += (int) $r->outstanding_amount;
        }

        $data = [
            'from'   => $from,
            'to'     => $to,
            'rows'   => $rows,
            'totals' => $totals,
        ];

        $html = $this->load->view('report/ar/daftar_piutang_print', $data, true);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('daftar_piutang_' . $from . '_' . $to . '.pdf', ['Attachment' => 0]);
    }

    public function export_excel_daftar()
    {
        check_allowed_levels([1, 2]);

        $from        = $this->input->get('from') ?: date('Y-m-01');
        $to          = $this->input->get('to') ?: date('Y-m-d');
        $status      = $this->input->get('status') ?: '';
        $customer_id = $this->input->get('customer_id') ?: '';
        $rows = $this->Report_ar_m->get_period_list($from, $to, $status, $customer_id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Piutang');

        $status_label = ['outstanding' => 'Belum Lunas', 'partial' => 'Belum Lunas', 'paid' => 'Lunas', 'void' => 'Void'];

        $headers = ['No', 'No. Invoice', 'Customer', 'Tgl Invoice', 'Jatuh Tempo', 'Jumlah', 'Dibayar', 'Sisa', 'Status'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        $no = 1;
        foreach ($rows as $r) {
            $sheet->fromArray([
                $no++,
                $r->ar_no,
                $r->nama_customer,
                $r->invoice_date,
                $r->due_date,
                (int) $r->amount,
                (int) $r->paid_amount,
                (int) $r->outstanding_amount,
                $status_label[$r->status] ?? $r->status,
            ], null, 'A' . $row);
            $row++;
        }

        $filename = 'daftar_piutang_' . $from . '_' . $to . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function cetak_kartu($customer_id)
    {
        $from = $this->input->get('from') ?: date('Y-m-01');
        $to   = $this->input->get('to') ?: date('Y-m-d');

        $data = [
            'customer' => $this->customer_m->get($customer_id)->row(),
            'from'     => $from,
            'to'       => $to,
            'rows'     => $this->Report_ar_m->get_kartu_piutang($customer_id, $from, $to),
        ];

        $html = $this->load->view('report/ar/kartu_piutang_print', $data, true);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('kartu_piutang_' . $customer_id . '.pdf', ['Attachment' => 0]);
    }

    public function cetak_aging()
    {
        $as_of_date = $this->input->get('as_of') ?: date('Y-m-d');

        $data = [
            'as_of'   => $as_of_date,
            'rows'    => $this->Report_ar_m->get_aging($as_of_date),
            'summary' => $this->Report_ar_m->get_aging_summary_by_customer($as_of_date),
        ];

        $html = $this->load->view('report/ar/aging_piutang_print', $data, true);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('aging_piutang_' . $as_of_date . '.pdf', ['Attachment' => 0]);
    }

    public function export_excel_aging()
    {
        check_allowed_levels([1, 2]);

        $as_of_date = $this->input->get('as_of') ?: date('Y-m-d');
        $rows = $this->Report_ar_m->get_aging($as_of_date);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Aging Piutang');

        $headers = ['No', 'No. Invoice', 'Customer', 'Tgl Invoice', 'Jatuh Tempo', 'Hari Terlambat', 'Jumlah', 'Sisa', 'Bucket'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        $no = 1;
        foreach ($rows as $r) {
            $sheet->fromArray([
                $no++,
                $r->ar_no,
                $r->nama_customer,
                $r->invoice_date,
                $r->due_date,
                (int) $r->days_overdue,
                (int) $r->amount,
                (int) $r->outstanding_amount,
                $r->bucket,
            ], null, 'A' . $row);
            $row++;
        }

        $filename = 'aging_piutang_' . $as_of_date . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
