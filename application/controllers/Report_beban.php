<?php
require_once FCPATH . 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_beban extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2]);
        $this->load->model('Report_beban_m');
    }

    private function _coa_list()
    {
        return $this->db
            ->where('coa_type', 'beban')
            ->where('is_postable', 1)
            ->order_by('coa_code', 'ASC')
            ->get('finance_coa')->result();
    }

    private function _total($rows)
    {
        $total = 0;
        foreach ($rows as $r) {
            if (!$r->is_void) $total += (int) $r->amount;
        }
        return $total;
    }

    public function index()
    {
        $from   = $this->input->get('from') ?: date('Y-m-01');
        $to     = $this->input->get('to') ?: date('Y-m-d');
        $coa_id = $this->input->get('coa_id') ?: '';

        $rows = $this->Report_beban_m->get_period_list($from, $to, $coa_id);

        $data['title']   = 'Laporan Operasional';
        $data['coa_list'] = $this->_coa_list();
        $data['from']    = $from;
        $data['to']      = $to;
        $data['coa_id']  = $coa_id;
        $data['rows']    = $rows;
        $data['summary'] = $this->Report_beban_m->get_summary_by_category($from, $to);
        $data['total']   = $this->_total($rows);

        $this->template->load('template', 'finance/beban/report_beban', $data);
    }

    public function cetak()
    {
        $from   = $this->input->get('from') ?: date('Y-m-01');
        $to     = $this->input->get('to') ?: date('Y-m-d');
        $coa_id = $this->input->get('coa_id') ?: '';

        $rows = $this->Report_beban_m->get_period_list($from, $to, $coa_id);

        $data = [
            'from'    => $from,
            'to'      => $to,
            'rows'    => $rows,
            'summary' => $this->Report_beban_m->get_summary_by_category($from, $to),
            'total'   => $this->_total($rows),
        ];

        $html = $this->load->view('finance/beban/report_beban_print', $data, true);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('laporan_operasional_' . $from . '_' . $to . '.pdf', ['Attachment' => 0]);
    }

    public function export_excel()
    {
        $from   = $this->input->get('from') ?: date('Y-m-01');
        $to     = $this->input->get('to') ?: date('Y-m-d');
        $coa_id = $this->input->get('coa_id') ?: '';
        $rows = $this->Report_beban_m->get_period_list($from, $to, $coa_id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Operasional');

        $headers = ['No', 'No. Beban', 'Tanggal', 'Kategori', 'Jumlah', 'Cara Bayar', 'Keterangan', 'Status'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        $no = 1;
        foreach ($rows as $r) {
            $sheet->fromArray([
                $no++,
                $r->expense_no,
                $r->expense_date,
                $r->coa_name,
                (int) $r->amount,
                $r->payment_method === 'cash' ? 'Cash' : 'Transfer',
                $r->description,
                $r->is_void ? 'Dibatalkan' : 'Aktif',
            ], null, 'A' . $row);
            $row++;
        }

        $filename = 'laporan_operasional_' . $from . '_' . $to . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
