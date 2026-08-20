<?php
use Dompdf\Dompdf;
use Dompdf\Options;
defined('BASEPATH') OR exit('No direct script access allowed');

class Report extends CI_Controller {

    function __construct(){
        parent::__construct();
        check_not_login();
        $this->load->model('sale_m');
    }
    
    // public function sale()
    // {
    //     $this->load->model('customer_m');
    //     $this->load->library('pagination');
        
    //     if(isset($_POST['reset'])) {
    //         $this->session->unset_userdata('search');
    //     }
    //     if(isset($_POST['filter'])) {
    //         $post = $this->input->post(null, TRUE);
    //         $this->session->set_userdata('search', $post);
    //     } else {
    //         $post = $this->session->userdata('search');
    //     }
 
    //     $config['base_url'] = site_url('report/sale');
    //     $config['total_rows'] = $this->sale_m->get_sale_pagination()->num_rows();
    //     $config['per_page'] = 100;
    //     $config['uri_segment'] = 3;
    //     $config['first_link'] = 'First';
    //     $config['last_link'] = 'Last';
    //     $config['next_link'] = 'Next';
    //     $config['prev_link'] = 'Prev';
    //     $config['num_tag_open'] = '<li>';
    //     $config['num_tag_close'] = '</li>';
    //     $config['cur_tag_open'] = '<li class="active"><a>';
    //     $config['cur_tag_close'] = '</a></li>';
    //     $config['next_tag_open'] = '<li>';
    //     $config['next_tag_close'] = '</li>';
    //     $config['prev_tag_open'] = '<li>';
    //     $config['prev_tag_close'] = '</li>';
    //     $config['first_tag_open'] = '<li>';
    //     $config['first_tag_close'] = '</li>';
    //     $config['last_tag_open'] = '<li>';
    //     $config['last_tag_close'] = '</li>';
 
    //     $this->pagination->initialize($config);
 
    //     $data['pagination'] = $this->pagination->create_links();
    //     $data['customer'] = $this->customer_m->get()->result();
    //     $data['row'] = $this->sale_m->get_sale_pagination($config['per_page'], $this->uri->segment(3));
    //     $data['post'] = $post;
    //     $this->template->load('template', 'report/sale_report', $data);
    // }

    public function sale()
    {
        $this->load->model(['customer_m', 'sale_m']);

        // RESET
        if(isset($_POST['reset'])) {
            $this->session->unset_userdata('search');
        }

        // FILTER
        if(isset($_POST['filter'])) {
            $post = $this->input->post(null, TRUE);
            $this->session->set_userdata('search', $post);
        } else {

            // 🔥 DEFAULT: HARI INI
            $post = $this->session->userdata('search');

            if(!$post){
                $post = [
                    'date1' => date('Y-m-d'),
                    'date2' => date('Y-m-d')
                ];
            }
        }

        $data['customer'] = $this->customer_m->get()->result();
        $data['row'] = $this->sale_m->get_sale_filtered($post);
        $data['post'] = $post;

        $this->template->load('template', 'report/sale_report', $data);
    }

