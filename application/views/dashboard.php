<link rel="icon"
      href="<?=base_url()?>assets/dist/img/LOGO JDM BW.jpg" class="img-circle" alt="User Image">

<!-- Content Header -->
<section class="content-header">
    <h1>Dashboard</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
    </ol>
</section>

<!-- Main Content -->
<section class="content">
    <!-- Info Boxes -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-cubes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Items</span>
                    <span class="info-box-number"><?= $this->fungsi->count_item() ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Suppliers</span>
                    <span class="info-box-number"><?= $this->fungsi->count_supplier() ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Customers</span>
                    <span class="info-box-number"><?= $this->fungsi->count_customer() ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="info-box bg-red">
                <span class="info-box-icon"><i class="fa fa-user-secret"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Users</span>
                    <span class="info-box-number"><?= $this->fungsi->count_user() ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php $lvl = (int) $this->fungsi->user_login()->level; ?>
    <?php if ($lvl == 1 || $lvl == 2): ?>
    <!-- Tabs -->
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#stock-habis" data-toggle="tab">Stock Habis</a></li>
            <li><a href="#stock-menipis" data-toggle="tab">Stock Menipis</a></li>
            <?php if ($lvl == 1): ?>
            <li><a href="#analisis-penjualan" data-toggle="tab">Analisis Penjualan</a></li>
            <?php endif; ?>
        </ul>
        <div class="tab-content">
            <!-- Tab 1: Stock Habis -->
            <div class="tab-pane active" id="stock-habis">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-exclamation-circle"></i> Data Barang (Stock Habis)</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table id="table-stock-habis" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Barcode</th>
                                    <th>Nama Barang</th>
                                    <th>Supplier</th>
                                    <th>Kategori</th>
                                    <th>Satuan</th>
                                    <th>Modal</th>
                                    <th>PK</th>
                                    <th>Harga</th>
                                    <th>Stock</th>
                                    <th>Terjual</th>
                                    <th>Aksi </th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan dimuat oleh server -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Stock Menipis -->
            <div class="tab-pane" id="stock-menipis">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-warning"></i> Data Barang (Stock Menipis)</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table id="table-stock-menipis" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Barcode</th>
                                    <th>Nama Barang</th>
                                    <th>Supplier</th>
                                    <th>Kategori</th>
                                    <th>Satuan</th>
                                    <th>Modal</th>
                                    <th>PK</th>
                                    <th>Harga</th>
                                    <th>Stock</th>
                                    <th>Terjual</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan dimuat oleh server -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Analisis Penjualan (Super Admin only) -->
            <?php if ($lvl == 1): ?>
            <div class="tab-pane" id="analisis-penjualan">
                <!-- Form Pencarian -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> Cari Barang</h3>
                    </div>
                    <div class="box-body">
                            <form id="search-form">
                                <input type="text" id="keyword" name="keyword" placeholder="Cari Barang (contoh: tierod)">
                                <button type="submit">Cari</button>
                            <form>
                    </div>
                </div>

                <!-- Tabel Analisis Penjualan -->
                <div class="box box-success">
                <div class="box">
                            <div class="box-header">
                            <h3>Analisis Penjualan</h3>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <h4>Barang yang Dianalisis</h4>
                                    <table id="barang_teranalisis_table" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Barang</th>
                                                <th>Supplier</th>
                                                <th>Modal</th>
                                                <th>PK</th>
                                                <th>Total Qty Terjual</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                    <hr>
                                    <h4>Analisis Per Bulan</h4>
                                    <table id="analisis_table" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Tahun</th>
                                                <th>Bulan</th>
                                                <th>Total Qty Terjual</th>
                                                <th>Total Transaksi</th>
                                                <th>Rata-rata Qty per Transaksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
</section>
<?php endif; ?>
<!-- Script untuk DataTables -->
<script>
    $(document).ready(function() {
        // Inisialisasi DataTable untuk Stock Habis
        $('#table-stock-habis').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('dashboard/get_json_stock_0') ?>",
                type: "POST"
            },
            columns: [
            { 
                data: null, // Tidak ada data dari server untuk kolom ini
                render: function(data, type, row, meta) {
                    return meta.row + 1; // Nomor urut otomatis (dimulai dari 1)
                },
                width: "40px",
                searchable: false,
                orderable: false,
                className: "text-center"
            },
            { data: "barcode", width: "100px" },
            { data: "nama_item", width: "200px" },
            { data: "nama_supplier", width: "150px" },
            { data: "nama_category", width: "150px" },
            { data: "nama_unit", width: "100px" },
            { data: "modal", width: "100px", className: "text-right" },
            { data: "pk", width: "100px" },
            { data: "price", width: "100px", className: "text-right" },
            { data: "stock", width: "80px", className: "text-center" },
            { data: "total_sold", width: "80px", className: "text-center" },
            { data: "action", width: "80px", className: "text-center" }
        ]
    });

        // Inisialisasi DataTable untuk Stock Menipis
        $('#table-stock-menipis').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('dashboard/get_json_stock_low') ?>",
                type: "POST"
            },
            columns: [
            { 
                data: null, // Tidak ada data dari server untuk kolom ini
                render: function(data, type, row, meta) {
                    return meta.row + 1; // Nomor urut otomatis (dimulai dari 1)
                },
                width: "40px",
                searchable: false,
                orderable: false,
                className: "text-center"
            },
            { data: "barcode", width: "100px" },
            { data: "nama_item", width: "200px" },
            { data: "nama_supplier", width: "150px" },
            { data: "nama_category", width: "150px" },
            { data: "nama_unit", width: "100px" },
            { data: "modal", width: "100px", className: "text-right" },
            { data: "pk", width: "100px" },
            { data: "price", width: "100px", className: "text-right" },
            { data: "stock", width: "80px", className: "text-center" },
            { data: "total_sold", width: "80px", className: "text-center" },
            { data: "action", width: "80px", className: "text-center" }
        ]
    });
        $(document).ready(function() {
        // Tabel Barang yang Dianalisis
        var barangTable = $('#barang_teranalisis_table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?= site_url("dashboard/get_analisis_penjualan_json") ?>',
                type: 'POST',
                data: function(d) {
                    d.keyword = $('#keyword').val(); // Kirim keyword sebagai parameter
                },
                dataSrc: 'barang_teranalisis'
            },
            columns: [
                { data: null, render: function (data, type, row, meta) { return meta.row + 1; } }, // Nomor
                { data: 'nama_item' },
                { data: 'nama_supplier' },
                { data: 'modal' },
                { data: 'pk' },
                { data: 'total_qty_sold' }
            ]
        });

        // Tabel Analisis Bulanan
        var analisisTable = $('#analisis_table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?= site_url("dashboard/get_analisis_penjualan_json") ?>',
                type: 'POST',
                data: function(d) {
                    d.keyword = $('#keyword').val(); // Kirim keyword sebagai parameter
                },
                dataSrc: 'analisis_bulanan'
            },
            columns: [
                { data: 'year' },
                { data: 'month' },
                { data: 'total_qty_sold' },
                { data: 'total_transactions' },
                { data: 'avg_qty_per_transaction' }
            ]
        });

        // Submit form pencarian
        $('#search-form').on('submit', function(e) {
            e.preventDefault();
            barangTable.ajax.reload(); // Reload tabel barang
            analisisTable.ajax.reload(); // Reload tabel analisis bulanan
        });
    });
    });
    </script> 