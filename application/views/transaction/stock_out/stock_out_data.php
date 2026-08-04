<section class="content-header">
    <h1>Barang Keluar <small>Daftar Barang Keluar</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Stock Out</li>
    </ol>
</section>

<section class="content">
<div id="flash" data-flash="<?= $this->session->flashdata('success') ?>"></div>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-arrow-up text-red"></i> Data Barang Keluar</h3>
        <div class="box-tools pull-right">
            <a href="<?= site_url('stock/out/add') ?>" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Tambah
            </a>
        </div>
    </div>
    <div class="box-body table-responsive no-padding">
        <table class="table table-bordered table-hover" id="tbl-stock-out" style="width:100%">
            <thead>
                <tr>
                    <th width="5%"  class="text-center">No</th>
                    <th width="10%">Barcode</th>
                    <th>Nama Barang</th>
                    <th width="8%"  class="text-center">Qty</th>
                    <th width="20%">Keterangan</th>
                    <th width="12%">Tanggal</th>
                    <th width="8%"  class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</section>

<script>
$(document).ready(function() {
    $('#tbl-stock-out').DataTable({
        processing  : true,
        serverSide  : true,
        ajax        : {
            url  : '<?= site_url('stock/get_json_out') ?>',
            type : 'POST'
        },
        columns: [
            { data: 'no',        orderable: false, className: 'text-center' },
            { data: 'barcode' },
            { data: 'nama_item' },
            { data: 'qty',       className: 'text-center' },
            { data: 'detail' },
            { data: 'date' },
            { data: 'action',    orderable: false, className: 'text-center' }
        ],
        order    : [[0, 'asc']],
        pageLength: 25,
        language : {
            search       : 'Cari:',
            lengthMenu   : 'Tampilkan _MENU_ data',
            info         : 'Data _START_–_END_ dari _TOTAL_',
            infoEmpty    : 'Tidak ada data',
            zeroRecords  : 'Data tidak ditemukan',
            paginate     : { previous: '‹', next: '›' },
            processing   : '<i class="fa fa-spinner fa-spin"></i> Memuat...'
        }
    });
});
</script>
