<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Supplier extends CI_Controller {

	function __construct(){
		parent::__construct();
		check_not_login();
		$this->load->model('supplier_m');
		$this->load->library('form_validation');
	}
	
	public function index()
	{	
		$data['row'] = $this->supplier_m->get();
		$this->template->load('template', 'supplier/supplier_data',$data);
	}

	public function add(){
		$supplier = new stdClass();
		$supplier->supplier_id = null;
		$supplier->nama_supplier = null;
		$supplier->phone = null;
		$supplier->alamat = null;
		$supplier->keterangan = null;
		$supplier->payment_term_days = 0;

		$data = array(
			'page' => 'add',
			'row' => $supplier
		);
		$this->template->load('template', 'supplier/supplier_form',$data);

	}

	public function edit($id)
	{
		$query = $this->supplier_m->get($id);
		if($query->num_rows() >  0){
			$supplier = $query->row();
			$data = array(
				'page' => 'edit',
				'row' => $supplier
			);
			$this->template->load('template', 'supplier/supplier_form', $data);
		}else{
			echo "<script>alert('Data Tidak Ditemukan');";
			redirect('supplier');
		}
	}

	public function process()
	{
		$this->form_validation->set_rules('nama_supplier', 'Nama Supplier', 'required|trim|min_length[2]|max_length[100]');
		$this->form_validation->set_rules('telp',          'No. Telepon',   'trim|max_length[20]');
		$this->form_validation->set_rules('alamat',        'Alamat',        'trim|max_length[255]');
		$this->form_validation->set_rules('keterangan',    'Keterangan',    'trim|max_length[255]');
		$this->form_validation->set_rules('payment_term_days', 'Termin Pembayaran', 'trim|numeric|greater_than_equal_to[0]');

		$this->form_validation->set_message('required',   '%s wajib diisi');
		$this->form_validation->set_message('min_length', '%s minimal 2 karakter');
		$this->form_validation->set_message('max_length', '%s terlalu panjang');
		$this->form_validation->set_message('numeric',    '%s harus berupa angka');
		$this->form_validation->set_message('greater_than_equal_to', '%s tidak boleh negatif');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', strip_tags(validation_errors()));
			redirect($this->input->server('HTTP_REFERER') ?: 'supplier');
			return;
		}

		$post = $this->input->post(null, TRUE);
		if (isset($_POST['add'])) {
			$this->supplier_m->add($post);
		} else if (isset($_POST['edit'])) {
			$this->supplier_m->edit($post);
		}
		if ($this->db->affected_rows() > 0) {
			$this->session->set_flashdata('success', 'Data Supplier berhasil disimpan');
		}
		redirect('supplier');
	}

	public function del($id)
	{
		if ($this->input->method() !== 'post') show_404();
		$this->supplier_m->del($id);
		if($this->db->affected_rows() > 0){
			$this->session->set_flashdata('success','Data Supplier berhasil dihapus');
		}
		redirect('supplier');
	}
}