    public function sale_detail_ajax($sale_id)
    {
        $sale_id = (int) $sale_id;
        $this->load->model('sale_m');
        // Ambil header penjualan
        $query = $this->sale_m->get_sale_pagination(); // Gunakan method yang sudah ada untuk ambil data sale
        $data['sale'] = $this->db->query("SELECT t_sale.*, customer.nama_customer, user.nama as nama_user
                                        FROM t_sale
                                        LEFT JOIN customer ON t_sale.customer_id = customer.customer_id
                                        JOIN user ON t_sale.user_id = user.user_id
                                        WHERE t_sale.sale_id = ?", [$sale_id])->row();
        
        // Ambil detail produk
        $data['products'] = $this->sale_m->get_sale_detail($sale_id)->result();

        // Ambil detail jasa
        $data['jasa'] = $this->sale_m->get_sale_jasa_detail($sale_id);

        $this->load->view('report/sale_detail_modal', $data);
    }
 
    public function sale_product($sale_id = null)
    {
        $detail = $this->sale_m->get_sale_detail($sale_id)->result();
        echo json_encode($detail);
    }

    public function print_pdf() {
        // Load model untuk mendapatkan data penjualan
        $this->load->model('sale_m');

        // Ambil data filter dari post atau session
        $post = $this->session->userdata('search');
        $data['post'] = $post;
        $data['row'] = $this->sale_m->get_filtered_sales($post);

        // Hitung total penjualan
        $total_penjualan = 0;
        foreach ($data['row']->result() as $sale) {
            $total_penjualan += $sale->final_price;
        }
        $data['total_penjualan'] = $total_penjualan;

        // Load tampilan HTML sebagai string
        $html = $this->load->view('report/cetak_report', $data, true);

        // Konfigurasi Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();

        // Output PDF
        $dompdf->stream("cetak_report.pdf", array("Attachment" => 0));
    }

    public function stock_in() {
        $this->load->model('stock_m');
        $this->load->model('supplier_m');
            
        $data['supplier'] = $this->supplier_m->get()->result(); 
        $post = $this->input->post(null, TRUE);
        $data['post'] = $post;
    
        if(isset($post['filter'])) {
            $data['row'] = $this->stock_m->filter_stock_in($post);
        } else {
        $data['row'] = $this->stock_m->get_stock_in();
        }
        $this->template->load('template', 'report/stock_report', $data);
        }

        public function print_stock_pdf() {
            // Load model untuk mendapatkan data penjualan
        $this->load->model('stock_m');
    
            // Ambil data filter dari post atau session
        $post = $this->session->userdata('search');
        $data['post'] = $post;
        $data['row'] = $this->stock_m->filter_stock_in($post);
    
            // Hitung total penjualan
        $totalpembelian = 0;
        foreach ($data['row']->result() as $buy) {
            $totalpembelian += $buy->subtotal;
        }
        $data['totalpembelian'] = $totalpembelian;
    
            // Load tampilan HTML sebagai string
        $html = $this->load->view('report/cetak_report_stock', $data, true);

            // Konfigurasi Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();   
    
            // Output PDF
        $dompdf->stream("cetak_report_stock.pdf", array("Attachment" => 0));
    }

    public function detail() {
        $data['row'] = $this->sale_m->get_detail();
        $this->template->load('template', 'report/sale_item_detail',$data);
    }

    public function history() {
        $this->template->load('template', 'report/sale_history');
    }


    function get_json_sale() {
        $this->load->library('datatables');
    
        // Get draw parameter from POST request
        $draw = intval($this->input->post('draw'));
    
        // Your query setup
        $this->db->select('t_sale.sale_id,p_item.item_id,p_item.barcode,nama_customer,invoice,nama_item,qty,price_sale,total,date');
        $this->db->from('t_sale_detail');
        $this->db->join('t_sale', 't_sale_detail.sale_id = t_sale.sale_id', 'left');
        $this->db->join('p_item', 't_sale_detail.item_id = p_item.item_id', 'left');
        $this->db->join('customer', 't_sale.customer_id = customer.customer_id', 'left');
    
        // Custom search functionality
        if (!empty($_POST['search']['value'])) {
            $search_value = $_POST['search']['value'];
            $keywords = explode(" ", $search_value);
    
            $this->db->group_start();
            foreach ($keywords as $keyword) {
                $this->db->group_start();
                $this->db->like('nama_item', $keyword);
                $this->db->or_like('invoice', $keyword);
                $this->db->or_like('barcode', $keyword);
                $this->db->or_like('nama_customer', $keyword);
                $this->db->group_end();
            }
            $this->db->group_end();
        }
    
        // Handle sorting
        if (isset($_POST['order'][0]['column'])) {
            $column_index = intval($_POST['order'][0]['column']); // Column index
            $column_name = $_POST['columns'][$column_index]['data']; // Column name
            $column_sort_order = $_POST['order'][0]['dir'] === 'desc' ? 'DESC' : 'ASC';

            $allowed_columns = [
                'nama_customer' => 'nama_customer',
                'invoice'       => 'invoice',
                'nama_item'     => 'nama_item',
                'qty'           => 'qty',
                'price_sale'    => 'price_sale',
                'total'         => 'total',
                'date'          => 'date',
            ];
            if (isset($allowed_columns[$column_name])) {
                $this->db->order_by($allowed_columns[$column_name], $column_sort_order);
            }
        } else {
            // Default order by 'po' in descending order
            $this->db->order_by('detail_id', 'DESC');
        }
    
        // Count records for filtering
        $totalFiltered = $this->db->count_all_results('', false);
    
        // Apply pagination
        $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        $data = $query->result_array();
    
        // Count total records
        $totalRecords = $this->db->count_all('p_item');
    
        // Prepare the response with sequential numbers
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFiltered,
            "data" => array_map(function($row, $index) {
                return array(
                    'no' => $index + 1, // Sequential number
                    'nama_customer' => $row['nama_customer'],
                    'invoice' => $row['invoice'],
                    'nama_item' => $row['barcode'] . ' - ' . $row['nama_item'],
                    'qty' => $row['qty'],
                    'price_sale' => $row['price_sale'],
                    'total' => $row['total'],
                    'date' => date('d/m/Y', strtotime($row['date'])),
                    'action' => '<a href="' . site_url('retur/add/' . $row['sale_id'] . '/' . $row['item_id']) . '" class="btn btn-warning btn-xs"><i class="fa fa-undo"></i> Retur</a>'
                );
            }, $data, array_keys($data)) // Pass the index for sequential numbering
        );
    
        echo json_encode($output);
        exit();
    }

