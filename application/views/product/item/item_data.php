<section class="content-header">
    <h1>Data Barang <small>Kelola stok dan produk</small></h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-archive"></i></a></li>
        <li class="active">Produk</li>
    </ol>
</section>

<section class="content">
    <div id="flash" data-flash="<?= $this->session->flashdata('success'); ?>"></div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Daftar Barang</h3>
            <div class="pull-right">
                <?php if (in_array($this->fungsi->user_login()->level, [1, 2])) : ?>
                    <a href="<?= site_url('item/add_multiple') ?>" class="btn btn-primary btn-flat btn-sm">
                        <i class="fa fa-plus"></i> Tambah Barang
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="ox-body table-responsive">
            <div class="well well-sm">
                <i class="fa fa-info-circle text-blue"></i> Shortcut: <kbd>F2</kbd> atau <kbd>Ctrl + F</kbd> untuk cari barang. 
            </div>
            <table id="table-item" class="table table-striped table-bordered dt-responsive" style="width:100%">
                <thead>
                    <tr id="thead-row">
                        <!-- Diisi JS berdasarkan level user -->
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>

<style>
#table-item .item-sub  { display: none; }
#table-item tr:hover .item-sub { display: inline; }
</style>

<script>
$(document).ready(function() {
    // 1. Definisikan Level User ke Variabel JS agar PHP tidak nyelip di tengah logika
    const userLevel = <?= (int)$this->fungsi->user_login()->level ?>;
    const tableSelector = '#table-item';

    // Nomor urut
    var noCol = {
        data: null, width: "40px", searchable: false, orderable: false, className: "text-center",
        render: function(d, t, r, m) { let p=$(tableSelector).DataTable().page.info(); return p.start+m.row+1; }
    };

    var isAdmin = (userLevel === 1 || userLevel === 2);

    // Kolom & TH untuk admin/superadmin
    var adminTH = '<th width="30px">No</th><th>Barcode</th><th>Nama Barang</th>'
                + '<th>Supplier</th><th>Satuan</th><th>Modal</th>'
                + '<th>PK</th><th>Harga Jual</th><th>Stok</th><th width="120px">Aksi</th>';

    var adminCols = [
        noCol,
        { data: "barcode",       width: "110px" },
        { data: "nama_item",     width: "360px" },
        { data: "nama_supplier", width: "130px" },
        { data: "nama_unit",     searchable: false, width: "70px" },
        { data: "modal",         width: "110px", searchable: false, className: "text-right" },
        { data: "pk",            width: "90px"  },
        { data: "price",         width: "110px", className: "text-right" },
        { data: "stock",         width: "70px",  className: "text-center" },
        { data: "action",        searchable: false, orderable: false, width: "180px", className: "text-center" }
    ];

    // Kolom & TH untuk kasir
    var kasirTH = '<th width="30">No</th><th width="110">Barcode</th>'
                + '<th>Nama Barang</th>'
                + '<th width="130">Supplier</th>'
                + '<th width="90" class="text-center">Stok</th>'
                + '<th width="90">PK</th>'
                + '<th width="110" class="text-right">Harga Jual</th>';

    var kasirCols = [
        noCol,
        { data: "barcode",   width: "110px" },
        { data: "nama_item" },
        { data: "nama_supplier", width: "130px" },
        {
            data: null, width: "90px", className: "text-center", searchable: false, orderable: false,
            render: function(d) { return d.stock + ' <small class="text-muted">' + d.nama_unit + '</small>'; }
        },
        { data: "pk",    width: "200px"  },
        { data: "price", width: "110px", className: "text-right" },
    ];

    // Render TH
    $('#thead-row').html(isAdmin ? adminTH : kasirTH);

    // 2. Inisialisasi DataTable
    const table = $(tableSelector).DataTable({
        stateSave: true,
        stateDuration: 120,
        serverSide: true,
        order: [],
        ajax: {
            url: "<?= site_url('item/get_json') ?>",
            type: "POST"
        },
        columns: isAdmin ? adminCols : kasirCols
    });

    // 3. Shortcut Keyboard & Event Handlers
    $(window).on('keydown', function(e) {
        // Ctrl + F (70) atau F2 (113)
        if ((e.ctrlKey && e.keyCode === 70) || e.keyCode === 113) {
            e.preventDefault();
            
            const $searchInput = $('input[aria-controls="table-item"]');
            if ($searchInput.length) {
                $searchInput.focus().select();
            }
        }
    });

    // Auto-select saat klik input pencarian
    $(document).on('focus', 'input[aria-controls="table-item"]', function () {
        $(this).select();
    });
});
</script>