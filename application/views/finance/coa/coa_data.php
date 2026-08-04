<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-sitemap text-primary"></i> Chart of Accounts</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li class="active">Chart of Accounts</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> Daftar Akun</h3>
            <div class="box-tools pull-right">
                <a href="<?= site_url('coa/add') ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-plus"></i> Tambah Akun
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

            <table id="table-coa" class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th width="5%">No</th>
                        <th>Kode</th>
                        <th>Nama Akun</th>
                        <th>Tipe</th>
                        <th>Induk</th>
                        <th class="text-center">Saldo Normal</th>
                        <th class="text-center">Postable</th>
                        <th width="130" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="modal-delete">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Konfirmasi Hapus</h4>
            </div>
            <div class="modal-body">
                Yakin hapus akun <strong id="del-nama"></strong>?
            </div>
            <div class="modal-footer">
                <form method="post" id="form-delete" action="">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#table-coa').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('coa/get_json') ?>',
            type: 'POST'
        },
        columns: [
            { data: null, render: function(d, t, r, m) { return m.row + m.settings._iDisplayStart + 1; }, orderable: false },
            { data: 'coa_code' },
            { data: 'coa_name' },
            { data: 'coa_type' },
            { data: 'parent_name', render: function(d) { return d ?? '-'; } },
            { data: 'normal_balance', className: 'text-center' },
            { data: 'is_postable', className: 'text-center', render: function(d) {
                return d == 1 ? '<span class="label label-success">Ya</span>' : '<span class="label label-default">Header</span>';
            }},
            { data: null, orderable: false, className: 'text-center', render: function(d, t, r) {
                var btns = '<a href="<?= site_url('coa/edit/') ?>' + r.coa_id + '" class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i></a> ';
                if (r.is_system == 0) {
                    btns += '<button class="btn btn-xs btn-danger btn-delete" data-id="' + r.coa_id + '" data-nama="' + r.coa_name + '"><i class="fa fa-trash"></i></button>';
                }
                return btns;
            }}
        ]
    });

    $(document).on('click', '.btn-delete', function() {
        var id   = $(this).data('id');
        var nama = $(this).data('nama');
        $('#del-nama').text(nama);
        $('#form-delete').attr('action', '<?= site_url('coa/delete/') ?>' + id);
        $('#modal-delete').modal('show');
    });
});
</script>
