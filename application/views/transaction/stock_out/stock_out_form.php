<section class="content-header">
    <h1>Barang Keluar <small>Tambah Barang Keluar</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?= site_url('stock/out') ?>">Stock Out</a></li>
        <li class="active">Tambah</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-arrow-up text-red"></i> Form Barang Keluar</h3>
                    <div class="box-tools pull-right">
                        <a href="<?= site_url('stock/out') ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <form action="<?= site_url('stock/process') ?>" method="post">

                        <div class="form-group">
                            <label>Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Barang <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="hidden" name="item_id" id="item_id">
                                <input type="text"   name="barcode" id="barcode" class="form-control"
                                       placeholder="Scan barcode atau pilih barang..." required autofocus>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-info btn-flat"
                                            data-toggle="modal" data-target="#modal-item">
                                        <i class="fa fa-search"></i> Pilih
                                    </button>
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Nama Barang</label>
                            <input type="text" name="nama_item" id="nama_item" class="form-control" readonly>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Supplier</label>
                                    <input type="text" name="nama_supplier" id="nama_supplier" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Satuan</label>
                                    <input type="text" name="nama_unit" id="nama_unit" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Stok Saat Ini</label>
                            <input type="text" name="stock" id="stock" class="form-control" readonly>
                        </div>

                        <div class="form-group">
                            <label>Keterangan / Detail <span class="text-danger">*</span></label>
                            <input type="text" name="detail" class="form-control"
                                   placeholder="Misal: Rusak, Hilang, Retur ke supplier..." required>
                        </div>

                        <div class="form-group">
                            <label>Qty Keluar <span class="text-danger">*</span></label>
                            <input type="number" name="qty" id="qty" class="form-control"
                                   min="1" placeholder="0" required>
                            <span class="help-block" id="qty-info" style="color:#888;"></span>
                        </div>

                        <div class="form-group" style="margin-top:20px;">
                            <button type="submit" name="out_add" class="btn btn-success btn-flat">
                                <i class="fa fa-paper-plane"></i> Simpan
                            </button>
                            <button type="reset" class="btn btn-default btn-flat" onclick="resetForm()">
                                <i class="fa fa-refresh"></i> Reset
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Pilih Barang -->
<div class="modal fade" id="modal-item" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#222d32; border-radius:4px 4px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:.8;">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title" style="color:#fff; font-size:15px;">
                    <i class="fa fa-search" style="color:#00a65a;"></i> Pilih Barang
                </h4>
            </div>
            <div class="modal-body" style="padding:16px;">
                <div class="form-group" style="position:relative;">
                    <input type="text" id="modal_item_search" class="form-control" autocomplete="off"
                           placeholder="Ketik barcode / nama barang... (min 2 huruf)" autofocus>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped"
                           id="tbl-modal-item" style="width:100%">
                        <thead>
                            <tr>
                                <th>Barcode</th>
                                <th>Nama Barang</th>
                                <th>Supplier</th>
                                <th>Satuan</th>
                                <th class="text-right">Harga</th>
                                <th class="text-center">Stok</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="modal_item_result">
                            <tr><td colspan="7" class="text-center text-muted">Ketik minimal 2 huruf untuk mencari barang...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    // ── Pencarian barang via AJAX (agar modal tidak perlu memuat semua barang sekaligus) ──
    function renderModalItemRows(items) {
        var $tbody = $('#modal_item_result').empty();

        if (!items.length) {
            $tbody.append('<tr><td colspan="7" class="text-center text-muted">Barang tidak ditemukan</td></tr>');
            return;
        }

        items.forEach(function (d) {
            var stockLabel;
            if (d.stock == 0) {
                stockLabel = $('<span class="label label-danger">Habis</span>');
            } else if (d.stock <= 3) {
                stockLabel = $('<span class="label label-warning"></span>').text(d.stock);
            } else {
                stockLabel = $('<span class="label label-success"></span>').text(d.stock);
            }

            var $btn = $('<button type="button" class="btn btn-primary btn-xs btn-pilih"><i class="fa fa-check"></i> Pilih</button>')
                .attr('data-id', d.item_id)
                .attr('data-barcode', d.barcode)
                .attr('data-nama', d.nama_item)
                .attr('data-supplier', d.nama_supplier || '-')
                .attr('data-unit', d.nama_unit || '-')
                .attr('data-stock', d.stock);
            if (d.stock <= 0) $btn.attr('disabled', 'disabled');

            var $row = $('<tr></tr>').append(
                $('<td></td>').text(d.barcode),
                $('<td></td>').text(d.nama_item),
                $('<td></td>').text(d.nama_supplier || '-'),
                $('<td></td>').text(d.nama_unit || '-'),
                $('<td class="text-right"></td>').text(formatNumber(d.price)),
                $('<td class="text-center"></td>').append(stockLabel),
                $('<td class="text-center"></td>').append($btn)
            );
            $tbody.append($row);
        });
    }

    function formatNumber(input) {
        return input.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    var modalItemSearchTimer;
    $(document).on('keyup', '#modal_item_search', function () {
        clearTimeout(modalItemSearchTimer);
        var keyword = $(this).val().trim();

        if (keyword.length < 2) {
            $('#modal_item_result').html('<tr><td colspan="7" class="text-center text-muted">Ketik minimal 2 huruf untuk mencari barang...</td></tr>');
            return;
        }

        modalItemSearchTimer = setTimeout(function () {
            $.post('<?= site_url('stock/search_item') ?>', { keyword: keyword }, function (items) {
                renderModalItemRows(items);
            }, 'json');
        }, 300);
    });

    // Reset & fokus ke pencarian setiap kali modal dibuka
    $('#modal-item').on('shown.bs.modal', function() {
        $('#modal_item_search').val('').focus();
        $('#modal_item_result').html('<tr><td colspan="7" class="text-center text-muted">Ketik minimal 2 huruf untuk mencari barang...</td></tr>');
    });

    // Pilih barang dari modal
    $(document).on('click', '.btn-pilih', function() {
        $('#item_id').val($(this).data('id'));
        $('#barcode').val($(this).data('barcode'));
        $('#nama_item').val($(this).data('nama'));
        $('#nama_supplier').val($(this).data('supplier'));
        $('#nama_unit').val($(this).data('unit'));

        var stok = $(this).data('stock');
        $('#stock').val(stok);
        $('#qty').attr('max', stok);
        $('#qty-info').text('Maks. qty keluar: ' + stok);
        $('#qty').focus();

        $('#modal-item').modal('hide');
    });

    // Validasi qty tidak melebihi stok
    $('#qty').on('input', function() {
        var max = parseInt($('#stock').val()) || 0;
        var val = parseInt($(this).val()) || 0;
        if (max > 0 && val > max) {
            $(this).val(max);
            $('#qty-info').css('color', '#dd4b39').text('Qty tidak boleh melebihi stok (' + max + ')');
        } else {
            $('#qty-info').css('color', '#888');
        }
    });

    // Reset form
    window.resetForm = function() {
        $('#item_id, #barcode, #nama_item, #nama_supplier, #nama_unit, #stock').val('');
        $('#qty').val('').removeAttr('max');
        $('#qty-info').text('');
    };

});
</script>
