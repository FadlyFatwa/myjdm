<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-header">
    <h1><i class="fa fa-inbox" style="color:#f39c12"></i> Penerimaan Barang
        <small>PO terkirim &amp; sebagian diterima</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Penerimaan</li>
    </ol>
</div>

<div class="content">

<style>
body.dark-mode #sup-nav .sup-chip.btn-default {
    background:#2a2f45; border-color:#4a5070; color:#c5cbe0;
}
body.dark-mode #sup-nav .sup-chip.btn-default:hover {
    background:#343a56; border-color:#6070a0; color:#e8ecf5;
}
#sup-nav .sup-chip.btn-default { border-color:#bfc8d6; }
</style>

<!-- Loading state -->
<div id="page-loading" class="text-center" style="padding:60px 0;color:#aaa">
    <i class="fa fa-spinner fa-spin fa-2x"></i>
    <p style="margin-top:12px">Memuat data...</p>
</div>

<!-- Empty state -->
<div id="empty-state" style="display:none">
    <div class="box box-default">
        <div class="box-body text-center" style="padding:60px 20px">
            <i class="fa fa-inbox" style="font-size:52px;color:#ddd"></i>
            <p style="margin:18px 0 4px;font-size:16px;color:#888;font-weight:600">Tidak ada PO yang menunggu penerimaan</p>
            <p class="text-muted">Semua PO sudah diterima atau belum ada yang dikirim.</p>
            <a href="<?= site_url('purchase-order') ?>" class="btn btn-default btn-sm" style="margin-top:8px">
                <i class="fa fa-list"></i> Lihat Semua PO
            </a>
        </div>
    </div>
</div>

<!-- Main layout -->
<div id="main-layout" style="display:none">

    <!-- Info strip -->
    <div class="row" style="margin-bottom:10px">
        <div class="col-xs-12" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <span class="badge bg-yellow" style="font-size:13px;padding:5px 10px" id="badge-total-po">0 PO</span>
            <span class="badge bg-blue"   style="font-size:13px;padding:5px 10px" id="badge-total-sup">0 Supplier</span>
            <div style="margin-left:auto;display:flex;gap:8px">
                <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal-receive-direct">
                    <i class="fa fa-inbox"></i> Terima Tanpa PO
                </button>
                <a href="<?= site_url('purchase-order') ?>" class="btn btn-default btn-sm">
                    <i class="fa fa-list"></i> Semua PO
                </a>
            </div>
        </div>
    </div>

    <div class="row">

        <!-- Kolom kiri: supplier capsule -->
        <div class="col-md-3 col-sm-4">
            <div class="box box-default">
                <div class="box-header with-border" style="padding:10px 12px">
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        <input type="text" id="sup-search" class="form-control" placeholder="Cari supplier...">
                    </div>
                </div>
                <div class="box-body" style="max-height:calc(100vh - 340px);overflow-y:auto;display:grid;grid-template-columns:repeat(4,1fr);gap:5px" id="sup-nav">
                    <!-- diisi JS -->
                </div>
                <div class="box-footer" style="padding:8px 12px;font-size:11px">
                    <i class="fa fa-info-circle text-muted"></i>
                    <span class="text-muted" id="sup-count-label">— supplier</span>
                </div>
            </div>
        </div>

        <!-- Kolom kanan: daftar PO -->
        <div class="col-md-9 col-sm-8" id="panel-area">
            <!-- diisi JS -->
        </div>

    </div>
</div>

