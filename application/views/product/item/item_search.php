<section class="content-header">
    <h1>Data Variasi / SKU
        <small>Kelola data barang, variasi, dan supplier</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('item') ?>"><i class="fa fa-archive"></i> Barang</a></li>
        <li class="active">Variasi / SKU</li>
    </ol>
</section>

<section class="content">

    <!-- Filter Bar -->
    <div class="box box-default" style="margin-bottom:10px;">
        <div class="box-body" style="padding:10px 15px;">
            <div class="row">
                <div class="col-md-3">
                    <select id="filter-category" class="form-control select2" style="width:100%">
                        <option value="">-- Semua Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->category_id ?>"><?= $cat->nama_category ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filter-mapped" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="mapped">Sudah Dimapping</option>
                        <option value="unmapped">Belum Dimapping</option>
                    </select>
                </div>
                <div class="col-md-3" style="padding-top:2px;">
                    <button id="btn-filter" class="btn btn-info btn-sm btn-flat">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <button id="btn-reset" class="btn btn-default btn-sm btn-flat">
                        <i class="fa fa-refresh"></i> Reset
                    </button>
                </div>
                <div class="col-md-2 text-right" style="padding-top:2px;">
                    <?php if ($this->fungsi->user_login()->level <= 2): ?>
                    <a href="<?= site_url('item/add') ?>" class="btn btn-primary btn-sm btn-flat">
                        <i class="fa fa-plus"></i> Tambah Variasi
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-body table-responsive" style="padding:0;">
            <table id="table-search" class="table table-striped table-bordered dt-responsive" style="width:100%">
                <thead>
                    <tr>
                        <th width="40px">No</th>
                        <th width="90px">Barcode</th>
                        <th>Nama Barang</th>
                        <th width="140px">Supplier</th>
                        <th width="90px" class="text-center">Cost Code</th>
                        <th width="110px" class="text-right">Harga Beli</th>
                        <th width="110px" class="text-right">Harga Jual</th>
                        <th width="55px" class="text-center">Stok</th>
                        <th width="65px" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>

<style>
#table-search td { vertical-align: middle !important; padding: 8px 10px !important; }
#table-search td:nth-child(3) { min-width: 260px; }
#table-search .label { white-space: nowrap; }
</style>

<script>
$(document).ready(function () {
    var table = $('#table-search').DataTable({
        serverSide: true,
        stateSave: true,
        stateDuration: 120,
        order: [[1, 'asc']],
        pageLength: 10,
        dom: '<"row"<"col-sm-3"l><"col-sm-6 text-center"i><"col-sm-3"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
        ajax: {
            url:  '<?= site_url('item/get_json_cross') ?>',
            type: 'POST',
            data: function (d) {
                d.filter_category = $('#filter-category').val();
                d.filter_mapped   = $('#filter-mapped').val();
            }
        },
        language: {
            search:         '',
            searchPlaceholder: 'Cari barcode, nama barang, supplier...',
            lengthMenu:     'Tampilkan _MENU_',
            info:           'Menampilkan _START_–_END_ dari _TOTAL_',
            infoFiltered:   '(filter dari _MAX_)',
            paginate: { previous: '‹', next: '›' }
        },
        columns: [
            {
                data: null, orderable: false, searchable: false, className: 'text-center',
                render: function (d, t, r, meta) {
                    return $('#table-search').DataTable().page.info().start + meta.row + 1;
                }
            },
            { data: 'barcode', width: '90px', className: 'text-muted' },
            { data: 'nama_barang', orderable: false },
            { data: 'nama_supplier', orderable: false, searchable: false, width: '140px' },
            { data: 'cost_code', orderable: false, searchable: false, className: 'text-center', width: '90px' },
            { data: 'harga_beli', searchable: false, className: 'text-right', width: '110px' },
            { data: 'harga_jual', searchable: false, className: 'text-right', width: '110px' },
            { data: 'stock', searchable: false, className: 'text-center', width: '55px' },
            { data: 'action', orderable: false, searchable: false, className: 'text-center', width: '65px' }
        ]
    });

    $('#btn-filter').on('click', function () { table.ajax.reload(); });
    $('#filter-mapped').on('change', function () { table.ajax.reload(); });

    $('#btn-reset').on('click', function () {
        $('#filter-mapped').val('');
        $('#filter-category').val('').trigger('change');
        table.search('').ajax.reload();
    });

    $('#filter-category').select2({ width: '100%', placeholder: '-- Semua Kategori --', allowClear: true });

    // Shortcut F2 / Ctrl+F
    $(window).on('keydown', function (e) {
        if ((e.ctrlKey && e.keyCode === 70) || e.keyCode === 113) {
            e.preventDefault();
            $('input[aria-controls="table-search"]').focus().select();
        }
    });
    $(document).on('focus', 'input[aria-controls="table-search"]', function () { $(this).select(); });
});
</script>
