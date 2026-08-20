<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php $level = (int) $this->fungsi->user_login()->level; ?>

<?php if ($level == 1): ?>

<section class="content" style="padding-top:0">

<!-- Row 1: 5 existing summary cards -->
<div class="row">
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-money"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Penjualan Hari Ini</span>
                <span class="info-box-number" style="font-size:16px"><?= indo_currency($sum_today) ?></span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <div class="info-box bg-blue">
            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Penjualan Bulan Ini</span>
                <span class="info-box-number" style="font-size:16px"><?= indo_currency($sum_month) ?></span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-shopping-cart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Transaksi Hari Ini</span>
                <span class="info-box-number"><?= $count_today ?></span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-xs-6">
        <a href="<?= site_url('purchase-order/receiving') ?>" style="text-decoration:none">
        <div class="info-box bg-orange">
            <span class="info-box-icon"><i class="fa fa-file-text-o"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">PO Aktif (Terkirim/Sebagian)</span>
                <span class="info-box-number"><?= $po_aktif ?></span>
            </div>
        </div>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6 col-xs-6">
        <a href="<?= site_url('po-cart') ?>" style="text-decoration:none">
        <div class="info-box" style="background:#8e44ad;color:#fff">
            <span class="info-box-icon"><i class="fa fa-shopping-basket"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Item di Keranjang PO</span>
                <span class="info-box-number"><?= $cart_items ?></span>
            </div>
        </div>
        </a>
    </div>
</div>

<!-- Row 2: 2 extra SuperAdmin comparison cards -->
<div class="row">
    <div class="col-sm-4 col-xs-6">
        <div class="info-box" style="background:#16a085;color:#fff">
            <span class="info-box-icon"><i class="fa fa-history"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Penjualan Bulan Lalu</span>
                <span class="info-box-number" id="sa-last-month" style="font-size:16px">—</span>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-xs-6">
        <div class="info-box" style="background:#2c3e50;color:#fff">
            <span class="info-box-icon"><i class="fa fa-user"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Kasir Aktif Hari Ini</span>
                <span class="info-box-number" id="sa-kasir-active">—</span>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Chart penjualan + Top 5 Terlaris -->
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bar-chart"></i> Penjualan 30 Hari Terakhir</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body">
                <canvas id="chart-sales" height="90"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-trophy"></i> Top 5 Terlaris Bulan Ini</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body" id="top-items-body" style="min-height:180px">
                <div class="text-center text-muted" style="padding:40px 0"><i class="fa fa-spinner fa-spin"></i> Memuat...</div>
            </div>
        </div>
    </div>
</div>

<!-- Row 3.5: Top 5 Pelanggan Bulan Ini -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-star"></i> Top 5 Pelanggan Bulan Ini</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body" id="top-customers-body" style="min-height:90px">
                <div class="text-center text-muted" style="padding:20px 0"><i class="fa fa-spinner fa-spin"></i> Memuat...</div>
            </div>
        </div>
    </div>
</div>

<!-- Row 4: Transaksi Terbaru + PO Aktif -->
<div class="row">
    <div class="col-md-7">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clock-o"></i> Transaksi Terbaru</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body table-responsive" style="padding:0">
                <table class="table table-condensed table-hover" style="margin:0">
                    <thead style="background:#f4f6f9;font-size:11px;text-transform:uppercase">
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th class="text-right">Total</th>
                            <th>Kasir</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="recent-sales-body">
                        <tr><td colspan="5" class="text-center text-muted" style="padding:20px"><i class="fa fa-spinner fa-spin"></i> Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-inbox"></i> PO Aktif</h3>
                <div class="box-tools pull-right">
                    <a href="<?= site_url('purchase-order/receiving') ?>" class="btn btn-box-tool" title="Lihat semua"><i class="fa fa-external-link"></i></a>
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body" id="po-active-body" style="min-height:140px">
                <div class="text-center text-muted" style="padding:40px 0"><i class="fa fa-spinner fa-spin"></i> Memuat...</div>
            </div>
        </div>
    </div>
</div>

</section>

