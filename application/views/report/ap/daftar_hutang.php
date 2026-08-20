<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-list text-primary"></i> Daftar Hutang</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li><a href="<?= site_url('report-ap') ?>">Laporan Hutang</a></li>
        <li class="active">Daftar</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Daftar Hutang per Periode</h3>
        </div>
        <div class="box-body">
            <?php
            $qs = http_build_query(['from' => $from, 'to' => $to, 'status' => $status, 'supplier_id' => $supplier_id]);
            ?>
            <form method="get" action="<?= site_url('report-ap/daftar') ?>" class="form-inline" style="margin-bottom:15px;">
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Supplier</label>
                    <select name="supplier_id" class="form-control select2" style="width:200px;">
                        <option value="">- Semua Supplier -</option>
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
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Status</label>
                    <select name="status" class="form-control">
                        <option value="" <?= $status === '' ? 'selected' : '' ?>>Semua</option>
                        <option value="lunas" <?= $status === 'lunas' ? 'selected' : '' ?>>Lunas</option>
                        <option value="belum_lunas" <?= $status === 'belum_lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                        <option value="void" <?= $status === 'void' ? 'selected' : '' ?>>Void</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button>
                <a href="<?= site_url('report-ap/cetak-daftar?' . $qs) ?>" target="_blank" class="btn btn-default">
                    <i class="fa fa-print"></i> Cetak PDF
                </a>
                <?php if (in_array($this->session->userdata('level'), [1, 2])): ?>
                <a href="<?= site_url('report-ap/export-daftar?' . $qs) ?>" class="btn btn-success">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </a>
                <?php endif; ?>
            </form>

            <p>
                Total Jumlah: <strong>Rp <?= number_format($totals['amount'], 0, ',', '.') ?></strong> |
                Total Dibayar: <strong>Rp <?= number_format($totals['paid'], 0, ',', '.') ?></strong> |
                Total Sisa: <strong>Rp <?= number_format($totals['outstanding'], 0, ',', '.') ?></strong>
            </p>

            <table class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th>No</th>
                        <th>No. AP</th>
                        <th>No. Invoice Supplier</th>
                        <th>Supplier</th>
                        <th>Tgl Invoice</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-right">Dibayar</th>
                        <th class="text-right">Sisa</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $status_label = [
                        'outstanding' => ['Belum Lunas', 'warning'],
                        'partial'     => ['Belum Lunas', 'warning'],
                        'paid'        => ['Lunas', 'success'],
                        'void'        => ['Void', 'default'],
                    ];
                    ?>
                    <?php if (empty($rows)): ?>
                    <tr><td colspan="10" class="text-center">Tidak ada hutang pada periode ini</td></tr>
                    <?php else: $no = 1; foreach ($rows as $r): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $r->ap_no ?></td>
                        <td><?= $r->supplier_invoice_no ?: '-' ?></td>
                        <td><?= $r->nama_supplier ?></td>
                        <td><?= tgl_finance($r->invoice_date) ?></td>
                        <td><?= tgl_finance($r->due_date) ?></td>
                        <td class="text-right"><?= number_format($r->amount, 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($r->paid_amount, 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($r->outstanding_amount, 0, ',', '.') ?></td>
                        <td class="text-center">
                            <?php [$label, $class] = $status_label[$r->status] ?? [$r->status, 'default']; ?>
                            <span class="label label-<?= $class ?>"><?= $label ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="bg-gray">
                        <th colspan="6" class="text-right">Total</th>
                        <th class="text-right"><?= number_format($totals['amount'], 0, ',', '.') ?></th>
                        <th class="text-right"><?= number_format($totals['paid'], 0, ',', '.') ?></th>
                        <th class="text-right"><?= number_format($totals['outstanding'], 0, ',', '.') ?></th>
                        <th></th>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('.select2').select2();
});
</script>
