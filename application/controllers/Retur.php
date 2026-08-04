<?php
defined('BASEPATH') OR exit('No direct script access allowed');
defined('BASEPATH') OR exit('No direct script access allowed');

class Retur extends CI_Controller {

    function __construct(){
        parent::__construct();
        check_not_login();
        $this->load->model('return_m');
        $this->load->model('sale_m');
        $this->load->model('item_m');
        
    }


    public function get_detail($return_id)
    {
        $return = $this->return_m->get_return($return_id)->row();
        $return_detail = $this->return_m->get_return_detail($return_id)->result();
        
        $data = [
            'return' => $return,
            'return_detail' => $return_detail
        ];

        echo json_encode($data);
    }

    public function index()
    {
        $data['returns'] = $this->return_m->get_return()->result();
        $this->template->load('template', 'retur/return_data', $data);
    }

    public function add($sale_id, $item_id = null)
    {
        // Validasi sale_id
        $sale = $this->sale_m->get_sale($sale_id)->row();
        if(!$sale) {
            $this->session->set_flashdata('error', 'Data penjualan tidak ditemukan');
            redirect('report/detail');
        }

        // Ambil detail penjualan
        $sale_detail = $this->sale_m->get_sale_detail($sale_id)->result();
        
        $data = [
            'sale_id' => $sale_id,
            'invoice' => $sale->invoice,
            'sale_detail' => $sale_detail,
            'customer_id' => $sale->customer_id,
            'nama_customer' => $sale->nama_customer ?? 'Pelanggan Umum',
            'selected_item_id' => $item_id
        ];
        
        $this->template->load('template', 'retur/return_form', $data);
    }

    public function process()
    {
        $data = $this->input->post(null, TRUE);
        $return_id = $this->return_m->add_return($data);

        $return_details = [];
        foreach ($data['items'] as $item) {
            if ($item['qty'] > 0) {
                array_push($return_details, [
                    'return_id' => $return_id,
                    'item_id' => $item['item_id'],
                    'qty' => $item['qty'],
                    'price_retur' => $item['price'],
                    'total' => $item['qty'] * $item['price']
                ]);
            }
        }

        $this->return_m->add_return_detail($return_details);

        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Data Retur berhasil disimpan');
        } else {
            $this->session->set_flashdata('error', 'Data Retur gagal disimpan');
        }
        redirect('retur');
    }

    public function sale_product($sale_id = null)
    {
        $detail = $this->return_m->get_return_detail($sale_id)->result();
        echo json_encode($detail);
    }

    public function del($return_id)
    {
        $this->return_m->delete_return($return_id);
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Data Retur berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Data Retur gagal dihapus');
        }
        redirect('retur');
    }
}
