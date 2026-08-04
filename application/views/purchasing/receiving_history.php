<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-header">
    <h1><i class="fa fa-history" style="color:#3c8dbc"></i> Histori Penerimaan
        <small>semua transaksi Goods Receipt</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?= site_url('purchase-order') ?>">Purchase Order</a></li>
        <li class="active">Histori Penerimaan</li>
    </ol>
</div>

<div class="content">

    <div style="margin-bottom:14px;display:flex;align-items:center;gap:8px">
        <a href="<?= site_url('purchase-order/receiving') ?>" class="btn btn-warning btn-sm">
            <i class="fa fa-inbox"></i> Penerimaan Aktif
        </a>
        <a href="<?= site_url('purchase-order') ?>" class="btn btn-default btn-sm">
            <i class="fa fa-list"></i> Semua PO
        </a>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-history"></i> Riwayat Goods Receipt</h3>
        </div>
        <div class="box-body">
            <table id="tbl-history" class="table table-bordered table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th>Tgl Terima</th>
                        <th>Tgl Invoice</th>
                        <th>No. Invoice</th>
                        <th>No. PO</th>
                        <th>Supplier</th>
                        <th class="text-center">Item</th>
                        <th class="text-center">Total Qty</th>
                        <th>Diterima Oleh</th>
                        <th width="80" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>

<script>
$(function () {
    function toast(icon, msg) {
        Swal.fire({ toast:true, position:'top-end', icon:icon, title:msg,
            showConfirmButton:false, timer:3000, timerProgressBar:true });
    }

    var table = $('#tbl-history').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('purchase-order/history-data') ?>',
            type: 'POST',
        },
        columns: [
            { data: 'no',                  orderable: false, className: 'text-center' },
            { data: 'receive_date' },
            { data: 'invoice_date' },
            { data: 'supplier_invoice_no', orderable: false },
            { data: 'po_number',           orderable: false },
            { data: 'nama_supplier',       orderable: false },
            { data: 'total_lines',         orderable: false, className: 'text-center' },
            { data: 'total_qty',           orderable: false, className: 'text-center' },
            { data: 'received_by_name',    orderable: false },
            { data: 'action',              orderable: false, className: 'text-center' },
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ penerimaan',
            paginate: { previous: '‹', next: '›' },
            zeroRecords: 'Belum ada data penerimaan.',
            processing: '<i class="fa fa-spinner fa-spin"></i> Memuat...',
        },
        createdRow: function (row, data) {
            if (data.is_empty) {
                $(row).css({ background: '#fff3cd', opacity: '.85' });
                $(row).attr('title', 'Penerimaan kosong — tidak ada barang diterima');
            }
        },
    });

    $(document).on('click', '.btn-del-receipt', function () {
        var $btn = $(this);
        var id   = $btn.data('id');

        Swal.fire({
            title: 'Hapus data penerimaan ini?',
            html : 'Stok yang sudah bertambah akan <b>dikembalikan</b>.<br>Tindakan ini tidak bisa dibatalkan.',
            icon : 'warning',
            showCancelButton : true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText : 'Batal',
            confirmButtonColor: '#dd4b39',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $btn.prop('disabled', true);
            var csrf = {};
            csrf['<?= $this->security->get_csrf_token_name() ?>'] = '<?= $this->security->get_csrf_hash() ?>';
            $.post('<?= site_url('purchase-order/history/delete/') ?>' + id, csrf, function (res) {
                if (res.status === 'success') {
                    toast('success', 'Data penerimaan berhasil dihapus.');
                    table.ajax.reload(null, false);
                } else {
                    toast('error', res.message || 'Gagal menghapus.');
                    $btn.prop('disabled', false);
                }
            }, 'json').fail(function () {
                toast('error', 'Terjadi kesalahan server.');
                $btn.prop('disabled', false);
            });
        });
    });
});
</script>
