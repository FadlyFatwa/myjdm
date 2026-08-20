<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-file-text text-red"></i> Hutang (AP)</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li class="active">Hutang</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> Daftar Invoice Hutang</h3>
            <?php if (in_array($this->session->userdata('level'), [1, 2])): ?>
            <div class="box-tools pull-right">
                <form method="post" action="<?= site_url('ap-invoice/refresh-all-due-dates') ?>" style="display:inline-block;" onsubmit="return confirm('Hitung ulang jatuh tempo semua invoice outstanding/partial sesuai termin supplier terkini?');">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <button type="submit" class="btn btn-default btn-sm" title="Hitung ulang jatuh tempo semua invoice sesuai termin supplier terkini">
                        <i class="fa fa-refresh"></i> Refresh Jatuh Tempo
                    </button>
                </form>
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

            <table id="table-ap" class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th width="5%">No</th>
                        <th>No. AP</th>
                        <th>No. Invoice Supplier</th>
                        <th>No. PO</th>
                        <th>Supplier</th>
                        <th>Tgl Invoice</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-right">Jumlah</th>
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
    $('#table-ap').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('ap-invoice/get_json') ?>',
            type: 'POST'
        },
        order: [[1, 'desc']],
        columns: [
            { data: null, render: function(d, t, r, m) { return m.row + m.settings._iDisplayStart + 1; }, orderable: false },
            { data: 'ap_no', render: function(d, t, r) {
                var badge = r.kontra_bon_id ? ' <span class="label label-primary" title="Tergabung dalam kontra bon"><i class="fa fa-clone"></i></span>' : '';
                return d + badge;
            }},
            { data: 'supplier_invoice_no', render: function(d) { return d ? '<span class="label label-default">' + d + '</span>' : '-'; } },
            { data: 'po_number', render: function(d) { return d || '-'; } },
            { data: 'nama_supplier' },
            { data: 'invoice_date', render: function(d) { return fmtTglID(d); } },
            { data: 'due_date', render: function(d) { return fmtTglID(d); } },
            { data: 'amount', className: 'text-right', render: function(d) { return parseInt(d).toLocaleString('id-ID'); } },
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
                return '<a href="<?= site_url('ap-invoice/detail/') ?>' + r.ap_invoice_id + '" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a>';
            }}
        ]
    });
});
</script>
