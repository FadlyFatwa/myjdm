<section class="content-header">
    <h1>Edit Sale <small class="text-muted"><?= $sale->invoice ?></small></h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?= site_url('report/sale') ?>">Penjualan</a></li>
        <li class="active">Edit Sale</li>
    </ol>
</section>

<style>
    /* Returned items */
    .returned-item { background-color: #fff8e1 !important; }
    .returned-item td { color: #e65100; }
    .returned-item input { background-color: #f9f3e0 !important; border-color: #ffe0b2 !important; }

    /* Table compact */
    .table-edit td,
    .table-edit th { vertical-align: middle !important; padding: 6px 6px !important; }
    .table-edit .form-control { height: 30px; padding: 4px 8px; font-size: 13px; }

    /* Grand total display */
    .grand-total-display { font-size: 2.8rem; font-weight: 700; color: #00a65a; line-height: 1.1; }

    /* Summary labels */
    .summary-label { font-weight: 600; color: #555; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; }

    /* Box info side panel */
    .info-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; font-weight: 600; }
</style>

<section class="content">
<form action="<?= site_url('sale/update') ?>" method="post" id="form-edit-sale">
    <input type="hidden" name="sale_id"        value="<?= $sale->sale_id ?>">
    <input type="hidden" name="redirect_after" value="<?= $redirect_after ?? 'report' ?>">

    <div class="row">

        <!-- ===================== LEFT: Info Transaksi ===================== -->
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Info Transaksi</h3>
                </div>
                <div class="box-body">

                    <div class="form-group">
                        <label class="info-label">Invoice</label>
                        <input type="text" name="invoice" class="form-control" value="<?= $sale->invoice ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="info-label">Pembeli</label>
                        <select id="customer" name="customer_id" class="form-control">
                            <option value="">— Umum / Walk-in —</option>
                            <?php foreach ($customer as $cust): ?>
                                <option value="<?= $cust->customer_id ?>"
                                    <?= $sale->customer_id == $cust->customer_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cust->nama_customer) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" id="walkin-name-group"
                         style="<?= empty($sale->customer_id) ? '' : 'display:none;' ?>">
                        <label class="info-label">Nama Pembeli (Walk-in)</label>
                        <input type="text" name="customer_name" id="customer_name"
                               class="form-control"
                               value="<?= htmlspecialchars($sale->customer_name ?? '') ?>"
                               placeholder="Nama pembeli walk-in...">
                    </div>

                    <div class="form-group">
                        <label class="info-label">Tanggal</label>
                        <input type="date" name="date" class="form-control" value="<?= $sale->date ?>">
                    </div>

                    <div class="form-group">
                        <label class="info-label">Metode Pembayaran</label>
                        <select name="payment_method" class="form-control">
                            <option value="cash"     <?= $sale->payment_method == 'cash'     ? 'selected' : '' ?>>Cash</option>
                            <option value="transfer" <?= $sale->payment_method == 'transfer' ? 'selected' : '' ?>>Transfer</option>
                            <option value="qris"     <?= $sale->payment_method == 'qris'     ? 'selected' : '' ?>>QRIS</option>
                            <option value="debit"    <?= $sale->payment_method == 'debit'    ? 'selected' : '' ?>>Debit</option>
                            <option value="credit"   <?= $sale->payment_method == 'credit'   ? 'selected' : '' ?>>Kredit</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="info-label">Status Pembayaran</label>
                        <select name="payment_status" class="form-control">
                            <option value="lunas"       <?= $sale->payment_status == 'lunas'       ? 'selected' : '' ?>>Lunas</option>
                            <option value="belum lunas" <?= $sale->payment_status == 'belum lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="info-label">Catatan</label>
                        <textarea name="note" class="form-control" rows="3"><?= htmlspecialchars($sale->note ?? '') ?></textarea>
                    </div>

                </div>
            </div>
        </div>

        <!-- ===================== RIGHT: Detail + Summary ===================== -->
        <div class="col-md-9">

            <!-- Detail Barang -->
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Detail Barang</h3>
                    <div class="box-tools pull-right">
                        <?php if (!empty($returned_items)): ?>
                            <span class="label label-warning" style="margin-right:8px; font-size:12px; padding:5px 8px;">
                                <i class="fa fa-undo"></i> Ada barang diretur
                            </span>
                        <?php endif; ?>
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-item">
                            <i class="fa fa-plus"></i> Tambah Barang
                        </button>
                        <a href="<?= site_url('report/sale') ?>" class="btn btn-default btn-sm" style="margin-left:4px;">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-bordered table-hover table-edit" id="sale_detail_table">
                        <thead>
                            <tr class="bg-gray-light">
                                <th width="95">Barcode</th>
                                <th>Nama Barang</th>
                                <th width="120">Harga (Rp)</th>
                                <th width="75">Qty</th>
                                <th width="70" class="text-center">Retur</th>
                                <th width="110">Diskon (Rp)</th>
                                <th width="130">Total (Rp)</th>
                                <th width="60" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="sale_detail">
                            <?php foreach ($sale_details as $detail):
                                $returned_qty = $returned_items[$detail->item_id] ?? 0;
                                $is_returned  = $returned_qty > 0;
                                $net_total    = $detail->total - ($detail->price_sale * $returned_qty);
                            ?>
                            <tr class="<?= $is_returned ? 'returned-item' : '' ?>">
                                <td>
                                    <input type="hidden" name="item_id[]"      value="<?= $detail->item_id ?>">
                                    <input type="hidden" name="is_modified[]"  value="0" class="is-modified">
                                    <input type="hidden" name="stock[]"        value="<?= $detail->stock ?>">
                                    <input type="hidden" name="original_qty[]" value="<?= $detail->qty ?>">
                                    <input type="text"   name="barcode[]"      class="form-control" value="<?= $detail->barcode ?>" readonly>
                                </td>
                                <td>
                                    <input type="text" name="nama_barang_jual[]" class="form-control"
                                           value="<?= htmlspecialchars($detail->nama_barang_jual) ?>"
                                           <?= $is_returned ? 'readonly' : '' ?>>
                                </td>
                                <td>
                                    <input type="text" name="price[]" class="form-control price-input"
                                           value="<?= number_format($detail->price_sale, 0, ',', '.') ?>"
                                           <?= $is_returned ? 'readonly' : '' ?>>
                                </td>
                                <td>
                                    <input type="number" name="qty[]" class="form-control qty-input"
                                           value="<?= $detail->qty ?>" min="0"
                                           <?= $is_returned ? 'readonly' : '' ?>>
                                </td>
                                <td class="text-center">
                                    <?php if ($returned_qty > 0): ?>
                                        <span class="badge" style="background:#e65100;"><?= $returned_qty ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="text" name="discount_item[]" class="form-control discount-input"
                                           value="<?= number_format($detail->discount_item, 0, ',', '.') ?>"
                                           <?= $is_returned ? 'readonly' : '' ?>>
                                </td>
                                <td>
                                    <input type="text" name="total[]" class="form-control total-input"
                                           value="<?= number_format($net_total, 0, ',', '.') ?>" readonly>
                                    <input type="hidden" name="total_raw[]" class="total-raw" value="<?= $net_total ?>">
                                </td>
                                <td class="text-center">
                                    <?php if (!$is_returned): ?>
                                        <button type="button" class="btn btn-danger btn-xs btn-remove-item" title="Hapus baris ini">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-warning" title="Item diretur"><i class="fa fa-undo fa-lg"></i></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detail Jasa -->
            <div class="box box-default" style="border-top:3px solid #3c8dbc;">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-wrench" style="color:#3c8dbc;"></i> Detail Jasa</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-info btn-sm" id="btn-show-add-jasa">
                            <i class="fa fa-plus"></i> Tambah Jasa
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <!-- Inline form tambah jasa -->
                    <div id="add-jasa-row" style="display:none; margin-bottom:12px;">
                        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                            <select id="jasa_add_select" class="form-control" style="flex:2; min-width:200px; height:34px;">
                                <option value="">— Pilih dari master jasa —</option>
                                <?php foreach ($jasa_list as $j): ?>
                                <option value="<?= $j->jasa_id ?>"
                                        data-tarif="<?= $j->tarif ?>"
                                        data-nama="<?= htmlspecialchars($j->nama_jasa) ?>">
                                    <?= htmlspecialchars($j->nama_jasa) ?> — Rp <?= number_format($j->tarif, 0, ',', '.') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" id="jasa_add_qty" value="1" min="1"
                                   style="width:70px; height:34px; text-align:center; font-weight:700; border:1px solid #ccc; border-radius:4px; padding:4px 8px;">
                            <button type="button" class="btn btn-info btn-sm" id="btn-do-add-jasa" style="height:34px; padding:0 14px;">
                                <i class="fa fa-plus"></i> Tambah
                            </button>
                            <button type="button" class="btn btn-default btn-sm" id="btn-cancel-add-jasa" style="height:34px;">Batal</button>
                        </div>
                    </div>

                    <table class="table table-bordered table-hover table-edit" id="jasa_detail_table">
                        <thead>
                            <tr class="bg-gray-light">
                                <th>Nama Jasa</th>
                                <th width="140">Tarif (Rp)</th>
                                <th width="75">Qty</th>
                                <th width="130">Total (Rp)</th>
                                <th width="60" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="jasa_detail">
                            <?php foreach ($sale_jasa_details as $jd): ?>
                            <tr>
                                <input type="hidden" name="jasa_source_id[]" value="<?= $jd->jasa_id ?>">
                                <td>
                                    <input type="text" name="jasa_nama[]" class="form-control jasa-nama-input"
                                           value="<?= htmlspecialchars($jd->nama_jasa) ?>">
                                </td>
                                <td>
                                    <input type="text" name="jasa_tarif[]" class="form-control jasa-tarif-input"
                                           value="<?= number_format($jd->tarif, 0, ',', '.') ?>">
                                </td>
                                <td>
                                    <input type="number" name="jasa_qty[]" class="form-control jasa-qty-input"
                                           value="<?= $jd->qty ?>" min="1">
                                </td>
                                <td>
                                    <input type="text" class="form-control jasa-total-input"
                                           value="<?= number_format($jd->total, 0, ',', '.') ?>" readonly>
                                    <input type="hidden" class="jasa-total-raw" value="<?= $jd->total ?>">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-xs btn-remove-jasa">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary & Save -->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-calculator"></i> Ringkasan Pembayaran</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-5">
                            <div class="form-group">
                                <label class="summary-label">Subtotal</label>
                                <div class="input-group">
                                    <span class="input-group-addon">Rp</span>
                                    <input type="text" id="subtotal" name="subtotal" class="form-control" value="<?= number_format($sale->total_price, 0, ',', '.') ?>" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="summary-label">Diskon Global</label>
                                <div class="input-group">
                                    <input type="number" id="discount" name="discount" class="form-control"
                                           value="<?= $sale->discount ?>" min="0" max="100">
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="summary-label">Grand Total</label>
                                <div class="input-group">
                                    <span class="input-group-addon">Rp</span>
                                    <input type="text" id="grandtotal" name="grandtotal" class="form-control"
                                           value="<?= number_format($sale->final_price, 0, ',', '.') ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-7">
                            <div class="text-right">
                                <p class="text-muted" style="margin-bottom:2px; font-size:13px;">Grand Total</p>
                                <div class="grand-total-display" id="grand_total2">Rp 0</div>
                                <hr style="margin:12px 0;">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fa fa-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>
</section>

<!-- ===================== Modal Tambah Barang ===================== -->
<div class="modal fade" id="modal-item" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-search"></i> Pilih Barang</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped table-hover" id="tbl-modal-item" style="width:100%">
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Nama Barang</th>
                            <th>Supplier</th>
                            <th class="text-right">Harga</th>
                            <th class="text-right">Stok</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item):
                            $item_exists = false;
                            foreach ($sale_details as $d) {
                                if ($d->item_id == $item->item_id) { $item_exists = true; break; }
                            }
                        ?>
                        <tr>
                            <td><?= $item->barcode ?></td>
                            <td><?= htmlspecialchars($item->nama_item) ?></td>
                            <td><?= htmlspecialchars($item->nama_supplier ?? '-') ?></td>
                            <td class="text-right"><?= indo_currency($item->price) ?></td>
                            <td class="text-right">
                                <?php if ($item->stock == 0): ?>
                                    <span class="label label-danger">Habis</span>
                                <?php elseif ($item->stock <= 3): ?>
                                    <span class="label label-warning"><?= $item->stock ?></span>
                                <?php else: ?>
                                    <?= $item->stock ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($item_exists): ?>
                                    <span class="label label-warning">Sudah Ada</span>
                                <?php else: ?>
                                    <span class="label label-success">Tersedia</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-xs btn-select-item"
                                    data-id="<?= $item->item_id ?>"
                                    data-barcode="<?= $item->barcode ?>"
                                    data-name="<?= htmlspecialchars($item->nama_item, ENT_QUOTES) ?>"
                                    data-price="<?= $item->price ?>"
                                    data-stock="<?= $item->stock ?>"
                                    <?= ($item_exists || $item->stock <= 0) ? 'disabled' : '' ?>>
                                    <i class="fa fa-plus"></i> Pilih
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

    // ── DataTables untuk modal item ──────────────────────────────────────────
    $('#tbl-modal-item').DataTable({
        language: {
            search:      "Cari:",
            lengthMenu:  "Tampilkan _MENU_ data",
            info:        "Menampilkan _START_–_END_ dari _TOTAL_ barang",
            paginate:    { previous: "‹", next: "›" },
            zeroRecords: "Barang tidak ditemukan"
        },
        columnDefs: [{ orderable: false, targets: [4, 5] }],
        pageLength: 10
    });

    // ── Select2 untuk dropdown pembeli ──────────────────────────────────────
    $('#customer').select2({
        placeholder: 'Pilih pembeli',
        allowClear: true,
        width: '100%'
    });

    // Tampilkan input nama walk-in jika Umum dipilih
    function toggleWalkinName() {
        if (!$('#customer').val()) {
            $('#walkin-name-group').show();
        } else {
            $('#walkin-name-group').hide();
            $('#customer_name').val('');
        }
    }
    $('#customer').on('change', toggleWalkinName);
    toggleWalkinName(); // init saat halaman load

    // ── Pilih barang dari modal ──────────────────────────────────────────────
    $(document).on('click', '.btn-select-item', function() {
        var itemId  = $(this).data('id');
        var barcode = $(this).data('barcode');
        var name    = $(this).data('name');
        var price   = parseFloat($(this).data('price')) || 0;
        var stock   = parseInt($(this).data('stock'))   || 0;

        // Cek duplikat
        var isDuplicate = false;
        $('#sale_detail tr').each(function() {
            if ($(this).find('input[name="item_id[]"]').val() == itemId) {
                isDuplicate = true; return false;
            }
        });

        if (isDuplicate) {
            Swal.fire({ icon: 'warning', title: 'Sudah Ada', text: name + ' sudah ada di daftar. Ubah qty-nya saja.' });
            return;
        }
        if (stock <= 0) {
            Swal.fire({ icon: 'error', title: 'Stok Habis', text: 'Stok ' + name + ' habis.' });
            return;
        }

        var row = `
            <tr>
                <td>
                    <input type="hidden" name="item_id[]"      value="${itemId}">
                    <input type="hidden" name="is_modified[]"  value="1" class="is-modified">
                    <input type="hidden" name="stock[]"        value="${stock}">
                    <input type="hidden" name="original_qty[]" value="0">
                    <input type="hidden" name="total_raw[]"    class="total-raw" value="${price}">
                    <input type="text"   name="barcode[]"      class="form-control" value="${barcode}" readonly>
                </td>
                <td>
                    <input type="text" name="nama_barang_jual[]" class="form-control" value="${name}">
                </td>
                <td>
                    <input type="number" name="price[]" class="form-control price-input" value="${price}" min="0">
                </td>
                <td>
                    <input type="number" name="qty[]" class="form-control qty-input" value="1" min="1" max="${stock}">
                </td>
                <td class="text-center"><span class="text-muted">—</span></td>
                <td>
                    <input type="number" name="discount_item[]" class="form-control discount-input" value="0" min="0">
                </td>
                <td>
                    <input type="text" name="total[]" class="form-control total-input" value="${formatRupiah(price)}" readonly>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-xs btn-remove-item" title="Hapus">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`;

        $('#sale_detail').append(row);
        $('#modal-item').modal('hide');
        calculateTotals();
    });

    // ── Hapus baris ─────────────────────────────────────────────────────────
    $(document).on('click', '.btn-remove-item', function() {
        Swal.fire({
            title: 'Hapus barang ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then(function(result) {
            if (result.isConfirmed) {
                // tombol sudah disimpan di closure sebelumnya, tapi event sudah fired,
                // jadi kita perlu cara lain — gunakan currentTarget di event listener
            }
        });
    });

    // Cara yang lebih langsung tanpa masalah closure:
    $(document).off('click', '.btn-remove-item').on('click', '.btn-remove-item', function() {
        var $btn = $(this);
        Swal.fire({
            title: 'Hapus barang ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then(function(result) {
            if (result.isConfirmed) {
                $btn.closest('tr').remove();
                calculateTotals();
            }
        });
    });

    // ── Recalculate saat input berubah ──────────────────────────────────────
    $(document).on('input change', '.price-input, .discount-input', function() {
        var row = $(this).closest('tr');
        row.find('.is-modified').val('1');
        calculateRowTotal(row);
        calculateTotals();
    });

    // ── Validasi qty + recalculate ───────────────────────────────────────────
    $(document).on('input change', '.qty-input', function() {
        var row         = $(this).closest('tr');
        var stock       = parseInt(row.find('input[name="stock[]"]').val())        || 0;
        var originalQty = parseInt(row.find('input[name="original_qty[]"]').val()) || 0;
        var qty         = parseInt($(this).val()) || 0;
        var available   = stock + originalQty;

        if (qty > available) {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'error',
                title: 'Stok tidak cukup. Tersedia: ' + available,
                showConfirmButton: false, timer: 3000
            });
            $(this).val(originalQty > 0 ? originalQty : 1);
        }
        row.find('.is-modified').val('1');
        calculateRowTotal(row);
        calculateTotals();
    });

    // ── Diskon global ────────────────────────────────────────────────────────
    $(document).on('input change', '#discount', calculateTotals);

    // ── Kalkulasi total per baris ────────────────────────────────────────────
    function calculateRowTotal(row) {
        var price    = parseFloat(row.find('.price-input').val().replace(/\./g, ''))    || 0;
        var qty      = parseInt(row.find('.qty-input').val())                           || 0;
        var discount = parseFloat(row.find('.discount-input').val().replace(/\./g, '')) || 0;
        var total    = (price * qty) - discount;
        if (total < 0) total = 0;

        row.find('.total-input').val(formatRupiah(total));
        row.find('.total-raw').val(total);
        return total;
    }

    // ── Auto-format harga & diskon saat mengetik (real-time) ────────────────
    $(document).on('keyup', '.price-input, .discount-input', function(e) {
        // Lewati tombol kontrol agar tidak ganggu navigasi
        var skip = [8, 9, 46, 37, 38, 39, 40]; // backspace, tab, delete, arrows
        if (skip.indexOf(e.which) !== -1) return;

        var raw = parseInt($(this).val().replace(/\./g, '')) || 0;
        $(this).val(formatRupiah(raw));
    });

    // ── Pastikan format rapi saat fokus keluar ────────────────────────────
    $(document).on('blur', '.price-input, .discount-input', function() {
        var raw = parseInt($(this).val().replace(/\./g, '')) || 0;
        $(this).val(formatRupiah(raw));
    });

    // ── Kalkulasi grand total ────────────────────────────────────────────────
    function calculateTotals() {
        var subtotal = 0;
        $('#sale_detail tr').each(function() {
            subtotal += parseFloat($(this).find('.total-raw').val()) || 0;
        });
        // Tambah total jasa
        $('#jasa_detail tr').each(function() {
            subtotal += parseFloat($(this).find('.jasa-total-raw').val()) || 0;
        });

        var discountPct   = parseFloat($('#discount').val()) || 0;
        var discountAmt   = subtotal * discountPct / 100;
        var grandTotal    = subtotal - discountAmt;

        $('#subtotal').val(formatRupiah(subtotal));
        $('#grandtotal').val(formatRupiah(grandTotal));
        $('#grand_total2').text('Rp ' + formatRupiah(grandTotal));
    }

    // ── Kalkulasi total per baris jasa ───────────────────────────────────────
    function calculateJasaRowTotal(row) {
        var tarif = parseInt(row.find('.jasa-tarif-input').val().replace(/\./g, '')) || 0;
        var qty   = parseInt(row.find('.jasa-qty-input').val()) || 0;
        var total = tarif * qty;
        row.find('.jasa-total-input').val(formatRupiah(total));
        row.find('.jasa-total-raw').val(total);
        return total;
    }

    // Rekalkul jasa saat tarif/qty berubah
    $(document).on('input change', '.jasa-tarif-input, .jasa-qty-input', function() {
        var row = $(this).closest('tr');
        calculateJasaRowTotal(row);
        calculateTotals();
    });

    // Format tarif saat keyup
    $(document).on('keyup blur', '.jasa-tarif-input', function(e) {
        var skip = [8, 9, 46, 37, 38, 39, 40];
        if (skip.indexOf(e.which) !== -1) return;
        var raw = parseInt($(this).val().replace(/\./g, '')) || 0;
        $(this).val(formatRupiah(raw));
    });

    // Hapus baris jasa
    $(document).on('click', '.btn-remove-jasa', function() {
        var $btn = $(this);
        Swal.fire({
            title: 'Hapus jasa ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then(function(result) {
            if (result.isConfirmed) {
                $btn.closest('tr').remove();
                calculateTotals();
            }
        });
    });

    // Tampil/sembunyikan form tambah jasa
    $('#btn-show-add-jasa').on('click', function() {
        $('#add-jasa-row').slideDown(150);
    });
    $('#btn-cancel-add-jasa').on('click', function() {
        $('#add-jasa-row').slideUp(150);
        $('#jasa_add_select').val('');
        $('#jasa_add_qty').val(1);
    });

    // Tambah baris jasa baru
    $('#btn-do-add-jasa').on('click', function() {
        var $opt   = $('#jasa_add_select option:selected');
        var jasaId = $('#jasa_add_select').val();
        var qty    = parseInt($('#jasa_add_qty').val()) || 1;

        if (!jasaId) {
            Swal.fire({ icon: 'warning', title: 'Pilih jasa terlebih dahulu' });
            return;
        }

        var nama  = $opt.data('nama');
        var tarif = parseInt($opt.data('tarif')) || 0;
        var total = tarif * qty;

        var row = `<tr>
            <input type="hidden" name="jasa_source_id[]" value="${jasaId}">
            <td><input type="text"   name="jasa_nama[]"  class="form-control jasa-nama-input"  value="${nama}"></td>
            <td><input type="text"   name="jasa_tarif[]" class="form-control jasa-tarif-input" value="${formatRupiah(tarif)}"></td>
            <td><input type="number" name="jasa_qty[]"   class="form-control jasa-qty-input"   value="${qty}" min="1"></td>
            <td>
                <input type="text"   class="form-control jasa-total-input" value="${formatRupiah(total)}" readonly>
                <input type="hidden" class="jasa-total-raw" value="${total}">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-xs btn-remove-jasa"><i class="fa fa-trash"></i></button>
            </td>
        </tr>`;

        $('#jasa_detail').append(row);
        $('#add-jasa-row').slideUp(150);
        $('#jasa_add_select').val('');
        $('#jasa_add_qty').val(1);
        calculateTotals();
    });

    // ── Format angka Rupiah (tanpa Rp prefix) ───────────────────────────────
    function formatRupiah(value) {
        var num = parseFloat(value) || 0;
        return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    // ── Init: hitung ulang saat halaman load ────────────────────────────────
    $('#sale_detail tr').each(function() {
        var rawVal = $(this).find('.total-raw').val();
        if (!rawVal) {
            var totalStr = $(this).find('.total-input').val().replace(/\./g, '').replace(',', '.');
            $(this).find('.total-raw').val(parseFloat(totalStr) || 0);
        }
    });
    // Sync jasa-total-raw
    $('#jasa_detail tr').each(function() {
        var raw = $(this).find('.jasa-total-raw').val();
        if (!raw) {
            var str = $(this).find('.jasa-total-input').val().replace(/\./g, '').replace(',', '.');
            $(this).find('.jasa-total-raw').val(parseFloat(str) || 0);
        }
    });
    calculateTotals();

});
</script>
