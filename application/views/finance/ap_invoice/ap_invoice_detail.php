<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1>Detail Hutang
        <small><?= $ap->ap_no ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li><a href="<?= site_url('ap-invoice') ?>">Hutang</a></li>
        <li class="active"><?= $ap->ap_no ?></li>
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
            <h3 class="box-title">Info Invoice</h3>
            <div class="pull-right">
                <a href="<?= site_url('ap-invoice') ?>" class="btn btn-warning btn-flat btn-sm">
                    <i class="fa fa-undo"></i> Kembali
                </a>
            </div>
        </div>
        <div class="box-body">
            <table class="table table-bordered">
                <tr>
                    <th width="200">No. AP</th>
                    <td><?= $ap->ap_no ?></td>
                    <th width="200">Supplier</th>
                    <td><?= $ap->nama_supplier ?> (<?= $ap->phone ?>)</td>
                </tr>
                <?php if (!empty($ap->po_number)): ?>
                <tr>
                    <th>No. PO</th>
                    <td><a href="<?= site_url('purchase-order/' . $ap->po_id) ?>" target="_blank"><?= $ap->po_number ?></a></td>
                    <th>No. Invoice Supplier</th>
                    <td><?= $ap->supplier_invoice_no ?: '-' ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>Tgl Invoice</th>
                    <td><?= tgl_finance($ap->invoice_date) ?></td>
                    <th>Jatuh Tempo</th>
                    <td><?= tgl_finance($ap->due_date) ?></td>
                </tr>
                <tr>
                    <th>Cara Bayar</th>
                    <td><?= $ap->payment_type === 'cash' ? 'Cash (Lunas)' : 'Kredit (Tempo)' ?></td>
                    <th>Status</th>
                    <td>
                        <?php
                        $map = [
                            'outstanding' => '<span class="label label-warning">Belum Dibayar</span>',
                            'partial'     => '<span class="label label-info">Dibayar Sebagian</span>',
                            'paid'        => '<span class="label label-success">Lunas</span>',
                            'void'        => '<span class="label label-default">Dibatalkan</span>',
                        ];
                        echo $map[$ap->status] ?? $ap->status;
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Jumlah</th>
                    <td>Rp <?= number_format($ap->amount, 0, ',', '.') ?></td>
                    <th>Sudah Dibayar</th>
                    <td>Rp <?= number_format($ap->paid_amount, 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <th>Sisa</th>
                    <td colspan="3"><strong>Rp <?= number_format($ap->outstanding_amount, 0, ',', '.') ?></strong></td>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <td colspan="3"><?= $ap->description ?></td>
                </tr>
                <?php if (!empty($ap->kontra_bon_id)): ?>
                <tr>
                    <th>Kontra Bon</th>
                    <td colspan="3">
                        <span class="label label-primary"><i class="fa fa-clone"></i> Tergabung dalam <?= $ap->kontra_bon_no ?></span>
                        <a href="<?= site_url('ap-kontra-bon/detail/' . $ap->kontra_bon_id) ?>">Lihat Kontra Bon</a>
                    </td>
                </tr>
                <?php endif; ?>
            </table>

            <?php if (!empty($ap->kontra_bon_id)): ?>
                <p class="text-muted">Nota ini sudah tergabung dalam kontra bon — pembayaran dicatat lewat kontra bon, bukan per nota.</p>
                <a href="<?= site_url('ap-kontra-bon/detail/' . $ap->kontra_bon_id) ?>" class="btn btn-primary">
                    <i class="fa fa-clone"></i> Ke Halaman Kontra Bon
                </a>
            <?php elseif (in_array($ap->status, ['outstanding', 'partial'])): ?>
                <a href="<?= site_url('ap-payment/add/' . $ap->ap_invoice_id) ?>" class="btn btn-success">
                    <i class="fa fa-money"></i> Catat Pembayaran
                </a>
            <?php endif; ?>

            <?php if (empty($ap->kontra_bon_id) && $ap->status != 'void' && $ap->paid_amount == 0 && in_array($this->session->userdata('level'), [1, 2])): ?>
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-void">
                    <i class="fa fa-ban"></i> Batalkan
                </button>
            <?php endif; ?>
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
                            <form method="post" action="<?= site_url('ap-payment/void/' . $p->ap_payment_id) ?>" onsubmit="return confirmVoidPayment(this);" style="display:inline-block;">
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
                <h4 class="modal-title">Batalkan Invoice</h4>
            </div>
            <form method="post" action="<?= site_url('ap-invoice/void/' . $ap->ap_invoice_id) ?>">
                <div class="modal-body">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
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
