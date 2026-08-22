<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-bar-chart text-primary"></i> Laporan Operasional</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li><a href="<?= site_url('beban') ?>">Operasional</a></li>
        <li class="active">Laporan</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Laporan Operasional per Periode</h3>
        </div>
        <div class="box-body">
            <?php
            $qs = http_build_query(['from' => $from, 'to' => $to, 'coa_id' => $coa_id]);
            ?>
            <form method="get" action="<?= site_url('report-beban') ?>" class="form-inline" style="margin-bottom:15px;">
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Kategori</label>
                    <select name="coa_id" class="form-control select2" style="width:200px;">
                        <option value="">- Semua Kategori -</option>
                        <?php foreach ($coa_list as $c): ?>
                            <option value="<?= $c->coa_id ?>" <?= $coa_id == $c->coa_id ? 'selected' : '' ?>><?= $c->coa_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Dari</label>
                    <input type="date" name="from" value="<?= $from ?>" class="form-control">
                </div>
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Sampai</label>
                    <input type="date" name="to" value="<?= $to ?>" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button>
                <a href="<?= site_url('report-beban/cetak?' . $qs) ?>" target="_blank" class="btn btn-default">
                    <i class="fa fa-print"></i> Cetak PDF
                </a>
                <a href="<?= site_url('report-beban/export?' . $qs) ?>" class="btn btn-success">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </a>
            </form>

            <h4>Ringkasan per Kategori</h4>
            <table class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th>Kategori</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($summary)): ?>
                    <tr><td colspan="2" class="text-center">Tidak ada beban pada periode ini</td></tr>
                    <?php else: foreach ($summary as $s): ?>
                    <tr>
                        <td><?= $s->coa_name ?></td>
                        <td class="text-right"><?= number_format($s->total, 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="bg-gray">
                        <th>Total</th>
                        <th class="text-right"><?= number_format($total, 0, ',', '.') ?></th>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h4>Detail Transaksi</h4>
            <div class="table-responsive">
            <table id="bebanTable" class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th>No</th>
                        <th>No. Beban</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-center">Cara Bayar</th>
                        <th>Keterangan</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($rows as $r): ?>
                    <tr<?= $r->is_void ? ' style="opacity:.55"' : '' ?>>
                        <td><?= $no++ ?></td>
                        <td><?= $r->expense_no ?></td>
                        <td><?= tgl_finance($r->expense_date) ?></td>
                        <td><?= $r->coa_name ?></td>
                        <td class="text-right"><?= number_format($r->amount, 0, ',', '.') ?></td>
                        <td class="text-center"><?= $r->payment_method === 'cash' ? 'Cash' : 'Transfer' ?></td>
                        <td><?= htmlspecialchars($r->description) ?></td>
                        <td class="text-center">
                            <?php if ($r->is_void): ?>
                            <span class="label label-default" title="<?= htmlspecialchars($r->void_reason ?? '') ?>">
                                <i class="fa fa-ban"></i> Dibatalkan
                            </span>
                            <?php else: ?>
                            <span class="label label-success"><i class="fa fa-check"></i> Aktif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('.select2').select2();

    var t = $('#bebanTable').DataTable({
        "columnDefs": [{
            "searchable": false,
            "orderable": false,
            "targets": 0
        }],
        "order": [[2, 'desc']],
        "pageLength": 25
    });

    t.on('order.dt search.dt', function() {
        t.column(0, {search:'applied', order:'applied'}).nodes().each(function(cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();
});
</script>
