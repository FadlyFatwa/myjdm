<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><?= $page ?> Akun
        <small>Chart of Accounts</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li><a href="<?= site_url('coa') ?>">Chart of Accounts</a></li>
        <li class="active"><?= $page ?></li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><?= $page ?> Akun</h3>
            <div class="pull-right">
                <a href="<?= site_url('coa') ?>" class="btn btn-warning btn-flat btn-sm">
                    <i class="fa fa-undo"></i> Kembali
                </a>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <form action="<?= $page === 'Tambah' ? site_url('coa/add') : site_url('coa/edit/' . $row->coa_id) ?>" method="post">
                        <div class="form-group">
                            <label>Kode Akun *</label>
                            <input type="text" name="coa_code" value="<?= $row->coa_code ?>" class="form-control" required <?= $page === 'Edit' ? 'readonly' : '' ?>>
                        </div>
                        <div class="form-group">
                            <label>Nama Akun *</label>
                            <input type="text" name="coa_name" value="<?= $row->coa_name ?>" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Tipe Akun *</label>
                            <select name="coa_type" class="form-control" required>
                                <?php foreach (['aset', 'kewajiban', 'modal', 'pendapatan', 'beban'] as $t): ?>
                                    <option value="<?= $t ?>" <?= $row->coa_type == $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Sub Tipe (kunci lookup program, kosongkan jika akun umum)</label>
                            <input type="text" name="coa_subtype" value="<?= $row->coa_subtype ?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Akun Induk</label>
                            <select name="parent_id" class="form-control">
                                <option value="">- Tidak ada -</option>
                                <?php foreach ($parents as $p): ?>
                                    <option value="<?= $p->coa_id ?>" <?= $row->parent_id == $p->coa_id ? 'selected' : '' ?>><?= $p->coa_code . ' - ' . $p->coa_name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Saldo Normal *</label>
                            <select name="normal_balance" class="form-control" required>
                                <option value="debit" <?= $row->normal_balance == 'debit' ? 'selected' : '' ?>>Debit</option>
                                <option value="kredit" <?= $row->normal_balance == 'kredit' ? 'selected' : '' ?>>Kredit</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_postable" value="1" <?= $row->is_postable ? 'checked' : '' ?>>
                                Postable (bisa dipakai jurnal langsung)
                            </label>
                            <p class="help-block">Uncheck untuk akun header/grup (misal "1000 ASET").</p>
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea name="description" class="form-control"><?= $row->description ?></textarea>
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
