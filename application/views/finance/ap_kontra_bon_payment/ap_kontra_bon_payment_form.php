<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1>Catat Pembayaran
        <small><?= $kb->kontra_bon_no ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li><a href="<?= site_url('ap-kontra-bon') ?>">Kontra Bon Hutang</a></li>
        <li><a href="<?= site_url('ap-kontra-bon/detail/' . $kb->kontra_bon_id) ?>"><?= $kb->kontra_bon_no ?></a></li>
        <li class="active">Bayar</li>
    </ol>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title">Form Pembayaran</h3>
            <div class="pull-right">
                <a href="<?= site_url('ap-kontra-bon/detail/' . $kb->kontra_bon_id) ?>" class="btn btn-warning btn-flat btn-sm">
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
                            <td><?= $kb->nama_supplier ?></td>
                        </tr>
                        <tr>
                            <th>Periode</th>
                            <td><?= tgl_finance($kb->period_start) ?> s/d <?= tgl_finance($kb->period_end) ?></td>
                        </tr>
                        <tr>
                            <th>Total Tagihan</th>
                            <td>Rp <?= number_format($kb->total_amount, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <th>Sudah Dibayar</th>
                            <td>Rp <?= number_format($kb->paid_amount, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <th>Sisa Tagihan</th>
                            <td><strong>Rp <?= number_format($kb->outstanding_amount, 0, ',', '.') ?></strong></td>
                        </tr>
                    </table>

                    <p class="help-block">Pembayaran akan otomatis didistribusikan ke nota-nota di dalam kontra bon ini, dimulai dari nota dengan tanggal paling lama.</p>

                    <form action="<?= site_url('ap-kontra-bon-payment/process') ?>" method="post">
                        <input type="hidden" name="kontra_bon_id" value="<?= $kb->kontra_bon_id ?>">
                        <div class="form-group">
                            <label>Tanggal Bayar *</label>
                            <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Jumlah Bayar (Rp) *</label>
                            <input type="text" name="amount" class="form-control input-rupiah" value="<?= $kb->outstanding_amount ?>" required>
                            <p class="help-block">Maksimal Rp <?= number_format($kb->outstanding_amount, 0, ',', '.') ?> (sisa tagihan). Boleh diisi kurang untuk cicilan.</p>
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
