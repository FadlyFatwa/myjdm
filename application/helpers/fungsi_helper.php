<?php

function check_already_login(){
    $ci =& get_instance();
    $user_session = $ci->session->userdata('userid');
    if($user_session){
        redirect('dashboard');
    }
}

function check_not_login(){
    $ci =& get_instance();
    $user_session = $ci->session->userdata('userid');
    if(!$user_session){
        redirect('auth/login');
    }
}

function check_admin(){
    $ci =& get_instance();
    $ci ->load->library('fungsi');
    if($ci->fungsi->user_login()->level != 1){
        redirect('dashboard');
    }

}

function check_allowed_levels(array $levels) {
    $ci    =& get_instance();
    $ci->load->library('fungsi');
    $level = (int) $ci->fungsi->user_login()->level;
    if (!in_array($level, $levels)) {
        redirect('dashboard');
    }
}

function indo_currency($nominal){
    $result = "Rp. " . number_format($nominal, 0, '.', '.');
    return $result;
}

function indo_currencyex($nominal){
    $result = "Rp" . number_format($nominal, 0, '.', '.');
    return $result;
}

function indo_date($date){
    $d = substr($date,8,2);
    $m = substr($date,5,2);
    $y = substr($date,0,4);
    return $d.'/'.$m. '/'.$y;
}

// Format tanggal khusus modul Finance (dd-mm-yyyy dengan strip), dipisah dari
// indo_date()/indo_datetime() (format slash) supaya tidak mengubah tampilan modul lain.
function tgl_finance($date){
    if (empty($date)) return '-';
    $d = substr($date,8,2);
    $m = substr($date,5,2);
    $y = substr($date,0,4);
    return $d.'-'.$m.'-'.$y;
}

function indo_datetime($datetime, $show_time = false)
{
    if (!$datetime) return '-';

    $date = new DateTime($datetime);

    if ($show_time) {
        return $date->format('d/m/Y H:i:s');
    }

    return $date->format('d/m/Y');
}

// Validasi tanggal transaksi agar logis (bukan cuma berformat benar). Dibuat untuk menolak
// tahun yang ke-corrupt jadi 2-3 digit lalu di-zero-pad oleh MySQL, misal "0026-06-06" atau
// "0226-06-17" -- ini terjadi saat user mengetik tahun pendek di <input type="date"> dan
// browser commit value tanpa auto-melengkapi ke 4 digit.
function is_sane_transaction_date($date_string, $min_year = 2014, $max_year_offset = 1)
{
    if (empty($date_string) || !is_string($date_string)) {
        return false;
    }
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $date_string, $m)) {
        return false;
    }
    [$y, $mo, $d] = [(int) $m[1], (int) $m[2], (int) $m[3]];
    if (!checkdate($mo, $d, $y)) {
        return false;
    }
    $max_year = (int) date('Y') + $max_year_offset;
    return $y >= $min_year && $y <= $max_year;
}

