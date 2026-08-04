<section class="content-header">
    <h1>Update Stock</h1>
    <small>Tambah Stock Barang Masuk</small>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i></a></li>
        <li class="active">Stock in</li>
    </ol>
</section>
<section class="content">
    <?php $this->view('massage') ?>
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Tambah Stock</h3>
            <div class="pull-right">
                <a href="<?= site_url('stock/in') ?>" class="btn btn-warning btn-flat">
                    <i class="fa fa-undo"></i> Kembali
                </a>
            </div>
        </div>
        <div class="box-body">
            <form action="<?= site_url('stock/process') ?>" method="post" id="addStockForm">
                <div class="row" id="itemRows">
                    <!-- Placeholder for dynamic rows -->
                </div>
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-primary btn-flat" id="addRow">
                        <i class="fa fa-plus"></i> Tambah Barang
                    </button>
                    <button type="submit" name="in_add_multiple" class="btn btn-success btn-flat">
                        <i class="fa fa-paper-plane"></i> Simpan Semua
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Modal Pilih Barang -->
<div class="modal fade" id="modal-item">
    <div class="modal-dialog" style="width: 80%;">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><i class="fa fa-cubes"></i> Pilih Barang</h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body table-responsive">
                <table id="table1" class="table table-striped table-bordered dt-responsive" style="width:100%">
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Nama Barang</th>
                            <th>Supplier</th>
                            <th>Unit</th>
                            <th>Modal</th>
                            <th>PK</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($item as $data): ?>
                        <tr>
                            <td><?= $data->barcode ?></td>
                            <td><?= $data->nama_item ?></td>
                            <td><?= $data->nama_supplier ?></td>
                            <td><?= $data->nama_unit ?></td>
                            <td class="text-right"><?=indo_currency($data->modal) ?></td>
                            <td class="text-right"><?= $data->pk ?></td>
                            <td class="text-right"><?= $data->stock ?></td>
                            <td class="text-right">
                                <button class="btn btn-xs btn-info" id="select"
                                    data-id="<?= $data->item_id ?>"
                                    data-barcode="<?= $data->barcode ?>"
                                    data-modal="<?= $data->modal ?>"
                                    data-pk="<?= $data->pk ?>"
                                    data-nama_item="<?= $data->nama_item ?>"
                                    data-supplier_id="<?= $data->supplier_id ?>"
                                    data-nama_supplier="<?= $data->nama_supplier ?>"
                                    data-nama_unit="<?= $data->nama_unit ?>"
                                    data-stock="<?= $data->stock ?>">
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
$(document).ready(function () {
    // Variabel untuk menyimpan referensi baris aktif
    let activeRow = null;

    // Function to generate a new row
    function generateRow() {
        return `
            <div class="col-md-6 col-lg-4 stock-row">
                <div class="card mb-4" style="border: 1px solid #ddd; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Barang Baru</h5>
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tanggal *</label>
                            <input type="date" name="date[]" value="<?= date('Y-m-d') ?>" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Barcode *</label>
                            <div class="input-group">
                                <input type="hidden" name="item_id[]" class="itemId">
                                <input type="text" name="barcode[]" class="form-control barcodeInput" required readonly>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-info btn-flat select-item" data-toggle="modal" data-target="#modal-item">
                                        <i class="fa fa-search"></i> Pilih Barang
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nama Barang *</label>
                            <input type="text" name="nama_item[]" class="form-control namaItem" required readonly>
                        </div>
                        <div class="form-group">
                            <label>Supplier</label>
                            <select name="supplier_id[]" class="form-control supplierSelect">
                                <option value="">— Pilih setelah barang dipilih —</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Satuan *</label>
                            <input type="text" name="nama_unit[]" class="form-control unitName" required readonly>
                        </div>
                        <div class="form-group">
                            <label>Modal *</label>
                            <input type="text" name="modal[]" class="form-control modalOutput" required >
                        </div>
                        <div class="form-group">
                            <label>PK *</label>
                            <input type="text" name="pk[]" class="form-control pkOutput" required >
                        </div>
                        <div class="form-group">
                            <label>Stok Awal *</label>
                            <input type="text" name="stock[]" class="form-control stockInitial" required readonly>
                        </div>
                         <div class="form-group">
                            <label>Detail *</label>
                            <input type="text" name="detail[]" class="form-control" placeholder="Contoh: Datang Barang">
                        </div>
                        <div class="form-group">
                            <label>Qty *</label>
                            <input type="number" name="qty[]" class="form-control qtyInput" placeholder="Masukkan jumlah" required>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Add the first row initially
    $('#itemRows').append(generateRow());

    // Add new rows dynamically with validation
    $('#addRow').on('click', function () {
        // Check if the first row's barcode is empty
        var firstBarcode = $('.stock-row').first().find('.barcodeInput').val();
        if (!firstBarcode) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: 'Silakan isi data barang pada form pertama terlebih dahulu.',
            });
            return;
        }
        // Add new row
        $('#itemRows').append(generateRow());
    });

    // Remove row functionality
    $(document).on('click', '.remove-row', function () {
        $(this).closest('.stock-row').remove();
    });

    // Set active row when "Pilih Barang" button is clicked
    $(document).on('click', '.select-item', function () {
        activeRow = $(this).closest('.stock-row'); // Simpan referensi baris aktif
    });

    // Populate item details when selecting an item from modal
    $(document).on('click', '#select', function () {
        if (!activeRow) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Tidak ada baris aktif yang dipilih.',
            });
            return;
        }

        var item_id = $(this).data('id');
        var barcode = $(this).data('barcode');
        var modal = $(this).data('modal');
        var pk = $(this).data('pk');
        var nama_item = $(this).data('nama_item');
        var supplier_id = $(this).data('supplier_id');
        var nama_supplier = $(this).data('nama_supplier');
        var nama_unit = $(this).data('nama_unit');
        var stock = $(this).data('stock');

        // Populate fields in the active row
        activeRow.find('.itemId').val(item_id);
        activeRow.find('.barcodeInput').val(barcode);
        activeRow.find('.modalOutput').val(formatNumber(modal));
        activeRow.find('.pkOutput').val(pk);
        activeRow.find('.namaItem').val(nama_item);
        activeRow.find('.unitName').val(nama_unit);
        activeRow.find('.stockInitial').val(stock);

        // Load suppliers dari supplier_barang untuk item ini
        var $sel = activeRow.find('.supplierSelect');
        $sel.empty().append('<option value="">Memuat...</option>');
        $.post('<?= site_url('stock-review/get_item_suppliers') ?>', { item_id: item_id }, function (suppliers) {
            $sel.empty().append('<option value="">— Pilih Supplier —</option>');
            suppliers.forEach(function (s) {
                var selected = (s.supplier_id == supplier_id) ? ' selected' : '';
                $sel.append('<option value="' + s.supplier_id + '"' + selected + '>'
                    + s.nama_supplier
                    + (s.harga_beli > 0 ? ' (Rp ' + parseInt(s.harga_beli).toLocaleString('id-ID') + ')' : '')
                    + '</option>');
            });
            // Fallback: jika item hanya punya 1 supplier, pilih otomatis
            if (suppliers.length === 1) $sel.val(suppliers[0].supplier_id);
        }, 'json');

        // Close modal
        $('#modal-item').modal('hide');

        // Reset activeRow after use
        activeRow = null;
    });
    // Format number functions
    function formatNumber(input) {
        return input.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function unformatNumber(input) {
        return input.replace(/\./g, "");
    }

    // Auto-format modal and price inputs
    $(document).on('input', '.modalOutput', function () {
        let value = $(this).val();
        let unformattedValue = unformatNumber(value);
        $(this).val(formatNumber(unformattedValue));
    });
    // Auto-generate PK based on Modal input
    $(document).on('input', '.modalOutput', function () {
            let value = $(this).val();
            let unformattedValue = unformatNumber(value);
            let modalOutput = unformattedValue.toUpperCase();
            let pkOutput = '';
            let mapping = {
                '0': 'Y', '1': 'S', '2': 'I', '3': 'T', '4': 'O',
                '5': 'M', '6': 'P', '7': 'U', '8': 'L', '9': 'X'
            };
            let zeroCount = 0;
            for (let i = 0; i < modalOutput.length; i++) {
                let char = modalOutput.charAt(i);
                if (char === '0') {
                    zeroCount++;
                } else {
                    if (zeroCount > 1) {
                        pkOutput += 'Y' + zeroCount;
                    } else if (zeroCount === 1) {
                        pkOutput += 'Y';
                    }
                    zeroCount = 0;
                    if (mapping[char] !== undefined) {
                        pkOutput += mapping[char];
                    } else {
                        pkOutput += char;
                    }
                }
            }
            if (zeroCount > 1) {
                pkOutput += 'Y' + zeroCount;
            } else if (zeroCount === 1) {
                pkOutput += 'Y';
            }
            $(this).closest('.col-md-6').find('.pkOutput').val(pkOutput);
        });
        $('#addStockForm').on('submit', function (e) {
            $('.modalOutput').each(function () {
                let formattedValue = $(this).val();
                let unformattedValue = unformatNumber(formattedValue);
                $(this).val(unformattedValue); // Set the raw number back to the input
            });
        });
});
</script>