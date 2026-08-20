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
                                    <option value="<?= $c->customer_id ?>" data-percent="<?= $c->gross_discount_percent ?>"><?= $c->nama_customer ?> (<?= $c->phone ?>)</option>
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
                        <div class="form-group" id="gross_amount_group" style="display:none">
                            <label>Brutto (Rp) *</label>
                            <input type="text" name="gross_amount" id="gross_amount" class="form-control input-rupiah" value="0">
                            <p class="help-block">Customer ini punya kesepakatan brutto/netto — isi nominal brutto sesuai nota, netto dihitung otomatis.</p>
                        </div>
                        <div class="form-group">
                            <label>Jumlah (Rp) *</label>
                            <input type="text" name="amount" id="amount" class="form-control input-rupiah" value="0" required>
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

    function rupiahToInt(val) {
        return parseInt(String(val).replace(/\D/g, '') || 0, 10);
    }
    function formatRupiah(val) {
        return val.toLocaleString('id-ID').replace(/,/g, '.');
    }

    function recalcNetto() {
        var percent = parseFloat($('select[name="customer_id"]').find(':selected').data('percent')) || 0;
        if (percent > 0) {
            $('#gross_amount_group').show();
            var brutto = rupiahToInt($('#gross_amount').val());
            var netto = Math.round(brutto * (1 - percent / 100));
            $('#amount').val(formatRupiah(netto)).prop('readonly', true);
        } else {
            $('#gross_amount_group').hide();
            $('#gross_amount').val('0');
            $('#amount').prop('readonly', false);
        }
    }

    $(document).on('change', 'select[name="customer_id"]', recalcNetto);
    $(document).on('input', '#gross_amount', recalcNetto);
});
</script>
