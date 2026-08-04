<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-wrench text-primary"></i> Master Jasa / Press</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Master</a></li>
        <li class="active">Jasa</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> Daftar Item Jasa</h3>
            <div class="box-tools pull-right">
                <a href="<?= site_url('service-item/add') ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-plus"></i> Tambah Jasa
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

            <table id="table-service" class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Jasa</th>
                        <th class="text-right">Tarif (Rp)</th>
                        <th width="130" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal konfirmasi hapus -->
<div class="modal fade" id="modal-delete">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Konfirmasi Hapus</h4>
            </div>
            <div class="modal-body">
                Yakin hapus item jasa <strong id="del-nama"></strong>?
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
    var table = $('#table-service').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('service-item/get_json') ?>',
            type: 'POST'
        },
        columns: [
            { data: null, render: function(d, t, r, m) { return m.row + m.settings._iDisplayStart + 1; }, orderable: false },
            { data: 'nama_jasa' },
            { data: 'tarif', className: 'text-right', render: function(d) {
                return parseInt(d).toLocaleString('id-ID');
            }},
            { data: null, orderable: false, className: 'text-center', render: function(d, t, r) {
                return '<a href="<?= site_url('service-item/edit/') ?>' + r.jasa_id + '" class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i></a> '
                     + '<button class="btn btn-xs btn-danger btn-delete" data-id="' + r.jasa_id + '" data-nama="' + r.nama_jasa + '"><i class="fa fa-trash"></i></button>';
            }}
        ]
    });

    $(document).on('click', '.btn-delete', function() {
        var id   = $(this).data('id');
        var nama = $(this).data('nama');
        $('#del-nama').text(nama);
        $('#form-delete').attr('action', '<?= site_url('service-item/delete/') ?>' + id);
        $('#modal-delete').modal('show');
    });
});
</script>
