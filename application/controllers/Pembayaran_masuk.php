<?php
require_once FCPATH . 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
defined('BASEPATH') OR exit('No direct script access allowed');

class Pembayaran_masuk extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2]);
        $this->load->model('Pembayaran_masuk_m');
        $this->load->model('customer_m');
    }

    private function _filters()
    {
        return [
            'from'            => $this->input->get('from') ?: date('Y-m-01'),
            'to'              => $this->input->get('to') ?: date('Y-m-d'),
            'customer_id'     => $this->input->get('customer_id') ?: '',
            'payment_method'  => $this->input->get('payment_method') ?: '',
            'status'          => $this->input->get('status') ?: '',
        ];
    }

    private function _totals($rows)
    {
        $total = 0;
        foreach ($rows as $r) {
            if (!$r->is_void) $total += (int) $r->amount;
        }
        return $total;
    }

    public function index()
    {
        $f = $this->_filters();
        $rows = $this->Pembayaran_masuk_m->get_period_list($f['from'], $f['to'], $f['customer_id'], $f['payment_method'], $f['status']);

        $data              = $f;
        $data['title']     = 'Pembayaran Masuk';
        $data['customers'] = $this->customer_m->get()->result();
        $data['rows']      = $rows;
        $data['total']     = $this->_totals($rows);

        $this->template->load('template', 'finance/pembayaran_masuk/pembayaran_masuk', $data);
    }

    public function cetak()
    {
        $f = $this->_filters();
        $rows = $this->Pembayaran_masuk_m->get_period_list($f['from'], $f['to'], $f['customer_id'], $f['payment_method'], $f['status']);

        $data          = $f;
        $data['rows']  = $rows;
        $data['total'] = $this->_totals($rows);

        $html = $this->load->view('finance/pembayaran_masuk/pembayaran_masuk_print', $data, true);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('pembayaran_masuk_' . $f['from'] . '_' . $f['to'] . '.pdf', ['Attachment' => 0]);
    }

    public function export_excel()
    {
        check_allowed_levels([1, 2]);

        $f = $this->_filters();
        $rows = $this->Pembayaran_masuk_m->get_period_list($f['from'], $f['to'], $f['customer_id'], $f['payment_method'], $f['status']);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pembayaran Masuk');

        $headers = ['No', 'No. Bukti', 'Tanggal', 'No. Kontra Bon', 'Customer', 'Jumlah', 'Cara Bayar', 'Diterima Oleh', 'Keterangan', 'Status'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        $no = 1;
        foreach ($rows as $r) {
            $sheet->fromArray([
                $no++,
                $r->payment_no,
                $r->payment_date,
                $r->reference_no,
                $r->nama_customer,
                (int) $r->amount,
                $r->payment_method === 'cash' ? 'Cash' : 'Bank',
                $r->received_by_name,
                $r->notes,
                $r->is_void ? 'Void' : 'Aktif',
            ], null, 'A' . $row);
            $row++;
        }

        $filename = 'pembayaran_masuk_' . $f['from'] . '_' . $f['to'] . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
