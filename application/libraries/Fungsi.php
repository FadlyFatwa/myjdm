<?php

Class Fungsi {

    protected $ci;

    function __construct() {
        $this->ci =& get_instance();
    }

    function user_login() {
        $this->ci->load->model('user_m');
        $user_id = $this->ci->session->userdata('userid');
        $user_data = $this->ci->user_m->get($user_id)->row();
        return $user_data; 
    }
    function user_level_name()
    {
        $level = $this->user_login()->level;

        switch ($level) {
            case 1:
                return 'Superadmin';
            case 2:
                return 'Admin';
            case 3:
                return 'Kasir';
            case 4:
                return 'Gudang';
            default:
                return 'User';
        }
    }

    function PdfGenerator($html, $filename, $paper, $orientation) {
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper($paper, $orientation);
        // Render the HTML as PDF
        $dompdf->render();
        // Output the generated PDF to Browser
        $dompdf->stream($filename, array('Attachment' => 0));
    }


    public function count_item() {
        $this->ci->load->model('item_m');
        return $this->ci->item_m->get()->num_rows();
    }
    public function count_supplier() {
        $this->ci->load->model('supplier_m');
        return $this->ci->supplier_m->get()->num_rows();
    }
    public function count_customer() {
        $this->ci->load->model('customer_m');
        return $this->ci->customer_m->get()->num_rows();
    }
    public function count_user() {
        $this->ci->load->model('user_m');
        return $this->ci->user_m->get()->num_rows();
    }
}