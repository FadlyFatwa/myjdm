<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-exchange text-primary"></i> Migrasi Data Piutang Lama</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li class="active">Migrasi Data Lama</li>
    </ol>
</section>

<section class="content">
    <div class="alert alert-warning">
        <strong>Fitur sekali pakai.</strong> Ini membawa transaksi penjualan kredit lama (yang statusnya masih "belum lunas" dan sudah punya customer terdaftar) ke modul Piutang (AR), lengkap dengan jurnalnya. Transaksi yang sudah pernah dimigrasi tidak akan dobel kalau dijalankan ulang. Setelah selesai dipakai, minta developer hapus fitur ini.
    </div>

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
            <h3 class="box-title">Pilih Rentang Tanggal</h3>
        </div>
        <div class="box-body">
            <form method="get" action="<?= site_url('migrate-ar') ?>" class="form-inline" style="margin-bottom:15px;">
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Dari</label>
                    <input type="date" name="from" value="<?= $from ?>" class="form-control">
                </div>
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Sampai</label>
                    <input type="date" name="to" value="<?= $to ?>" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Cek Data</button>
            </form>

            <h4>Akan dimigrasi (<?= count($preview['eligible']) ?> transaksi)</h4>
            <table class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th>Invoice</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th class="text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($preview['eligible'])): ?>
                    <tr><td colspan="4" class="text-center">Tidak ada transaksi yang perlu dimigrasi pada rentang ini</td></tr>
                    <?php else:
                    $total_eligible = 0;
                    foreach ($preview['eligible'] as $s): $total_eligible += $s->final_price; ?>
                    <tr>
                        <td><?= $s->invoice ?></td>
                        <td><?= tgl_finance($s->date) ?></td>
                        <td><?= $s->nama_customer ?></td>
                        <td class="text-right"><?= number_format($s->final_price, 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="bg-gray">
                        <th colspan="3" class="text-right">Total</th>
                        <th class="text-right"><?= number_format($total_eligible, 0, ',', '.') ?></th>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (!empty($preview['eligible'])): ?>
            <form method="post" action="<?= site_url('migrate-ar/run') ?>" onsubmit="return confirm('Jalankan migrasi untuk <?= count($preview['eligible']) ?> transaksi ini? Aksi ini akan membuat invoice piutang + jurnal permanen.');">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <input type="hidden" name="from" value="<?= $from ?>">
                <input type="hidden" name="to" value="<?= $to ?>">
                <button type="submit" class="btn btn-success">
                    <i class="fa fa-play"></i> Jalankan Migrasi
                </button>
            </form>
            <?php endif; ?>

            <?php if (!empty($preview['skipped_walkin'])): ?>
            <hr>
            <h4 class="text-red">Dilewati — tidak ada customer terdaftar (<?= count($preview['skipped_walkin']) ?> transaksi)</h4>
            <p class="text-muted">Transaksi ini tetap "belum lunas" seperti biasa (tidak berubah). Kalau mau ditagih lewat AR, isi dulu customer-nya lalu proses manual.</p>
            <table class="table table-bordered table-striped">
                <thead class="bg-danger">
                    <tr>
                        <th>Invoice</th>
                        <th>Tanggal</th>
                        <th>Nama (walk-in)</th>
                        <th class="text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total_skipped = 0; foreach ($preview['skipped_walkin'] as $s): $total_skipped += $s->final_price; ?>
                    <tr>
                        <td><?= $s->invoice ?></td>
                        <td><?= tgl_finance($s->date) ?></td>
                        <td><?= $s->customer_name ?></td>
                        <td class="text-right"><?= number_format($s->final_price, 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="bg-gray">
                        <th colspan="3" class="text-right">Total</th>
                        <th class="text-right"><?= number_format($total_skipped, 0, ',', '.') ?></th>
                    </tr>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</section>
