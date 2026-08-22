<!DOCTYPE html>
<html>
<head>
  <link rel="icon"
  href="<?=base_url()?>assets/dist/img/LOGO JDM BW.jpg" class="img-circle" alt="User Image">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Jadi Motor Bandung </title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta name="csrf-token" content="<?= $this->security->get_csrf_hash() ?>">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="<?=base_url()?>assets/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?=base_url()?>assets/bower_components/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?=base_url()?>assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="<?=base_url()?>assets/bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?=base_url()?>assets/dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="<?=base_url()?>assets/dist/css/skins/_all-skins.min.css">
  <link href="<?=base_url()?>assets/plugins/DataTables/datatables.min.css" rel="stylesheet">
  <link href="<?=base_url()?>assets/plugins/DataTables/datatables.css" rel="stylesheet">
  <link href="<?=base_url()?>assets/plugins/DataTables/responsive.bootstrap.min.css" rel="stylesheet">
  <link href="<?=base_url()?>assets/plugins/DataTables/stateRestore.bootstrap.min.css" rel="stylesheet">
  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
  <link rel="stylesheet" href="<?=base_url()?>assets/plugins/sweetalert2/sweetalert2.min.css">
  <link rel="stylesheet" href="<?=base_url()?>assets/plugins/select2/select2.min.css">
  <!-- Anti-flash: apply dark mode sebelum render agar tidak kedip -->
  <script>
    (function(){
      if (localStorage.getItem('jdm_theme') === 'dark') {
        document.documentElement.classList.add('dark-mode');
      }
    })();
  </script>

  <style>
    .swal2-popup { font-size:1.4rem !important; }


    /* ═══════════════════════════════════════
       DARK MODE OVERRIDES
       ═══════════════════════════════════════ */
    html.dark-mode body,
    body.dark-mode {
      background-color: #131620 !important;
      color: #d1d5db;
    }

    /* Wrapper & Content */
    body.dark-mode .wrapper,
    html.dark-mode .wrapper            { background: #131620; }
    body.dark-mode .content-wrapper,
    html.dark-mode .content-wrapper    { background: #1a1d27 !important; }

    /* Navbar top */
    body.dark-mode .main-header .navbar,
    body.dark-mode .main-header .logo,
    html.dark-mode .main-header .navbar,
    html.dark-mode .main-header .logo  { background: #1f2335 !important; border-color: #2d3148 !important; }
    body.dark-mode .main-header .navbar .nav > li > a { color: #9ca3af !important; }
    body.dark-mode .main-header .navbar .nav > li > a:hover { color: #fff !important; background: rgba(255,255,255,.05) !important; }

    /* Boxes / Cards */
    body.dark-mode .box,
    html.dark-mode .box                { background: #222537 !important; border-color: #2d3148 !important; box-shadow: none !important; }
    body.dark-mode .box-header,
    html.dark-mode .box-header         { background: #1e2233 !important; border-color: #2d3148 !important; color: #e5e7eb !important; }
    body.dark-mode .box-body,
    html.dark-mode .box-body           { background: #222537 !important; }
    body.dark-mode .box-footer,
    html.dark-mode .box-footer         { background: #1e2233 !important; border-color: #2d3148 !important; }
    body.dark-mode .box-title,
    body.dark-mode .box-header h3      { color: #e5e7eb !important; }
    body.dark-mode .box-solid          { border: 1px solid #2d3148 !important; }

    /* Small boxes (dashboard) */
    body.dark-mode .small-box          { background: #1e2233 !important; }
    body.dark-mode .small-box h3,
    body.dark-mode .small-box p        { color: #fff !important; }

    /* Tables */
    body.dark-mode .table,
    body.dark-mode table               { color: #d1d5db !important; }
    body.dark-mode .table > thead > tr > th,
    body.dark-mode .table > thead > tr > td { background: #1a1d27 !important; color: #9ca3af !important; border-color: #2d3148 !important; }
    body.dark-mode .table > tbody > tr > td,
    body.dark-mode .table > tbody > tr > th { border-color: #2d3148 !important; color: #d1d5db; }
    body.dark-mode .table-bordered      { border-color: #2d3148 !important; }
    body.dark-mode .table-striped > tbody > tr:nth-of-type(odd) { background: #1e2233 !important; }
    body.dark-mode .table-hover > tbody > tr:hover { background: #2a2d40 !important; }
    body.dark-mode .bg-gray-light,
    body.dark-mode thead.bg-primary     { background: #1a1d27 !important; }
    body.dark-mode .table > tfoot > tr,
    body.dark-mode .table > tfoot > tr > td,
    body.dark-mode .table > tfoot > tr > th { background: #1a1d27 !important; color: #d1d5db !important; border-color: #2d3148 !important; }
    body.dark-mode .table tr.bg-gray,
    body.dark-mode .table tr.bg-gray > td,
    body.dark-mode .table tr.bg-gray > th   { background: #1a1d27 !important; color: #d1d5db !important; border-color: #2d3148 !important; }

    /* Inputs */
    body.dark-mode .form-control,
    body.dark-mode input[type="text"],
    body.dark-mode input[type="number"],
    body.dark-mode input[type="date"],
    body.dark-mode input[type="email"],
    body.dark-mode input[type="password"],
    body.dark-mode select,
    body.dark-mode textarea            { background: #252836 !important; color: #e5e7eb !important; border-color: #374151 !important; }
    body.dark-mode .form-control:focus { border-color: #00a65a !important; background: #2a2d3e !important; }
    body.dark-mode .input-group-addon  { background: #1e2233 !important; color: #9ca3af !important; border-color: #374151 !important; }

    /* Select2 */
    body.dark-mode .select2-container--default .select2-selection--single { background: #252836 !important; border-color: #374151 !important; color: #e5e7eb !important; }
    body.dark-mode .select2-container--default .select2-selection--single .select2-selection__rendered { color: #e5e7eb !important; }
    body.dark-mode .select2-dropdown   { background: #252836 !important; border-color: #374151 !important; }
    body.dark-mode .select2-results__option { color: #d1d5db !important; }
    body.dark-mode .select2-results__option--highlighted { background: #00a65a !important; }

    /* Text */
    body.dark-mode h1,body.dark-mode h2,body.dark-mode h3,
    body.dark-mode h4,body.dark-mode h5,body.dark-mode h6 { color: #e5e7eb; }
    body.dark-mode p, body.dark-mode label                { color: #9ca3af; }
    body.dark-mode .text-muted                             { color: #6b7280 !important; }

    /* Breadcrumb */
    body.dark-mode .content-header     { background: transparent; }
    body.dark-mode .breadcrumb         { background: transparent; }
    body.dark-mode .breadcrumb > li,
    body.dark-mode .breadcrumb > li a  { color: #6b7280; }

    /* Modal */
    body.dark-mode .modal-content      { background: #222537 !important; border-color: #2d3148 !important; }
    body.dark-mode .modal-header,
    body.dark-mode .modal-footer       { background: #1e2233 !important; border-color: #2d3148 !important; }
    body.dark-mode .modal-title        { color: #e5e7eb !important; }

    /* Dropdown menu */
    body.dark-mode .dropdown-menu      { background: #1e2233 !important; border-color: #2d3148 !important; }
    body.dark-mode .dropdown-menu > li > a { color: #d1d5db !important; }
    body.dark-mode .dropdown-menu > li > a:hover { background: #2a2d40 !important; }
    body.dark-mode .dropdown-menu .divider { background: #2d3148 !important; }

    /* DataTables wrapper bg */
    body.dark-mode .dataTables_wrapper      { color: #d1d5db !important; }

    /* DataTables length & filter labels */
    body.dark-mode .dataTables_wrapper .dataTables_length label,
    body.dark-mode .dataTables_wrapper .dataTables_filter label,
    body.dark-mode .dataTables_info         { color: #9ca3af !important; }

    /* DataTables length select */
    body.dark-mode .dataTables_wrapper .dataTables_length select {
      background: #252836 !important;
      color: #e5e7eb !important;
      border: 1px solid #374151 !important;
    }

    /* DataTables search input */
    body.dark-mode .dataTables_wrapper .dataTables_filter input {
      background: #252836 !important;
      color: #e5e7eb !important;
      border: 1px solid #374151 !important;
    }
    body.dark-mode .dataTables_wrapper .dataTables_filter input::placeholder { color: #6b7280 !important; }

    /* Pagination — Bootstrap DataTables renders as .pagination li > a */
    body.dark-mode .dataTables_paginate .paginate_button,
    body.dark-mode .dataTables_paginate .paginate_button:hover {
      color: #9ca3af !important;
    }
    body.dark-mode .dataTables_wrapper .pagination > li > a,
    body.dark-mode .dataTables_wrapper .pagination > li > span {
      background: #252836 !important;
      color: #9ca3af !important;
      border-color: #374151 !important;
    }
    body.dark-mode .dataTables_wrapper .pagination > li > a:hover,
    body.dark-mode .dataTables_wrapper .pagination > li > span:hover {
      background: #2a2d40 !important;
      color: #fff !important;
      border-color: #374151 !important;
    }
    body.dark-mode .dataTables_wrapper .pagination > .active > a,
    body.dark-mode .dataTables_wrapper .pagination > .active > span,
    body.dark-mode .dataTables_wrapper .pagination > .active > a:hover {
      background: #00a65a !important;
      border-color: #00a65a !important;
      color: #fff !important;
    }
    body.dark-mode .dataTables_wrapper .pagination > .disabled > a,
    body.dark-mode .dataTables_wrapper .pagination > .disabled > span {
      background: #1a1d27 !important;
      color: #4b5563 !important;
      border-color: #2d3148 !important;
    }

    /* ── Sale Form right panel ── */
    body.dark-mode .pos-panel              { background: #1e2233 !important; border-color: #2d3148 !important; }
    body.dark-mode .pos-section            { border-color: #2d3148 !important; }
    body.dark-mode .pos-label              { color: #9ca3af !important; }

    /* Payment tabs */
    body.dark-mode .pay-tab                {
      background: #252836 !important;
      border-color: #374151 !important;
      color: #9ca3af !important;
    }
    body.dark-mode .pay-tab.active-cash    { background: #0d2e1d !important; border-color: #00a65a !important; color: #00a65a !important; }
    body.dark-mode .pay-tab.active-transfer{ background: #0d1e2e !important; border-color: #3c8dbc !important; color: #3c8dbc !important; }
    body.dark-mode .pay-tab.active-credit  { background: #2e0d0d !important; border-color: #dd4b39 !important; color: #dd4b39 !important; }
    body.dark-mode .pay-tab.active-qris    { background: #2a1a33 !important; border-color: #8e44ad !important; color: #8e44ad !important; }
    body.dark-mode .pay-tab.active-debit   { background: #0d2e28 !important; border-color: #16a085 !important; color: #16a085 !important; }

    /* Edit-cart modal info box (Barcode / PK / Nama Barang) */
    body.dark-mode .edit-info-box              { background: #1a1d27 !important; }
    body.dark-mode .edit-info-box input[type="text"] { background: transparent !important; color: #e5e7eb !important; }
    body.dark-mode .edit-info-box > div > div:first-child { color: #6b7280 !important; }

    /* Search bar */
    body.dark-mode #search-wrap            { background: #252836 !important; border-color: #374151 !important; }
    body.dark-mode #search_item            { color: #e5e7eb !important; background: transparent !important; }
    body.dark-mode #search_item::placeholder { color: #6b7280 !important; }

    /* Barang masuk indicator */
    body.dark-mode #barang_masuk-wrap      { background: #1a2820 !important; border-color: #2d4a38 !important; }
    body.dark-mode #barang_masuk           { color: #d1d5db !important; }

    /* Search result dropdown */
    body.dark-mode #result_item            { background: #252836 !important; border-color: #374151 !important; }
    body.dark-mode #result_item .list-group-item {
      background: #252836 !important;
      border-color: #374151 !important;
      color: #d1d5db !important;
    }
    body.dark-mode #result_item .list-group-item:hover { background: #2a2d40 !important; }
    body.dark-mode #result_item small      { color: #9ca3af !important; }
    body.dark-mode #result_item .label-success { background: #1a4a30 !important; }
    body.dark-mode #result_item .label-danger  { background: #4a1a1a !important; }

    /* ── SweetAlert2 dark mode ── */
    body.dark-mode .swal2-popup {
      background: #1e2233 !important;
      border: 1px solid #2d3148;
      color: #d1d5db !important;
    }
    body.dark-mode .swal2-title        { color: #e5e7eb !important; }
    body.dark-mode .swal2-html-container,
    body.dark-mode .swal2-content      { color: #9ca3af !important; }
    body.dark-mode .swal2-footer       { border-top-color: #2d3148 !important; color: #6b7280 !important; }
    body.dark-mode .swal2-footer button { background: #252836 !important; color: #d1d5db !important; border: 1px solid #374151 !important; }
    body.dark-mode .swal2-footer button:hover { background: #2a2d40 !important; }

    /* Icon border warna sesuai type — biar tidak hilang di bg gelap */
    body.dark-mode .swal2-icon.swal2-warning { border-color: #f59e0b !important; color: #f59e0b !important; }
    body.dark-mode .swal2-icon.swal2-error   { border-color: #ef4444 !important; color: #ef4444 !important; }
    body.dark-mode .swal2-icon.swal2-success  { border-color: #00a65a !important; }
    body.dark-mode .swal2-icon.swal2-success .swal2-success-ring { border-color: rgba(0,166,90,.3) !important; }
    body.dark-mode .swal2-icon.swal2-info    { border-color: #3b82f6 !important; color: #3b82f6 !important; }
    body.dark-mode .swal2-icon.swal2-question { border-color: #8b5cf6 !important; color: #8b5cf6 !important; }

    /* Input di dalam SweetAlert2 (jika ada) */
    body.dark-mode .swal2-input,
    body.dark-mode .swal2-textarea     { background: #252836 !important; color: #e5e7eb !important; border-color: #374151 !important; }

    /* Well (Bootstrap) */
    body.dark-mode .well { background: #1e2233 !important; border-color: #2d3148 !important; color: #9ca3af !important; }
    body.dark-mode .well kbd { background: #252836 !important; border-color: #374151 !important; color: #e5e7eb !important; box-shadow: none !important; }

    /* Alert Bootstrap */
    body.dark-mode .alert-info    { background: #0d1e2e !important; border-color: #1a3a52 !important; color: #7ec8e3 !important; }
    body.dark-mode .alert-success { background: #0d2e1d !important; border-color: #1a4a30 !important; color: #6ee7b7 !important; }
    body.dark-mode .alert-warning { background: #2e1d0d !important; border-color: #4a3010 !important; color: #fcd34d !important; }
    body.dark-mode .alert-danger  { background: #2e0d0d !important; border-color: #4a1515 !important; color: #f87171 !important; }

    /* Scrollbar */
    body.dark-mode ::-webkit-scrollbar       { width: 6px; height: 6px; }
    body.dark-mode ::-webkit-scrollbar-track { background: #1a1d27; }
    body.dark-mode ::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }

    /* Theme toggle button */
    #theme-toggle {
      font-size: 16px;
      padding: 0 14px !important;
      line-height: 50px;
      display: block;
      color: inherit;
    }
    #theme-toggle:hover { background: rgba(255,255,255,.05) !important; }

    /* ── User dropdown ───────────────────────────────── */
    .user-menu > a { display:flex !important; align-items:center; gap:8px; padding:0 14px !important; height:50px; }
    .user-avatar-sm {
      width:30px; height:30px; border-radius:50%; flex-shrink:0;
      font-size:11px; font-weight:700; color:#fff;
      display:inline-flex; align-items:center; justify-content:center;
    }
    .user-avatar-sm.lvl-1 { background:#e74c3c; }
    .user-avatar-sm.lvl-2 { background:#3c8dbc; }
    .user-avatar-sm.lvl-3 { background:#00a65a; }
    .user-avatar-sm.lvl-4 { background:#f39c12; }

    .user-menu .dropdown-menu {
      min-width:230px; right:0; left:auto;
      border-radius:12px; overflow:hidden;
      border:none; padding:0;
      box-shadow:0 12px 40px rgba(0,0,0,.2);
    }
    .user-dh {
      padding:16px; display:flex; align-items:center; gap:12px;
    }
    .user-dh.lvl-1 { background:linear-gradient(135deg,#c0392b,#e74c3c); }
    .user-dh.lvl-2 { background:linear-gradient(135deg,#2980b9,#3c8dbc); }
    .user-dh.lvl-3 { background:linear-gradient(135deg,#00a65a,#00c96e); }
    .user-dh.lvl-4 { background:linear-gradient(135deg,#e67e22,#f39c12); }
    .user-avatar-lg {
      width:44px; height:44px; border-radius:50%; flex-shrink:0;
      background:rgba(255,255,255,.22); color:#fff;
      font-size:17px; font-weight:700;
      display:flex; align-items:center; justify-content:center;
    }
    .user-dh-info strong { display:block; color:#fff; font-size:14px; line-height:1.3; }
    .user-dh-info small  { color:rgba(255,255,255,.8); font-size:11px; }

    .user-menu-items { padding:6px 0; }
    .user-menu-items a {
      display:flex; align-items:center; gap:10px;
      padding:10px 16px; font-size:13px; color:#374151;
      text-decoration:none; transition:background .15s;
    }
    .user-menu-items a:hover { background:#f3f4f6; }
    .user-menu-items a i { font-size:14px; width:16px; color:#6b7280; }
    .user-menu-divider { height:1px; background:#e5e7eb; margin:4px 0; }
    .user-menu-footer { padding:8px 12px 12px; }
    .btn-signout {
      display:flex !important; align-items:center; justify-content:center; gap:8px;
      width:100%; padding:9px 0 !important;
      background:#ef4444 !important; color:#fff !important;
      border:none; border-radius:8px;
      font-size:13px; font-weight:600;
      text-decoration:none; transition:background .15s;
    }
    .btn-signout:hover { background:#dc2626 !important; color:#fff !important; }

    body.dark-mode .user-menu .dropdown-menu { background:#1e2535; }
    body.dark-mode .user-menu-items a { color:#d1d5db; }
    body.dark-mode .user-menu-items a:hover { background:#252d40; }
    body.dark-mode .user-menu-items a i { color:#9ca3af; }
    body.dark-mode .user-menu-divider { background:#2d3748; }
    body.dark-mode .user-menu-footer  { background:#1e2535; }
  </style>
</head>
<body class="hold-transition skin-green sidebar-mini fixed <?=$this->uri->segment(1) == 'sale' ||  ($this->uri->segment(1) == 'item' && $this->uri->segment(2) == '') ? 'sidebar-collapse' : null?>">
<!-- Site wrapper -->
<div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a  class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>J</b>DM</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>Jadi Motor</b>  <?= $this->fungsi->user_level_name(); ?> </span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Notification Bell (level 1 & 2 only) -->
          <?php if (in_array($this->fungsi->user_login()->level, [1, 2])): ?>
          <li id="notif-bell-li">
            <a href="#" id="notif-bell-btn">
              <i class="fa fa-bell-o"></i>
              <span class="label label-warning" id="notif-badge" style="display:none">0</span>
            </a>
          </li>
          <?php endif; ?>
          <!-- Theme Toggle -->
          <li>
            <a href="#" id="theme-toggle" title="Ganti tema gelap/terang">
              <i class="fa fa-moon-o" id="theme-icon"></i>
            </a>
          </li>
          <?php
            $_u2       = $this->fungsi->user_login();
            $_lvl2     = (int) $_u2->level;
            $_lvlnames = [1=>'Super Admin',2=>'Admin',3=>'Kasir',4=>'Gudang'];
            $_lvlname2 = $_lvlnames[$_lvl2] ?? 'Staff';
            $_initials2 = implode('', array_map(function($w){ return strtoupper(substr($w,0,1)); }, array_slice(explode(' ',$_u2->nama),0,2)));
          ?>
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <span class="user-avatar-sm lvl-<?= $_lvl2 ?>"><?= $_initials2 ?></span>
              <span class="hidden-xs" style="line-height:1.2">
                <small style="display:block;font-size:10px;opacity:.75"><?= $_lvlname2 ?></small>
                <strong style="font-size:13px"><?= htmlspecialchars($_u2->nama) ?></strong>
              </span>
              <i class="fa fa-angle-down hidden-xs" style="font-size:11px;opacity:.55;margin-left:2px"></i>
            </a>
            <ul class="dropdown-menu">
              <li>
                <div class="user-dh lvl-<?= $_lvl2 ?>">
                  <div class="user-avatar-lg"><?= $_initials2 ?></div>
                  <div class="user-dh-info">
                    <strong><?= htmlspecialchars($_u2->nama) ?></strong>
                    <small><?= $_lvlname2 ?></small>
                  </div>
                </div>
              </li>
              <li>
                <div class="user-menu-items">
                  <a href="#" id="btn-lihat-profil"><i class="fa fa-id-card-o"></i> Lihat Profil</a>
                  <a href="#" id="btn-ganti-profil"><i class="fa fa-key"></i> Ubah Username & Password</a>
                  <div class="user-menu-divider"></div>
                </div>
              </li>
              <li>
                <div class="user-menu-footer">
                  <a href="<?=site_url('auth/logout')?>" class="btn-signout">
                    <i class="fa fa-sign-out"></i> Sign Out
                  </a>
                </div>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>
  </header>

  <!-- =============================================== -->

  <!-- Left side column. contains the sidebar -->
<?php
 $user  = $this->fungsi->user_login();
$level = $user->level;

$seg1 = $this->uri->segment(1) ?? '';
$seg2 = $this->uri->segment(2) ?? '';
?>

<aside class="main-sidebar">
    <section class="sidebar">

        <!-- USER PANEL -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= base_url('assets/dist/img/LOGO JDM BW.jpg') ?>" 
                     class="img-circle" 
                     style="border: 2px solid #3c8dbc;" 
                     alt="User Image">
            </div>
            <div class="pull-left info">
                <p><?= htmlspecialchars($this->fungsi->user_level_name(), ENT_QUOTES, 'UTF-8'); ?></p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <!-- MENU -->
        <ul class="sidebar-menu" data-widget="tree">

            <!-- DASHBOARD -->
            <li class="header">UTAMA</li>
            <li <?= in_array($seg1, ['dashboard', '']) ? 'class="active"' : '' ?>>
                <a href="<?= site_url('dashboard') ?>">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>

            <!-- MASTER DATA -->
            <?php if(in_array($level, [1,2,3,4])): ?>
            <li class="header">MASTER DATA</li>

                <?php if(in_array($level, [1,2])): ?>
                <li <?= $seg1 == 'supplier' ? 'class="active"' : '' ?>>
                    <a href="<?= site_url('supplier') ?>">
                        <i class="fa fa-truck"></i> <span>Supplier</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if(in_array($level, [1,2,3])): ?>
                <li <?= $seg1 == 'customer' ? 'class="active"' : '' ?>>
                    <a href="<?= site_url('customer') ?>">
                        <i class="fa fa-users"></i> <span>Pembeli</span>
                    </a>
                </li>
                <?php endif ?>
                <?php if(in_array($this->fungsi->user_login()->level, [1, 2])) : ?>
              <li class="treeview <?= in_array($this->uri->segment(1), ['category', 'stock', 'unit', 'item', 'item_pending', 'service-item']) ? 'active menu-open' : '' ?>">
                  <a href="#">
                      <i class="fa fa-archive"></i> <span>Manajemen Produk</span>
                      <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                  </a>
                  <ul class="treeview-menu">
                      <li <?= ($this->uri->segment(1) == 'item' && $this->uri->segment(2) == '') ? 'class="active"' : '' ?>>
                          <a href="<?= site_url('item') ?>"><i class="fa fa-circle-o"></i> Daftar Barang</a>
                      </li>
                      <li <?= ($this->uri->segment(1) == 'item' && $this->uri->segment(2) == 'duplicate') ? 'class="active"' : '' ?>>
                          <a href="<?= site_url('item/duplicate') ?>"><i class="fa fa-clone text-orange"></i> Deteksi Item Mirip</a>
                      </li>
                      <!-- <li <?= ($this->uri->segment(1) == 'item' && $this->uri->segment(2) == 'multi_supplier') ? 'class="active"' : '' ?>>
                          <a href="<?= site_url('item/multi_supplier') ?>"><i class="fa fa-truck text-blue"></i> Barang Multi Supplier</a>
                      </li> -->

                      <li <?= ($this->uri->segment(1) == 'stock' && $this->uri->segment(2) == 'in') ? 'class="active"' : '' ?>>
                          <a href="<?= site_url('stock/in') ?>"><i class="fa fa-download text-green"></i> Stok Masuk</a>
                      </li>
                      <!-- <li <?= ($this->uri->segment(1) == 'stock' && $this->uri->segment(2) == 'in_report') ? 'class="active"' : '' ?>>
                          <a href="<?= site_url('stock/in/report') ?>"><i class="fa fa-download text-green"></i> Laporan Stok Masuk</a>
                      </li> -->
                      <li <?= ($this->uri->segment(1) == 'stock' && $this->uri->segment(2) == 'out') ? 'class="active"' : '' ?>>
                          <a href="<?= site_url('stock/out') ?>"><i class="fa fa-upload text-red"></i> Barang Keluar</a>
                      </li>

                      <li class="divider" style="border-top: 1px solid #4b646f; margin: 5px 10px;"></li>

                      <li <?= $this->uri->segment(1) == 'service-item' ? 'class="active"' : '' ?>>
                          <a href="<?= site_url('service-item') ?>"><i class="fa fa-wrench text-blue"></i> Barang Jasa</a>
                      </li>

                      <li class="divider" style="border-top: 1px solid #4b646f; margin: 5px 10px;"></li>

                      <li <?= ($this->uri->segment(1) == 'item' && $this->uri->segment(2) == 'temporary') ? 'class="active"' : '' ?>>
                          <a href="<?= site_url('item/temporary') ?>"><i class="fa fa-hourglass-start text-yellow"></i> Barang Sementara</a>
                      </li>
                      <li <?= $this->uri->segment(1) == 'item_pending' ? 'class="active"' : '' ?>>
                          <a href="<?= site_url('item_pending') ?>"><i class="fa fa-pause-circle"></i> Barang Pending</a>
                      </li>

                      <li class="divider" style="border-top: 1px solid #4b646f; margin: 5px 10px;"></li>

                      <li <?= $this->uri->segment(1) == 'category' ? 'class="active"' : '' ?>>
                          <a href="<?= site_url('category') ?>"><i class="fa fa-tags"></i> Kategori</a>
                      </li>
                      <li <?= $this->uri->segment(1) == 'unit' ? 'class="active"' : '' ?>>
                          <a href="<?= site_url('unit') ?>"><i class="fa fa-balance-scale"></i> Units</a>
                      </li>
                      <li <?= ($this->uri->segment(2) == 'archive' ? 'class="active"' : '') ?>>
                          <a href="<?= site_url('item/archive') ?>"><i class="fa fa-trash-o text-muted"></i> Arsip Barang</a>
                      </li>
                  </ul>
              </li>

                <!-- Menu Mapping & Master Data (level 1-2) -->
                <!-- <li class="treeview <?= in_array($seg1, ['master_barang','vehicle','item_mapping']) ? 'active menu-open' : '' ?>">
                    <a href="#">
                        <i class="fa fa-sitemap"></i> <span>Mapping Barang</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li <?= $seg1 == 'item_mapping' ? 'class="active"' : '' ?>>
                            <a href="<?= site_url('item_mapping') ?>">
                                <i class="fa fa-link"></i> Item Mapping
                            </a>
                        </li>
                        <li <?= $seg1 == 'master_barang' ? 'class="active"' : '' ?>>
                            <a href="<?= site_url('master_barang') ?>">
                                <i class="fa fa-cube"></i> Master Barang
                            </a>
                        </li>
                        <li <?= $seg1 == 'vehicle' ? 'class="active"' : '' ?>>
                            <a href="<?= site_url('vehicle') ?>">
                                <i class="fa fa-car"></i> Kendaraan
                            </a>
                        </li>
                    </ul>
                </li> -->
                <?php endif; ?>

                <?php if($level == 3): ?>
                <li <?= $seg1 == 'item' ? 'class="active"' : '' ?>>
                    <a href="<?= site_url('item') ?>">
                        <i class="fa fa-archive"></i> <span>Daftar Barang</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if($level == 4): ?>
                <li <?= $seg1 == 'item_pending' ? 'class="active"' : '' ?>>
                    <a href="<?= site_url('item_pending') ?>">
                        <i class="fa fa-archive"></i> <span>Barang Pending</span>
                    </a>
                </li>
                <?php endif; ?>

            <?php endif; ?>

            <!-- TRANSAKSI -->
             <?php if(in_array($level, [1,2,3])): ?>
            <li class="header">TRANSAKSI</li>
            <li <?= $seg1 == 'sale' ? 'class="active"' : '' ?>>
                <a href="<?= site_url('sale') ?>">
                    <i class="fa fa-shopping-cart text-aqua"></i> <span>Penjualan</span>
                </a>
            </li>

            <li <?= $seg1 == 'retur' ? 'class="active"' : '' ?>>
                <a href="<?= site_url('retur') ?>">
                    <i class="fa fa-undo text-yellow"></i> <span>Retur Barang</span>
                </a>
            </li>
            <?php endif; ?>

            <!-- PURCHASING -->
            <?php if(in_array($level, [1,2])): ?>
            <li class="header">PURCHASING</li>
            <li class="treeview <?= in_array($seg1, ['stock-review','po-cart','purchase-order']) ? 'active menu-open' : '' ?>">
                <a href="#">
                    <i class="fa fa-shopping-basket text-orange"></i>
                    <span>Pembelian</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <?php
                    // Segmen ke-2 di bawah purchase-order/* menentukan controller mana yang
                    // sebetulnya melayani halaman itu sejak Penerimaan dipisah dari Purchase_order.php:
                    // 'receiving'/'receive' & 'history' => controller Penerimaan, sisanya (index,
                    // detail PO, print) => controller Purchase_order. Dipakai supaya highlight menu
                    // aktif tidak dobel/salah kamar.
                    $po_seg2 = $this->uri->segment(2);
                    $is_penerimaan_area = in_array($po_seg2, ['receiving', 'receive']);
                    $is_history_area    = $po_seg2 == 'history';
                    $is_po_area         = $seg1 == 'purchase-order' && !$is_penerimaan_area && !$is_history_area;
                ?>
                <ul class="treeview-menu">
                    <li <?= $seg1 == 'stock-review' ? 'class="active"' : '' ?>>
                        <a href="<?= site_url('stock-review') ?>">
                            <i class="fa fa-bar-chart text-aqua"></i> Review Stok
                        </a>
                    </li>
                    <li <?= $seg1 == 'po-cart' ? 'class="active"' : '' ?>>
                        <a href="<?= site_url('po-cart') ?>">
                            <i class="fa fa-cart-plus text-green"></i> Keranjang PO
                            <?php
                              $cart_count = (int) $this->db->count_all('po_cart');
                              if ($cart_count > 0):
                            ?>
                            <span class="pull-right-container">
                                <span class="label label-warning pull-right"><?= $cart_count ?></span>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li <?= $is_po_area ? 'class="active"' : '' ?>>
                        <a href="<?= site_url('purchase-order') ?>">
                            <i class="fa fa-file-text-o text-blue"></i> Purchase Order
                        </a>
                    </li>
                    <li <?= ($seg1 == 'purchase-order' && $is_penerimaan_area) ? 'class="active"' : '' ?>>
                        <a href="<?= site_url('purchase-order/receiving') ?>">
                            <i class="fa fa-inbox text-orange"></i> Penerimaan
                            <?php
                                $recv_count = (int) $this->db->where_in('status', ['sent','partial'])->count_all_results('po_header');
                                if ($recv_count > 0):
                            ?>
                            <span class="pull-right-container">
                                <span class="label label-warning pull-right"><?= $recv_count ?></span>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li <?= ($seg1 == 'purchase-order' && $is_history_area) ? 'class="active"' : '' ?>>
                        <a href="<?= site_url('purchase-order/history') ?>">
                            <i class="fa fa-history text-aqua"></i> Histori Penerimaan
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>

            <!-- FINANCE -->
            <?php if(in_array($level, [1,2])): ?>
            <li class="header">FINANCE</li>
            <li class="treeview <?= in_array($seg1, ['coa','journal','ar-invoice','ar-payment','kontra-bon','kontra-bon-payment','pembayaran-masuk','report-ar','ap-invoice','ap-payment','ap-kontra-bon','ap-kontra-bon-payment','pembayaran-keluar','report-ap','beban']) ? 'active menu-open' : '' ?>">
                <a href="#">
                    <i class="fa fa-money text-green"></i>
                    <span>Finance</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="treeview <?= in_array($seg1, ['ar-invoice','ar-payment','kontra-bon','kontra-bon-payment','pembayaran-masuk','report-ar']) ? 'active menu-open' : '' ?>">
                        <a href="#">
                            <i class="fa fa-file-text-o text-blue"></i> Piutang
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li <?= $seg1 == 'ar-invoice' ? 'class="active"' : '' ?>>
                                <a href="<?= site_url('ar-invoice') ?>">
                                    <i class="fa fa-circle-o"></i> Piutang (AR)
                                </a>
                            </li>
                            <li <?= $seg1 == 'kontra-bon' ? 'class="active"' : '' ?>>
                                <a href="<?= site_url('kontra-bon') ?>">
                                    <i class="fa fa-circle-o"></i> Kontra Bon
                                </a>
                            </li>
                            <li <?= $seg1 == 'pembayaran-masuk' ? 'class="active"' : '' ?>>
                                <a href="<?= site_url('pembayaran-masuk') ?>">
                                    <i class="fa fa-circle-o"></i> Pembayaran Masuk
                                </a>
                            </li>
                            <li <?= $seg1 == 'report-ar' ? 'class="active"' : '' ?>>
                                <a href="<?= site_url('report-ar') ?>">
                                    <i class="fa fa-circle-o"></i> Laporan Piutang
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview <?= in_array($seg1, ['ap-invoice','ap-payment','ap-kontra-bon','ap-kontra-bon-payment','pembayaran-keluar','report-ap']) ? 'active menu-open' : '' ?>">
                        <a href="#">
                            <i class="fa fa-file-text text-red"></i> Hutang
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li <?= $seg1 == 'ap-invoice' ? 'class="active"' : '' ?>>
                                <a href="<?= site_url('ap-invoice') ?>">
                                    <i class="fa fa-circle-o"></i> Hutang (AP)
                                </a>
                            </li>
                            <li <?= $seg1 == 'ap-kontra-bon' ? 'class="active"' : '' ?>>
                                <a href="<?= site_url('ap-kontra-bon') ?>">
                                    <i class="fa fa-circle-o"></i> Kontra Bon Hutang
                                </a>
                            </li>
                            <li <?= $seg1 == 'pembayaran-keluar' ? 'class="active"' : '' ?>>
                                <a href="<?= site_url('pembayaran-keluar') ?>">
                                    <i class="fa fa-circle-o"></i> Pembayaran Keluar
                                </a>
                            </li>
                            <li <?= $seg1 == 'report-ap' ? 'class="active"' : '' ?>>
                                <a href="<?= site_url('report-ap') ?>">
                                    <i class="fa fa-circle-o"></i> Laporan Hutang
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php if(in_array($level, [1,2])): ?>
                    <li class="treeview <?= in_array($seg1, ['beban','journal','coa']) ? 'active menu-open' : '' ?>">
                        <a href="#">
                            <i class="fa fa-briefcase text-yellow"></i> Operasional
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li <?= $seg1 == 'beban' ? 'class="active"' : '' ?>>
                                <a href="<?= site_url('beban') ?>">
                                    <i class="fa fa-circle-o"></i> Beban Operasional
                                </a>
                            </li>
                            <li <?= $seg1 == 'journal' ? 'class="active"' : '' ?>>
                                <a href="<?= site_url('journal') ?>">
                                    <i class="fa fa-circle-o"></i> Jurnal Umum
                                </a>
                            </li>
                            <?php if($level == 1): ?>
                            <li <?= $seg1 == 'coa' ? 'class="active"' : '' ?>>
                                <a href="<?= site_url('coa') ?>">
                                    <i class="fa fa-circle-o"></i> Chart of Accounts
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>

            <!-- SDM -->
            <?php if(in_array($level, [1,2])): ?>
            <li class="header">SDM</li>
            <li class="treeview <?= in_array($seg1, ['karyawan','absensi']) ? 'active menu-open' : '' ?>">
                <a href="#">
                    <i class="fa fa-users text-blue"></i>
                    <span>Karyawan</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li <?= $seg1 == 'karyawan' ? 'class="active"' : '' ?>>
                        <a href="<?= site_url('karyawan') ?>">
                            <i class="fa fa-user text-blue"></i> Data Karyawan
                        </a>
                    </li>
                    <li <?= $seg1 == 'absensi' ? 'class="active"' : '' ?>>
                        <a href="<?= site_url('absensi') ?>">
                            <i class="fa fa-check-square-o text-green"></i> Absensi & Uang Makan
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>

            <!-- REPORT -->
            <?php if(in_array($level, [1,2,3])): ?>
            <li class="header">REPORTS</li>

            <li class="treeview <?= in_array($seg1, ['report','report_tax','report-purchase','report-beban']) ? 'active menu-open' : '' ?>">
                <a href="#">
                    <i class="fa fa-file-text-o"></i>
                    <span>
                        <?= $level == 3 ? 'Riwayat Transaksi' : 'Laporan Analitik' ?>
                    </span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>

                <ul class="treeview-menu">

                    <li <?= $seg2 == 'sale' ? 'class="active"' : '' ?>>
                        <a href="<?= site_url('report/sale') ?>">
                            <i class="fa fa-circle-o"></i> Penjualan
                        </a>
                    </li>

                    <li <?= $seg2 == 'detail' ? 'class="active"' : '' ?>>
                        <a href="<?= site_url('report/detail') ?>">
                            <i class="fa fa-circle-o"></i> Detail Transaksi
                        </a>
                    </li>

                    <?php if(in_array($level, [1,2])): ?>
                    <li <?= $seg1 == 'report-purchase' ? 'class="active"' : '' ?>>
                        <a href="<?= site_url('report-purchase') ?>">
                            <i class="fa fa-circle-o"></i> Pembelian
                        </a>
                    </li>

                    <li <?= $seg1 == 'report-beban' ? 'class="active"' : '' ?>>
                        <a href="<?= site_url('report-beban') ?>">
                            <i class="fa fa-circle-o"></i> Operasional
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if($level == 1): ?>
                    <li class="treeview <?= $seg1 == 'report_tax' ? 'active menu-open' : '' ?>">
                        <a href="#">
                            <i class="fa fa-circle-o text-red"></i> Pajak (Admin)
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>

                        <ul class="treeview-menu">
                            <li <?= ($seg1 == 'report_tax' && $seg2 == '') ? 'class="active"' : '' ?>>
                                <a href="<?= site_url('report_tax') ?>">
                                    <i class="fa fa-plus"></i> Input Pajak
                                </a>
                            </li>

                            <li <?= $seg2 == 'data' ? 'class="active"' : '' ?>>
                                <a href="<?= site_url('report_tax/data') ?>">
                                    <i class="fa fa-database"></i> Data Pajak
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                </ul>
            </li>
            <?php endif; ?>

            <!-- ADMIN -->
            <?php if($level == 1): ?>
            <li class="header">ADMINISTRATOR</li>
            <li <?= $seg1 == 'user' ? 'class="active"' : '' ?>>
                <a href="<?= site_url('user') ?>">
                    <i class="fa fa-user-secret text-red"></i> <span>Manajemen User</span>
                </a>
            </li>
            <?php endif; ?>

        </ul>
    </section>
</aside>
  <script src="<?=base_url()?>assets/bower_components/jquery/dist/jquery.min.js"></script>
  <script>
    // Format tanggal dd-mm-yyyy untuk kolom DataTables modul Finance (data mentah dari server: yyyy-mm-dd)
    function fmtTglID(d) {
      if (!d) return '-';
      var p = d.split('-');
      if (p.length !== 3) return d;
      return p[2] + '-' + p[1] + '-' + p[0];
    }

    // Sertakan CSRF token otomatis di semua AJAX POST
    $.ajaxSetup({
      beforeSend: function(xhr, settings) {
        if (settings.type === 'POST' || settings.type === 'post') {
          if (typeof settings.data === 'string') {
            settings.data += '&csrf_token=' + $('meta[name="csrf-token"]').attr('content');
          } else if (settings.data instanceof FormData) {
            settings.data.append('csrf_token', $('meta[name="csrf-token"]').attr('content'));
          }
        }
      }
    });
  </script>
  <script src="<?=base_url()?>assets/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="<?=base_url()?>assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

  <div class="content-wrapper">
    <?php echo $contents ?>
  </div>
  <!-- /.content-wrapper -->

  

  


<script src="<?=base_url()?>assets/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="<?=base_url()?>assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<script src="<?=base_url()?>assets/dist/js/adminlte.min.js"></script>
<script src="<?=base_url()?>assets/plugins/select2/select2.min.js"></script>
<script src="<?=base_url()?>assets/plugins/DataTables/datatables.js"></script>
<script src="<?=base_url()?>assets/plugins/DataTables/datatables.min.js"></script>
<script src="<?=base_url()?>assets/plugins/DataTables/responsive.bootstrap.min.js"></script>
<script src="<?=base_url()?>assets/plugins/DataTables/dataTables.responsive.min.js"></script>
<script src="<?=base_url()?>assets/plugins/DataTables/stateRestore.bootstrap.min.js"></script>
<script src="<?=base_url()?>assets/plugins/DataTables/dataTables.stateRestore.min.js"></script>
<script src="<?=base_url()?>assets/plugins/sweetalert2/sweetalert2.all.min.js"></script>


<script>
// Auto-inject CSRF token ke semua <form> di halaman (termasuk di dalam modal)
// Ini menangani regular form POST tanpa perlu ubah setiap file view satu per satu
$(document).ready(function() {
  var csrfToken = $('meta[name="csrf-token"]').attr('content');
  var csrfName  = 'csrf_token';

  function injectCsrf(ctx) {
    $(ctx).find('form').addBack('form').each(function() {
      if (!$(this).find('input[name="' + csrfName + '"]').length) {
        $(this).prepend('<input type="hidden" name="' + csrfName + '" value="' + csrfToken + '">');
      }
    });
  }

  // Inject ke semua form yang sudah ada di DOM
  injectCsrf(document);

  // Inject ke form di dalam modal yang dimuat dinamis (misal: modal edit cart)
  $(document).on('show.bs.modal', '.modal', function() {
    injectCsrf(this);
  });

  // Inject ke konten yang di-load via .load() jQuery
  $(document).ajaxComplete(function(event, xhr, settings) {
    if (settings.url && settings.url.indexOf('cart_data') !== -1) return; // skip cart_data, bukan form
    injectCsrf(document);
  });
});

var flash = $('#flash').data('flash');
if(flash) {
  Swal.fire({
    icon : 'success',
    title: 'Success',
    text : flash
  })
}
var flashError = $('#flash').data('flash-error');
if(flashError) {
  Swal.fire({
    icon : 'error',
    title: 'Gagal',
    text : flashError
  })
}

$(document).on('click',  '#btn-hapus', function(e) {
  e.preventDefault();
  var link = $(this).attr('href');
  Swal.fire({
    title: "Apakah Anda Yakin?",
    text: "Data Akan Terhapus!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya, Hapus"
  }).then((result) => {
    if (result.isConfirmed) {
        var $form = $('<form>', {method: 'POST', action: link});
        $form.append($('<input>', {type: 'hidden', name: 'csrf_token', value: $('meta[name="csrf-token"]').attr('content')}));
        $('body').append($form);
        $form.submit();
    }
  });


})

$(document).on('click', '.btn-validate, .btn-not-validate', function(e){
    e.preventDefault();
    let url = $(this).attr('href');
    let isValidate = $(this).hasClass('btn-validate');
    let actionText = isValidate ? 'Validasi' : 'Batalkan Validasi';

    Swal.fire({
        title: actionText + ' Barang?',
        text: "Apakah kamu yakin ingin " + actionText.toLowerCase() + " barang ini?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: isValidate ? '#28a745' : '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, ' + actionText
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
});

$(document).ready(function() {
            <?php if ($this->session->flashdata('show_sweetalert')): ?>
                Swal.fire({
                    title: 'Update Stock?',
                    text: 'Pilih :',
                    icon: 'question',
                    showCancelButton: false,
                    showConfirmButton: false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Update Stock',
                    footer: '<button id="btn-update-stock" class="swal2-confirm swal2-styled">Ya, Update Stok</button> <button id="btn-go-item" class="swal2-confirm swal2-styled">Tidak, Ke Daftar Barang</button>'
                }).then((result) => {
                    // You can add extra logic here if needed
                });

                $(document).on('click', '#btn-update-stock', function() {
                    window.location.href = '<?php echo site_url('stock/in/add_after/' . $this->session->flashdata('item_id')); ?>';
                });

                $(document).on('click', '#btn-go-item', function() {
                    window.location.href = '<?php echo site_url('item'); ?>';
                });
            <?php endif; ?>
        });

        $(document).ready(function() {
    <?php if ($this->session->flashdata('item_ids')): ?>
        Swal.fire({
            title: 'Update Stock?',
            text: 'Pilih :',
            icon: 'question',
            showCancelButton: false,
            showConfirmButton: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Update Stock',
            footer: '<button id="btn-update-stock" class="swal2-confirm swal2-styled">Ya, Update Stok</button> <button id="btn-go-item" class="swal2-confirm swal2-styled">Tidak, Ke Daftar Barang</button>'
        });

        $(document).on('click', '#btn-update-stock', function() {
            Swal.close(); // Menutup alert tanpa redirect
        });

        $(document).on('click', '#btn-go-item', function() {
            window.location.href = '<?php echo site_url('item'); ?>';
        });
    <?php endif; ?>
});



$(document).on('click',  '#btn-logout', function(e) {
  e.preventDefault();
  var link = $(this).attr('href');
  Swal.fire({
    title: "Apakah Anda Yakin?",
    text: "Anda Akan Logout",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya, Logout"
  }).then((result) => {
    if (result.isConfirmed) {
        window.location = link;
    }
  });


})

</script>

<script>
  $(document).ready(function(){
  $(document).ready(function () {
    $('#supplierDropdown').select2({
        placeholder: 'Pilih Supplier',
        allowClear: true
    });
    $('#categoryDropdown').select2({
        placeholder: 'Pilih Kategori',
        allowClear: true
    });
    $('#unitDropdown').select2({
        placeholder: 'Pilih Unit',
        allowClear: true
    });
    $('#customer').select2({
        placeholder: 'Pilih customer',
        allowClear: true
    });
    $('#table1').DataTable()
    })
    $('#table2').DataTable()
  })

  // ── Dark / Light Mode Toggle ──────────────────────────────────────
  $(document).ready(function() {
    function applyTheme(dark) {
      if (dark) {
        $('body, html').addClass('dark-mode');
        $('#theme-icon').removeClass('fa-moon-o').addClass('fa-sun-o');
      } else {
        $('body, html').removeClass('dark-mode');
        $('#theme-icon').removeClass('fa-sun-o').addClass('fa-moon-o');
      }
    }

    // Apply saved theme on load
    applyTheme(localStorage.getItem('jdm_theme') === 'dark');

    // Toggle on click
    $('#theme-toggle').on('click', function(e) {
      e.preventDefault();
      var isDark = !$('body').hasClass('dark-mode');
      localStorage.setItem('jdm_theme', isDark ? 'dark' : 'light');
      applyTheme(isDark);
    });
  });


</script>

<?php if (in_array($this->fungsi->user_login()->level, [1, 2])): ?>
<!-- Notification Sidebar -->
<div id="notif-overlay"></div>
<div id="notif-sidebar">
  <div id="notif-sidebar-hdr">
    <span><i class="fa fa-bell" style="margin-right:7px;color:#f59e0b"></i>Notifikasi</span>
    <div style="display:flex;align-items:center;gap:12px">
      <a href="#" id="notif-mark-read" style="font-size:11px;color:#9ca3af;text-decoration:none">Tandai semua dibaca</a>
      <button id="notif-sidebar-close" style="background:none;border:none;font-size:22px;line-height:1;cursor:pointer;padding:0">&times;</button>
    </div>
  </div>
  <ul id="notif-list">
    <li id="notif-empty">
      <a href="#"><i class="fa fa-check-circle" style="font-size:22px;display:block;margin-bottom:6px;color:#10b981"></i>Tidak ada notifikasi baru</a>
    </li>
  </ul>
</div>
<style>
  /* ── Notification Sidebar ────────────── */
  #notif-overlay {
    display:none; position:fixed; inset:0;
    z-index:1048; background:rgba(0,0,0,.2);
  }
  #notif-overlay.open { display:block; }
  #notif-sidebar {
    position:fixed; top:50px; right:-310px; width:280px;
    height:calc(100vh - 50px);
    background:#222d32; border-left:1px solid #1a2226;
    box-shadow:-4px 0 20px rgba(0,0,0,.35);
    z-index:1049; display:flex; flex-direction:column;
    transition:right .28s cubic-bezier(.4,0,.2,1);
  }
  #notif-sidebar.open { right:0; }
  #notif-sidebar-hdr {
    display:flex; align-items:center; justify-content:space-between;
    padding:14px 16px; border-bottom:1px solid #1a2226;
    font-weight:600; font-size:13px; flex-shrink:0;
    background:#1a2226; color:#b8c7ce; text-transform:uppercase; letter-spacing:.5px;
  }
  #notif-mark-read { color:#6c8a9a !important; text-decoration:none; font-size:11px; text-transform:none; letter-spacing:0; }
  #notif-mark-read:hover { color:#b8c7ce !important; }
  #notif-sidebar-close { color:#6c8a9a; }
  #notif-sidebar-close:hover { color:#b8c7ce; }
  #notif-list {
    flex:1; overflow-y:auto; list-style:none; padding:0; margin:0;
  }
  #notif-list #notif-empty a {
    display:block; padding:30px 16px; text-align:center;
    color:#4b6070; text-decoration:none;
  }
  #notif-list li[data-notif-id] { border-bottom:1px solid #1a2226; display:flex; align-items:center; }
  #notif-list li[data-notif-id] > a {
    flex:1; min-width:0; padding:12px 0 12px 14px;
    color:#b8c7ce; text-decoration:none; white-space:normal; display:block;
  }
  #notif-list li[data-notif-id] > a:hover { background:#1e282c; color:#fff; }
  #notif-list li[data-notif-id] > a strong { color:#d8e6ec; }
  #notif-list li[data-notif-id] > a small { color:#6c8a9a !important; }
  #notif-list .notif-dismiss-one { color:#4b6070 !important; }
  #notif-list .notif-dismiss-one:hover { color:#b8c7ce !important; }
  /* ── Toast ───────────────────────────── */
  #jdm-toast-wrap {
    position:fixed; bottom:24px; right:24px; z-index:9999;
    display:flex; flex-direction:column-reverse; gap:8px; pointer-events:none;
  }
  .jdm-toast {
    background:#1e2a3a; color:#f3f4f6;
    border-left:4px solid #f59e0b; border-radius:10px;
    padding:14px 16px; min-width:280px; max-width:340px;
    box-shadow:0 8px 24px rgba(0,0,0,.4);
    display:flex; gap:12px; align-items:flex-start;
    animation:toastIn .3s ease; pointer-events:all;
  }
  body.skin-black-light .jdm-toast { background:#fff; color:#111827; box-shadow:0 4px 16px rgba(0,0,0,.12); }
  .jdm-toast .jdm-ti { font-size:16px; margin-top:2px; flex-shrink:0; }
  .jdm-toast-title { font-size:13px; font-weight:600; margin-bottom:3px; }
  .jdm-toast-msg   { font-size:12px; opacity:.75; }
  @keyframes toastIn { from{opacity:0;transform:translateX(30px)} to{opacity:1;transform:translateX(0)} }
</style>
<script>
(function() {
  var SS_KEY = 'jdm_notif_shown';

  // localStorage (bukan sessionStorage) supaya status "sudah ditampilkan" dishare
  // antar tab/window pada origin yang sama — tab baru tidak akan menganggap semua
  // notifikasi unread sebagai baru dan menampilkannya sekaligus sebagai toast.
  function getShown()  { try { return JSON.parse(localStorage.getItem(SS_KEY)||'[]'); } catch(e){ return []; } }
  function addShown(id){
    var ids=getShown();
    ids.push(id);
    if (ids.length > 200) ids = ids.slice(ids.length - 200); // cegah localStorage membengkak
    localStorage.setItem(SS_KEY,JSON.stringify(ids));
  }
  function clearShown(){ localStorage.removeItem(SS_KEY); }

  var emptyHtml =
    '<li id="notif-empty"><a href="#"><i class="fa fa-check-circle" style="font-size:22px;display:block;margin-bottom:6px;color:#10b981"></i>Tidak ada notifikasi baru</a></li>';

  function openSidebar()  { $('#notif-sidebar,#notif-overlay').addClass('open'); }
  function closeSidebar() { $('#notif-sidebar,#notif-overlay').removeClass('open'); }

  function fetchNotifications() {
    $.getJSON('<?=site_url('dashboard/notifications')?>', function(res) {
      res.count > 0 ? $('#notif-badge').text(res.count).show() : $('#notif-badge').hide();
      var $list = $('#notif-list').empty();
      if (!res.items.length) {
        $list.append(emptyHtml);
      } else {
        var shown = getShown();
        var typeMap = {
          'po_cart_auto': { icon:'fa-shopping-basket', color:'#f59e0b', getUrl:function(n){ return '<?=site_url('po-cart')?>'; } },
          'po_sent'     : { icon:'fa-paper-plane',     color:'#3c8dbc', getUrl:function(n){ return '<?=site_url('purchase-order')?>'+(n.ref_id?'/'+n.ref_id:''); } },
          'po_received' : { icon:'fa-truck',            color:'#00a65a', getUrl:function(n){ return '<?=site_url('purchase-order/history')?>'+(n.ref_id?'/'+n.ref_id:''); } },
        };
        $.each(res.items, function(i, n) {
          var id  = parseInt(n.id);
          var tm  = typeMap[n.type] || { icon:'fa-bell', color:'#6b7280', getUrl:function(){ return '#'; } };
          var url = tm.getUrl(n);
          var d   = new Date(n.created_at.replace(' ','T'));
          var pad = function(x){ return x<10?'0'+x:x; };
          var ts  = pad(d.getDate())+'/'+pad(d.getMonth()+1)+' '+pad(d.getHours())+':'+pad(d.getMinutes());
          $list.append(
            '<li data-notif-id="'+id+'">' +
            '<a href="'+url+'">' +
            '<i class="fa '+tm.icon+'" style="color:'+tm.color+';margin-right:6px"></i>' +
            '<strong>'+n.title+'</strong>' +
            '<br><small style="color:#9ca3af">'+n.message+'</small>' +
            '<br><small style="color:#6b7280;font-size:10px"><i class="fa fa-clock-o" style="margin-right:3px"></i>'+ts+'</small></a>' +
            '<button class="notif-dismiss-one" data-id="'+id+'" title="Tandai dibaca" '+
            'style="background:none;border:none;color:#9ca3af;font-size:18px;line-height:1;padding:0 10px;cursor:pointer;flex-shrink:0">&times;</button>' +
            '</li>'
          );
          if (shown.indexOf(id) === -1) { addShown(id); showToast(n.title, n.message, tm.icon, tm.color); }
        });
      }
    });
  }

  function showToast(title, msg, icon, color) {
    icon=icon||'fa-bell'; color=color||'#f59e0b';
    if (!$('#jdm-toast-wrap').length) $('body').append('<div id="jdm-toast-wrap"></div>');
    var $t = $(
      '<div class="jdm-toast" style="border-left-color:'+color+'">' +
      '<i class="fa '+icon+' jdm-ti" style="color:'+color+'"></i>' +
      '<div><div class="jdm-toast-title">'+title+'</div><div class="jdm-toast-msg">'+msg+'</div></div>' +
      '</div>'
    );
    $('#jdm-toast-wrap').append($t);
    setTimeout(function(){ $t.fadeOut(400,function(){ $t.remove(); }); }, 5000);
  }

  $(document).on('click', '#notif-bell-btn', function(e) {
    e.preventDefault();
    $('#notif-sidebar').hasClass('open') ? closeSidebar() : openSidebar();
  });
  $(document).on('click', '#notif-sidebar-close, #notif-overlay', closeSidebar);

  $(document).on('click', '#notif-mark-read', function(e) {
    e.preventDefault(); e.stopPropagation();
    var csrf = $('meta[name="csrf-token"]').attr('content');
    $.post('<?=site_url('dashboard/notifications/read')?>', {csrf_token:csrf}, function() {
      clearShown(); $('#notif-badge').hide(); $('#notif-list').html(emptyHtml);
    });
  });

  $(document).on('click', '.notif-dismiss-one', function(e) {
    e.preventDefault(); e.stopPropagation();
    var $btn=$(this), id=$btn.data('id'), csrf=$('meta[name="csrf-token"]').attr('content');
    $.post('<?=site_url('dashboard/notifications/read-one')?>', {id:id,csrf_token:csrf}, function() {
      $btn.closest('li').remove();
      var rem = $('#notif-list').children('li').length;
      if (rem === 0) { $('#notif-list').html(emptyHtml); $('#notif-badge').hide(); }
      else { $('#notif-badge').text(Math.max(0,parseInt($('#notif-badge').text())-1)); }
    });
  });

  $(document).ready(function() {
    fetchNotifications();
    setInterval(fetchNotifications, 8000);
    document.addEventListener('visibilitychange', function() {
      if (document.visibilityState === 'visible') fetchNotifications();
    });
  });
})();
</script>
<?php endif; ?>

<?php
  $_up   = $this->fungsi->user_login();
  $_lvlp = (int) $_up->level;
  $_lvlnp = [1=>'Super Admin',2=>'Admin',3=>'Kasir',4=>'Gudang'][$_lvlp] ?? 'Staff';
  $_dhcl  = ['1'=>'#e74c3c','2'=>'#3c8dbc','3'=>'#00a65a','4'=>'#f39c12'];
  $_dhcolor = $_dhcl[$_lvlp] ?? '#00a65a';
  $_initp = implode('', array_map(function($w){ return strtoupper(substr($w,0,1)); }, array_slice(explode(' ',$_up->nama),0,2)));
?>

<!-- Modal Lihat Profil -->
<div class="modal fade" id="modal-profil" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content" style="border-radius:12px;overflow:hidden;border:none">
      <div class="modal-header" style="background:linear-gradient(135deg,<?=$_dhcolor?>,<?=$_dhcolor?>cc);border:none;padding:20px 20px 16px">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8;margin-top:-2px"><span>&times;</span></button>
        <h4 class="modal-title" style="color:#fff;font-weight:700"><i class="fa fa-id-card-o"></i> Profil Saya</h4>
      </div>
      <div class="modal-body" style="padding:24px;text-align:center">
        <div style="width:72px;height:72px;border-radius:50%;background:<?=$_dhcolor?>;color:#fff;font-size:26px;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 14px"><?=$_initp?></div>
        <h4 style="margin:0 0 6px;font-weight:700"><?= htmlspecialchars($_up->nama) ?></h4>
        <span style="display:inline-block;padding:3px 14px;border-radius:20px;background:<?=$_dhcolor?>22;color:<?=$_dhcolor?>;border:1px solid <?=$_dhcolor?>44;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px"><?=$_lvlnp?></span>
        <table class="table table-bordered" style="margin-top:20px;text-align:left;font-size:13px">
          <tr><th style="width:42%;color:#6b7280;font-weight:600;font-size:11px;text-transform:uppercase">Nama</th><td><?= htmlspecialchars($_up->nama) ?></td></tr>
          <tr><th style="color:#6b7280;font-weight:600;font-size:11px;text-transform:uppercase">Username</th><td><?= htmlspecialchars($_up->username) ?></td></tr>
          <tr><th style="color:#6b7280;font-weight:600;font-size:11px;text-transform:uppercase">Level</th><td><?=$_lvlnp?></td></tr>
        </table>
      </div>
      <div class="modal-footer" style="border-top:1px solid #f0f0f0">
        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn-modal-ke-ganti"><i class="fa fa-key"></i> Ubah Password</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ganti Username & Password -->
<div class="modal fade" id="modal-ganti-profil" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content" style="border-radius:12px;overflow:hidden;border:none">
      <div class="modal-header" style="background:linear-gradient(135deg,#2980b9,#3c8dbc);border:none;padding:20px 20px 16px">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8;margin-top:-2px"><span>&times;</span></button>
        <h4 class="modal-title" style="color:#fff;font-weight:700"><i class="fa fa-key"></i> Ubah Username & Password</h4>
      </div>
      <div class="modal-body" style="padding:20px 24px">
        <div id="ganti-alert" class="alert" style="display:none;border-radius:8px;font-size:13px"></div>
        <form id="form-ganti-profil" autocomplete="off">
          <div class="form-group">
            <label style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px">Nama Lengkap</label>
            <input type="text" class="form-control input-sm" name="nama" id="gp-nama" value="<?= htmlspecialchars($_up->nama) ?>">
          </div>
          <div class="form-group">
            <label style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px">Username</label>
            <input type="text" class="form-control input-sm" name="username" id="gp-username" value="<?= htmlspecialchars($_up->username) ?>">
          </div>
          <div style="height:1px;background:#e5e7eb;margin:16px 0"></div>
          <div class="form-group">
            <label style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px">Password Lama <span style="color:#ef4444">*</span></label>
            <input type="password" class="form-control input-sm" name="password_lama" id="gp-pw-lama" placeholder="Wajib diisi">
          </div>
          <div class="form-group">
            <label style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px">Password Baru <span style="color:#9ca3af">(opsional)</span></label>
            <input type="password" class="form-control input-sm" name="password_baru" id="gp-pw-baru" placeholder="Kosongkan jika tidak diubah">
          </div>
          <div class="form-group" id="gp-konfirm-wrap" style="display:none">
            <label style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px">Konfirmasi Password Baru</label>
            <input type="password" class="form-control input-sm" name="password_konfirm" id="gp-pw-konfirm" placeholder="Ulangi password baru">
          </div>
        </form>
      </div>
      <div class="modal-footer" style="border-top:1px solid #f0f0f0">
        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success btn-sm" id="btn-simpan-profil"><i class="fa fa-save"></i> Simpan</button>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  // Buka modal profil
  $(document).on('click', '#btn-lihat-profil', function(e) {
    e.preventDefault(); $('#modal-profil').modal('show');
  });
  // Buka modal ganti profil
  $(document).on('click', '#btn-ganti-profil, #btn-modal-ke-ganti', function(e) {
    e.preventDefault(); $('#modal-profil').modal('hide'); $('#modal-ganti-profil').modal('show');
  });
  // Tampil konfirmasi hanya saat password baru diisi
  $('#gp-pw-baru').on('input', function() {
    $('#gp-konfirm-wrap').toggle($(this).val().length > 0);
  });
  // Reset form saat modal dibuka
  $('#modal-ganti-profil').on('show.bs.modal', function() {
    $('#ganti-alert').hide();
    $('#gp-pw-lama, #gp-pw-baru, #gp-pw-konfirm').val('');
    $('#gp-konfirm-wrap').hide();
  });
  // Simpan
  $('#btn-simpan-profil').on('click', function() {
    var pwBaru    = $('#gp-pw-baru').val();
    var pwKonfirm = $('#gp-pw-konfirm').val();
    if (!$('#gp-pw-lama').val()) { showGantiAlert('danger','Password lama wajib diisi.'); return; }
    if (pwBaru && pwBaru !== pwKonfirm) { showGantiAlert('danger','Konfirmasi password baru tidak cocok.'); return; }
    $('#btn-simpan-profil').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
    $.post('<?=site_url('auth/update-profile')?>', {
      nama          : $('#gp-nama').val(),
      username      : $('#gp-username').val(),
      password_lama : $('#gp-pw-lama').val(),
      password_baru : pwBaru,
      csrf_token    : $('meta[name="csrf-token"]').attr('content')
    }, function(res) {
      if (res.success) {
        showGantiAlert('success', res.message);
        setTimeout(function(){ location.reload(); }, 1400);
      } else {
        showGantiAlert('danger', res.message);
        $('#btn-simpan-profil').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan');
      }
    }, 'json').fail(function(){ showGantiAlert('danger','Terjadi kesalahan server.'); $('#btn-simpan-profil').prop('disabled',false).html('<i class="fa fa-save"></i> Simpan'); });
  });

  function showGantiAlert(type, msg) {
    $('#ganti-alert').attr('class','alert alert-'+type).html(msg).show();
  }
});
</script>
</body>
</html>
