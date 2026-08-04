<section class="content-header">
    <h1>Form Tambah Barang
        <small>Data Barang</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i></a></li>
        <li class="active">Tambah Barang</li>
    </ol>
</section>

<section class="content">
    <?php $this->view('massage') ?>
    <div class="box">
        <div class="box-header">
            <h3 class="box-title"><?= ucfirst($page) ?> Barang</h3>
            <div class="pull-right">
                <a href="<?= site_url('item') ?>" class="btn btn-warning btn-flat">
                    <i class="fa fa-undo"></i> Kembali
                </a>
            </div>
        </div>

        <div class="box-body">
            <div class="row">
                <div class="col-md-4 col-md-offset-4">
                    <form action="<?= site_url('item/process') ?>" method="post" id="addItemForm">
                        <div class="form-group">
                            <input type="hidden" name="id" value="<?= $row->item_id ?>">
                            <label>Barcode *</label>
                            <input type="text" name="barcode" value="<?= isset($new_barcode) ? $new_barcode : $row->barcode ?>" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Nama Barang *</label>
                            <input type="text" name="nama_item" value="<?= $row->nama_item ?>" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Supplier *</label>
                            <?php echo form_dropdown('supplier', $supplier, $selectedsupplier, ['class' => 'form-control', 'id' => 'supplierDropdown', 'required' => 'required']) ?>
                        </div>
                        <div class="form-group">
                            <label>Kategori *</label>
                            <?php echo form_dropdown('category', $category, $selectedcategory, ['class' => 'form-control','id' => 'categoryDropdown', 'required' => 'required']) ?>
                        </div>
                        <div class="form-group">
                            <label>Units *</label>
                            <?php echo form_dropdown('unit', $unit, $selectedunit, ['class' => 'form-control', 'id' => 'unitDropdown', 'required' => 'required']) ?>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Modal *</label>
                                    <input type="text" name="modal" id="modal" value="<?= number_format($row->modal, 0, ',', '.') ?>" class="form-control modalInput" required>
                                </div>
                                <div class="col-md-6">
                                    <label>PK *</label>
                                    <input type="text" name="pk" id="pk" value="<?= $row->pk ?>" class="form-control" required>
                                </div>   
                            </div>        
                        </div>
                        <div class="form-group">
                            <label>Harga *</label>
                            <input type="text" name="price" id="price" value="<?= number_format($row->price, 0, ',', '.') ?>" class="form-control price" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="<?= $page ?>" class="btn btn-success btn-flat">
                                <i class="fa fa-paper-plane"></i> Simpan
                            </button>
                            <button type="Reset" class="btn btn-flat">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($page === 'edit' && isset($supplier_barang)): ?>
