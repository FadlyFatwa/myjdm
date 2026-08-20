<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-header">
    <h1><i class="fa fa-file-text-o" style="color:#3c8dbc"></i> Purchase Order
        <small>daftar semua PO</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Purchase Order</li>
    </ol>
</div>

<div class="content">

<style>
/* ── Filter tabs ── */
.po-filter-tabs { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:14px; }
.po-tab {
    padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600;
    border:2px solid #ddd; background:#fff; color:#666; cursor:pointer;
    transition:all .18s; white-space:nowrap; display:flex; align-items:center; gap:6px;
}
.po-tab:hover { border-color:#3c8dbc; color:#3c8dbc; }
.po-tab.active       { background:#3c8dbc; border-color:#3c8dbc; color:#fff; }
.po-tab.active-danger{ background:#e65100; border-color:#e65100; color:#fff; }
.po-tab .tab-badge   { background:rgba(255,255,255,.3); border-radius:10px; padding:1px 7px; font-size:11px; font-weight:700; }
.po-tab:not(.active):not(.active-danger) .tab-badge { background:#e9ecef; color:#555; }

/* dark mode — tabs */
body.dark-mode .po-tab { background:#252836; border-color:#374151; color:#9ca3af; }
body.dark-mode .po-tab:hover { border-color:#3c8dbc; color:#60a5fa; }
body.dark-mode .po-tab:not(.active):not(.active-danger) .tab-badge { background:#374151; color:#d1d5db; }
body.dark-mode .po-tab.active        { background:#3c8dbc; border-color:#3c8dbc; color:#fff; }
body.dark-mode .po-tab.active-danger { background:#e65100; border-color:#e65100; color:#fff; }

/* dark mode — box */
body.dark-mode .box { background:#222537; border-color:#2d3148; }
body.dark-mode .box-header { background:#1e2233 !important; border-color:#2d3148 !important; color:#e5e7eb !important; }
body.dark-mode .box-title  { color:#e5e7eb !important; }
body.dark-mode .box-body   { background:#222537; }

/* dark mode — DataTables wrapper */
body.dark-mode .dataTables_wrapper .dataTables_length,
body.dark-mode .dataTables_wrapper .dataTables_filter,
body.dark-mode .dataTables_wrapper .dataTables_info,
body.dark-mode .dataTables_wrapper .dataTables_paginate { color:#9ca3af !important; }
body.dark-mode .dataTables_wrapper .dataTables_filter input,
body.dark-mode .dataTables_wrapper .dataTables_length select {
    background:#1a1d27 !important; border-color:#374151 !important;
    color:#e5e7eb !important; border-radius:4px;
}
body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button {
    color:#9ca3af !important;
}
body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.current,
body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background:#3c8dbc !important; border-color:#3c8dbc !important; color:#fff !important;
}
body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background:#2d3148 !important; border-color:#374151 !important; color:#e5e7eb !important;
}

/* dark mode — table */
body.dark-mode table.dataTable thead th,
body.dark-mode table.dataTable thead td {
    background:#1e2233 !important; color:#9ca3af !important;
    border-color:#2d3148 !important;
}
body.dark-mode table.dataTable thead th.sorting:after,
body.dark-mode table.dataTable thead th.sorting_asc:after,
body.dark-mode table.dataTable thead th.sorting_desc:after { color:#6b7280 !important; }
body.dark-mode table.dataTable tbody tr { background:#222537 !important; color:#e5e7eb; }
body.dark-mode table.dataTable tbody tr:hover { background:#252836 !important; }
body.dark-mode table.dataTable tbody tr.odd  { background:#1e2233 !important; }
body.dark-mode table.dataTable tbody tr.even { background:#222537 !important; }
body.dark-mode table.dataTable tbody td { border-color:#3d4463 !important; }
body.dark-mode table.dataTable.table-bordered { border-color:#3d4463 !important; }

/* Selector ber-ID: memastikan menang lawan CSS bawaan plugin DataTables (border-top/right-width:0) */
#tbl-po-list.dataTable > tbody > tr > td {
    border-bottom: 1px solid #dee2e6 !important;
}
body.dark-mode #tbl-po-list.dataTable > tbody > tr > td {
    border-bottom: 1px solid #3d4463 !important;
}

/* ── Row states — aksen border kiri saja, tidak menimpa background baris ── */
table.dataTable tbody tr.row-overdue   { border-left:3px solid #e65100; }
table.dataTable tbody tr.row-received  { border-left:3px solid #00a65a; }
table.dataTable tbody tr.row-cancelled { opacity:.6; }
body.dark-mode table.dataTable tbody tr.row-overdue   { border-left:3px solid #e65100 !important; }
body.dark-mode table.dataTable tbody tr.row-received  { border-left:3px solid #00a65a !important; }
body.dark-mode table.dataTable tbody tr.row-cancelled { opacity:.6; }

/* ── Tombol aksi — sejajar walaupun jumlah tombol tiap baris beda ── */
.po-actions { display:flex; gap:4px; justify-content:center; align-items:center; }
.po-action-placeholder { visibility:hidden; }

/* ── Status — badge ditumpuk rapi, bukan wrap sembarangan ── */
.po-status { display:flex; flex-direction:column; align-items:flex-start; gap:4px; }
.po-status .label { white-space:nowrap; }
</style>

<!-- Filter tabs -->
<div class="po-filter-tabs" id="po-filter-tabs">
    <div class="po-tab active" data-filter="all">
        <i class="fa fa-list"></i> Semua
        <span class="tab-badge" id="badge-all">—</span>
    </div>
    <div class="po-tab" data-filter="overdue" id="tab-overdue">
        <i class="fa fa-exclamation-triangle"></i> Perlu Ditindak
        <span class="tab-badge" id="badge-overdue">0</span>
    </div>
    <div class="po-tab" data-filter="draft">
        <i class="fa fa-pencil"></i> Draft
        <span class="tab-badge" id="badge-draft">—</span>
    </div>
    <div class="po-tab" data-filter="sent">
        <i class="fa fa-paper-plane"></i> Terkirim
        <span class="tab-badge" id="badge-sent">—</span>
    </div>
    <div class="po-tab" data-filter="partial">
        <i class="fa fa-adjust"></i> Sebagian Diterima
        <span class="tab-badge" id="badge-partial">—</span>
    </div>
    <div class="po-tab" data-filter="received">
        <i class="fa fa-check-circle"></i> Selesai
        <span class="tab-badge" id="badge-received">—</span>
    </div>
    <div class="po-tab" data-filter="cancelled">
        <i class="fa fa-ban"></i> Dibatalkan
        <span class="tab-badge" id="badge-cancelled">—</span>
    </div>
</div>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title" id="table-title">
            <i class="fa fa-list-alt"></i> Semua Purchase Order
        </h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal-receive-direct">
                <i class="fa fa-inbox"></i> Terima Tanpa PO
            </button>
            <a href="<?= site_url('po-cart') ?>" class="btn btn-success btn-sm">
                <i class="fa fa-cart-plus"></i> Keranjang PO
            </a>
        </div>
    </div>
    <div class="box-body">
        <table id="tbl-po-list" class="table table-bordered table-hover" style="width:100%">
            <thead>
                <tr style="background:#f4f6f9;font-size:12px;text-transform:uppercase;letter-spacing:.4px;color:#555">
                    <th width="40">#</th>
                    <th>No. PO</th>
                    <th>Supplier</th>
                    <th width="105">Tgl PO</th>
                    <th width="180">Status</th>
                    <th width="160" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

</div>

<!-- Modal Terima Tanpa PO -->
<div class="modal fade" id="modal-receive-direct" tabindex="-1">
    <div class="modal-dialog" style="max-width:440px">
        <div class="modal-content">
            <div class="modal-header" style="background:#222d32;color:#fff;border-bottom:3px solid #00a65a">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff">&times;</button>
                <h4 class="modal-title"><i class="fa fa-inbox"></i> Terima Barang Tanpa PO</h4>
            </div>
            <form action="<?= site_url('purchase-order/receive-direct') ?>" method="post">
                <div class="modal-body">
                    <p class="text-muted" style="font-size:12.5px">
                        Untuk barang yang langsung diorder &amp; diterima tanpa PO formal
                        (mis. pesanan via WhatsApp). Pilih supplier, lalu tambahkan barangnya
                        satu per satu di layar penerimaan.
                    </p>
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="form-group">
                        <label>Supplier <span class="text-red">*</span></label>
                        <select name="supplier_id" class="form-control" required>
                            <option value="">— Pilih Supplier —</option>
                            <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s->supplier_id ?>"><?= htmlspecialchars($s->nama_supplier) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-arrow-right"></i> Lanjutkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function () {
    function toast(icon, msg) {
        Swal.fire({
            toast: true, position: 'top-end', icon: icon, title: msg,
            showConfirmButton: false, timer: 3000, timerProgressBar: true,
        });
    }

    <?php $flash = $this->session->flashdata('success'); if ($flash): ?>
    toast('success', '<?= addslashes($flash) ?>');
    <?php endif; ?>
    <?php $ferr = $this->session->flashdata('error'); if ($ferr): ?>
    toast('error', '<?= addslashes($ferr) ?>');
    <?php endif; ?>

    var activeFilter = 'all';

    var titleMap = {
        all       : 'Semua Purchase Order',
        overdue   : '<i class="fa fa-exclamation-triangle" style="color:#e65100"></i> Perlu Ditindaklanjuti',
        draft     : 'PO — Draft',
        sent      : 'PO — Terkirim',
        partial   : 'PO — Sebagian Diterima',
        received  : 'PO — Selesai',
        cancelled : 'PO — Dibatalkan',
    };

    var table = $('#tbl-po-list').DataTable({
        processing : true,
        serverSide : true,
        ajax: {
            url  : '<?= site_url('purchase-order/get_json') ?>',
            type : 'POST',
            data : function (d) { d.filter = activeFilter; }
        },
        columns: [
            { data: 'no',            orderable: false, width: '40px' },
            { data: 'po_number' },
            { data: 'nama_supplier' },
            { data: 'po_date' },
            { data: 'status',  orderable: false },
            { data: 'action',  orderable: false, className: 'text-center' },
        ],
        language : { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        order    : [[0, 'desc']],
        stateSave: false,
        createdRow: function (row, data) {
            if (data.is_overdue) {
                $(row).addClass('row-overdue');
            } else if (data.status && data.status.indexOf('label-success') !== -1) {
                $(row).addClass('row-received');
            } else if (data.status && data.status.indexOf('label-danger') !== -1) {
                $(row).addClass('row-cancelled');
            }
        },
    });

    // ── Tab click ─────────────────────────────────────────────
    $('#po-filter-tabs .po-tab').on('click', function () {
        activeFilter = $(this).data('filter');

        // Style tabs
        $('#po-filter-tabs .po-tab').removeClass('active active-danger');
        if (activeFilter === 'overdue') {
            $(this).addClass('active-danger');
        } else {
            $(this).addClass('active');
        }

        // Update title
        $('#table-title').html(titleMap[activeFilter] || 'Purchase Order');

        table.ajax.reload(null, false);
    });

    // ── Load badge counts ──────────────────────────────────────
    function loadBadges() {
        $.getJSON('<?= site_url('purchase-order/overdue-count') ?>', function (res) {
            var n = res.count || 0;
            $('#badge-overdue').text(n);
            if (n > 0) {
                $('#tab-overdue').css({
                    'border-color': '#e65100',
                    'color': '#e65100',
                    'background': '#fff3e0',
                });
                $('#badge-overdue').css({ 'background': '#e65100', 'color': '#fff' });
            }
        });

        $.post('<?= site_url('purchase-order/get_json') ?>',
            { draw:1, start:0, length:0, filter:'draft', 'search[value]':'', 'order[0][column]':0, 'order[0][dir]':'desc' },
            function (r) { $('#badge-draft').text(r.recordsFiltered); }, 'json');

        $.post('<?= site_url('purchase-order/get_json') ?>',
            { draw:1, start:0, length:0, filter:'sent', 'search[value]':'', 'order[0][column]':0, 'order[0][dir]':'desc' },
            function (r) { $('#badge-sent').text(r.recordsFiltered); }, 'json');

        $.post('<?= site_url('purchase-order/get_json') ?>',
            { draw:1, start:0, length:0, filter:'partial', 'search[value]':'', 'order[0][column]':0, 'order[0][dir]':'desc' },
            function (r) { $('#badge-partial').text(r.recordsFiltered); }, 'json');

        $.post('<?= site_url('purchase-order/get_json') ?>',
            { draw:1, start:0, length:0, filter:'received', 'search[value]':'', 'order[0][column]':0, 'order[0][dir]':'desc' },
            function (r) { $('#badge-received').text(r.recordsFiltered); }, 'json');

        $.post('<?= site_url('purchase-order/get_json') ?>',
            { draw:1, start:0, length:0, filter:'all', 'search[value]':'', 'order[0][column]':0, 'order[0][dir]':'desc' },
            function (r) { $('#badge-all').text(r.recordsFiltered); }, 'json');
    }

    loadBadges();

    // ── Cancel PO ─────────────────────────────────────────────
    $(document).on('click', '.btn-cancel-po', function () {
        var $btn  = $(this);
        var po_id = $btn.data('id');
        if ($btn.prop('disabled')) return;
        Swal.fire({
            title: 'Batalkan PO ini?',
            text : 'PO yang dibatalkan tidak bisa dikembalikan.',
            icon : 'warning',
            showCancelButton : true,
            confirmButtonText: 'Ya, batalkan',
            cancelButtonText : 'Tidak',
            confirmButtonColor: '#dd4b39',
        }).then(function (result) {
            if (result.isConfirmed) {
                $btn.prop('disabled', true);
                $.post('<?= site_url('purchase-order/status') ?>', { po_id: po_id, status: 'cancelled' }, function (res) {
                    if (res.status === 'success') {
                        toast('success', 'PO berhasil dibatalkan.');
                        table.ajax.reload(null, false);
                        loadBadges();
                    } else {
                        $btn.prop('disabled', false);
                    }
                }, 'json');
            }
        });
    });
});
</script>
