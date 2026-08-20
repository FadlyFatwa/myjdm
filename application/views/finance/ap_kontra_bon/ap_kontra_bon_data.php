<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-clone text-primary"></i> Kontra Bon Hutang</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li class="active">Kontra Bon Hutang</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> Daftar Kontra Bon Hutang</h3>
            <?php if (in_array($this->session->userdata('level'), [1, 2])): ?>
            <div class="box-tools pull-right">
                <a href="<?= site_url('ap-kontra-bon/add') ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-plus"></i> Buat Kontra Bon
                </a>
            </div>
            <?php endif; ?>
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

            <table id="table-kbh" class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th width="5%">No</th>
                        <th>No. Kontra Bon</th>
                        <th>Supplier</th>
                        <th>Periode</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Sisa</th>
                        <th class="text-center">Status</th>
                        <th width="80" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('#table-kbh').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('ap-kontra-bon/get_json') ?>',
            type: 'POST'
        },
        order: [[1, 'desc']],
        columns: [
            { data: null, render: function(d, t, r, m) { return m.row + m.settings._iDisplayStart + 1; }, orderable: false },
            { data: 'kontra_bon_no' },
            { data: 'nama_supplier' },
            { data: null, render: function(d, t, r) { return fmtTglID(r.period_start) + ' s/d ' + fmtTglID(r.period_end); } },
            { data: 'due_date', render: function(d) { return fmtTglID(d); } },
            { data: 'total_amount', className: 'text-right', render: function(d) { return parseInt(d).toLocaleString('id-ID'); } },
            { data: 'outstanding_amount', className: 'text-right', render: function(d) { return parseInt(d).toLocaleString('id-ID'); } },
            { data: 'status', className: 'text-center', render: function(d) {
                var map = {
                    outstanding: '<span class="label label-warning">Belum Dibayar</span>',
                    partial: '<span class="label label-info">Dibayar Sebagian</span>',
                    paid: '<span class="label label-success">Lunas</span>',
                    void: '<span class="label label-default">Dibatalkan</span>'
                };
                return map[d] || d;
            }},
            { data: null, orderable: false, className: 'text-center', render: function(d, t, r) {
                return '<a href="<?= site_url('ap-kontra-bon/detail/') ?>' + r.kontra_bon_id + '" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a>';
            }}
        ]
    });
});
</script>
