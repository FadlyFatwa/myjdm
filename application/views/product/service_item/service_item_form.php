<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><?= $page ?> Jasa</h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('service-item') ?>"><i class="fa fa-wrench"></i> Master Jasa</a></li>
        <li class="active"><?= $page ?></li>
    </ol>
</section>

<section class="content">
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?= $this->session->flashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><?= $page ?> Item Jasa</h3>
            <div class="box-tools pull-right">
                <a href="<?= site_url('service-item') ?>" class="btn btn-warning btn-sm">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <form method="post" action="<?= site_url('service-item/' . ($row->jasa_id ? 'edit/'.$row->jasa_id : 'add')) ?>">
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

                        <div class="form-group">
                            <label>Nama Jasa <span class="text-danger">*</span></label>
                            <input type="text" name="nama_jasa" class="form-control"
                                   value="<?= htmlspecialchars($row->nama_jasa ?? '') ?>"
                                   placeholder="cth: Jasa Press Bearing" required>
                        </div>

                        <div class="form-group">
                            <label>Tarif (Rp) <span class="text-danger">*</span></label>
                            <input type="text" name="tarif" id="input-tarif" class="form-control"
                                   value="<?= isset($row->tarif) && $row->tarif ? number_format($row->tarif, 0, ',', '.') : '' ?>"
                                   placeholder="cth: 25.000" required>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('input-tarif').addEventListener('input', function() {
    let raw = this.value.replace(/\D/g, '');
    this.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
});
</script>
