<section class="content-header">
    <h1>Produk <small>Arsip &amp; Barang Tidak Dipakai</small></h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-archive"></i></a></li>
        <li class="active">Produk</li>
        <li class="active">Arsip Barang</li>
    </ol>
</section>

<section class="content">
    <div id="flash" data-flash="<?= $this->session->flashdata('success'); ?>"></div>

    <!-- Tabs -->
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#tab-arsip" data-toggle="tab">
                    <i class="fa fa-archive"></i> Arsip (Nonaktif)
                </a>
            </li>
            <li>
                <a href="#tab-unused" data-toggle="tab">
                    <i class="fa fa-barcode"></i> Barcode Tidak Dipakai
                    <span id="unused-badge" class="badge" style="background:#f39c12;margin-left:4px"></span>
                </a>
            </li>
            <li>
                <a href="#tab-range" data-toggle="tab">
                    <i class="fa fa-search"></i> Cek Celah Barcode
                </a>
            </li>
        </ul>

        <div class="tab-content">

            <!-- Tab 1: Arsip Nonaktif -->
            <div class="tab-pane active" id="tab-arsip">
                <table id="table-archive" class="table table-striped table-bordered dt-responsive" style="width:100%">
                    <thead>
                        <tr>
                            <th width="40">No</th>
                            <th>Barcode Lama</th>
                            <th>Nama Barang</th>
                            <th>Supplier</th>
                            <th>Satuan</th>
                            <?php if ($this->fungsi->user_login()->level == 1): ?>
                            <th>Modal</th>
                            <?php endif; ?>
                            <th>PK</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <?php if ($this->fungsi->user_login()->level == 1): ?>
                            <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Tab 2: Barcode Tidak Dipakai -->
            <div class="tab-pane" id="tab-unused">
                <div class="callout callout-warning" style="margin-bottom:14px;padding:10px 14px">
                    <i class="fa fa-info-circle"></i>
                    Barang aktif yang <strong>belum pernah ada stok masuk</strong> dan <strong>belum pernah terjual</strong>.
                    Barcode ini bisa digunakan ulang dengan menonaktifkan barang.
                </div>
                <table id="table-unused" class="table table-striped table-bordered dt-responsive" style="width:100%">
                    <thead>
                        <tr>
                            <th width="40">No</th>
                            <th>Barcode</th>
                            <th>Nama Barang</th>
                            <th>Supplier</th>
                            <th>Satuan</th>
                            <?php if (in_array($this->fungsi->user_login()->level, [1,2])): ?>
                            <th>Modal</th>
                            <?php endif; ?>
                            <th>PK</th>
                            <th>Harga Jual</th>
                            <?php if ($this->fungsi->user_login()->level == 1): ?>
                            <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Tab 3: Cek Celah Barcode -->
            <div class="tab-pane" id="tab-range">
                <div class="row" style="margin-top:10px">
                    <div class="col-md-5 col-md-offset-3">
                        <div class="callout callout-info" style="padding:10px 14px;margin-bottom:14px;font-size:13px">
                            <i class="fa fa-info-circle"></i>
                            Masukkan range barcode untuk melihat nomor yang <strong>belum terdaftar</strong>.
                        </div>
                        <div style="display:flex;gap:10px;align-items:flex-end;margin-bottom:10px">
                            <div style="flex:1">
                                <label style="font-size:12px;font-weight:600">Dari</label>
                                <input type="number" id="range-from" class="form-control" placeholder="100" min="1">
                            </div>
                            <div style="flex:1">
                                <label style="font-size:12px;font-weight:600">Sampai</label>
                                <input type="number" id="range-to" class="form-control" placeholder="500" min="1">
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary" id="btn-check-range">
                                    <i class="fa fa-search"></i> Cek
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="range-result" style="display:none;margin-top:6px">
                    <div class="row" id="range-stats" style="margin-bottom:12px"></div>
                    <div id="range-list"></div>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
