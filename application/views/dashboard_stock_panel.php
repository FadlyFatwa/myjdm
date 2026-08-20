<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php $lvl = (int) $this->fungsi->user_login()->level; ?>
<?php if ($lvl == 1 || $lvl == 2): ?>

<section class="content" style="padding-top:0">
    <div class="box box-primary" id="box-stock-panel">
        <div class="box-header with-border" id="toggle-stock-panel" style="cursor:pointer">
            <h3 class="box-title"><i class="fa fa-exclamation-circle"></i> Data Stok (Habis / Menipis)<?= $lvl == 1 ? ' &amp; Analisis Penjualan' : '' ?></h3>
            <small class="text-muted" style="margin-left:8px">klik untuk tampilkan — tidak dimuat otomatis</small>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool"><i class="fa fa-plus" id="stock-panel-icon"></i></button>
            </div>
        </div>
        <div class="box-body" id="stock-panel-body" style="display:none">
            <div class="nav-tabs-custom" style="box-shadow:none;margin-bottom:0">
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
                        <div class="table-responsive">
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
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data akan dimuat oleh server -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Stock Menipis -->
                    <div class="tab-pane" id="stock-menipis">
                        <div class="table-responsive">
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

                    <!-- Tab 3: Analisis Penjualan (Super Admin only) -->
                    <?php if ($lvl == 1): ?>
                    <div class="tab-pane" id="analisis-penjualan">
                        <div class="box box-primary" style="box-shadow:none;border:1px solid #f4f4f4">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-search"></i> Cari Barang</h3>
                            </div>
                            <div class="box-body">
                                <form id="search-form">
                                    <input type="text" id="keyword" name="keyword" placeholder="Cari Barang (contoh: tierod)">
                                    <button type="submit">Cari</button>
                                </form>
                            </div>
                        </div>

                        <div class="box box-success" style="box-shadow:none;border:1px solid #f4f4f4">
                            <div class="box-header">
                                <h3 class="box-title">Analisis Penjualan</h3>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(function () {
    var stockPanelLoaded  = false;
    var menipisLoaded     = false;

    function initStockHabis() {
        $('#table-stock-habis').DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: "<?= site_url('dashboard/get_json_stock_0') ?>", type: "POST" },
            columns: [
                { data: null, render: function (d, t, r, meta) { return meta.row + 1; }, width: "40px", searchable: false, orderable: false, className: "text-center" },
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
    }

    function initStockMenipis() {
        $('#table-stock-menipis').DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: "<?= site_url('dashboard/get_json_stock_low') ?>", type: "POST" },
            columns: [
                { data: null, render: function (d, t, r, meta) { return meta.row + 1; }, width: "40px", searchable: false, orderable: false, className: "text-center" },
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
    }

    function initAnalisis() {
        var barangTable = $('#barang_teranalisis_table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?= site_url("dashboard/get_analisis_penjualan_json") ?>',
                type: 'POST',
                data: function (d) { d.keyword = $('#keyword').val(); },
                dataSrc: 'barang_teranalisis'
            },
            columns: [
                { data: null, render: function (d, t, r, meta) { return meta.row + 1; } },
                { data: 'nama_item' },
                { data: 'nama_supplier' },
                { data: 'modal' },
                { data: 'pk' },
                { data: 'total_qty_sold' }
            ]
        });

        var analisisTable = $('#analisis_table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?= site_url("dashboard/get_analisis_penjualan_json") ?>',
                type: 'POST',
                data: function (d) { d.keyword = $('#keyword').val(); },
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

        $('#search-form').on('submit', function (e) {
            e.preventDefault();
            barangTable.ajax.reload();
            analisisTable.ajax.reload();
        });
    }

    // Baru fetch data pas box dibuka pertama kali — supaya load awal dashboard ringan
    $('#toggle-stock-panel').on('click', function () {
        $('#stock-panel-body').slideToggle(150);
        $('#stock-panel-icon').toggleClass('fa-plus fa-minus');

        if (!stockPanelLoaded) {
            stockPanelLoaded = true;
            initStockHabis();
            <?php if ($lvl == 1): ?>
            initAnalisis();
            <?php endif; ?>
        }
    });

    // Tab Stock Menipis baru fetch pas pertama kali diklik
    $('a[href="#stock-menipis"]').on('shown.bs.tab', function () {
        if (!menipisLoaded) {
            menipisLoaded = true;
            initStockMenipis();
        }
    });
});
</script>

<?php endif; ?>
