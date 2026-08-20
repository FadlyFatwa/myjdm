<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-history text-primary"></i> Riwayat Uang Makan</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> SDM</a></li>
        <li><a href="<?= site_url('absensi') ?>">Absensi & Uang Makan</a></li>
        <li class="active">Riwayat</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> Daftar Uang Makan Terproses</h3>
            <div class="box-tools pull-right">
                <a href="<?= site_url('absensi') ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-undo"></i> Kembali ke Absensi
                </a>
            </div>
        </div>
        <div class="box-body">

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

            <table id="table-uang-makan" class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th width="5%">No</th>
                        <th>Tanggal</th>
                        <th class="text-right">Jumlah Karyawan</th>
                        <th class="text-right">Tarif</th>
                        <th class="text-right">Total</th>
                        <th>Diproses Oleh</th>
                        <th class="text-center">Status</th>
                        <th width="80" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="modal-void">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Batalkan Uang Makan</h4>
            </div>
            <form method="post" id="form-void" action="">
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
$(document).ready(function() {
    $('#table-uang-makan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('absensi/history-json') ?>',
            type: 'POST'
        },
        order: [[1, 'desc']],
        columns: [
            { data: null, render: function(d, t, r, m) { return m.row + m.settings._iDisplayStart + 1; }, orderable: false },
            { data: 'tanggal', render: function(d) { return fmtTglID(d); } },
            { data: 'jumlah_karyawan', className: 'text-right' },
            { data: 'tarif', className: 'text-right', render: function(d) { return parseInt(d).toLocaleString('id-ID'); } },
            { data: 'total_amount', className: 'text-right', render: function(d) { return parseInt(d).toLocaleString('id-ID'); } },
            { data: 'created_by_name' },
            { data: 'is_void', className: 'text-center', render: function(d) {
                return d == 1 ? '<span class="label label-default">Dibatalkan</span>' : '<span class="label label-success">Aktif</span>';
            }},
            { data: null, orderable: false, className: 'text-center', render: function(d, t, r) {
                <?php if ($this->session->userdata('level') != 1): ?>
                return '-';
                <?php endif; ?>
                if (r.is_void == 1) return '-';
                return '<button class="btn btn-xs btn-danger btn-void" data-id="' + r.uang_makan_id + '"><i class="fa fa-ban"></i></button>';
            }}
        ]
    });

    $(document).on('click', '.btn-void', function() {
        var id = $(this).data('id');
        $('#form-void').attr('action', '<?= site_url('absensi/void/') ?>' + id);
        $('#modal-void').modal('show');
    });
});
</script>
