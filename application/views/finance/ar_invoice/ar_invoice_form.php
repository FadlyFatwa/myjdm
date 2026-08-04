<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1>Piutang Manual
        <small>Bukan dari transaksi penjualan</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li><a href="<?= site_url('ar-invoice') ?>">Piutang</a></li>
        <li class="active">Tambah</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Tambah Piutang Manual</h3>
            <div class="pull-right">
                <a href="<?= site_url('ar-invoice') ?>" class="btn btn-warning btn-flat btn-sm">
                    <i class="fa fa-undo"></i> Kembali
                </a>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <form action="<?= site_url('ar-invoice/add') ?>" method="post">
                        <div class="form-group">
                            <label>Customer *</label>
                            <select name="customer_id" class="form-control select2" style="width:100%" required>
                                <option value="">- Pilih Customer -</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c->customer_id ?>"><?= $c->nama_customer ?> (<?= $c->phone ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Invoice *</label>
                            <input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Jatuh Tempo (opsional, default sesuai termin customer)</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Jumlah (Rp) *</label>
                            <input type="text" name="amount" class="form-control input-rupiah" value="0" required>
                        </div>
                        <div class="form-group">
                            <label>Akun Lawan (Kredit) *</label>
                            <select name="lawan_coa_id" class="form-control" required>
                                <option value="">- Pilih Akun -</option>
                                <?php foreach ($coa_list as $c): ?>
                                    <option value="<?= $c->coa_id ?>" <?= $c->coa_subtype == 'pendapatan_penjualan' ? 'selected' : '' ?>><?= $c->coa_code . ' - ' . $c->coa_name ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="help-block">Piutang manual belum tentu dari penjualan barang — pilih akun pendapatan yang sesuai.</p>
                        </div>
                        <div class="form-group">
                            <label>Keterangan *</label>
                            <textarea name="description" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-flat">
                                <i class="fa fa-paper-plane"></i> Simpan
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
    $('.select2').select2();
    $(document).on('input', '.input-rupiah', function() {
        var val = $(this).val().replace(/\D/g, '');
        $(this).val(parseInt(val || 0).toLocaleString('id-ID').replace(/,/g, '.'));
    });
});
</script>