</div><!-- .content -->

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

    function fmtDate(str) {
        if (!str) return '—';
        var d = new Date(str.substring(0, 10));
        var months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }

    function statusBadge(status) {
        if (status === 'partial') return '<span class="label label-warning"><i class="fa fa-adjust"></i> Sebagian Diterima</span>';
        return '<span class="label label-info"><i class="fa fa-paper-plane"></i> Terkirim</span>';
    }

    function renderProgress(ordered, received) {
        ordered  = parseInt(ordered)  || 0;
        received = parseInt(received) || 0;
        var pct   = ordered > 0 ? Math.round(received / ordered * 100) : 0;
        var color = pct >= 100 ? '#00a65a' : (pct > 0 ? '#f39c12' : '#ddd');
        var textColor = pct >= 100 ? '#00a65a' : (pct > 0 ? '#e08e0b' : '#999');
        return '<div style="font-size:12px;font-weight:600;color:' + textColor + '">'
             + received + '/' + ordered + ' <span style="font-weight:400;color:#aaa;font-size:11px">(' + pct + '%)</span></div>'
             + '<div style="height:5px;background:#e9ecef;border-radius:3px;margin-top:3px">'
             + '<div style="height:5px;width:' + pct + '%;background:' + color + ';border-radius:3px"></div></div>';
    }

    $.get('<?= site_url('purchase-order/receiving-data') ?>', function (res) {
        $('#page-loading').hide();

        var grouped = res.grouped || [];
        if (grouped.length === 0) {
            $('#empty-state').show();
            return;
        }

        var totalPO = 0;
        grouped.forEach(function (s) { totalPO += s.pos.length; });
        $('#badge-total-po').text(totalPO + ' PO');
        $('#badge-total-sup').text(grouped.length + ' Supplier');
        $('#sup-count-label').text(grouped.length + ' supplier');

        // ── Bangun capsule kiri ──────────────────────────────────
        var $nav = $('#sup-nav');
        grouped.forEach(function (sup, i) {
            var $chip = $(
                '<a href="#" class="btn btn-sm btn-default sup-chip' + (i === 0 ? ' btn-success' : '') + '"' +
                ' data-sid="' + i + '"' +
                ' data-name="' + sup.nama_supplier.toLowerCase() + '"' +
                ' style="border-radius:20px;font-size:12px;display:flex;align-items:center;justify-content:center;gap:4px;overflow:hidden;white-space:nowrap;padding:4px 8px">' +
                '<span style="overflow:hidden;text-overflow:ellipsis;min-width:0">' + $('<span>').text(sup.nama_supplier).html() + '</span>' +
                '<span class="badge" style="flex-shrink:0">' + sup.pos.length + '</span>' +
                '</a>'
            );
            $nav.append($chip);
        });

        // ── Bangun panel kanan ───────────────────────────────────
        var $area = $('#panel-area');
        grouped.forEach(function (sup, i) {
            var rows = sup.pos.map(function (po, pi) {
                var btnClass = po.status === 'partial' ? 'btn-warning' : 'btn-info';
                return '<tr>' +
                    '<td style="vertical-align:middle">' + (pi + 1) + '</td>' +
                    '<td style="vertical-align:middle;font-weight:600">' + $('<s>').text(po.po_number).html() + '</td>' +
                    '<td style="vertical-align:middle">' +
                        '<span style="font-size:13px">' + fmtDate(po.po_date) + '</span>' +
                        (po.expected_date ? '<br><small class="text-muted"><i class="fa fa-calendar-check-o"></i> ' + fmtDate(po.expected_date) + '</small>' : '') +
                    '</td>' +
                    '<td style="vertical-align:middle">' + statusBadge(po.status) + '</td>' +
                    '<td style="vertical-align:middle;min-width:120px">' + renderProgress(po.total_ordered, po.total_received) + '</td>' +
                    '<td style="vertical-align:middle;text-align:center">' +
                        '<a href="<?= site_url('purchase-order') ?>/' + po.po_id + '" class="btn btn-sm ' + btnClass + '">' +
                        '<i class="fa fa-inbox"></i> Terima</a>' +
                    '</td>' +
                    '</tr>';
            }).join('');

            var $panel = $(
                '<div class="po-panel' + (i === 0 ? '' : ' hidden') + '" id="panel-' + i + '">' +
                '<div class="box box-warning box-solid">' +
                '<div class="box-header with-border">' +
                '<h3 class="box-title"><i class="fa fa-truck"></i> ' + $('<s>').text(sup.nama_supplier).html() +
                ' <small class="text-muted" style="font-size:12px">&nbsp;· ' + sup.pos.length + ' PO menunggu</small></h3>' +
                '</div>' +
                '<div class="box-body no-padding" style="max-height:calc(100vh - 340px);overflow-y:auto">' +
                '<table class="table table-hover table-condensed" style="margin:0">' +
                '<thead><tr>' +
                '<th width="36">No</th>' +
                '<th>No. PO</th>' +
                '<th width="140">Tgl PO / Exp. Tiba</th>' +
                '<th width="160">Status</th>' +
                '<th width="140">Progress Terima</th>' +
                '<th width="90" class="text-center">Aksi</th>' +
                '</tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
                '</table></div></div></div>'
            );
            $area.append($panel);
        });

        $('#main-layout').show();

        // ── Klik capsule ─────────────────────────────────────────
        $nav.on('click', '.sup-chip', function (e) {
            e.preventDefault();
            var sid = $(this).data('sid');
            $('.sup-chip').removeClass('btn-success').addClass('btn-default');
            $(this).removeClass('btn-default').addClass('btn-success');
            $('.po-panel').addClass('hidden');
            $('#panel-' + sid).removeClass('hidden');
        });

        // ── Search ───────────────────────────────────────────────
        $('#sup-search').on('input', function () {
            var kw = $(this).val().toLowerCase().trim();
            var vis = 0;
            $('.sup-chip').each(function () {
                var match = !kw || $(this).data('name').indexOf(kw) !== -1;
                $(this).toggle(match);
                if (match) vis++;
            });
            $('#sup-count-label').text(vis + ' supplier');
        });

    }, 'json');

});
</script>
