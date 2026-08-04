<section class="content-header">
    <h1>Update Stock Multiple</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i></a></li>
        <li class="active">Stock in</li>
    </ol>
</section>
<section class="content">
    <?php $this->view('massage') ?>
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Tambah Stock Multiple</h3>
            <div class="pull-right">
                <a href="<?= site_url('item') ?>" class="btn btn-warning btn-flat">
                    <i class="fa fa-undo"></i> Kembali
                </a>
            </div>
        </div>
        <div class="box-body">
            <form action="<?= site_url('stock/process') ?>" method="post" id="addStockForm">
                <div class="row" id="itemRows">
                    <!-- Placeholder for dynamic rows -->
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card mb-4" style="border: 1px solid #ddd; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Barang <?= $item->nama_item ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Tanggal *</label>
                                            <input type="date" name="date[]" value="<?= date('Y-m-d') ?>" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Barcode *</label>
                                            <input type="text" name="barcode[]" class="form-control barcodeInput" value="<?= $item->barcode ?>" required readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Nama Barang *</label>
                                            <input type="text" name="nama_item[]" class="form-control namaItem" value="<?= $item->nama_item ?>" required readonly>
                                            <input type="hidden" name="item_id[]" class="itemId" value="<?= $item->item_id ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Supplier *</label>
                                            <?php
                                            // Cari nama supplier berdasarkan supplier_id barang
                                            $supplier_name = '';
                                            foreach ($supplier as $s) {
                                                if ($s->supplier_id == $item->supplier_id) {
                                                    $supplier_name = $s->nama_supplier;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <input type="text" name="nama_supplier[]" class="form-control supplierName" value="<?= $supplier_name ?>" required readonly>
                                            <input type="hidden" name="supplier_id[]" class="supplierId" value="<?= $item->supplier_id ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Satuan *</label>
                                            <?php
                                            // Cari nama unit berdasarkan unit_id barang
                                            $unit_name = '';
                                            foreach ($unit as $u) {
                                                if ($u->unit_id == $item->unit_id) {
                                                    $unit_name = $u->nama_unit;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <input type="text" name="nama_unit[]" class="form-control unitName" value="<?= $unit_name ?>" required readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Modal *</label>
                                            <input type="text" name="modal[]" class="form-control modalOutput" value="<?= number_format($item->modal, 0, ',', '.') ?>" required >
                                        </div>
                                        <div class="form-group">
                                            <label>PK *</label>
                                            <input type="text" name="pk[]" class="form-control pkOutput" value="<?= $item->pk ?>" required >
                                        </div>
                                        <div class="form-group">
                                            <label>Stok Awal *</label>
                                            <input type="text" name="stock[]" class="form-control stockInitial" value="<?= $item->stock ?>" required readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Detail *</label>
                                            <input type="text" name="detail[]" class="form-control" placeholder="Contoh: Datang Barang" >
                                        </div>
                                        <div class="form-group">
                                            <label>Qty *</label>
                                            <input type="number" name="qty[]" class="form-control qtyInput" placeholder="Masukkan jumlah" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="text-center">
                    <button type="submit" name="in_add_multiple" class="btn btn-success btn-flat">
                        <i class="fa fa-paper-plane"></i> Simpan Semua
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    // Format number functions
    function formatNumber(input) {
        return input.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function unformatNumber(input) {
        return input.replace(/\./g, "");
    }
    $('#addStockForm').on('submit', function (e) {
            $('.modalOutput').each(function () {
                let formattedValue = $(this).val();
                let unformattedValue = unformatNumber(formattedValue);
                $(this).val(unformattedValue); // Set the raw number back to the input
            });
        });
</script>