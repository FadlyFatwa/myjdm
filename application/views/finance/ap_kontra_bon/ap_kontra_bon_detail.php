<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1>Detail Kontra Bon Hutang
        <small><?= $kb->kontra_bon_no ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li><a href="<?= site_url('ap-kontra-bon') ?>">Kontra Bon Hutang</a></li>
        <li class="active"><?= $kb->kontra_bon_no ?></li>
    </ol>
</section>

<section class="content">

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?= $this->session->flashdata('success') ?>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?= $this->session->flashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Info Kontra Bon</h3>
            <div class="pull-right">
                <a href="<?= site_url('ap-kontra-bon/cetak/' . $kb->kontra_bon_id) ?>" target="_blank" class="btn btn-default btn-flat btn-sm">
                    <i class="fa fa-print"></i> Cetak
                </a>
                <a href="<?= site_url('ap-kontra-bon') ?>" class="btn btn-warning btn-flat btn-sm">
                    <i class="fa fa-undo"></i> Kembali
                </a>
            </div>
        </div>
        <div class="box-body">
            <table class="table table-bordered">
                <tr>
                    <th width="200">No. Kontra Bon</th>
                    <td><?= $kb->kontra_bon_no ?></td>
                    <th width="200">Supplier</th>
                    <td><?= $kb->nama_supplier ?> (<?= $kb->phone ?>)</td>
                </tr>
                <tr>
                    <th>Periode</th>
                    <td><?= tgl_finance($kb->period_start) ?> s/d <?= tgl_finance($kb->period_end) ?></td>
                    <th>Jatuh Tempo</th>
                    <td><?= tgl_finance($kb->due_date) ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <?php
                        $map = [
                            'outstanding' => '<span class="label label-warning">Belum Dibayar</span>',
                            'partial'     => '<span class="label label-info">Dibayar Sebagian</span>',
                            'paid'        => '<span class="label label-success">Lunas</span>',
                            'void'        => '<span class="label label-default">Dibatalkan</span>',
                        ];
                        echo $map[$kb->status] ?? $kb->status;
                        ?>
                    </td>
                    <th>Jumlah Nota</th>
                    <td><?= count($invoices) ?> nota</td>
                </tr>
                <tr>
                    <th>Total Tagihan</th>
                    <td>Rp <?= number_format($kb->total_amount, 0, ',', '.') ?></td>
                    <th>Sudah Dibayar</th>
                    <td>Rp <?= number_format($kb->paid_amount, 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <th>Sisa</th>
                    <td colspan="3"><strong>Rp <?= number_format($kb->outstanding_amount, 0, ',', '.') ?></strong></td>
                </tr>
            </table>

            <?php if (in_array($kb->status, ['outstanding', 'partial'])): ?>
                <a href="<?= site_url('ap-kontra-bon-payment/add/' . $kb->kontra_bon_id) ?>" class="btn btn-success">
                    <i class="fa fa-money"></i> Catat Pembayaran
                </a>
            <?php endif; ?>

            <?php if ($kb->status != 'void' && $kb->paid_amount == 0 && in_array($this->session->userdata('level'), [1, 2])): ?>
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-void">
                    <i class="fa fa-ban"></i> Batalkan
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">Nota yang Digabung</h3>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No. AP</th>
                        <th>No. Invoice Supplier</th>
                        <th>No. PO</th>
                        <th>Tgl Invoice</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-right">Sudah Dibayar</th>
                        <th class="text-right">Sisa</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $status_map = [
                        'outstanding' => '<span class="label label-warning">Belum Dibayar</span>',
                        'partial'     => '<span class="label label-info">Dibayar Sebagian</span>',
                        'paid'        => '<span class="label label-success">Lunas</span>',
                        'void'        => '<span class="label label-default">Dibatalkan</span>',
                    ];
                    foreach ($invoices as $inv): ?>
                    <tr>
                        <td><a href="<?= site_url('ap-invoice/detail/' . $inv->ap_invoice_id) ?>"><?= $inv->ap_no ?></a></td>
                        <td><?= $inv->supplier_invoice_no ?: '-' ?></td>
                        <td><?= $inv->po_number ?: '-' ?></td>
                        <td><?= tgl_finance($inv->invoice_date) ?></td>
                        <td class="text-right"><?= number_format($inv->amount, 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($inv->paid_amount, 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($inv->outstanding_amount, 0, ',', '.') ?></td>
                        <td class="text-center"><?= $status_map[$inv->status] ?? $inv->status ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">Histori Pembayaran</h3>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No. Bukti</th>
                        <th>Tanggal</th>
                        <th class="text-right">Jumlah</th>
                        <th>Metode</th>
                        <th>Dibayar Oleh</th>
                        <th>Catatan</th>
                        <th>Status</th>
                        <?php if (in_array($this->session->userdata('level'), [1, 2])): ?>
                        <th width="80" class="text-center">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                    <tr><td colspan="8" class="text-center">Belum ada pembayaran</td></tr>
                    <?php else: foreach ($payments as $p): ?>
                    <tr>
                        <td><?= $p->payment_no ?></td>
                        <td><?= tgl_finance($p->payment_date) ?></td>
                        <td class="text-right">Rp <?= number_format($p->amount, 0, ',', '.') ?></td>
                        <td><?= ucfirst($p->payment_method) ?></td>
                        <td><?= $p->paid_by_name ?></td>
                        <td><?= $p->notes ?></td>
                        <td><?= $p->is_void ? '<span class="label label-default">Dibatalkan</span>' : '<span class="label label-success">Aktif</span>' ?></td>
                        <?php if (in_array($this->session->userdata('level'), [1, 2])): ?>
                        <td class="text-center">
                            <?php if (!$p->is_void): ?>
                            <form method="post" action="<?= site_url('ap-kontra-bon-payment/void/' . $p->kontra_bon_payment_id) ?>" onsubmit="return confirmVoidPayment(this);" style="display:inline-block;">
                                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                                <input type="hidden" name="void_reason" value="">
                                <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-ban"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="modal-void">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Batalkan Kontra Bon</h4>
            </div>
            <form method="post" action="<?= site_url('ap-kontra-bon/void/' . $kb->kontra_bon_id) ?>">
                <div class="modal-body">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <p>Semua nota di dalam kontra bon ini akan dilepas dan jatuh temponya dikembalikan sesuai termin supplier.</p>
                    <div class="form-group">
                        <label>Alasan Pembatalan</label>
                        <input type="text" name="void_reason" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Batalkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmVoidPayment(form) {
    var reason = prompt('Alasan pembatalan pembayaran ini:');
    if (reason === null || reason.trim() === '') return false;
    form.void_reason.value = reason;
    return true;
}
</script>
