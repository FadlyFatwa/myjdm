<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-book text-primary"></i> Jurnal Umum</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li class="active">Jurnal Umum</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> Daftar Jurnal</h3>
            <?php if (in_array($this->session->userdata('level'), [1])): ?>
            <div class="box-tools pull-right">
                <a href="<?= site_url('journal/add') ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-plus"></i> Jurnal Manual
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

            <table id="table-journal" class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th width="5%">No</th>
                        <th>No. Jurnal</th>
                        <th>Tanggal</th>
                        <th>Sumber</th>
                        <th>Keterangan</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Kredit</th>
                        <th class="text-center">Status</th>
                        <th width="90" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="modal-journal-detail">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modal-journal-content">
            <!-- diisi via AJAX -->
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#table-journal').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('journal/get_json') ?>',
            type: 'POST'
        },
        order: [[1, 'desc']],
        columns: [
            { data: null, render: function(d, t, r, m) { return m.row + m.settings._iDisplayStart + 1; }, orderable: false },
            { data: 'journal_no' },
            { data: 'journal_date', render: function(d) { return fmtTglID(d); } },
            { data: 'source_type', render: function(d) {
                var map = { ar_invoice: 'Piutang', ar_payment: 'Pembayaran Piutang', manual_adjustment: 'Jurnal Manual' };
                return map[d] || d;
            }},
            { data: 'description' },
            { data: 'total_debit', className: 'text-right', render: function(d) { return parseInt(d).toLocaleString('id-ID'); } },
            { data: 'total_kredit', className: 'text-right', render: function(d) { return parseInt(d).toLocaleString('id-ID'); } },
            { data: 'status', className: 'text-center', render: function(d) {
                return d == 'posted' ? '<span class="label label-success">Terposting</span>' : '<span class="label label-default">Dibatalkan</span>';
            }},
            { data: null, orderable: false, className: 'text-center', render: function(d, t, r) {
                return '<button class="btn btn-xs btn-primary btn-detail" data-id="' + r.journal_id + '"><i class="fa fa-eye"></i></button>';
            }}
        ]
    });

    $(document).on('click', '.btn-detail', function() {
        var id = $(this).data('id');
        $('#modal-journal-content').load('<?= site_url('journal/detail/') ?>' + id, function() {
            $('#modal-journal-detail').modal('show');
        });
    });
});
</script>
