<!DOCTYPE html>
<html>
<head>
  <link rel="icon"
  href="<?=base_url()?>assets/dist/img/LOGO JDM BW.jpg" class="img-circle" alt="User Image">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Jadi Motor Bandung </title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
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
  <style>
    .swal2-popup {
      font-size:1.4rem !important;
    }
    </style>
</head>
<body class="hold-transition skin-green sidebar-mini <?=$this->uri->segment(1) == 'sale' ||  ($this->uri->segment(1) == 'item' && $this->uri->segment(2) == '') ? 'sidebar-collapse' : null?>">
<!-- Site wrapper -->
<div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a  class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>J</b>DM</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>Jadi Motor</b>  <?= $this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2 ? 'Admin' : 'Kasir' ?> </span>
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
          <li class="dropdown tasks-menu">
            <ul class="dropdown-menu">
                <ul class="menu">
                  </li>
                </ul>
            </ul>
          </li>
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-user-circle"></i> 
            <span class="hidden-xs">
                <?= $this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2 ? 'Admin' : 'Kasir' ?> 
                <strong><?=$this->fungsi->user_login()->nama?></strong>
            </span>

            </a>
            <ul class="dropdown-menu">
                <!-- Dropdown footer with logout button -->
                <li class="user-footer">
                    <div class="pull-right">
                        <a href="<?=site_url('auth/logout')?>" class="btn btn-danger btn-flat" id="btn-logout">
                        <i class="fa fa-sign-out "> Sign out</i></a>
                        
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
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="<?=base_url()?>assets/dist/img/LOGO JDM BW.jpg" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>
                <?= $this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2 ? 'Admin' : 'Kasir' ?> 
          </p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>

      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MAIN NAVIGATION</li>
        <li <?=$this->uri->segment(1) =='dashboard' || $this->uri->segment(1) =='' ? 'class="active"' : ''  ?>>
          <a href="<?=site_url('dashboard')?>">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
           </a>   
       </li>
        <?php if($this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2){?>
        <li <?=$this->uri->segment(1) =='supplier' ? 'class="active"' : ''  ?>>
          <a href="<?=site_url('supplier') ?>">
            <i class="fa fa-truck"></i> <span>Supplier</span>
           </a>   
        </li>
        <?php } ?> 
        <li <?=$this->uri->segment(1) =='customer' ? 'class="active"' : ''  ?>>
          <a href="<?=site_url('customer') ?>">
            <i class="fa fa-users"></i> <span>Pembeli</span>
           </a>   
        </li>
        <?php if($this->fungsi->user_login()->level == 3){?>
        <li <?=$this->uri->segment(1) =='caribarang' ? 'class="active"' : ''  ?>>
          <a href="<?=site_url('item') ?>">
            <i class="fa fa-archive"></i> <span>Daftar Barang</span>
           </a>   
        </li>
        <?php } ?> 
        <?php if($this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2) { ?>
    <li class="treeview <?= in_array($this->uri->segment(1), ['category', 'stock', 'unit', 'item']) ? 'active' : '' ?>">
        <a href="#">
            <i class="fa fa-archive"></i>
            <span>Produk</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li <?= ($this->uri->segment(1) == 'item' && $this->uri->segment(2) == '') ? 'class="active"' : '' ?>>
                <a href="<?= site_url('item') ?>"><i class="fa fa-circle-o"></i> Daftar Barang</a>
            </li>
            <li <?= ($this->uri->segment(1) == 'stock' && $this->uri->segment(2) == 'in') ? 'class="active"' : '' ?>>
                <a href="<?= site_url('stock/in') ?>"><i class="fa fa-circle-o"></i> Update Stock</a>
            </li>
            <li <?= $this->uri->segment(1) == 'category' ? 'class="active"' : '' ?>>
                <a href="<?= site_url('category') ?>"><i class="fa fa-circle-o"></i> Kategori</a>
            </li>
            <li <?= $this->uri->segment(1) == 'unit' ? 'class="active"' : '' ?>>
                <a href="<?= site_url('unit') ?>"><i class="fa fa-circle-o"></i> Units</a>
            </li>
            <li <?= ($this->uri->segment(1) == 'stock' && $this->uri->segment(2) == 'out') ? 'class="active"' : '' ?>>
                <a href="<?= site_url('stock/out') ?>"><i class="fa fa-circle-o"></i> Barang Keluar</a>
            </li>
            <li <?= ($this->uri->segment(1) == 'item' && $this->uri->segment(2) == 'archive') ? 'class="active"' : '' ?>>
                <a href="<?= site_url('item/archive') ?>"><i class="fa fa-circle-o"></i> Arsip Barang</a>
            </li>
        </ul>
    </li>
<?php } ?>



        <li <?=$this->uri->segment(1) =='sale' ? 'class="active"' : ''  ?>>
          <a href="<?=site_url('sale')?>">
            <i class="fa fa-shopping-cart"></i> <span>Penjualan</span>
           </a>   
        </li>
        <?php if($this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2){?>
        <li <?=$this->uri->segment(1) =='retur' ? 'class="active"' : ''  ?>>
          <a href="<?=site_url('retur')?>">
            <i class="fa fa-undo"></i> <span>Retur</span>
           </a>   
        </li>
        <?php } ?>
        <?php if($this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2){?>
        <li class="treeview <?=$this->uri->segment(1) == 'report' ? 'active' : ''  ?>">
          <a>
            <i class="fa fa-pie-chart"></i>
            <span>Laporan</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
            <ul class="treeview-menu">
            <li <?=$this->uri->segment(1) =='report' && $this->uri->segment(2) == 'report_sale' ? 'class="active"' : ''  ?>>
              <a href="<?=site_url('report/sale') ?>"><i class="fa fa-circle-o"></i> Laporan Penjualan </a></li>
            <li <?=$this->uri->segment(1) =='report' && $this->uri->segment(2) == 'report_detail' ? 'class="active"' : ''  ?>>
              <a href="<?=site_url('report/detail') ?>"><i class="fa fa-circle-o"></i> Laporan Detail</a></li>
            <!-- <li <?=$this->uri->segment(1) =='report' && $this->uri->segment(2) == 'report_stock' ? 'class="active"' : ''  ?>>
              <a href="<?=site_url('report/stock_in') ?>"><i class="fa fa-circle-o"></i> Laporan Pembelian</a></li> -->
          </ul>
        </li>
        <?php } ?>
      
        <?php if($this->fungsi->user_login()->level == 1 ) { ?>
					<li class="header">SETTINGS</li>
					<li <?=$this->uri->segment(1) == 'user' ? 'class="active"' : ''?>>
						<a href="<?=site_url('user')?>"><i class="fa fa-user"></i> <span>Users</span></a>
					</li>
					<?php } ?>
    </section>

    <!-- /.sidebar -->
  </aside>
  <script src="<?=base_url()?>assets/bower_components/jquery/dist/jquery.min.js"></script>
    
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
var flash = $('#flash').data('flash');
if(flash) {
  Swal.fire({
    icon : 'success',
    title: 'Success',
    text : flash
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
        window.location = link;
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
  
  

</script>
</body>
</html> 
