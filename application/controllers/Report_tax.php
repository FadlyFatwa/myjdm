<?php
require_once FCPATH . 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_tax extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_not_login();
        $this->load->model('Report_tax_m');
    }

    /**
     * =========================
     * HALAMAN UTAMA
     * =========================
     */
    public function index()
    {
        $data['title'] = 'Laporan Pajak';

        // default bulan sekarang
        $period = $this->input->get('period') ?: date('Y-m');
        $data['period'] = $period;

        // preview data
        $data['preview'] = $this->Report_tax_m->get_preview($period);

        $this->template->load('template','report/tax/report_tax', $data);
    }

    /**
     * =========================
     * GENERATE SELECTED
     * =========================
     */
    public function generate_selected()
    {
        $sale_ids = $this->input->post('sale_ids');

        if (empty($sale_ids)) {
            $this->session->set_flashdata('error', 'Tidak ada invoice dipilih');
            redirect('report_tax');
        }

        $result = $this->Report_tax_m->generate_selected($sale_ids);

        if ($result) {
            $this->session->set_flashdata('success', 'Data pajak berhasil digenerate');
        } else {
            $this->session->set_flashdata('error', 'Generate gagal');
        }

        redirect('report_tax');
    }

    public function data()
    {
        $period = $this->input->get('period') ?: date('Y-m');

        $data['title']  = 'Data Pajak';
        $data['period'] = $period;
        $data['rows']   = $this->Report_tax_m->get_tax_data($period);

        $this->template->load('template','report/tax/report_tax_data', $data);
    }

    // ===============================
    // AJAX DETAIL
    // ===============================
    public function detail_ajax($tax_id)
    {
        $data['header'] = $this->Report_tax_m->get_tax_header($tax_id);
        $data['items']  = $this->Report_tax_m->get_tax_detail($tax_id);

        $this->load->view('report/tax/report_tax_detail_modal', $data);
    }

    // ===============================
    // DELETE
    // ===============================
    public function delete($tax_id)
    {
        if ($this->input->method() !== 'post') show_404();

        $this->Report_tax_m->delete_tax($tax_id);

        $this->session->set_flashdata('success', 'Data tax berhasil dihapus');
        redirect('report_tax/data');
    }

    public function delete_all($period)
    {
        if ($this->input->method() !== 'post') show_404();

        $count = $this->Report_tax_m->delete_all_by_period($period);

        $this->session->set_flashdata('success', $count . ' data pajak periode ' . $period . ' berhasil dihapus');
        redirect('report_tax/data?period=' . $period);
    }

    public function cetak($tax_id)
    {
        $header = $this->Report_tax_m->get_tax_header($tax_id);
        $detail = $this->Report_tax_m->get_tax_detail($tax_id);

        if (!$header) {
            show_404();
        }

        $data = [
            'sale' => $header,
            'sale_detail' => $detail
        ];

        $html = $this->load->view('report/tax/invoice_tax_print', $data, true);

        // Dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);

        // Ukuran A4 Portrait
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        $dompdf->stream("invoice_tax_".$header->invoice.".pdf", [
            "Attachment" => 0
        ]);
    }

    public function cetak_laporan($period)
    {
        $rows = $this->Report_tax_m->get_tax_by_period($period);
        $summary = $this->Report_tax_m->get_tax_summary($period);

        $data = [
            'period' => $period,
            'rows' => $rows,
            'summary' => $summary
        ];

        $html = $this->load->view('report/tax/report_tax_perio_print', $data, true);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("laporan_pajak_".$period.".pdf", ["Attachment" => 0]);
    }

    public function export_excel($period)
{
    $rows = $this->Report_tax_m->get_tax_by_period($period);

    if (!$rows) {
        show_error('Tidak ada data untuk periode ini.');
    }

    $template = FCPATH.'assets/dist/template/laporan_gunggungan.xlsx';
    $temp     = FCPATH.'assets/dist/template/temp_'.$period.'.xlsx';

    // WAJIB: clone template
    if (!copy($template, $temp)) {
        show_error('Gagal copy template.');
    }

    // Load hasil copy
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($temp);
    $sheet = $spreadsheet->getSheetByName('DATA');

    $row = 5;

    foreach ($rows as $r) {

    $date = new DateTime($r->sale_date);
    $excelDate = Date::PHPToExcel($date);

    $hari_ke = $date->format('d');

    $sheet->setCellValue('B'.$row, 'Normal');
    $sheet->setCellValue('C'.$row, 'Penjualan Hari ke '.$hari_ke);
    $sheet->setCellValue('D'.$row, 'NIK');
    $sheet->setCellValue('E'.$row, '0000000000000000');
    $sheet->setCellValue('F'.$row, 'A');
    $sheet->setCellValue('G'.$row, $r->invoice);

    $sheet->setCellValue('H'.$row, $excelDate);

    $sheet->getStyle('H'.$row)
        ->getNumberFormat()
        ->setFormatCode('yyyy-mm-dd');

    $sheet->setCellValue('I'.$row, (int)$r->grand_total);
    $sheet->setCellValue('J'.$row, (int)$r->dpp);
    $sheet->setCellValue('K'.$row, (int)$r->ppn);
    $sheet->setCellValue('L'.$row, 0);
    $sheet->setCellValue('M'.$row, 'ok');

    $row++;
}

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($temp);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="laporan_pajak_'.$period.'.xlsx"');
    header('Cache-Control: max-age=0');

    readfile($temp);
    unlink($temp);
    exit;
}

public function export_xml($period)
{
    $rows = $this->Report_tax_m->get_tax_by_period($period);

    if (!$rows) {
        show_error('Tidak ada data untuk periode ini.');
    }

    $month = date('m', strtotime($period.'-01'));
    $year  = date('Y', strtotime($period.'-01'));

    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><RetailInvoiceBulk></RetailInvoiceBulk>');

    $xml->addChild('TIN', '0000000000000000');
    $xml->addChild('TaxPeriodMonth', $month);
    $xml->addChild('TaxPeriodYear', $year);

    $list = $xml->addChild('ListOfRetailInvoice');

    foreach ($rows as $r) {

        $hari_ke = date('d', strtotime($r->sale_date));

        $inv = $list->addChild('RetailInvoice');

        $inv->addChild('TrxCode', 'Normal');
        $inv->addChild('BuyerName', 'Penjualan Hari ke '.$hari_ke);
        $inv->addChild('BuyerIdOpt', 'NIK');
        $inv->addChild('BuyerIdNumber', '0000000000000000');
        $inv->addChild('GoodServiceOpt', 'A');
        $inv->addChild('SerialNo', htmlspecialchars($r->invoice));
        $inv->addChild('TransactionDate', date('Y-m-d', strtotime($r->sale_date)));

        $inv->addChild('TaxBaseSellingPrice', (int)$r->grand_total);
        $inv->addChild('OtherTaxBaseSellingPrice', (int)$r->dpp);
        $inv->addChild('VAT', (int)$r->ppn);
        $inv->addChild('STLG', 0);
        $inv->addChild('Info', 'ok');
    }

    header('Content-type: text/xml');
    header('Content-Disposition: attachment; filename="laporan_pajak_'.$period.'.xml"');

    echo $xml->asXML();
    exit;
}

}