<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-line-chart text-primary"></i> Laporan Hutang</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li class="active">Kartu Hutang</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Kartu Hutang per Supplier</h3>
            <div class="box-tools pull-right">
                <a href="<?= site_url('report-ap/daftar') ?>" class="btn btn-info btn-sm">
                    <i class="fa fa-list"></i> Lihat Daftar Hutang
                </a>
                <a href="<?= site_url('report-ap/aging') ?>" class="btn btn-warning btn-sm">
                    <i class="fa fa-clock-o"></i> Lihat Aging Hutang
                </a>
            </div>
        </div>
        <div class="box-body">
            <form method="get" action="<?= site_url('report-ap') ?>" class="form-inline" style="margin-bottom:15px;">
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Supplier</label>
                    <select name="supplier_id" class="form-control select2" style="width:220px;">
                        <option value="">- Pilih Supplier -</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s->supplier_id ?>" <?= $supplier_id == $s->supplier_id ? 'selected' : '' ?>><?= $s->nama_supplier ?></option>
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
                <?php if ($supplier_id): ?>
                <a href="<?= site_url('report-ap/cetak-kartu/' . $supplier_id . '?from=' . $from . '&to=' . $to) ?>" target="_blank" class="btn btn-default">
                    <i class="fa fa-print"></i> Cetak PDF
                </a>
                <?php endif; ?>
            </form>

            <?php if ($supplier): ?>
                <h4>
                    <?= $supplier->nama_supplier ?>
                    <small>Termin Bayar: <?= (int) $supplier->payment_term_days ?> hari |
                        Saldo Hutang Saat Ini: <strong>Rp <?= number_format($supplier->ap_balance, 0, ',', '.') ?></strong></small>
                </h4>
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary">
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Ref</th>
                            <th>Keterangan</th>
                            <th class="text-right">Debit</th>
                            <th class="text-right">Kredit</th>
                            <th class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                        <tr><td colspan="6" class="text-center">Tidak ada transaksi pada periode ini</td></tr>
                        <?php else: foreach ($rows as $r): ?>
                        <tr>
                            <td><?= tgl_finance($r->trx_date) ?></td>
                            <td><?= $r->ref_no ?></td>
                            <td><?= $r->description ?></td>
                            <td class="text-right"><?= $r->debit > 0 ? number_format($r->debit, 0, ',', '.') : '' ?></td>
                            <td class="text-right"><?= $r->kredit > 0 ? number_format($r->kredit, 0, ',', '.') : '' ?></td>
                            <td class="text-right"><?= number_format($r->balance, 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">Pilih supplier untuk melihat kartu hutang.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('.select2').select2();
});
</script>
