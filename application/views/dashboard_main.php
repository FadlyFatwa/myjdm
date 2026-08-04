<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('dashboard'); ?>
<?php $this->load->view('dashboard_extra', $data ?? []); ?>
