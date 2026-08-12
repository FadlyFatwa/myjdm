<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

	
	public function login()
	{
		check_already_login();
		$this->load->view('login');
	}

    public function process()
    {
        $this->output->set_content_type('application/json');

        // Rate limiting: max 5 percobaan dalam 15 menit
        $attempts     = (int) ($this->session->userdata('login_attempts') ?? 0);
        $last_attempt = (int) ($this->session->userdata('last_attempt')   ?? 0);

        if ($attempts >= 5 && (time() - $last_attempt) < 300) {
            $menit = ceil((300 - (time() - $last_attempt)) / 60);
            echo json_encode([
                'success' => false,
                'message' => "Terlalu banyak percobaan. Coba lagi dalam $menit menit."
            ]);
            return;
        }

        $post = $this->input->post(null, TRUE);
        $this->load->model('user_m');
        $row = $this->user_m->login($post);

        if ($row) {
            $this->session->sess_regenerate(TRUE);
            $this->session->unset_userdata(['login_attempts', 'last_attempt']);
            $this->session->set_userdata([
                'userid' => $row->user_id,
                'level'  => $row->level,
            ]);
            $level_names = [1 => 'Super Admin', 2 => 'Admin', 3 => 'Kasir', 4 => 'Gudang'];
            echo json_encode([
                'success'    => true,
                'redirect'   => site_url('dashboard'),
                'nama'       => $row->nama,
                'level_name' => $level_names[$row->level] ?? 'Staff',
                'level'      => (int) $row->level,
            ]);
        } else {
            $this->session->set_userdata('login_attempts', $attempts + 1);
            $this->session->set_userdata('last_attempt', time());
            echo json_encode([
                'success' => false,
                'message' => 'Username atau password salah.',
            ]);
        }
    }

	public function logout(){
		$params = array('userid', 'level');
		$this->session->unset_userdata($params);
		redirect('auth/login');
	}
}
