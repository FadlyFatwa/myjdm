<?php
require_once FCPATH . 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_purchase extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2]);
        $this->load->model('Report_purchase_m');
        $this->load->model('supplier_m');
    }

    private function _totals($rows)
    {
        $totals = ['barang' => 0];
        foreach ($rows as $r) {
            $totals['barang'] += (int) $r->total_amount;
        }
        return $totals;
    }

    public function index()
    {
        $from        = $this->input->get('from') ?: date('Y-m-01');
        $to          = $this->input->get('to') ?: date('Y-m-d');
        $status      = $this->input->get('status') ?: '';
        $supplier_id = $this->input->get('supplier_id') ?: '';

        $rows = $this->Report_purchase_m->get_period_list($from, $to, $status, $supplier_id);

        $data['title']       = 'Laporan Pembelian';
        $data['suppliers']   = $this->supplier_m->get()->result();
        $data['from']        = $from;
        $data['to']          = $to;
        $data['status']      = $status;
        $data['supplier_id'] = $supplier_id;
        $data['rows']        = $rows;
        $data['totals']      = $this->_totals($rows);

        $this->template->load('template', 'purchasing/report_purchase', $data);
    }

    public function cetak()
    {
        $from        = $this->input->get('from') ?: date('Y-m-01');
        $to          = $this->input->get('to') ?: date('Y-m-d');
        $status      = $this->input->get('status') ?: '';
        $supplier_id = $this->input->get('supplier_id') ?: '';

        $rows = $this->Report_purchase_m->get_period_list($from, $to, $status, $supplier_id);

        $data = [
            'from'   => $from,
            'to'     => $to,
            'rows'   => $rows,
            'totals' => $this->_totals($rows),
        ];

        $html = $this->load->view('purchasing/report_purchase_print', $data, true);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('laporan_pembelian_' . $from . '_' . $to . '.pdf', ['Attachment' => 0]);
    }

    public function export_excel()
    {
        check_allowed_levels([1, 2]);

        $from        = $this->input->get('from') ?: date('Y-m-01');
        $to          = $this->input->get('to') ?: date('Y-m-d');
        $status      = $this->input->get('status') ?: '';
        $supplier_id = $this->input->get('supplier_id') ?: '';
        $rows = $this->Report_purchase_m->get_period_list($from, $to, $status, $supplier_id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Pembelian');

        $status_label = ['outstanding' => 'Belum Lunas', 'partial' => 'Belum Lunas', 'paid' => 'Lunas', 'void' => 'Void'];

        $headers = ['No', 'No. PO', 'Supplier', 'No. Invoice Supplier', 'Tgl Terima', 'Diskon Invoice', 'PPN', 'Total Barang', 'Cara Bayar', 'Status'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        $no = 1;
        foreach ($rows as $r) {
            $sheet->fromArray([
                $no++,
                $r->po_number,
                $r->nama_supplier,
                $r->supplier_invoice_no ?: '-',
                $r->receive_date,
                (int) $r->diskon_invoice,
                (int) $r->ppn_nominal,
                (int) $r->total_amount,
                $r->payment_type === 'cash' ? 'Cash' : ($r->payment_type === 'credit' ? 'Kredit' : '-'),
                $r->ap_status ? ($status_label[$r->ap_status] ?? $r->ap_status) : '-',
            ], null, 'A' . $row);
            $row++;
        }

        $filename = 'laporan_pembelian_' . $from . '_' . $to . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