    function get_json_sale_history() {
        $draw = intval($this->input->post('draw'));

        $this->db->select('
            t_sale.sale_id,
            invoice,
            nama_customer,
            total_price,
            payment_method,
            date,
            user_id
        ');
        $this->db->from('t_sale');
        $this->db->join('customer', 't_sale.customer_id = customer.customer_id', 'left');

        // 🔒 Jika role kasir → hanya lihat transaksi miliknya
        if ($this->session->userdata('level') == '3') {
            $this->db->where('user_id', $this->session->userdata('user_id'));
        }

        // 🔎 Search
        if (!empty($_POST['search']['value'])) {
            $search = $_POST['search']['value'];
            $this->db->group_start();
            $this->db->like('invoice', $search);
            $this->db->or_like('nama_customer', $search);
            $this->db->group_end();
        }

        // Sorting
        if (isset($_POST['order'][0]['column'])) {
            $column_index = intval($_POST['order'][0]['column']);
            $column_name = $_POST['columns'][$column_index]['data'];
            $column_sort_order = $_POST['order'][0]['dir'] === 'desc' ? 'DESC' : 'ASC';

            $allowed_columns = [
                'invoice'        => 'invoice',
                'nama_customer'  => 'nama_customer',
                'total_price'    => 'total_price',
                'payment_method' => 'payment_method',
                'date'           => 'date',
            ];
            if (isset($allowed_columns[$column_name])) {
                $this->db->order_by($allowed_columns[$column_name], $column_sort_order);
            }
        } else {
            $this->db->order_by('date', 'DESC');
        }

        $totalFiltered = $this->db->count_all_results('', false);

        $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        $data = $query->result_array();

        $totalRecords = $this->db->count_all('t_sale');

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFiltered,
            "data" => array_map(function($row, $index) {
                return array(
                    'no' => $index + 1,
                    'invoice' => $row['invoice'],
                    'nama_customer' => $row['nama_customer'],
                    'total_price' => number_format($row['total_price'],0,',','.'),
                    'payment_method' => $row['payment_method'],
                    'date' => date('d/m/Y H:i', strtotime($row['date'])),
                    'action' => '
                        <a href="'.site_url('sale/detail/'.$row['sale_id']).'" class="btn btn-info btn-xs">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a href="'.site_url('sale/print/'.$row['sale_id']).'" class="btn btn-success btn-xs" target="_blank">
                            <i class="fa fa-print"></i>
                        </a>
                    '
                );
            }, $data, array_keys($data))
        );

        echo json_encode($output);
        exit();
    }

    
    
    
}