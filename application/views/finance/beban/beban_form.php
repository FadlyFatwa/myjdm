<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1>Catat Beban Operasional</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li><a href="<?= site_url('beban') ?>">Beban Operasional</a></li>
        <li class="active">Tambah</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Form Beban Operasional</h3>
            <div class="pull-right">
                <a href="<?= site_url('beban') ?>" class="btn btn-warning btn-flat btn-sm">
                    <i class="fa fa-undo"></i> Kembali
                </a>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <form action="<?= site_url('beban/add') ?>" method="post">
                        <div class="form-group">
                            <label>Tanggal *</label>
                            <input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Kategori Beban *</label>
                            <select name="coa_id" class="form-control" required>
                                <option value="">- Pilih Kategori -</option>
                                <?php foreach ($coa_list as $c): ?>
                                    <option value="<?= $c->coa_id ?>"><?= $c->coa_code . ' - ' . $c->coa_name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Jumlah (Rp) *</label>
                            <input type="text" name="amount" class="form-control input-rupiah" value="0" required>
                        </div>
                        <div class="form-group">
                            <label>Sumber Dana *</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="cash">Kas</option>
                                <option value="transfer">Bank</option>
                            </select>
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
    $(document).on('input', '.input-rupiah', function() {
        var val = $(this).val().replace(/\D/g, '');
        $(this).val(parseInt(val || 0).toLocaleString('id-ID').replace(/,/g, '.'));
    });
});
</script>
