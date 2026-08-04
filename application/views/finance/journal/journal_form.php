<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1>Jurnal Manual
        <small>Penyesuaian / Adjustment</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
        <li><a href="<?= site_url('journal') ?>">Jurnal Umum</a></li>
        <li class="active">Tambah</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Buat Jurnal Manual</h3>
            <div class="pull-right">
                <a href="<?= site_url('journal') ?>" class="btn btn-warning btn-flat btn-sm">
                    <i class="fa fa-undo"></i> Kembali
                </a>
            </div>
        </div>
        <div class="box-body">
            <form action="<?= site_url('journal/add') ?>" method="post" id="form-journal">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal *</label>
                            <input type="date" name="journal_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group">
                            <label>Keterangan *</label>
                            <input type="text" name="description" class="form-control" required>
                        </div>
                    </div>
                </div>

                <table class="table table-bordered" id="table-lines">
                    <thead>
                        <tr>
                            <th width="30%">Akun</th>
                            <th width="18%" class="text-right">Debit</th>
                            <th width="18%" class="text-right">Kredit</th>
                            <th>Catatan</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody id="line-rows">
                        <!-- baris awal ditambahkan via JS -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-right">Total</th>
                            <th class="text-right" id="total-debit">0</th>
                            <th class="text-right" id="total-kredit">0</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
                <button type="button" class="btn btn-default btn-sm" id="btn-add-row">
                    <i class="fa fa-plus"></i> Tambah Baris
                </button>

                <hr>
                <button type="submit" class="btn btn-success btn-flat">
                    <i class="fa fa-paper-plane"></i> Posting Jurnal
                </button>
            </form>
        </div>
    </div>
</section>

<script>
var coaOptions = '<option value="">- Pilih Akun -</option>';
<?php foreach ($coa_list as $c): ?>
coaOptions += '<option value="<?= $c->coa_id ?>"><?= $c->coa_code . ' - ' . addslashes($c->coa_name) ?></option>';
<?php endforeach; ?>

function addRow() {
    var row = '<tr>' +
        '<td><select name="coa_id[]" class="form-control">' + coaOptions + '</select></td>' +
        '<td><input type="text" name="debit[]" class="form-control text-right input-rupiah" value="0"></td>' +
        '<td><input type="text" name="kredit[]" class="form-control text-right input-rupiah" value="0"></td>' +
        '<td><input type="text" name="notes[]" class="form-control"></td>' +
        '<td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-remove-row"><i class="fa fa-trash"></i></button></td>' +
        '</tr>';
    $('#line-rows').append(row);
}

function recalcTotal() {
    var totalDebit = 0, totalKredit = 0;
    $('input[name="debit[]"]').each(function() { totalDebit += parseInt($(this).val().replace(/\./g, '')) || 0; });
    $('input[name="kredit[]"]').each(function() { totalKredit += parseInt($(this).val().replace(/\./g, '')) || 0; });
    $('#total-debit').text(totalDebit.toLocaleString('id-ID'));
    $('#total-kredit').text(totalKredit.toLocaleString('id-ID'));
}

$(document).ready(function() {
    addRow();
    addRow();

    $('#btn-add-row').on('click', addRow);

    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        recalcTotal();
    });

    $(document).on('input', '.input-rupiah', function() {
        var val = $(this).val().replace(/\D/g, '');
        $(this).val(parseInt(val || 0).toLocaleString('id-ID').replace(/,/g, '.'));
        recalcTotal();
    });
});
</script>
