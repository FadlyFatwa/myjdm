<?php
use Dompdf\Dompdf;
use Dompdf\Options;
defined('BASEPATH') OR exit('No direct script access allowed');

class Barcode extends CI_Controller {
    function __construct(){
		parent::__construct();
		check_not_login();
		$this->load->model(['stock_m','item_m']);
	}


    #BARCODE DARI DAFTAR BARANG
    function barcode_qrcode($id) {
        $data['row'] = $this->item_m->get($id)->row();
        $this->template->load('template', 'barcode/item/barcode_qrcode', $data);
    }
	public function barcode_print($id) {
		$quantity = $this->input->post('quantity', TRUE);
		$start_col = $this->input->post('start_col', TRUE);
		$start_row = $this->input->post('start_row', TRUE);
		$date = $this->input->post('date', TRUE);
		$max_characters = $this->input->post('max_characters', TRUE);
	
		$data['row'] = $this->item_m->get($id)->row();
		$data['quantity'] = $quantity;
		$data['start_col'] = $start_col;
		$data['start_row'] = $start_row;
		$data['date'] = $date;
		$data['max_characters'] = $max_characters;
	
		$html = $this->load->view('barcode/stock/barcode_print', $data, true);
	
		// Configure Dompdf
		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isRemoteEnabled', true);
		$dompdf = new Dompdf($options);
	
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'portrait'); // You can adjust the paper size and orientation if needed
		$dompdf->render();
	
		// Output the generated PDF (1 = download and 0 = preview)
		$dompdf->stream('barcode-' . $data['row']->barcode, array("Attachment" => 0));
	}

    public function barcode_print_thermal($id)
    {
        // ===== INPUT =====
        $quantity  = (int) $this->input->post('quantity', TRUE) ?: 30;
        $start_col = (int) $this->input->post('start_col', TRUE) ?: 1;
        $start_row = (int) $this->input->post('start_row', TRUE) ?: 1;
        $date      = $this->input->post('date', TRUE);

        // ===== AMBIL DATA STOCK =====
        $items = $this->stock_m->get_stock_with_supplier_unit([$id]);

        if (empty($items)) {
            show_404();
        }

        $row = $items[0];

        // inject supaya sama seperti template multiple
        $row->quantity = $quantity;
        $row->date     = $date ?: date('Y-m-d');

        $data = [
            'row'       => $row,
            'quantity'  => $quantity,
            'start_col' => $start_col,
            'start_row' => $start_row,
            'date'      => $row->date,
        ];

        // ✅ LANGSUNG LOAD VIEW (THERMAL PRINT)
        $this->load->view('barcode/stock/barcode_print_thermal', $data);
    }

	public function barcode_print_101($id) {
		$quantity = $this->input->post('quantity', TRUE);
		$start_col = $this->input->post('start_col', TRUE);
		$start_row = $this->input->post('start_row', TRUE);
		$nama_item = $this->input->post('nama_item', TRUE);  // Perbaiki nama variabel menjadi 'nama_item'
		$nama_mobil = $this->input->post('nama_mobil', TRUE); 
		$no_part = $this->input->post('no_part', TRUE); 
		$date = $this->input->post('date', TRUE);
		
		$data['row'] = $this->item_m->get($id)->row();
		$data['quantity'] = $quantity;
		$data['start_col'] = $start_col;
		$data['start_row'] = $start_row;
		$data['nama_item'] = $nama_item;  // Kirim 'nama_item' ke view
		$data['nama_mobil'] = $nama_mobil; 
		$data['no_part'] = $no_part; 
		$data['date'] = $date;
		
		$html = $this->load->view('barcode/stock/barcode_101_print', $data, true);
		
		// Configure Dompdf
		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isRemoteEnabled', true);
		$dompdf = new Dompdf($options);
		
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'landscape'); // Kamu bisa menyesuaikan ukuran kertas dan orientasi
		$dompdf->render();
		
		// Output PDF (1 = download, 0 = preview)
		$dompdf->stream('barcode-' . $data['row']->barcode, array("Attachment" => 0));
	}
	
	
	public function barcode_print_124($id) {
		$quantity = $this->input->post('quantity', TRUE);
		$start_col = $this->input->post('start_col', TRUE);
		$start_row = $this->input->post('start_row', TRUE);
		$nama_item = $this->input->post('nama_item', TRUE);  // Perbaiki nama variabel menjadi 'nama_item'
		$nama_mobil = $this->input->post('nama_mobil', TRUE); 
		$no_part = $this->input->post('no_part', TRUE); 
		
		$data['row'] = $this->item_m->get($id)->row();
		$data['quantity'] = $quantity;
		$data['start_col'] = $start_col;
		$data['start_row'] = $start_row;
		$data['nama_item'] = $nama_item;  // Kirim 'nama_item' ke view
		$data['nama_mobil'] = $nama_mobil; 
		$data['no_part'] = $no_part; 
		
		$html = $this->load->view('barcode/stock/barcode_124_print', $data, true);
		
		// Configure Dompdf
		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isRemoteEnabled', true);
		$dompdf = new Dompdf($options);
		
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'landscape'); // Kamu bisa menyesuaikan ukuran kertas dan orientasi
		$dompdf->render();
		
		// Output PDF (1 = download, 0 = preview)
		$dompdf->stream('barcode-' . $data['row']->barcode, array("Attachment" => 0));
	}
	public function barcode_print_fanbelt($id) {
		$quantity = $this->input->post('quantity', TRUE);
		$start_col = $this->input->post('start_col', TRUE);
		$start_row = $this->input->post('start_row', TRUE);
		$nama_item = $this->input->post('nama_item', TRUE);  // Perbaiki nama variabel menjadi 'nama_item'
		$nama_mobil = $this->input->post('nama_mobil', TRUE); 
		$no_part = $this->input->post('no_part', TRUE); 
		$date = $this->input->post('date', TRUE);
		
		$data['row'] = $this->item_m->get($id)->row();
		$data['quantity'] = $quantity;
		$data['start_col'] = $start_col;
		$data['start_row'] = $start_row;
		$data['nama_item'] = $nama_item;  // Kirim 'nama_item' ke view
		$data['nama_mobil'] = $nama_mobil; 
		$data['no_part'] = $no_part; 
		$data['date'] = $date;
		
		$html = $this->load->view('barcode/stock/barcode_fanbelt', $data, true);
		
		// Configure Dompdf
		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isRemoteEnabled', true);
		$dompdf = new Dompdf($options);
		
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'landscape'); // Kamu bisa menyesuaikan ukuran kertas dan orientasi
		$dompdf->render();
		
		// Output PDF (1 = download, 0 = preview)
		$dompdf->stream('barcode-' . $data['row']->barcode, array("Attachment" => 0));
	}





    #BARCODE DARI STOCK IN

    function barcode_qrcode_stock($id,$stock_id) {
        // Mengambil data item
        $data['row'] = $this->item_m->get($id)->row();
    
        // Mengambil data stok berdasarkan item_id
        $data['stock'] = $this->stock_m->get_stock_by_item_id($id,$stock_id)->row();
    
        // Memuat view
        $this->template->load('template', 'barcode/stock/barcode_qrcode', $data);
    }

    function barcode_qrcode_124($id,$stock_id) {
        // Mengambil data item
        $data['row'] = $this->item_m->get($id)->row();
    
        // Mengambil data stok berdasarkan item_id
        $data['stock'] = $this->stock_m->get_stock_by_item_id($id,$stock_id)->row();
    
        // Memuat view
        $this->template->load('template', 'barcode/stock/barcode_124', $data);
    }
    function barcode_qrcode_fanbelt($id,$stock_id) {
        // Mengambil data item
        $data['row'] = $this->item_m->get($id)->row();
    
        // Mengambil data stok berdasarkan item_id
        $data['stock'] = $this->stock_m->get_stock_by_item_id($id,$stock_id)->row();
    
        // Memuat view
        $this->template->load('template', 'barcode/stock/barcode_124', $data);
    }

    
    public function barcode_qrcode_multiple()
{
    $ids = $this->input->get('ids'); // Ambil ID yang dipilih
    $id_array = explode(',', $ids); // Pisahkan ID menjadi array

    // Gunakan fungsi baru di model untuk mendapatkan data barang, supplier, dan unit
    $data['items'] = $this->stock_m->get_stock_with_supplier_unit($id_array);

    // Load view untuk cetak barcode multiple
    $this->template->load('template', 'barcode/stock/barcode_qrcode_multiple', $data);
}

