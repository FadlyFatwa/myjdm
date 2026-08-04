<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-money text-primary"></i> Beban Operasional</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li class="active">Beban Operasional</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> Daftar Beban Operasional</h3>
            <div class="box-tools pull-right">
                <a href="<?= site_url('beban/add') ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-plus"></i> Catat Beban
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

            <table id="table-beban" class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th width="5%">No</th>
                        <th>No. Beban</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th class="text-right">Jumlah</th>
                        <th>Metode</th>
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
                <h4 class="modal-title">Batalkan Beban</h4>
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
    $('#table-beban').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('beban/get_json') ?>',
            type: 'POST'
        },
        order: [[1, 'desc']],
        columns: [
            { data: null, render: function(d, t, r, m) { return m.row + m.settings._iDisplayStart + 1; }, orderable: false },
            { data: 'expense_no' },
            { data: 'expense_date', render: function(d) { return fmtTglID(d); } },
            { data: 'coa_name' },
            { data: 'description' },
            { data: 'amount', className: 'text-right', render: function(d) { return parseInt(d).toLocaleString('id-ID'); } },
            { data: 'payment_method', render: function(d) { return d === 'cash' ? 'Kas' : 'Bank'; } },
            { data: 'is_void', className: 'text-center', render: function(d) {
                return d == 1 ? '<span class="label label-default">Dibatalkan</span>' : '<span class="label label-success">Aktif</span>';
            }},
            { data: null, orderable: false, className: 'text-center', render: function(d, t, r) {
                <?php if ($this->session->userdata('level') != 1): ?>
                return '-';
                <?php endif; ?>
                if (r.is_void == 1) return '-';
                return '<button class="btn btn-xs btn-danger btn-void" data-id="' + r.expense_id + '"><i class="fa fa-ban"></i></button>';
            }}
        ]
    });

    $(document).on('click', '.btn-void', function() {
        var id = $(this).data('id');
        $('#form-void').attr('action', '<?= site_url('beban/void/') ?>' + id);
        $('#modal-void').modal('show');
    });
});
</script>
