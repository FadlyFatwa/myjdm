<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-line-chart text-primary"></i> Laporan Piutang</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li class="active">Kartu Piutang</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Kartu Piutang per Customer</h3>
            <div class="box-tools pull-right">
                <a href="<?= site_url('report-ar/daftar') ?>" class="btn btn-info btn-sm">
                    <i class="fa fa-list"></i> Lihat Daftar Piutang
                </a>
                <a href="<?= site_url('report-ar/aging') ?>" class="btn btn-warning btn-sm">
                    <i class="fa fa-clock-o"></i> Lihat Aging Piutang
                </a>
            </div>
        </div>
        <div class="box-body">
            <form method="get" action="<?= site_url('report-ar') ?>" class="form-inline" style="margin-bottom:15px;">
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Customer</label>
                    <select name="customer_id" class="form-control select2" style="width:220px;">
                        <option value="">- Pilih Customer -</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c->customer_id ?>" <?= $customer_id == $c->customer_id ? 'selected' : '' ?>><?= $c->nama_customer ?></option>
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
                <?php if ($customer_id): ?>
                <a href="<?= site_url('report-ar/cetak-kartu/' . $customer_id . '?from=' . $from . '&to=' . $to) ?>" target="_blank" class="btn btn-default">
                    <i class="fa fa-print"></i> Cetak PDF
                </a>
                <?php endif; ?>
            </form>

            <?php if ($customer): ?>
                <h4>
                    <?= $customer->nama_customer ?>
                    <small>Limit Kredit: Rp <?= number_format($customer->credit_limit, 0, ',', '.') ?> |
                        Saldo Piutang Saat Ini: <strong>Rp <?= number_format($customer->ar_balance, 0, ',', '.') ?></strong></small>
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
                <p class="text-muted">Pilih customer untuk melihat kartu piutang.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('.select2').select2();
});
</script>
