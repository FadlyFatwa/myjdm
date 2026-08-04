<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer extends CI_Controller {

	function __construct(){
		parent::__construct();
		check_not_login();
		$this->load->model('customer_m');
		$this->load->library('form_validation');
	}
	
	public function index()
	{	
		$data['row'] = $this->customer_m->get();
		$this->template->load('template', 'customer/customer_data',$data);
	}

	public function add(){
		$customer = new stdClass();
		$customer->customer_id = null;
		$customer->nama_customer = null;
		$customer->phone = null;
		$customer->alamat = null;
		$customer->keterangan = null;
		$customer->credit_limit = 0;
		$customer->payment_term_days = 0;
		$customer->ar_balance = 0;
		
		$data = array(
			'page' => 'add',
			'row' => $customer
		);
		$this->template->load('template', 'customer/customer_form',$data);

	}

	public function edit($id)
	{
		$query = $this->customer_m->get($id);
		if($query->num_rows() >  0){
			$customer = $query->row();
			$data = array(
				'page' => 'edit',
				'row' => $customer
			);
			$this->template->load('template', 'customer/customer_form', $data);
		}else{
			echo "<script>alert('Data Tidak Ditemukan');";
			redirect('customer');
		}
	}

	public function process()
	{
		$this->form_validation->set_rules('nama_customer', 'Nama Pembeli', 'required|trim|min_length[2]|max_length[100]');
		$this->form_validation->set_rules('telp',          'No. Telepon',  'trim|max_length[20]');
		$this->form_validation->set_rules('alamat',        'Alamat',       'trim|max_length[255]');
		$this->form_validation->set_rules('credit_limit',      'Limit Kredit',        'trim|numeric|greater_than_equal_to[0]');
		$this->form_validation->set_rules('payment_term_days', 'Termin Pembayaran',   'trim|numeric|greater_than_equal_to[0]');

		$this->form_validation->set_message('required',   '%s wajib diisi');
		$this->form_validation->set_message('min_length', '%s minimal 2 karakter');
		$this->form_validation->set_message('max_length', '%s terlalu panjang');
		$this->form_validation->set_message('numeric',    '%s harus berupa angka');
		$this->form_validation->set_message('greater_than_equal_to', '%s tidak boleh negatif');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', strip_tags(validation_errors()));
			redirect($this->input->server('HTTP_REFERER') ?: 'customer');
			return;
		}

		$post = $this->input->post(null, TRUE);
		if (isset($_POST['add'])) {
			$this->customer_m->add($post);
		} else if (isset($_POST['edit'])) {
			$this->customer_m->edit($post);
		}
		if ($this->db->affected_rows() > 0) {
			$this->session->set_flashdata('success', 'Data Pembeli berhasil disimpan');
		}
		redirect('customer');
	}

	public function del($id)
	{
		$this->customer_m->del($id);
		if($this->db->affected_rows() > 0){
			$this->session->set_flashdata('success','Data Pembeli berhasil dihapus');
		}
		redirect('customer');
	}
}