<section class="content" style="padding-top:0">
<div class="box box-default" style="border-top:3px solid #3c8dbc">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-truck" style="color:#3c8dbc"></i> Supplier Terdaftar
            <small class="text-muted" style="font-size:12px;margin-left:6px">— harga beli per supplier</small>
        </h3>
    </div>
    <div class="box-body">

        <!-- Tabel supplier yang sudah ada -->
        <table class="table table-condensed table-bordered" id="tbl-supplier-barang" style="margin-bottom:14px">
            <thead style="background:#f4f6f9;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#555">
                <tr>
                    <th style="padding:8px 12px">Supplier</th>
                    <th style="padding:8px 12px;width:160px;text-align:right">Harga Beli (Rp)</th>
                    <th style="padding:8px 12px;width:160px">PK / Kode Beli</th>
                    <th style="padding:8px 12px;width:80px;text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody id="supplier-list">
                <?php foreach ($supplier_barang as $sb): ?>
                <tr data-sid="<?= $sb->supplier_id ?>">
                    <td style="padding:8px 12px">
                        <?= htmlspecialchars($sb->nama_supplier) ?>
                        <?php if ($sb->supplier_id == $row->supplier_id): ?>
                        <span class="label label-info" style="font-size:10px;margin-left:4px">Terbaru</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:8px 12px;text-align:right"><?= number_format($sb->harga_beli, 0, ',', '.') ?></td>
                    <td style="padding:8px 6px">
                        <input type="text" class="form-control input-sm kode-beli-input"
                            value="<?= htmlspecialchars($sb->kode_beli ?? '') ?>"
                            data-sid="<?= $sb->supplier_id ?>"
                            placeholder="e.g. SLMY3"
                            style="font-family:monospace">
                    </td>
                    <td style="padding:8px 12px;text-align:center">
                        <?php if ($sb->supplier_id != $row->supplier_id): ?>
                        <button type="button" class="btn btn-danger btn-xs btn-remove-supplier"
                            data-sid="<?= $sb->supplier_id ?>"
                            data-nama="<?= htmlspecialchars($sb->nama_supplier) ?>">
                            <i class="fa fa-trash"></i>
                        </button>
                        <?php else: ?>
                        <span class="text-muted" style="font-size:11px">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($supplier_barang)): ?>
                <tr id="empty-row"><td colspan="4" class="text-center text-muted">Belum ada supplier</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Form tambah supplier -->
        <div style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap">
            <div style="flex:2;min-width:180px">
                <label style="font-size:12px;color:#555;font-weight:600;display:block;margin-bottom:3px">Tambah Supplier</label>
                <select id="new-supplier-id" class="form-control select2-supplier" style="width:100%">
                    <option value="">— Pilih Supplier —</option>
                    <?php foreach ($all_suppliers as $s): ?>
                    <option value="<?= $s->supplier_id ?>"><?= htmlspecialchars($s->nama_supplier) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="width:140px">
                <label style="font-size:12px;color:#555;font-weight:600;display:block;margin-bottom:3px">Harga Beli (Rp)</label>
                <input type="text" id="new-supplier-price" class="form-control" placeholder="0">
            </div>
            <div style="width:140px">
                <label style="font-size:12px;color:#555;font-weight:600;display:block;margin-bottom:3px">PK / Kode Beli</label>
                <input type="text" id="new-supplier-kode" class="form-control" placeholder="e.g. SLMY3" style="font-family:monospace">
            </div>
            <div>
                <button type="button" class="btn btn-info btn-sm" id="btn-add-supplier">
                    <i class="fa fa-plus"></i> Tambah
                </button>
            </div>
        </div>
    </div>
</div>
</section>