$(document).ready(function () {
    var userLevel = <?= (int) $this->fungsi->user_login()->level ?>;

    // ── Tab 1: Arsip ──────────────────────────────────────────
    $('#table-archive').DataTable({
        serverSide: true,
        order: [],
        ajax: { url: '<?= site_url('item/get_json_archive') ?>', type: 'POST' },
        columns: [
            { data: null, searchable: false, orderable: false, className: 'text-center', width: '40px',
              render: function(d, t, r, m) { return m.row + m.settings._iDisplayStart + 1; } },
            { data: 'barcode',        width: '110px' },
            { data: 'nama_item',      width: '350px' },
            { data: 'nama_supplier',  width: '130px' },
            { data: 'nama_unit',      searchable: false, width: '80px' },
        ].concat(userLevel === 1 ? [{ data: 'modal', searchable: false, width: '100px', className: 'text-right' }] : [])
         .concat([
            { data: 'pk',    width: '90px' },
            { data: 'price', width: '100px', className: 'text-right' },
            { data: 'stock', width: '70px',  className: 'text-center' },
         ])
         .concat(userLevel === 1 ? [{ data: 'action', searchable: false, orderable: false, width: '120px', className: 'text-center' }] : []),
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
    });

    // ── Tab 2: Tidak Dipakai ──────────────────────────────────
    var unusedTable = $('#table-unused').DataTable({
        serverSide: true,
        order: [[1, 'asc']],
        ajax: { url: '<?= site_url('item/get_json_unused') ?>', type: 'POST' },
        columns: [
            { data: null, searchable: false, orderable: false, className: 'text-center', width: '40px',
              render: function(d, t, r, m) { return m.row + m.settings._iDisplayStart + 1; } },
            { data: 'barcode',       width: '110px' },
            { data: 'nama_item',     width: '360px' },
            { data: 'nama_supplier', width: '130px' },
            { data: 'nama_unit',     searchable: false, width: '80px' },
        ].concat(userLevel <= 2 ? [{ data: 'modal', searchable: false, width: '100px', className: 'text-right' }] : [])
         .concat([{ data: 'pk', width: '90px' }, { data: 'price', width: '100px', className: 'text-right' }])
         .concat(userLevel === 1 ? [{
            data: null, searchable: false, orderable: false, width: '120px', className: 'text-center',
            render: function(d, t, r) {
                return '<a href="<?= site_url('item/del/') ?>' + r.item_id + '" class="btn btn-warning btn-xs btn-nonaktif" data-id="' + r.item_id + '">'
                     + '<i class="fa fa-ban"></i> Nonaktifkan</a>';
            }
         }] : []),
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        initComplete: function() {
            $('#unused-badge').text(this.api().page.info().recordsTotal);
        },
        drawCallback: function() {
            $('#unused-badge').text(this.api().page.info().recordsTotal);
        },
    });

    // Load tab kedua saat diklik (lazy load)
    $('a[href="#tab-unused"]').on('shown.bs.tab', function () {
        unusedTable.columns.adjust().draw(false);
    });

    // Konfirmasi nonaktifkan
    $(document).on('click', '.btn-nonaktif', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        Swal.fire({
            title: 'Nonaktifkan barang ini?',
            text: 'Barcode akan tersedia untuk digunakan ulang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, nonaktifkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#f39c12',
        }).then(function (r) {
            if (r.isConfirmed) window.location.href = href;
        });
    });

    // ── Tab 3: Cek Celah Barcode ──────────────────────────────
    $('#btn-check-range').on('click', function () {
        var from = parseInt($('#range-from').val()) || 0;
        var to   = parseInt($('#range-to').val())   || 0;

        if (from < 1 || to < from) {
            Swal.fire({ icon: 'warning', title: 'Range tidak valid', text: 'Pastikan "Dari" ≤ "Sampai" dan nilainya positif.' });
            return;
        }
        if ((to - from) > 10000) {
            Swal.fire({ icon: 'warning', title: 'Range terlalu besar', text: 'Maksimal 10.000 barcode sekaligus.' });
            return;
        }

        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Mengecek...');

        $.post('<?= site_url('item/check_barcode_range') ?>', {
            from: from, to: to,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>',
        }, function (res) {
            $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Cek');

            if (res.status !== 'success') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message }); return;
            }

            // Stats
            $('#range-stats').html(
                '<div class="col-xs-4"><div class="info-box bg-aqua">'
                + '<span class="info-box-icon"><i class="fa fa-list-ol"></i></span>'
                + '<div class="info-box-content"><span class="info-box-text">Total Range</span>'
                + '<span class="info-box-number">' + res.total + '</span></div></div></div>'

                + '<div class="col-xs-4"><div class="info-box bg-green">'
                + '<span class="info-box-icon"><i class="fa fa-check"></i></span>'
                + '<div class="info-box-content"><span class="info-box-text">Sudah Terdaftar</span>'
                + '<span class="info-box-number">' + res.used + '</span></div></div></div>'

                + '<div class="col-xs-4"><div class="info-box ' + (res.missing > 0 ? 'bg-red' : 'bg-green') + '">'
                + '<span class="info-box-icon"><i class="fa fa-' + (res.missing > 0 ? 'exclamation-triangle' : 'check-circle') + '"></i></span>'
                + '<div class="info-box-content"><span class="info-box-text">Belum Terdaftar</span>'
                + '<span class="info-box-number">' + res.missing + '</span></div></div></div>'
            );

            if (res.missing === 0) {
                $('#range-list').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> Semua barcode dalam range ini sudah terdaftar.</div>');
            } else {
                var chunks = [];
                for (var i = 0; i < res.barcodes.length; i += 20) {
                    chunks.push(res.barcodes.slice(i, i + 20).map(function (b) {
                        return '<span class="label label-danger" style="margin:2px;display:inline-block;font-size:13px;padding:5px 8px;font-family:monospace">' + b + '</span>';
                    }).join(''));
                }
                $('#range-list').html(
                    '<div class="box box-danger"><div class="box-header with-border">'
                    + '<h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Barcode yang belum terdaftar (' + res.missing + ')</h3>'
                    + '</div><div class="box-body">' + chunks.join('') + '</div></div>'
                );
            }

            $('#range-result').show();
        }, 'json').fail(function () {
            $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Cek');
            Swal.fire({ icon: 'error', title: 'Terjadi kesalahan server' });
        });
    });

    // Enter di input range
    $('#range-from, #range-to').on('keypress', function (e) {
        if (e.which === 13) $('#btn-check-range').trigger('click');
    });
});
</script>
