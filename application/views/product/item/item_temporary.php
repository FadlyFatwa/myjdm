<section class="content-header">
    <h1>Produk
        <small>Daftar Arsip Barang</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-archive"></i></a></li>
        <li class="active">Produk</li>
        <li class="active">Daftar Arsip Barang</li>
    </ol>
</section>

<section class="content">
    <!-- Flash Message -->
    <div id="flash" data-flash="<?= $this->session->flashdata('success'); ?>"></div>

    <!-- Box Container -->
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Data Arsip Barang</h3>
        </div>

        <!-- Box Body -->
        <div class="box-body table-responsive">
            <table id="table-item" class="table table-striped table-bordered dt-responsive" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Barcode Lama</th>
                        <th>Nama Barang</th>
                        <th>Supplier</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <?php if ($this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2) { ?>
                            <th>Modal</th>
                        <?php } ?>
                        <th>PK</th>
                        <th>Harga</th>
                        <th>Stock</th>
                        <?php if ($this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2) { ?>
                            <th>Aksi</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data akan dimuat oleh DataTables -->
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Script untuk DataTables -->
<script>
    $(document).ready(function() {
    $('#table-item').DataTable({
        // processing: true,
        serverSide: true,
        order: [],
        ajax: {
            url: "<?= site_url('item/get_json_temporary') ?>",
            type: "POST"
        },
        columns: [
            { 
                data: null, // Tidak ada data dari server untuk kolom ini
                render: function(data, type, row, meta) {
                    // Hitung nomor urut berdasarkan halaman dan indeks baris
                    var pageInfo = $('#table-item').DataTable().page.info();
                    return pageInfo.start + meta.row + 1; // Offset + indeks baris
                },
                width: "40px",
                searchable: false,
                orderable: false,
                className: "text-center"
            }, // Nomor urut
            { 
                data: "barcode", 
                width: "100px" 
            }, // Barcode
            { 
                data: "nama_item", 
                width: "400px" 
            }, // Nama Barang
            { 
                data: "nama_supplier", 
                width: "150px" 
            }, // Supplier
            { 
                data: "nama_category",
                searchable: false, 
                width: "150px" 
            }, // Kategori
            { 
                data: "nama_unit", 
                searchable: false,
                width: "100px" 
            }, // Satuan
            <?php if ($this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2) { ?>
                { 
                    data: "modal", 
                    searchable: false,
                    width: "100px", 
                    className: "text-right" 
                }, // Modal (hanya untuk admin)
            <?php } ?>
            { 
                data: "pk", 
                width: "100px" 
            }, // PK
            { 
                data: "price", 
                width: "100px", 
                className: "text-right" 
            }, // Harga
            { 
                data: "stock", 
                width: "80px", 
                className: "text-center" 
            }, // Stock
            <?php if ($this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2) { ?>
                { 
                    data: "action", 
                    width: "150px", 
                    searchable: false, 
                    orderable: false, 
                    className: "text-center" 
                } // Aksi (hanya untuk admin)
            <?php } ?>
        ]
    });
});
</script>