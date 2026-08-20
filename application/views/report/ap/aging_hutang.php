<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-clock-o text-primary"></i> Aging Hutang</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li><a href="<?= site_url('report-ap') ?>">Laporan Hutang</a></li>
        <li class="active">Aging</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Aging Hutang</h3>
        </div>
        <div class="box-body">
            <form method="get" action="<?= site_url('report-ap/aging') ?>" class="form-inline" style="margin-bottom:15px;">
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Per Tanggal</label>
                    <input type="date" name="as_of" value="<?= $as_of ?>" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button>
                <a href="<?= site_url('report-ap/cetak-aging?as_of=' . $as_of) ?>" target="_blank" class="btn btn-default">
                    <i class="fa fa-print"></i> Cetak PDF
                </a>
                <?php if (in_array($this->session->userdata('level'), [1, 2])): ?>
                <a href="<?= site_url('report-ap/export-aging?as_of=' . $as_of) ?>" class="btn btn-success">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </a>
                <?php endif; ?>
            </form>

            <h4>Ringkasan per Supplier</h4>
            <table class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th>Supplier</th>
                        <th class="text-right">0-30 hari</th>
                        <th class="text-right">31-60 hari</th>
                        <th class="text-right">61-90 hari</th>
                        <th class="text-right">&gt;90 hari</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($summary)): ?>
                    <tr><td colspan="6" class="text-center">Tidak ada hutang outstanding</td></tr>
                    <?php else:
                    $grand = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '>90' => 0, 'total' => 0];
                    foreach ($summary as $s):
                        foreach ($grand as $k => $v) $grand[$k] += $s[$k];
                    ?>
                    <tr>
                        <td><?= $s['nama_supplier'] ?></td>
                        <td class="text-right"><?= number_format($s['0-30'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($s['31-60'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($s['61-90'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($s['>90'], 0, ',', '.') ?></td>
                        <td class="text-right"><strong><?= number_format($s['total'], 0, ',', '.') ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="bg-gray">
                        <th>Total</th>
                        <th class="text-right"><?= number_format($grand['0-30'], 0, ',', '.') ?></th>
                        <th class="text-right"><?= number_format($grand['31-60'], 0, ',', '.') ?></th>
                        <th class="text-right"><?= number_format($grand['61-90'], 0, ',', '.') ?></th>
                        <th class="text-right"><?= number_format($grand['>90'], 0, ',', '.') ?></th>
                        <th class="text-right"><?= number_format($grand['total'], 0, ',', '.') ?></th>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h4>Detail Invoice</h4>
            <table class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th>No. AP</th>
                        <th>No. Invoice Supplier</th>
                        <th>Supplier</th>
                        <th>Tgl Invoice</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-center">Hari Terlambat</th>
                        <th class="text-right">Sisa</th>
                        <th class="text-center">Bucket</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr><td colspan="8" class="text-center">Tidak ada hutang outstanding</td></tr>
                    <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td><?= $r->ap_no ?></td>
                        <td><?= $r->supplier_invoice_no ?: '-' ?></td>
                        <td><?= $r->nama_supplier ?></td>
                        <td><?= tgl_finance($r->invoice_date) ?></td>
                        <td><?= tgl_finance($r->due_date) ?></td>
                        <td class="text-center"><?= (int) $r->days_overdue ?></td>
                        <td class="text-right"><?= number_format($r->outstanding_amount, 0, ',', '.') ?></td>
                        <td class="text-center">
                            <?php
                            $bc = ['0-30' => 'success', '31-60' => 'warning', '61-90' => 'orange', '>90' => 'danger'];
                            ?>
                            <span class="label label-<?= $bc[$r->bucket] ?? 'default' ?>"><?= $r->bucket ?></span>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
