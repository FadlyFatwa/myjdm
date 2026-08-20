<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1>Catat Pembayaran
        <small><?= $ap->ap_no ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li><a href="<?= site_url('ap-invoice') ?>">Hutang</a></li>
        <li><a href="<?= site_url('ap-invoice/detail/' . $ap->ap_invoice_id) ?>"><?= $ap->ap_no ?></a></li>
        <li class="active">Bayar</li>
    </ol>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title">Form Pembayaran</h3>
            <div class="pull-right">
                <a href="<?= site_url('ap-invoice/detail/' . $ap->ap_invoice_id) ?>" class="btn btn-warning btn-flat btn-sm">
                    <i class="fa fa-undo"></i> Kembali
                </a>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="150">Supplier</th>
                            <td><?= $ap->nama_supplier ?></td>
                        </tr>
                        <tr>
                            <th>Jumlah Invoice</th>
                            <td>Rp <?= number_format($ap->amount, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <th>Sudah Dibayar</th>
                            <td>Rp <?= number_format($ap->paid_amount, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <th>Sisa Tagihan</th>
                            <td><strong>Rp <?= number_format($ap->outstanding_amount, 0, ',', '.') ?></strong></td>
                        </tr>
                    </table>

                    <form action="<?= site_url('ap-payment/process') ?>" method="post">
                        <input type="hidden" name="ap_invoice_id" value="<?= $ap->ap_invoice_id ?>">
                        <div class="form-group">
                            <label>Tanggal Bayar *</label>
                            <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Jumlah Bayar (Rp) *</label>
                            <input type="text" name="amount" class="form-control input-rupiah" value="<?= $ap->outstanding_amount ?>" required>
                            <p class="help-block">Maksimal Rp <?= number_format($ap->outstanding_amount, 0, ',', '.') ?> (sisa tagihan). Boleh diisi kurang untuk cicilan.</p>
                        </div>
                        <div class="form-group">
                            <label>Metode Pembayaran *</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                                <option value="debit">Debit</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <input type="text" name="notes" class="form-control">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-flat">
                                <i class="fa fa-paper-plane"></i> Simpan Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $(document).on('input', '.input-rupiah', function() {
        var val = $(this).val().replace(/\D/g, '');
        $(this).val(parseInt(val || 0).toLocaleString('id-ID').replace(/,/g, '.'));
    });
});
</script>