<script>
$(function () {
    var itemId = <?= $row->item_id ?>;

    function toast(icon, msg) {
        Swal.fire({ toast:true, position:'top-end', icon:icon, title:msg,
            showConfirmButton:false, timer:3000, timerProgressBar:true });
    }
    function formatRp(val) {
        return String(val).replace(/[^0-9]/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.');
    }

    $('.select2-supplier').select2({ placeholder:'— Pilih Supplier —', allowClear:true, width:'100%' });

    $(document).on('input', '#new-supplier-price', function () {
        $(this).val(formatRp($(this).val()));
    });

    $('#btn-add-supplier').on('click', function () {
        var sid   = $('#new-supplier-id').val();
        var price = parseInt($('#new-supplier-price').val().replace(/\./g,'')) || 0;
        if (!sid)    { toast('warning', 'Pilih supplier terlebih dahulu.'); return; }
        if (!price)  { toast('warning', 'Isi harga beli.'); return; }

        // Cek duplikat
        if ($('#supplier-list tr[data-sid="' + sid + '"]').length) {
            toast('warning', 'Supplier ini sudah terdaftar.'); return;
        }

        var kode  = $('#new-supplier-kode').val().trim();
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        $.post('<?= site_url('item/add_supplier') ?>', {
            item_id:     itemId,
            supplier_id: sid,
            harga_beli:  price,
            kode_beli:   kode,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>',
        }, function (res) {
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Tambah');
            if (res.status !== 'success') { toast('error', res.message || 'Gagal.'); return; }

            $('#empty-row').remove();
            var sname = $('<s>').text(res.nama_supplier).html();
            var row = '<tr data-sid="' + res.supplier_id + '">'
                    + '<td style="padding:8px 12px">' + sname + '</td>'
                    + '<td style="padding:8px 12px;text-align:right">' + parseInt(res.harga_beli).toLocaleString('id-ID') + '</td>'
                    + '<td style="padding:8px 6px"><input type="text" class="form-control input-sm kode-beli-input"'
                    + ' value="' + $('<s>').text(res.kode_beli || '').html() + '"'
                    + ' data-sid="' + res.supplier_id + '" placeholder="e.g. SLMY3" style="font-family:monospace"></td>'
                    + '<td style="padding:8px 12px;text-align:center">'
                    + '<button type="button" class="btn btn-danger btn-xs btn-remove-supplier"'
                    + ' data-sid="' + res.supplier_id + '" data-nama="' + sname + '">'
                    + '<i class="fa fa-trash"></i></button></td></tr>';
            $('#supplier-list').append(row);
            $('#new-supplier-id').val('').trigger('change');
            $('#new-supplier-price').val('');
            $('#new-supplier-kode').val('');
            toast('success', res.nama_supplier + ' ditambahkan.');
        }, 'json');
    });

    $(document).on('click', '.btn-remove-supplier', function () {
        var $btn = $(this);
        var sid  = $btn.data('sid');
        var nama = $btn.data('nama');
        Swal.fire({
            title: 'Hapus ' + nama + '?',
            text : 'Supplier ini tidak akan muncul sebagai pilihan pemesanan.',
            icon : 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText : 'Batal',
            confirmButtonColor: '#dd4b39',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $btn.prop('disabled', true);
            $.post('<?= site_url('item/remove_supplier') ?>', {
                item_id:     itemId,
                supplier_id: sid,
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>',
            }, function (res) {
                if (res.status !== 'success') {
                    toast('error', res.message || 'Gagal.'); $btn.prop('disabled', false); return;
                }
                $btn.closest('tr').fadeOut(200, function () {
                    $(this).remove();
                    if ($('#supplier-list tr').length === 0) {
                        $('#supplier-list').append('<tr id="empty-row"><td colspan="4" class="text-center text-muted">Belum ada supplier</td></tr>');
                    }
                });
                toast('success', nama + ' dihapus dari daftar.');
            }, 'json');
        });
    });

    // Auto-save kode_beli saat blur
    $(document).on('change blur', '.kode-beli-input', function () {
        var $inp = $(this);
        var sid  = $inp.data('sid');
        var kode = $inp.val().trim();
        $inp.css('border-color', '');
        $.post('<?= site_url('item/update_supplier_kode') ?>', {
            item_id:     itemId,
            supplier_id: sid,
            kode_beli:   kode,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>',
        }, function (res) {
            $inp.css('border-color', res.status === 'success' ? '#00a65a' : '#dd4b39');
            setTimeout(function () { $inp.css('border-color', ''); }, 1500);
        }, 'json');
    });
});
</script>
<?php endif; ?>

<script>
    $(document).ready(function () {
    $('#supplierDropdown').select2({
        placeholder: 'Pilih Supplier',
        allowClear: true
    });
    $('#categoryDropdown').select2({
        placeholder: 'Pilih Kategori',
        allowClear: true
    });
    $('#unitDropdown').select2({
        placeholder: 'Pilih Unit',
        allowClear: true
    });

    function formatNumber(input) {
        return input.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function unformatNumber(input) {
        return input.replace(/\./g, "");
    }

    // Auto-format modal and price inputs
    $(document).on('input', '.modalInput, .price', function () {
        let value = $(this).val();
        let unformattedValue = unformatNumber(value);
        $(this).val(formatNumber(unformattedValue));
    });

    // Event listener untuk modal input dan auto-generate PK
    $(document).on('input', '#modal', function () {
        let modalValue = unformatNumber($(this).val());
        let pkOutput = generatePK(modalValue);
        $('input[name="pk"]').val(pkOutput);
    });

    function generatePK(value) {
        let mapping = {
            '0': 'Y', '1': 'S', '2': 'I', '3': 'T', '4': 'O',
            '5': 'M', '6': 'P', '7': 'U', '8': 'L', '9': 'X'
        };
        let zeroCount = 0;
        let pkOutput = '';

        for (let i = 0; i < value.length; i++) {
            let char = value.charAt(i);
            if (char === '0') {
                zeroCount++;
            } else {
                if (zeroCount > 1) {
                    pkOutput += 'Y' + zeroCount;
                } else if (zeroCount === 1) {
                    pkOutput += 'Y';
                }
                zeroCount = 0;  // Reset counter
                pkOutput += mapping[char] || char; // Jika tidak ada di mapping, tetap gunakan angka asli
            }
        }

        // Akhir string, jika masih ada nol bertumpuk
        if (zeroCount > 1) {
            pkOutput += 'Y' + zeroCount;
        } else if (zeroCount === 1) {
            pkOutput += 'Y';
        }

        return pkOutput;
    }

    // Remove formatting before form submission
    $('#addItemForm').on('submit', function (e) {
        $('.modalInput, .price').each(function () {
            let formattedValue = $(this).val();
            let unformattedValue = unformatNumber(formattedValue);
            $(this).val(unformattedValue); // Set the raw number back to the input
        });
    });
});

</script>

