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

    /**
     * Konversi angka harga jadi kode PK (dipakai modul Purchasing — PO & Penerimaan).
     */
    public function price_to_pk($price)
    {
        $map   = ['0'=>'Y','1'=>'S','2'=>'I','3'=>'T','4'=>'O','5'=>'M','6'=>'P','7'=>'U','8'=>'L','9'=>'X'];
        $s     = preg_replace('/[^0-9]/', '', (string) $price);
        $out   = '';
        $zeros = 0;
        for ($i = 0; $i < strlen($s); $i++) {
            if ($s[$i] === '0') {
                $zeros++;
            } else {
                if ($zeros > 1) $out .= 'Y' . $zeros;
                elseif ($zeros === 1) $out .= 'Y';
                $zeros = 0;
                $out .= $map[$s[$i]] ?? $s[$i];
            }
        }
        if ($zeros > 1) $out .= 'Y' . $zeros;
        elseif ($zeros === 1) $out .= 'Y';
        return strtoupper($out);
    }

    /**
     * Susun kode PK dari harga beli, ditambah kode harga list (kalau ada) dengan
     * konvensi " | PL <kode>" yang sudah dipakai staff secara manual. Override
     * manual staff tetap dihormati kalau diisi.
     */
    public function build_pk($actual_price, $harga_list = null, $manual_pk = null)
    {
        $manual_pk = trim((string) $manual_pk);
        if ($manual_pk !== '') return strtoupper($manual_pk);

        $pk = $this->price_to_pk($actual_price);
        if ($harga_list !== null && (float) $harga_list > 0) {
            $pk .= ' | PL ' . $this->price_to_pk($harga_list);
        }
        return $pk;
    }

    /**
     * PPN mekanisme DPP Nilai Lain: 11/12 x tarif 12% (efektif 11%). Dipisah
     * jadi 2 faktor (bukan langsung 0.11) supaya gampang disesuaikan kalau
     * tarif/mekanismenya berubah lagi di kemudian hari.
     *
     * _tambah(): dipakai kalau harga yang diketik BELUM termasuk PPN — PPN
     * dihitung dari DPP lalu ditambahkan.
     * _ekstrak(): dipakai kalau harga yang diketik SUDAH termasuk PPN — PPN
     * "dikeluarkan lagi" dari angka yang sudah termasuk pajak itu (Total =
     * DPP x 1.11, jadi PPN = Total x (0.11/1.11)).
     */
    public function hitung_ppn_tambah($dpp)
    {
        return (int) round($dpp * (11 / 12) * 0.12);
    }

    public function hitung_ppn_ekstrak($total_termasuk_ppn)
    {
        return (int) round($total_termasuk_ppn * (0.11 / 1.11));
    }
}