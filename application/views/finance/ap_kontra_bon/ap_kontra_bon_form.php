<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1>Buat Kontra Bon Hutang
        <small>Gabungkan nota dalam satu periode jadi satu tagihan</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li><a href="<?= site_url('ap-kontra-bon') ?>">Kontra Bon Hutang</a></li>
        <li class="active">Tambah</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Buat Kontra Bon</h3>
            <div class="pull-right">
                <a href="<?= site_url('ap-kontra-bon') ?>" class="btn btn-warning btn-flat btn-sm">
                    <i class="fa fa-undo"></i> Kembali
                </a>
            </div>
        </div>
        <div class="box-body">
            <form action="<?= site_url('ap-kontra-bon/add') ?>" method="post" id="form-kb">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Supplier *</label>
                            <select name="supplier_id" id="supplier_id" class="form-control select2" style="width:100%" required>
                                <option value="">- Pilih Supplier -</option>
                                <?php foreach ($suppliers as $s): ?>
                                    <option value="<?= $s->supplier_id ?>"><?= $s->nama_supplier ?> (<?= $s->phone ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Periode Mulai *</label>
                            <input type="date" name="period_start" id="period_start" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Periode Sampai *</label>
                            <input type="date" name="period_end" id="period_end" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Jatuh Tempo (opsional)</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                    </div>
                </div>

                <button type="button" id="btn-preview" class="btn btn-info">
                    <i class="fa fa-search"></i> Tampilkan Nota
                </button>

                <hr>
                <div id="preview-wrap" style="display:none;">
                    <h4>Nota yang akan digabung</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No. AP</th>
                                <th>No. Invoice Supplier</th>
                                <th>No. PO</th>
                                <th>Tgl Invoice</th>
                                <th class="text-right">Jumlah</th>
                                <th class="text-right">Sisa</th>
                            </tr>
                        </thead>
                        <tbody id="preview-rows"></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Total Sisa</th>
                                <th class="text-right" id="preview-total">0</th>
                            </tr>
                        </tfoot>
                    </table>
                    <button type="submit" class="btn btn-success btn-flat" id="btn-submit" disabled>
                        <i class="fa fa-paper-plane"></i> Buat Kontra Bon
                    </button>
                </div>
                <div id="preview-empty" style="display:none;" class="alert alert-warning">
                    Tidak ada nota outstanding pada rentang tanggal ini untuk supplier tersebut.
                </div>
            </form>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('.select2').select2();

    $('#btn-preview').on('click', function() {
        var supplier_id = $('#supplier_id').val();
        var period_start = $('#period_start').val();
        var period_end = $('#period_end').val();

        if (!supplier_id || !period_start || !period_end) {
            Swal.fire({ icon: 'error', title: 'Lengkapi supplier dan rentang tanggal dulu.' });
            return;
        }

        $.post('<?= site_url('ap-kontra-bon/preview') ?>', {
            supplier_id: supplier_id,
            period_start: period_start,
            period_end: period_end
        }, function(res) {
            if (!res.success || res.invoices.length === 0) {
                $('#preview-wrap').hide();
                $('#preview-empty').show();
                $('#btn-submit').prop('disabled', true);
                return;
            }

            var rows = '';
            res.invoices.forEach(function(inv) {
                rows += '<tr>' +
                    '<td>' + inv.ap_no + '</td>' +
                    '<td>' + (inv.supplier_invoice_no || '-') + '</td>' +
                    '<td>' + (inv.po_number || '-') + '</td>' +
                    '<td>' + fmtTglID(inv.invoice_date) + '</td>' +
                    '<td class="text-right">' + parseInt(inv.amount).toLocaleString('id-ID') + '</td>' +
                    '<td class="text-right">' + parseInt(inv.outstanding_amount).toLocaleString('id-ID') + '</td>' +
                    '</tr>';
            });
            $('#preview-rows').html(rows);
            $('#preview-total').text(parseInt(res.total).toLocaleString('id-ID'));
            $('#preview-empty').hide();
            $('#preview-wrap').show();
            $('#btn-submit').prop('disabled', false);
        }, 'json');
    });

    // Reset preview kalau filter berubah lagi (harus preview ulang sebelum submit)
    $('#supplier_id, #period_start, #period_end').on('change', function() {
        $('#preview-wrap').hide();
        $('#preview-empty').hide();
        $('#btn-submit').prop('disabled', true);
    });
});
</script>