public function barcode_print_multiple_40x20()
{
    // Ambil data dari form
    $quantities = $this->input->post('quantity');
    $dates = $this->input->post('date');
    $start_col = $this->input->post('start_col', TRUE);
    $start_row = $this->input->post('start_row', TRUE);
    $max_characters = $this->input->post('max_characters', TRUE);

    // Ambil semua stock_id yang dipilih
    $stock_ids = array_keys($quantities);

    // Ambil data barang
    $data['items'] = $this->stock_m->get_stock_with_supplier_unit($stock_ids);

    // Inject quantity & date
    foreach ($data['items'] as $item) {
        $item->quantity = $quantities[$item->stock_id];
        $item->date = $dates[$item->stock_id];
        $item->max_characters = $max_characters[$item->stock_id];
    }

    $data['start_col'] = $start_col;
    $data['start_row'] = $start_row;

    // ✅ LANGSUNG LOAD VIEW (WINDOWS PRINT)
    $this->load->view('barcode/stock/print_barcode_multiple_thermal', $data);
}

    #Print Barcode Multi
    public function barcode_print_multiple()
{
    // Ambil data dari form
    $quantities = $this->input->post('quantity'); // Jumlah print per item
    $dates = $this->input->post('date'); // Tanggal per item
    $start_col = $this->input->post('start_col', TRUE); // Kolom awal
    $start_row = $this->input->post('start_row', TRUE); // Baris awal
    $max_characters = $this->input->post('max_characters', TRUE);


    // Ambil semua stock_id yang dipilih
    $stock_ids = array_keys($quantities);

    // Gunakan fungsi baru di model untuk mendapatkan data barang, supplier, dan unit
    $data['items'] = $this->stock_m->get_stock_with_supplier_unit($stock_ids);

    // Tambahkan jumlah print dan tanggal ke setiap item
    foreach ($data['items'] as $item) {
        $item->quantity = $quantities[$item->stock_id];
        $item->date = $dates[$item->stock_id];
        $item->max_characters = $max_characters[$item->stock_id];
    }

    $data['start_col'] = $start_col;
    $data['start_row'] = $start_row;

    // Load view untuk mencetak barcode
    $html = $this->load->view('barcode/stock/print_barcode_multiple_result', $data, true);

    // Configure Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait'); // Sesuaikan ukuran kertas jika diperlukan
    $dompdf->render();

    // Output the generated PDF (1 = download and 0 = preview)
    $dompdf->stream('barcode-multiple.pdf', array("Attachment" => 0));
}
public function barcode_101_print_multiple()
{
    // Ambil data dari form
    $quantities = $this->input->post('quantity'); // Jumlah print per item
    $dates = $this->input->post('date'); // Tanggal per item
    $start_col = $this->input->post('start_col', TRUE); // Kolom awal
    $start_row = $this->input->post('start_row', TRUE); // Baris awal
    $nama_item = $this->input->post('nama_item'); // Nama barang
    $nama_mobil = $this->input->post('nama_mobil'); // Nama mobil
    $no_part = $this->input->post('no_part'); // No part / merk

    // Ambil semua stock_id yang dipilih
    $stock_ids = array_keys($quantities);

    // Gunakan fungsi baru di model untuk mendapatkan data barang, supplier, dan unit
    $data['items'] = $this->stock_m->get_stock_with_supplier_unit($stock_ids);

    // Tambahkan jumlah print, tanggal, nama_item, nama_mobil, dan no_part ke setiap item
    foreach ($data['items'] as $item) {
        $item->quantity = $quantities[$item->stock_id];
        $item->date = $dates[$item->stock_id];
        $item->nama_item = isset($nama_item[$item->stock_id]) ? $nama_item[$item->stock_id] : '';
        $item->nama_mobil = isset($nama_mobil[$item->stock_id]) ? $nama_mobil[$item->stock_id] : '';
        $item->no_part = isset($no_part[$item->stock_id]) ? $no_part[$item->stock_id] : '';
    }

    // Tambahkan start_col dan start_row ke data
    $data['start_col'] = $start_col;
    $data['start_row'] = $start_row;

    // Load view untuk mencetak barcode
    $html = $this->load->view('barcode/stock/print_barcode_101_multiple_result', $data, true);

    // Configure Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape'); // Sesuaikan ukuran kertas jika diperlukan
    $dompdf->render();

    // Output the generated PDF (1 = download and 0 = preview)
    $dompdf->stream('barcode-multiple.pdf', array("Attachment" => 0));
}
public function barcode_fanbelt_print_multiple()
{
    // Ambil data dari form
    $quantities = $this->input->post('quantity'); // Jumlah print per item
    $dates = $this->input->post('date'); // Tanggal per item
    $start_col = $this->input->post('start_col', TRUE); // Kolom awal
    $start_row = $this->input->post('start_row', TRUE); // Baris awal
    $nama_item = $this->input->post('nama_item'); // Nama barang
    $nama_mobil = $this->input->post('nama_mobil'); // Nama mobil
    $no_part = $this->input->post('no_part'); // No part / merk

    // Ambil semua stock_id yang dipilih
    $stock_ids = array_keys($quantities);

    // Gunakan fungsi baru di model untuk mendapatkan data barang, supplier, dan unit
    $data['items'] = $this->stock_m->get_stock_with_supplier_unit($stock_ids);

    // Tambahkan jumlah print, tanggal, nama_item, nama_mobil, dan no_part ke setiap item
    foreach ($data['items'] as $item) {
        $item->quantity = $quantities[$item->stock_id];
        $item->date = $dates[$item->stock_id];
        $item->nama_item = isset($nama_item[$item->stock_id]) ? $nama_item[$item->stock_id] : '';
        $item->nama_mobil = isset($nama_mobil[$item->stock_id]) ? $nama_mobil[$item->stock_id] : '';
        $item->no_part = isset($no_part[$item->stock_id]) ? $no_part[$item->stock_id] : '';
    }

    // Tambahkan start_col dan start_row ke data
    $data['start_col'] = $start_col;
    $data['start_row'] = $start_row;

    // Load view untuk mencetak barcode
    $html = $this->load->view('barcode/stock/print_barcode_fanbelt_multiple_result', $data, true);

    // Configure Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape'); // Sesuaikan ukuran kertas jika diperlukan
    $dompdf->render();

    // Output the generated PDF (1 = download and 0 = preview)
    $dompdf->stream('barcode-multiple.pdf', array("Attachment" => 0));
}
}