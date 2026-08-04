
<section class="content-header">
    <h1>Riwayat Transaksi Detail
        <small>Daftar Barang Terjual</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-history"></i></a></li>
        <li class="active">Riwayat Transaksi</li>
        <li class="active">Barang</li>
    </ol>
</section>

<section class="content">
    <div id="flash" data-flash="<?=$this->session->flashdata('success');?>"></div>
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Data Barang Terjual</h3>
            <?php if($this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2){?>
            <?php }?>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-striped" id="table-item">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pembeli</th>
                        <th>No Faktur</th>
                        <th>Nama Barang</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Total</th>
                        <th>Tanggal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>

$("#table-item").DataTable({
    "processing": true,
    "serverSide": true,
    "order": [[7, 'desc']],
    "ajax": {
        "url": "<?=site_url('report/get_json_sale')?>",
        "type": "POST"
    },
    "columns": [
        { "data": "no",            "width": 40,  "searchable": false, "orderable": false, "className": "text-center" },
        { "data": "nama_customer", "width": 120 },
        { "data": "invoice",       "width": 110 },
        { "data": "nama_item" },
        { "data": "qty",           "width": 50,  "className": "text-center" },
        { "data": "price_sale",    "width": 110, "className": "text-right" },
        { "data": "total",         "width": 120, "className": "text-right",
          "render": function(d) { return '<strong>' + d + '</strong>'; } },
        { "data": "date",          "width": 90,  "className": "text-center" },
        { "data": "action",        "width": 90,  "searchable": false, "orderable": false, "className": "text-center" },
    ]
});

$(document).ready(function() {
        // Tambahkan event listener untuk shortcut keyboard
        $(window).on('keydown', function(e) {
            // Ctrl + F = 70, F2 = 113
            if ((e.ctrlKey && e.keyCode === 70) || e.keyCode === 113) {
                e.preventDefault();

                // Cari input pencarian menggunakan atribut aria-controls
                var $searchInput = $('input[aria-controls="table-item"]');

                if ($searchInput.length) {
                    $searchInput.focus().select(); // Fokus dan pilih teks
                    console.log("Input pencarian ditemukan dan difokuskan");
                } else {
                    console.warn("Input pencarian tidak ditemukan");
                }
            }
        });

        // Opsional: auto-select saat pengguna klik input pencarian
        $(document).on('focus', 'input[aria-controls="table-item"]', function () {
            $(this).select();
        });
    });

</script>