<script src="<?= base_url('assets/bower_components/chart.js/Chart.min.js') ?>"></script>
<script>
$(function () {
    function fmtRp(v) {
        return 'Rp ' + parseInt(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Extra SuperAdmin cards
    $.getJSON('<?= site_url('dashboard/get_superadmin_extra_json') ?>', function (res) {
        $('#sa-last-month').text(fmtRp(res.sum_last_month));
        $('#sa-kasir-active').text(res.kasir_active_today + ' orang');
    });

    // Bar chart penjualan 30 hari
    $.getJSON('<?= site_url('dashboard/get_sales_chart_json') ?>', function (res) {
        var ctx = document.getElementById('chart-sales').getContext('2d');
        new Chart(ctx).Bar({
            labels: res.labels,
            datasets: [{
                fillColor:       'rgba(60,141,188,0.5)',
                strokeColor:     'rgba(60,141,188,0.8)',
                highlightFill:   'rgba(60,141,188,0.75)',
                highlightStroke: 'rgba(60,141,188,1)',
                data: res.values
            }]
        }, {
            responsive: true,
            scaleBeginAtZero: true,
            scaleLabel: function (v) {
                var n = parseInt(v.value);
                if (n >= 1000000) return (n / 1000000).toFixed(1) + 'jt';
                if (n >= 1000)    return (n / 1000).toFixed(0) + 'rb';
                return n;
            },
            tooltipTemplate: function (d) {
                return d.label + ': Rp ' + parseInt(d.value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            },
            showTooltips: true
        });
    });

    // Top 5 terlaris
    $.getJSON('<?= site_url('dashboard/get_top_items_json') ?>', function (res) {
        var html = '';
        if (!res.length) {
            html = '<p class="text-muted text-center" style="padding:20px 0">Belum ada data penjualan bulan ini.</p>';
        } else {
            html = '<ol style="padding-left:20px;margin:0">';
            res.forEach(function (r, i) {
                var colors = ['#00a65a','#3c8dbc','#f39c12','#dd4b39','#9b59b6'];
                html += '<li style="padding:6px 0;border-bottom:1px solid #f4f4f4">'
                      + '<span style="font-size:13px;font-weight:600">' + r.nama_item + '</span><br>'
                      + '<span class="badge" style="background:' + (colors[i]||'#777') + ';margin-top:2px">'
                      + r.total_qty + ' pcs</span>'
                      + '<span class="pull-right text-muted" style="font-size:11px;margin-top:3px">'
                      + fmtRp(r.total_nilai) + '</span>'
                      + '</li>';
            });
            html += '</ol>';
        }
        $('#top-items-body').html(html);
    });

    // Top 5 pelanggan (belanja terbanyak) bulan ini
    $.getJSON('<?= site_url('dashboard/get_top_customers_json') ?>', function (res) {
        var html = '';
        if (!res.length) {
            html = '<p class="text-muted text-center" style="padding:10px 0">Belum ada transaksi customer terdaftar bulan ini.</p>';
        } else {
            var medals = ['#f39c12','#95a5a6','#cd7f32','#3c8dbc','#3c8dbc'];
            html = '<div style="display:flex;gap:12px;flex-wrap:wrap">';
            res.forEach(function (r, i) {
                html += '<div style="flex:1;min-width:170px;padding:12px;border:1px solid #f4f4f4;border-radius:6px;text-align:center">'
                      + '<div style="width:30px;height:30px;border-radius:50%;background:' + medals[i] + ';color:#fff;'
                      + 'display:flex;align-items:center;justify-content:center;margin:0 auto 8px;font-weight:700;font-size:13px">'
                      + (i + 1) + '</div>'
                      + '<div style="font-size:13px;font-weight:600;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">'
                      + r.nama_customer + '</div>'
                      + '<div style="font-size:14px;color:#00a65a;font-weight:700">' + fmtRp(r.total_belanja) + '</div>'
                      + '<div style="font-size:11px;color:#888">' + r.total_transaksi + ' transaksi</div>'
                      + '</div>';
            });
            html += '</div>';
        }
        $('#top-customers-body').html(html);
    });

    // Transaksi terbaru
    $.getJSON('<?= site_url('dashboard/get_recent_sales_json') ?>', function (res) {
        var html = '';
        if (!res.length) {
            html = '<tr><td colspan="5" class="text-center text-muted" style="padding:20px">Belum ada transaksi.</td></tr>';
        } else {
            res.forEach(function (r) {
                var d = new Date(r.date);
                var tgl = d.getDate() + '/' + (d.getMonth()+1) + ' ' + d.toTimeString().slice(0,5);
                html += '<tr>'
                      + '<td><span style="font-size:11px;font-family:monospace">' + r.invoice + '</span></td>'
                      + '<td>' + (r.customer_name || '<span class="text-muted">—</span>') + '</td>'
                      + '<td class="text-right"><strong>' + fmtRp(r.final_price) + '</strong></td>'
                      + '<td>' + r.kasir + '</td>'
                      + '<td style="font-size:11px;color:#888;white-space:nowrap">' + tgl + '</td>'
                      + '</tr>';
            });
        }
        $('#recent-sales-body').html(html);
    });

    // PO Aktif
    $.getJSON('<?= site_url('dashboard/get_po_active_json') ?>', function (res) {
        var statusMap = { sent: ['label-info','Terkirim'], partial: ['label-warning','Sebagian'] };
        var html = '';
        if (!res.length) {
            html = '<p class="text-muted text-center" style="padding:20px 0">Tidak ada PO aktif.</p>';
        } else {
            res.forEach(function (r) {
                var pct   = r.total_ordered > 0 ? Math.round(r.total_received / r.total_ordered * 100) : 0;
                var color = pct >= 100 ? '#00a65a' : (pct > 0 ? '#f39c12' : '#ddd');
                var sm    = statusMap[r.status] || ['label-default', r.status];
                html += '<div style="padding:8px 0;border-bottom:1px solid #f4f4f4">'
                      + '<a href="<?= site_url('purchase-order/') ?>' + r.po_id + '" style="font-weight:600;font-size:13px">'
                      + r.po_number + '</a>'
                      + ' <span class="label ' + sm[0] + '" style="font-size:10px">' + sm[1] + '</span>'
                      + '<span class="pull-right text-muted" style="font-size:11px">' + r.nama_supplier + '</span><br>'
                      + '<div style="height:5px;background:#e9ecef;border-radius:3px;margin-top:5px">'
                      + '<div style="width:' + pct + '%;height:5px;background:' + color + ';border-radius:3px"></div>'
                      + '</div>'
                      + '<small class="text-muted">' + r.total_received + '/' + r.total_ordered + ' diterima (' + pct + '%)</small>'
                      + '</div>';
            });
        }
        $('#po-active-body').html(html);
    });
});
</script>

<?php elseif ($level == 2): ?>

<section class="content" style="padding-top:0">

<!-- Row 1: 5 operational cards for Admin -->
<div class="row">
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <a href="<?= site_url('purchase-order/receiving') ?>" style="text-decoration:none">
        <div class="info-box bg-orange">
            <span class="info-box-icon"><i class="fa fa-truck"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">PO Menunggu Terima</span>
                <span class="info-box-number" id="adm-po-pending">—</span>
            </div>
        </div>
        </a>
    </div>
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <div class="info-box bg-red">
            <span class="info-box-icon"><i class="fa fa-times-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Stok Habis</span>
                <span class="info-box-number" id="adm-stock-0">—</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fa fa-warning"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Stok Menipis</span>
                <span class="info-box-number" id="adm-stock-low">—</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-xs-6">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Penerimaan Hari Ini</span>
                <span class="info-box-number" id="adm-gr-today">—</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-xs-6">
        <a href="<?= site_url('item_pending') ?>" style="text-decoration:none">
        <div class="info-box" style="background:#8e44ad;color:#fff">
            <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Barang Pending (Perlu Approval)</span>
                <span class="info-box-number" id="adm-pending-items">—</span>
            </div>
        </div>
        </a>
    </div>
</div>

<!-- Row 2: PO Aktif + GR Terbaru -->
<div class="row">
    <div class="col-md-6">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-inbox"></i> PO Aktif</h3>
                <div class="box-tools pull-right">
                    <a href="<?= site_url('purchase-order/receiving') ?>" class="btn btn-box-tool" title="Lihat semua"><i class="fa fa-external-link"></i></a>
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body" id="adm-po-active-body" style="min-height:140px">
                <div class="text-center text-muted" style="padding:40px 0"><i class="fa fa-spinner fa-spin"></i> Memuat...</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-check-square-o"></i> Penerimaan Terbaru</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body table-responsive" style="padding:0">
                <table class="table table-condensed table-hover" style="margin:0">
                    <thead style="background:#f4f6f9;font-size:11px;text-transform:uppercase">
                        <tr>
                            <th>PO</th>
                            <th>Supplier</th>
                            <th>Tgl Terima</th>
                            <th class="text-center">Qty</th>
                            <th>Oleh</th>
                        </tr>
                    </thead>
                    <tbody id="adm-gr-body">
                        <tr><td colspan="5" class="text-center text-muted" style="padding:20px"><i class="fa fa-spinner fa-spin"></i> Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Barang Pending Belum Diapprove -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-exclamation-circle" style="color:#8e44ad"></i> Barang Pending — Belum Diapprove</h3>
                <div class="box-tools pull-right">
                    <a href="<?= site_url('item_pending') ?>" class="btn btn-xs btn-default">Lihat Semua</a>
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body table-responsive" style="padding:0">
                <table class="table table-condensed table-hover" style="margin:0">
                    <thead style="background:#f4f6f9;font-size:11px;text-transform:uppercase">
                        <tr>
                            <th>Nama Barang</th>
                            <th>Supplier</th>
                            <th class="text-center">Qty</th>
                            <th>Diajukan Oleh</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="adm-pending-body">
                        <tr><td colspan="5" class="text-center text-muted" style="padding:20px"><i class="fa fa-spinner fa-spin"></i> Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</section>

<script>
$(function () {
    // Operational cards
    $.getJSON('<?= site_url('dashboard/get_admin_stats_json') ?>', function (res) {
        $('#adm-po-pending').text(res.po_pending);
        $('#adm-stock-0').text(res.stock_0);
        $('#adm-stock-low').text(res.stock_low);
        $('#adm-gr-today').text(res.gr_today);
        $('#adm-pending-items').text(res.pending_items);
    });

    // PO Aktif
    $.getJSON('<?= site_url('dashboard/get_po_active_json') ?>', function (res) {
        var statusMap = { sent: ['label-info','Terkirim'], partial: ['label-warning','Sebagian'] };
        var html = '';
        if (!res.length) {
            html = '<p class="text-muted text-center" style="padding:20px 0">Tidak ada PO aktif.</p>';
        } else {
            res.forEach(function (r) {
                var pct   = r.total_ordered > 0 ? Math.round(r.total_received / r.total_ordered * 100) : 0;
                var color = pct >= 100 ? '#00a65a' : (pct > 0 ? '#f39c12' : '#ddd');
                var sm    = statusMap[r.status] || ['label-default', r.status];
                html += '<div style="padding:8px 0;border-bottom:1px solid #f4f4f4">'
                      + '<a href="<?= site_url('purchase-order/') ?>' + r.po_id + '" style="font-weight:600;font-size:13px">'
                      + r.po_number + '</a>'
                      + ' <span class="label ' + sm[0] + '" style="font-size:10px">' + sm[1] + '</span>'
                      + '<span class="pull-right text-muted" style="font-size:11px">' + r.nama_supplier + '</span><br>'
                      + '<div style="height:5px;background:#e9ecef;border-radius:3px;margin-top:5px">'
                      + '<div style="width:' + pct + '%;height:5px;background:' + color + ';border-radius:3px"></div>'
                      + '</div>'
                      + '<small class="text-muted">' + r.total_received + '/' + r.total_ordered + ' diterima (' + pct + '%)</small>'
                      + '</div>';
            });
        }
        $('#adm-po-active-body').html(html);
    });

    // GR Terbaru
    $.getJSON('<?= site_url('dashboard/get_admin_gr_recent_json') ?>', function (res) {
        var html = '';
        if (!res.length) {
            html = '<tr><td colspan="5" class="text-center text-muted" style="padding:20px">Belum ada penerimaan.</td></tr>';
        } else {
            res.forEach(function (r) {
                var tgl = r.receive_date ? r.receive_date.substring(0, 10) : '—';
                html += '<tr>'
                      + '<td><a href="<?= site_url('purchase-order/') ?>' + r.po_id + '" style="font-size:11px;font-family:monospace">' + r.po_number + '</a></td>'
                      + '<td style="font-size:12px">' + r.nama_supplier + '</td>'
                      + '<td style="font-size:11px;color:#888;white-space:nowrap">' + tgl + '</td>'
                      + '<td class="text-center"><span class="badge bg-green">' + (r.total_qty || 0) + '</span></td>'
                      + '<td style="font-size:11px">' + (r.received_by || '—') + '</td>'
                      + '</tr>';
            });
        }
        $('#adm-gr-body').html(html);
    });

    // Barang Pending belum diapprove
    $.getJSON('<?= site_url('dashboard/get_admin_pending_items_json') ?>', function (res) {
        var html = '';
        if (!res.length) {
            html = '<tr><td colspan="5" class="text-center text-success" style="padding:20px"><i class="fa fa-check-circle"></i> Tidak ada barang pending.</td></tr>';
        } else {
            res.forEach(function (r) {
                var tgl = r.created_at ? r.created_at.substring(0, 16).replace('T', ' ') : '—';
                html += '<tr>'
                      + '<td style="font-size:13px;font-weight:600">' + r.nama_item + '</td>'
                      + '<td style="font-size:12px">' + (r.nama_supplier || '—') + '</td>'
                      + '<td class="text-center">' + r.qty + '</td>'
                      + '<td style="font-size:12px">' + (r.submitted_by || '—') + '</td>'
                      + '<td style="font-size:11px;color:#888;white-space:nowrap">' + tgl + '</td>'
                      + '</tr>';
            });
        }
        $('#adm-pending-body').html(html);
    });
});
</script>

<?php elseif ($level == 3):
    $kasir_id   = (int) $this->session->userdata('userid');
    $kasir_nama = $this->fungsi->user_login()->nama;
?>

<section class="content" style="padding-top:0">

<!-- Row 1: 4 summary cards for Kasir -->
<div class="row">
    <div class="col-sm-3 col-xs-6">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-money"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Penjualan Saya Hari Ini</span>
                <span class="info-box-number" id="ks-today" style="font-size:16px">—</span>
            </div>
        </div>
    </div>
    <div class="col-sm-3 col-xs-6">
        <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-shopping-cart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Transaksi Hari Ini</span>
                <span class="info-box-number" id="ks-count">—</span>
            </div>
        </div>
    </div>
    <div class="col-sm-3 col-xs-6">
        <div class="info-box bg-blue">
            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Penjualan Bulan Ini</span>
                <span class="info-box-number" id="ks-month" style="font-size:16px">—</span>
            </div>
        </div>
    </div>
    <div class="col-sm-3 col-xs-6">
        <div class="info-box" style="background:#16a085;color:#fff">
            <span class="info-box-icon"><i class="fa fa-line-chart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Avg/Hari (7 Hari)</span>
                <span class="info-box-number" id="ks-avg" style="font-size:16px">—</span>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Transaksi Terbaru + Stok Alert -->
<div class="row">
    <div class="col-md-7">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clock-o"></i> Transaksi Terbaru Saya</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body table-responsive" style="padding:0">
                <table class="table table-condensed table-hover" style="margin:0">
                    <thead style="background:#f4f6f9;font-size:11px;text-transform:uppercase">
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th class="text-right">Total</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="ks-recent-body">
                        <tr><td colspan="4" class="text-center text-muted" style="padding:20px"><i class="fa fa-spinner fa-spin"></i> Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Stok Habis / Menipis</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body" id="ks-stock-body" style="min-height:120px;max-height:300px;overflow-y:auto">
                <div class="text-center text-muted" style="padding:30px 0"><i class="fa fa-spinner fa-spin"></i> Memuat...</div>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Top 3 Item Hari Ini -->
<div class="row">
    <div class="col-md-5 col-sm-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-star"></i> Top 3 Item Terjual Hari Ini</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body" id="ks-top-body" style="min-height:80px">
                <div class="text-center text-muted" style="padding:20px 0"><i class="fa fa-spinner fa-spin"></i> Memuat...</div>
            </div>
        </div>
    </div>
</div>

</section>

<script>
$(function () {
    function fmtRp(v) {
        return 'Rp ' + parseInt(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    $.getJSON('<?= site_url('dashboard/get_kasir_stats_json') ?>', function (res) {
        $('#ks-today').text(fmtRp(res.sum_today));
        $('#ks-count').text(res.count_today);
        $('#ks-month').text(fmtRp(res.sum_month));
    });

    $.getJSON('<?= site_url('dashboard/get_kasir_avg_json') ?>', function (res) {
        $('#ks-avg').text(fmtRp(res.avg));
    });

    $.getJSON('<?= site_url('dashboard/get_kasir_recent_json') ?>', function (res) {
        var html = '';
        if (!res.length) {
            html = '<tr><td colspan="4" class="text-center text-muted" style="padding:20px">Belum ada transaksi hari ini.</td></tr>';
        } else {
            res.forEach(function (r) {
                var d   = new Date(r.date);
                var tgl = d.getDate() + '/' + (d.getMonth() + 1) + ' ' + d.toTimeString().slice(0, 5);
                html += '<tr>'
                      + '<td style="font-size:11px;font-family:monospace">' + r.invoice + '</td>'
                      + '<td>' + (r.customer_name || '<span class="text-muted">—</span>') + '</td>'
                      + '<td class="text-right"><strong>' + fmtRp(r.final_price) + '</strong></td>'
                      + '<td style="font-size:11px;color:#888;white-space:nowrap">' + tgl + '</td>'
                      + '</tr>';
            });
        }
        $('#ks-recent-body').html(html);
    });

    $.getJSON('<?= site_url('dashboard/get_kasir_stock_alert_json') ?>', function (res) {
        var html = '';
        if (!res.length) {
            html = '<p class="text-success text-center" style="padding:20px 0"><i class="fa fa-check-circle"></i> Semua stok aman.</p>';
        } else {
            res.forEach(function (r) {
                var s     = parseInt(r.stock);
                var color = s <= 0 ? '#dd4b39' : '#f39c12';
                var label = s <= 0 ? 'HABIS' : 'Sisa ' + s;
                html += '<div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f4f4f4">'
                      + '<span style="font-size:12px">' + r.nama_item + '</span>'
                      + '<span style="background:' + color + ';color:#fff;border-radius:10px;padding:2px 8px;font-size:11px;white-space:nowrap;margin-left:8px">' + label + '</span>'
                      + '</div>';
            });
        }
        $('#ks-stock-body').html(html);
    });

    $.getJSON('<?= site_url('dashboard/get_kasir_top_items_json') ?>', function (res) {
        var html = '';
        if (!res.length) {
            html = '<p class="text-muted text-center" style="padding:10px 0">Belum ada penjualan hari ini.</p>';
        } else {
            var medals = ['<i class="fa fa-trophy" style="color:#f39c12"></i>', '<i class="fa fa-trophy" style="color:#aaa"></i>', '<i class="fa fa-trophy" style="color:#cd7f32"></i>'];
            res.forEach(function (r, i) {
                html += '<div style="display:flex;align-items:center;padding:8px 0;border-bottom:1px solid #f4f4f4">'
                      + '<span style="margin-right:10px;font-size:16px">' + (medals[i] || '') + '</span>'
                      + '<span style="font-size:13px;font-weight:600;flex:1">' + r.nama_item + '</span>'
                      + '<span class="badge bg-blue">' + r.qty + ' pcs</span>'
                      + '</div>';
            });
        }
        $('#ks-top-body').html(html);
    });
});
</script>

<?php elseif ($level == 4): ?>

<section class="content" style="padding-top:0">

<!-- Row 1: 4 Barang Pending summary cards -->
<div class="row">
    <div class="col-sm-3 col-xs-6">
        <a href="<?= site_url('item_pending') ?>" style="text-decoration:none">
        <div class="info-box bg-blue">
            <span class="info-box-icon"><i class="fa fa-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Pengajuan Saya</span>
                <span class="info-box-number" id="gd-my-total">—</span>
            </div>
        </div>
        </a>
    </div>
    <div class="col-sm-3 col-xs-6">
        <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fa fa-hourglass-half"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Menunggu Approval</span>
                <span class="info-box-number" id="gd-pending">—</span>
            </div>
        </div>
    </div>
    <div class="col-sm-3 col-xs-6">
        <div class="info-box bg-orange">
            <span class="info-box-icon"><i class="fa fa-tag"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Siap Ditempel</span>
                <span class="info-box-number" id="gd-ready">—</span>
            </div>
        </div>
    </div>
    <div class="col-sm-3 col-xs-6">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-size:11px">Ditempel Hari Ini</span>
                <span class="info-box-number" id="gd-attached">—</span>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Siap Ditempel + Pengajuan Terbaru Saya -->
<div class="row">
    <div class="col-md-7">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-tag"></i> Siap Ditempel <small class="text-muted">(barcode sudah dicetak)</small></h3>
                <div class="box-tools pull-right">
                    <a href="<?= site_url('item_pending') ?>" class="btn btn-xs btn-default">Lihat Semua</a>
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body table-responsive" style="padding:0">
                <table class="table table-condensed table-hover" style="margin:0">
                    <thead style="background:#f4f6f9;font-size:11px;text-transform:uppercase">
                        <tr>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th class="text-center">Qty</th>
                            <th>Tgl Print</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="gd-ready-body">
                        <tr><td colspan="6" class="text-center text-muted" style="padding:20px"><i class="fa fa-spinner fa-spin"></i> Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clock-o"></i> Pengajuan Terbaru Saya</h3>
                <div class="box-tools pull-right">
                    <a href="<?= site_url('item_pending') ?>" class="btn btn-xs btn-default">Lihat Semua</a>
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body" id="gd-recent-body" style="min-height:120px">
                <div class="text-center text-muted" style="padding:30px 0"><i class="fa fa-spinner fa-spin"></i> Memuat...</div>
            </div>
        </div>
    </div>
</div>

</section>

<script>
$(function () {
    var statusBadge = {
        pending:  '<span class="badge bg-yellow">Pending</span>',
        approved: '<span class="badge bg-blue">Approved</span>',
        printed:  '<span class="badge" style="background:#8e44ad">Sudah Print</span>',
        attached: '<span class="badge bg-green">Ditempel</span>',
        rejected: '<span class="badge bg-red">Ditolak</span>'
    };

    $.getJSON('<?= site_url('dashboard/get_gudang_stats_json') ?>', function (res) {
        $('#gd-my-total').text(res.my_total);
        $('#gd-pending').text(res.pending_count);
        $('#gd-ready').text(res.ready_count);
        $('#gd-attached').text(res.attached_today);
    });

    $.getJSON('<?= site_url('dashboard/get_gudang_ready_items_json') ?>', function (res) {
        var html = '';
        if (!res.length) {
            html = '<tr><td colspan="6" class="text-center text-success" style="padding:20px"><i class="fa fa-check-circle"></i> Tidak ada item yang perlu ditempel.</td></tr>';
        } else {
            res.forEach(function (r) {
                var tgl = r.printed_at ? r.printed_at.substring(0, 10) : '—';
                html += '<tr>'
                      + '<td style="font-size:13px;font-weight:600">' + r.nama_item + '</td>'
                      + '<td style="font-size:11px">' + (r.nama_category || '—') + '</td>'
                      + '<td style="font-size:11px">' + (r.nama_unit || '—') + '</td>'
                      + '<td class="text-center">' + r.qty + '</td>'
                      + '<td style="font-size:11px;color:#888;white-space:nowrap">' + tgl + '</td>'
                      + '<td><a href="<?= site_url('item_pending') ?>" class="btn btn-xs btn-warning"><i class="fa fa-tag"></i> Tempel</a></td>'
                      + '</tr>';
            });
        }
        $('#gd-ready-body').html(html);
    });

    $.getJSON('<?= site_url('dashboard/get_gudang_recent_submissions_json') ?>', function (res) {
        var html = '';
        if (!res.length) {
            html = '<p class="text-muted text-center" style="padding:20px 0">Belum ada pengajuan.</p>';
        } else {
            res.forEach(function (r) {
                var tgl = r.created_at ? r.created_at.substring(0, 10) : '—';
                html += '<div style="padding:8px 0;border-bottom:1px solid #f4f4f4">'
                      + '<span style="font-size:13px;font-weight:600">' + r.nama_item + '</span>'
                      + ' ' + (statusBadge[r.status] || r.status) + '<br>'
                      + '<small class="text-muted">'
                      + (r.nama_supplier || '—') + ' &mdash; ' + tgl
                      + '</small>'
                      + '</div>';
            });
        }
        $('#gd-recent-body').html(html);
    });
});
</script>

<?php endif; ?>
