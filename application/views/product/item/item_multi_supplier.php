<section class="content-header">
    <h1>Barang Multi Supplier <small>Item dengan lebih dari 1 supplier</small></h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-archive"></i></a></li>
        <li class="active">Barang Multi Supplier</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Daftar Barang dengan Banyak Supplier</h3>
        </div>

        <div class="ox-body table-responsive">
            <div class="well well-sm">
                <i class="fa fa-info-circle text-blue"></i> Menampilkan barang yang memiliki referensi lebih dari 1 supplier di <code>supplier_barang</code>.
            </div>
            <table id="table-item-multi-supplier" class="table table-striped table-bordered dt-responsive" style="width:100%">
                <thead>
                    <tr>
                        <th width="30px">No</th>
                        <th>Barcode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th width="90px">Jumlah</th>
                        <th>Supplier</th>
                        <th width="110px">Harga Jual</th>
                        <th width="70px">Stok</th>
                        <th width="100px">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    const tableSelector = '#table-item-multi-supplier';

    var noCol = {
        data: null, width: "40px", searchable: false, orderable: false, className: "text-center",
        render: function(d, t, r, m) { let p=$(tableSelector).DataTable().page.info(); return p.start+m.row+1; }
    };

    $(tableSelector).DataTable({
        stateSave: true,
        stateDuration: 120,
        serverSide: true,
        order: [],
        ajax: {
            url: "<?= site_url('item/get_json_multi_supplier') ?>",
            type: "POST"
        },
        columns: [
            noCol,
            { data: "barcode",        width: "110px" },
            { data: "nama_item",      width: "260px" },
            { data: "nama_category",  width: "110px" },
            { data: "nama_unit",      searchable: false, width: "70px" },
            { data: "supplier_count", searchable: false, orderable: true, className: "text-center", width: "90px" },
            { data: "supplier_list",  searchable: false, orderable: false },
            { data: "price",          width: "110px", className: "text-right" },
            { data: "stock",          width: "70px",  className: "text-center" },
            { data: "action",         searchable: false, orderable: false, width: "100px", className: "text-center" }
        ]
    });
});
</script>
