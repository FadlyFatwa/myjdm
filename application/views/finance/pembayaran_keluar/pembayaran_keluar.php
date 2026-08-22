<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-money text-primary"></i> Pembayaran Keluar</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li class="active">Pembayaran Keluar</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Riwayat Pembayaran Nota Invoice Penerimaan (Cash &amp; Kredit)</h3>
        </div>
        <div class="box-body">
            <?php
            $qs = http_build_query(['from' => $from, 'to' => $to, 'supplier_id' => $supplier_id, 'payment_method' => $payment_method, 'status' => $status]);
            ?>
            <form method="get" action="<?= site_url('pembayaran-keluar') ?>" class="form-inline" style="margin-bottom:15px;">
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
                    <label style="margin-right:5px;">Cara Bayar</label>
                    <select name="payment_method" class="form-control">
                        <option value="" <?= $payment_method === '' ? 'selected' : '' ?>>Semua</option>
                        <option value="cash" <?= $payment_method === 'cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="bank" <?= $payment_method === 'bank' ? 'selected' : '' ?>>Bank</option>
                    </select>
                </div>
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Status</label>
                    <select name="status" class="form-control">
                        <option value="" <?= $status === '' ? 'selected' : '' ?>>Semua</option>
                        <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="void" <?= $status === 'void' ? 'selected' : '' ?>>Void</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button>
                <a href="<?= site_url('pembayaran-keluar/cetak?' . $qs) ?>" target="_blank" class="btn btn-default">
                    <i class="fa fa-print"></i> Cetak PDF
                </a>
                <?php if (in_array($this->session->userdata('level'), [1, 2])): ?>
                <a href="<?= site_url('pembayaran-keluar/export?' . $qs) ?>" class="btn btn-success">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </a>
                <?php endif; ?>
            </form>

            <p>Total Pembayaran (aktif): <strong>Rp <?= number_format($total, 0, ',', '.') ?></strong></p>

            <table class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th>No</th>
                        <th>No. Bukti</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Referensi</th>
                        <th>Supplier</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-center">Cara Bayar</th>
                        <th>Dicatat Oleh</th>
                        <th>Keterangan</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr><td colspan="11" class="text-center">Tidak ada pembayaran keluar pada periode ini</td></tr>
                    <?php else: $no = 1; foreach ($rows as $r): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $r->payment_no ?></td>
                        <td><?= tgl_finance($r->payment_date) ?></td>
                        <td>
                            <span class="label label-<?= $r->jenis === 'kontra_bon' ? 'info' : 'default' ?>">
                                <?= $r->jenis === 'kontra_bon' ? 'Kontra Bon' : 'Invoice' ?>
                            </span>
                        </td>
                        <td><?= $r->reference_no ?></td>
                        <td><?= $r->nama_supplier ?></td>
                        <td class="text-right"><?= number_format($r->amount, 0, ',', '.') ?></td>
                        <td class="text-center">
                            <span class="label label-<?= $r->payment_method === 'cash' ? 'success' : 'default' ?>">
                                <?= $r->payment_method === 'cash' ? 'Cash' : 'Bank' ?>
                            </span>
                        </td>
                        <td><?= $r->paid_by_name ?></td>
                        <td><?= $r->notes ?: '-' ?></td>
                        <td class="text-center">
                            <?php if ($r->is_void): ?>
                            <span class="label label-default">Void</span>
                            <?php else: ?>
                            <span class="label label-success">Aktif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="bg-gray">
                        <th colspan="6" class="text-right">Total (aktif)</th>
                        <th class="text-right"><?= number_format($total, 0, ',', '.') ?></th>
                        <th colspan="4"></th>
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
