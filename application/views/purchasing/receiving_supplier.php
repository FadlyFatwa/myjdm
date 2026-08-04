<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-header">
    <h1><i class="fa fa-inbox" style="color:#f39c12"></i> Penerimaan Barang
        <small>PO terkirim &amp; sebagian diterima</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?= site_url('purchase-order') ?>">Purchase Order</a></li>
        <li class="active">Penerimaan</li>
    </ol>
</div>

<div class="content">

<style>
/* ── Overdue section ── */
.overdue-box {
    border-radius:8px; overflow:hidden;
    border:1px solid #ffccbc; box-shadow:0 2px 12px rgba(230,81,0,.12);
    margin-bottom:18px;
}
.overdue-box-header {
    background:linear-gradient(135deg,#e65100,#bf360c);
    color:#fff; padding:13px 18px;
    display:flex; align-items:center; gap:10px;
}
.overdue-box-header h4 { margin:0; font-size:15px; font-weight:700; flex:1; }
.overdue-count-badge {
    background:rgba(255,255,255,.25); color:#fff;
    border-radius:20px; padding:3px 12px; font-size:13px; font-weight:700;
}
.overdue-table { margin:0; background:#fff; }
body.dark-mode .overdue-table { background:#222537; }
.overdue-table thead tr { background:#fbe9e7; font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:#bf360c; }
body.dark-mode .overdue-table thead tr { background:#2d1a15; color:#ef9a9a; }
.overdue-table tbody tr { border-left:3px solid transparent; transition:background .12s; }
.overdue-table tbody tr:hover { background:#fff3e0; }
body.dark-mode .overdue-table tbody tr:hover { background:#2d1e10; }
.days-chip {
    display:inline-block; padding:2px 9px; border-radius:10px;
    font-size:12px; font-weight:700; color:#fff;
}

/* ── Normal section ── */
.recv-box {
    background:#fff; border-radius:8px;
    box-shadow:0 2px 12px rgba(0,0,0,.08);
    border:1px solid #e0e6ed; overflow:hidden;
}
body.dark-mode .recv-box { background:#222537; border-color:#2d3148; }
.recv-box-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:13px 18px; background:#f8f9fa; border-bottom:1px solid #e0e6ed;
}
body.dark-mode .recv-box-header { background:#1e2233; border-color:#2d3148; }
.recv-box-header h4 { margin:0; font-size:15px; font-weight:600; }
body.dark-mode #recv-supplier-filter { background:#1a1d27; border-color:#374151; color:#e5e7eb; }

/* ── Supplier accordion ── */
.supplier-block { margin-bottom:10px; border-radius:6px; overflow:hidden; border:1px solid #dde3ea; }
body.dark-mode .supplier-block { border-color:#2d3148; }
.supplier-block:last-child { margin-bottom:0; }
.supplier-header {
    background:#2e6b24; color:#fff; padding:11px 16px;
    display:flex; align-items:center; gap:10px;
    cursor:pointer; user-select:none;
}
body.dark-mode .supplier-header { background:#1e3d1a; }
.supplier-header .badge-count {
    background:rgba(255,255,255,.2); color:#fff;
    border-radius:10px; padding:2px 9px; font-size:12px; font-weight:700;
}
.supplier-header .toggle-icon { margin-left:auto; transition:transform .2s; }

/* ── Tables ── */
.po-table { margin:0; border-top:0; }
.po-table thead tr { background:#f4f6f9; font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:#666; }
body.dark-mode .po-table thead tr { background:#1a1d27; color:#9ca3af; }
.po-table tbody tr { background:#fff; }
body.dark-mode .po-table tbody tr { background:#222537; }
.po-table tbody tr:hover { background:#f6fbf7; }
.progress-mini { height:5px; background:#e9ecef; border-radius:3px; margin-top:3px; }
.progress-mini .bar { height:5px; border-radius:3px; }
</style>

<!-- Loading state -->
<div id="page-loading" class="text-center" style="padding:60px 0;color:#aaa">
    <i class="fa fa-spinner fa-spin fa-2x"></i>
    <p style="margin-top:12px">Memuat data...</p>
</div>

<!-- Section 1: Perlu Ditindaklanjuti -->
<div id="overdue-section" style="display:none">
    <div class="overdue-box">
        <div class="overdue-box-header">
            <i class="fa fa-exclamation-triangle fa-lg"></i>
            <h4>Perlu Ditindaklanjuti</h4>
            <span class="overdue-count-badge" id="overdue-badge">0 PO</span>
            <small style="opacity:.8;font-size:12px">Belum ada penerimaan &gt; 7 hari</small>
        </div>
        <div style="overflow-x:auto">
            <table class="table table-condensed overdue-table" style="margin:0">
                <thead>
                    <tr>
                        <th style="padding:8px 14px">Supplier</th>
                        <th style="padding:8px 14px">No. PO</th>
                        <th style="padding:8px 14px;width:90px;text-align:center">Terlambat</th>
                        <th style="padding:8px 14px">Keterangan</th>
                        <th style="padding:8px 14px">Status</th>
                        <th style="padding:8px 14px;width:140px">Progress Terima</th>
                        <th style="padding:8px 14px;width:80px;text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="overdue-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Section 2: Accordion normal -->
<div id="normal-section" style="display:none">
    <div class="recv-box">
        <div class="recv-box-header">
            <h4><i class="fa fa-inbox" style="color:#f39c12;margin-right:8px"></i>Menunggu Penerimaan</h4>
            <div style="display:flex;align-items:center;gap:10px">
                <a href="<?= site_url('purchase-order') ?>" class="btn btn-default btn-sm">
                    <i class="fa fa-list"></i> Semua PO
                </a>
            </div>
        </div>

        <!-- Search -->
        <div style="padding:11px 18px;border-bottom:1px solid #e0e6ed;position:relative">
            <i class="fa fa-search" style="position:absolute;left:30px;top:50%;transform:translateY(-50%);color:#aaa;pointer-events:none"></i>
            <input type="text" id="recv-supplier-filter"
                   placeholder="Cari supplier..."
                   style="width:100%;padding:7px 12px 7px 32px;border-radius:6px;border:1px solid #d0d5dd;
                          font-size:13px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04)">
        </div>

        <div style="padding:16px 18px">
            <div id="receiving-container"></div>
            <div id="recv-no-result" style="display:none;text-align:center;padding:20px;color:#aaa;font-size:13px">
                <i class="fa fa-search"></i> Supplier tidak ditemukan.
            </div>
            <div id="recv-all-ok" style="display:none;text-align:center;padding:30px 20px;color:#aaa">
                <i class="fa fa-check-circle" style="font-size:40px;color:#00a65a;margin-bottom:10px;display:block"></i>
                Semua PO dalam proses normal — tidak ada yang tertunda.
            </div>
        </div>
    </div>
</div>

<!-- Empty state (no POs at all) -->
<div id="empty-state" style="display:none">
    <div class="recv-box">
        <div class="recv-box-header">
            <h4><i class="fa fa-inbox" style="color:#f39c12;margin-right:8px"></i>Penerimaan Barang</h4>
        </div>
        <div style="text-align:center;padding:60px 20px">
            <i class="fa fa-inbox" style="font-size:52px;color:#ddd"></i>
            <p style="margin:18px 0 4px;font-size:16px;color:#888;font-weight:600">Tidak ada PO yang menunggu penerimaan</p>
            <p style="color:#bbb;font-size:13px">Semua PO sudah diterima atau belum ada yang dikirim.</p>
            <a href="<?= site_url('purchase-order') ?>" class="btn btn-default btn-sm" style="margin-top:8px">
                <i class="fa fa-list"></i> Lihat Semua PO
            </a>
        </div>
    </div>
</div>

</div><!-- .content -->

<script>
$(function () {
    var statusMap = {
        'sent':    '<span class="label label-info">Terkirim</span>',
        'partial': '<span class="label label-warning">Sebagian Diterima</span>'
    };

    var OVERDUE_DAYS = 7;

    function daysAgo(dateStr) {
        if (!dateStr) return 0;
        var d = new Date(dateStr.substring(0, 10));
        return Math.floor((Date.now() - d.getTime()) / 86400000);
    }

    function isOverdue(po) {
        if (po.status !== 'sent' && po.status !== 'partial') return false;
        var base = po.last_receipt_date || po.po_date;
        return daysAgo(base) > OVERDUE_DAYS;
    }

    function renderProgress(ordered, received) {
        ordered  = parseInt(ordered)  || 0;
        received = parseInt(received) || 0;
        var pct   = ordered > 0 ? Math.round(received / ordered * 100) : 0;
        var color = pct > 0 ? '#f39c12' : '#ddd';
        return '<div style="font-size:11px;font-weight:600;color:' + (pct > 0 ? '#e08e0b' : '#999') + '">'
             + received + '/' + ordered + ' (' + pct + '%)</div>'
             + '<div class="progress-mini"><div class="bar" style="width:' + pct + '%;background:' + color + '"></div></div>';
    }

    function chipColor(days) {
        if (days > 30) return '#c62828';
        if (days > 14) return '#e65100';
        return '#f57c00';
    }

    function load() {
        $.get('<?= site_url('purchase-order/receiving-data') ?>', function (res) {
            $('#page-loading').hide();

            // Flatten all POs
            var allPOs = [];
            (res.grouped || []).forEach(function (sup) {
                sup.pos.forEach(function (po) {
                    po._supplier = sup.nama_supplier;
                    allPOs.push(po);
                });
            });

            if (allPOs.length === 0) {
                $('#empty-state').show();
                return;
            }

            // Split overdue vs normal
            var overduePOs = allPOs.filter(isOverdue)
                .sort(function (a, b) {
                    var da = daysAgo(a.last_receipt_date || a.po_date);
                    var db = daysAgo(b.last_receipt_date || b.po_date);
                    return db - da; // paling lama dulu
                });

            var normalPOs = allPOs.filter(function (po) { return !isOverdue(po); });

            // ── Section 1: Overdue ─────────────────────────────
            if (overduePOs.length > 0) {
                $('#overdue-badge').text(overduePOs.length + ' PO');

                var overdueRows = overduePOs.map(function (po) {
                    var base  = po.last_receipt_date || po.po_date;
                    var days  = daysAgo(base);
                    var ctx   = po.last_receipt_date
                        ? 'GR terakhir ' + po.last_receipt_date.substring(0, 10)
                        : 'Belum ada penerimaan';
                    var color = chipColor(days);
                    return '<tr>' +
                        '<td style="padding:9px 14px;font-weight:600">' + $('<s>').text(po._supplier).html() + '</td>' +
                        '<td style="padding:9px 14px;font-weight:600">' + $('<s>').text(po.po_number).html() + '</td>' +
                        '<td style="padding:9px 14px;text-align:center">' +
                            '<span class="days-chip" style="background:' + color + '">' + days + ' hari</span>' +
                        '</td>' +
                        '<td style="padding:9px 14px;font-size:12px;color:#888">' + ctx + '</td>' +
                        '<td style="padding:9px 14px">' + (statusMap[po.status] || po.status) + '</td>' +
                        '<td style="padding:9px 14px">' + renderProgress(po.total_ordered, po.total_received) + '</td>' +
                        '<td style="padding:9px 14px;text-align:center">' +
                            '<a href="<?= site_url('purchase-order') ?>/' + po.po_id + '" class="btn btn-danger btn-xs">' +
                            '<i class="fa fa-inbox"></i> Proses</a>' +
                        '</td>' +
                        '</tr>';
                }).join('');

                $('#overdue-tbody').html(overdueRows);
                $('#overdue-section').show();
            }

            // ── Section 2: Normal accordion ────────────────────
            $('#normal-section').show();

            // Group normal POs by supplier
            var normalGrouped = {};
            normalPOs.forEach(function (po) {
                var sup = po._supplier;
                if (!normalGrouped[sup]) normalGrouped[sup] = [];
                normalGrouped[sup].push(po);
            });

            var $c = $('#receiving-container').empty();

            if (Object.keys(normalGrouped).length === 0) {
                $('#recv-all-ok').show();
            } else {
                Object.keys(normalGrouped).sort().forEach(function (supName, si) {
                    var pos    = normalGrouped[supName];
                    var $block = $('<div class="supplier-block">').attr('data-supplier', supName.toLowerCase());
                    var bodyId = 'sb-' + si;

                    var $header = $(
                        '<div class="supplier-header" data-target="#' + bodyId + '">' +
                        '<i class="fa fa-truck"></i>' +
                        '<span style="font-weight:600">' + $('<span>').text(supName).html() + '</span>' +
                        '<span class="badge-count">' + pos.length + ' PO</span>' +
                        '<i class="fa fa-angle-down toggle-icon"></i>' +
                        '</div>'
                    );

                    var rows = pos.map(function (po, pi) {
                        return '<tr>' +
                            '<td style="padding:9px 14px">' + (pi + 1) + '</td>' +
                            '<td style="padding:9px 14px;font-weight:600">' + $('<span>').text(po.po_number).html() + '</td>' +
                            '<td style="padding:9px 14px">' + (po.po_date ? po.po_date.substring(0, 10) : '—') + '</td>' +
                            '<td style="padding:9px 14px">' + (statusMap[po.status] || po.status) + '</td>' +
                            '<td style="padding:9px 14px;min-width:130px">' + renderProgress(po.total_ordered, po.total_received) + '</td>' +
                            '<td style="padding:9px 14px">' +
                                '<a href="<?= site_url('purchase-order') ?>/' + po.po_id + '" class="btn btn-warning btn-xs">' +
                                '<i class="fa fa-inbox"></i> Terima</a>' +
                            '</td>' +
                            '</tr>';
                    }).join('');

                    var $body = $(
                        '<div id="' + bodyId + '">' +
                        '<table class="table table-condensed po-table">' +
                        '<thead><tr>' +
                        '<th style="padding:8px 14px;width:40px">No</th>' +
                        '<th style="padding:8px 14px">No. PO</th>' +
                        '<th style="padding:8px 14px;width:100px">Tgl PO</th>' +
                        '<th style="padding:8px 14px">Status</th>' +
                        '<th style="padding:8px 14px;width:140px">Progress</th>' +
                        '<th style="padding:8px 14px;width:80px">Aksi</th>' +
                        '</tr></thead>' +
                        '<tbody>' + rows + '</tbody>' +
                        '</table></div>'
                    );

                    $block.append($header).append($body);
                    $c.append($block);
                });

                // Accordion toggle
                $c.on('click', '.supplier-header', function () {
                    var $target = $('#' + $(this).data('target').replace('#', ''));
                    var $icon   = $(this).find('.toggle-icon');
                    if ($target.is(':visible')) {
                        $target.slideUp(180);
                        $icon.css('transform', 'rotate(-90deg)');
                    } else {
                        $target.slideDown(180);
                        $icon.css('transform', 'rotate(0deg)');
                    }
                });
            }

            // Filter supplier
            $('#recv-supplier-filter').off('input').on('input', function () {
                var kw  = $(this).val().toLowerCase().trim();
                var any = false;
                $('#receiving-container .supplier-block').each(function () {
                    var match = !kw || $(this).data('supplier').indexOf(kw) !== -1;
                    $(this).toggle(match);
                    if (match) any = true;
                });
                $('#recv-no-result').toggle(!any && kw !== '');
                $('#recv-all-ok').toggle(Object.keys(normalGrouped).length === 0 && kw === '');
            }).val('');

        }, 'json');
    }

    load();
});
</script>
