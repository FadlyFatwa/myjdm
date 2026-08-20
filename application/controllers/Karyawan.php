<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Karyawan extends CI_Controller {

    function __construct(){
        parent::__construct();
        check_not_login();
        check_allowed_levels([1, 2]);
        $this->load->model('Karyawan_m');
        $this->load->model('User_m');
    }

    public function index()
    {
        $data['row'] = $this->Karyawan_m->get_list();
        $this->template->load('template', 'karyawan/karyawan_data', $data);
    }

    public function add(){
        $karyawan = new stdClass();
        $karyawan->karyawan_id = null;
        $karyawan->nama = null;
        $karyawan->user_id = null;

        $data = array(
            'page' => 'add',
            'row'  => $karyawan,
            'user_list' => $this->User_m->get()->result(),
        );
        $this->template->load('template', 'karyawan/karyawan_form', $data);
    }

    public function edit($id)
    {
        $query = $this->Karyawan_m->get($id);
        if($query->num_rows() > 0){
            $data = array(
                'page' => 'edit',
                'row'  => $query->row(),
                'user_list' => $this->User_m->get()->result(),
            );
            $this->template->load('template', 'karyawan/karyawan_form', $data);
        }else{
            echo "<script>alert('Data Tidak Ditemukan');";
            redirect('karyawan');
        }
    }

    public function process()
    {
        $post = $this->input->post(null, TRUE);
        if(empty($post['nama'])){
            $this->session->set_flashdata('error','Nama karyawan wajib diisi');
            redirect('karyawan');
            return;
        }
        if(isset($_POST['add'])){
            $this->Karyawan_m->add($post);
        }else if(isset($_POST['edit'])){
            $this->Karyawan_m->edit($post);
        }
        if($this->db->affected_rows() > 0){
            $this->session->set_flashdata('success','Data Karyawan berhasil disimpan');
        }
        redirect('karyawan');
    }

    public function del($id)
    {
        $this->Karyawan_m->del($id);
        if($this->db->affected_rows() > 0){
            $this->session->set_flashdata('success','Data Karyawan berhasil dihapus');
        }
        redirect('karyawan');
    }
}
