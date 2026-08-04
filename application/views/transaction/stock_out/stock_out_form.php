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
                    <tbody>
                        <?php foreach ($item as $d): ?>
                        <tr>
                            <td><?= $d->barcode ?></td>
                            <td><?= htmlspecialchars($d->nama_item) ?></td>
                            <td><?= $d->nama_supplier ?? '-' ?></td>
                            <td><?= $d->nama_unit ?? '-' ?></td>
                            <td class="text-right"><?= indo_currency($d->price) ?></td>
                            <td class="text-center">
                                <?php if ($d->stock == 0): ?>
                                    <span class="label label-danger">Habis</span>
                                <?php elseif ($d->stock <= 3): ?>
                                    <span class="label label-warning"><?= $d->stock ?></span>
                                <?php else: ?>
                                    <span class="label label-success"><?= $d->stock ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-xs btn-pilih"
                                    data-id="<?= $d->item_id ?>"
                                    data-barcode="<?= $d->barcode ?>"
                                    data-nama="<?= htmlspecialchars($d->nama_item, ENT_QUOTES) ?>"
                                    data-supplier="<?= htmlspecialchars($d->nama_supplier ?? '-', ENT_QUOTES) ?>"
                                    data-unit="<?= htmlspecialchars($d->nama_unit ?? '-', ENT_QUOTES) ?>"
                                    data-stock="<?= $d->stock ?>"
                                    <?= $d->stock <= 0 ? 'disabled' : '' ?>>
                                    <i class="fa fa-check"></i> Pilih
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    // Pre-inisialisasi DataTables saat halaman load (bukan saat modal dibuka)
    // agar modal langsung siap tanpa delay
    var modalTable = $('#tbl-modal-item').DataTable({
        language: {
            search      : 'Cari:',
            lengthMenu  : 'Tampilkan _MENU_ data',
            info        : 'Data _START_–_END_ dari _TOTAL_',
            zeroRecords : 'Barang tidak ditemukan',
            paginate    : { previous: '‹', next: '›' }
        },
        columnDefs : [{ orderable: false, targets: [5, 6] }],
        order      : [[0, 'asc']],
        pageLength : 10,
        autoWidth  : false
    });

    // Sesuaikan lebar kolom ketika modal benar-benar terlihat
    $('#modal-item').on('shown.bs.modal', function() {
        modalTable.columns.adjust().draw(false);
        // Fokus ke search box agar langsung bisa ketik
        setTimeout(function() {
            $('#modal-item .dataTables_filter input').focus();
        }, 100);
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
