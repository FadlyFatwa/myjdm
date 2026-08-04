<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-header">
    <h1><i class="fa fa-history" style="color:#3c8dbc"></i> Detail Penerimaan
        <small>#<?= $receipt->receipt_id ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?= site_url('purchase-order') ?>">Purchase Order</a></li>
        <li><a href="<?= site_url('purchase-order/history') ?>">Histori Penerimaan</a></li>
        <li class="active">#<?= $receipt->receipt_id ?></li>
    </ol>
</div>

<div class="content">

    <!-- Info Card -->
    <div class="box box-primary">
        <div class="box-header with-border" style="background:#3c8dbc;color:#fff">
            <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi Penerimaan</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-sm-6">
                    <table class="table table-condensed" style="margin:0">
                        <tr>
                            <td style="color:#888;width:160px;padding:6px 8px">No. PO</td>
                            <td style="padding:6px 8px">
                                <a href="<?= site_url('purchase-order/' . $receipt->po_id) ?>" style="font-weight:700">
                                    <?= htmlspecialchars($receipt->po_number) ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="color:#888;padding:6px 8px">Supplier</td>
                            <td style="padding:6px 8px;font-weight:600"><?= htmlspecialchars($receipt->nama_supplier) ?></td>
                        </tr>
                        <tr>
                            <td style="color:#888;padding:6px 8px">No. Invoice Supplier</td>
                            <td style="padding:6px 8px">
                                <?= $receipt->supplier_invoice_no
                                    ? '<span class="label label-default" style="font-size:12px;padding:3px 8px">' . htmlspecialchars($receipt->supplier_invoice_no) . '</span>'
                                    : '<span class="text-muted">—</span>' ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-sm-6">
                    <table class="table table-condensed" style="margin:0">
                        <tr>
                            <td style="color:#888;width:160px;padding:6px 8px">Tanggal Invoice</td>
                            <td style="padding:6px 8px">
                                <?= $receipt->invoice_date ? indo_date($receipt->invoice_date) : '<span class="text-muted">—</span>' ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="color:#888;padding:6px 8px">Tanggal Terima</td>
                            <td style="padding:6px 8px;font-weight:600"><?= indo_date($receipt->receive_date) ?></td>
                        </tr>
                        <tr>
                            <td style="color:#888;padding:6px 8px">Diterima Oleh</td>
                            <td style="padding:6px 8px"><?= htmlspecialchars($receipt->received_by_name ?? '—') ?></td>
                        </tr>
                        <?php if ($receipt->ongkir > 0): ?>
                        <tr>
                            <td style="color:#888;padding:6px 8px">Ongkir</td>
                            <td style="padding:6px 8px">
                                Rp <?= number_format($receipt->ongkir, 0, ',', '.') ?>
                                <span class="text-muted">(Kas)</span>
                                <?php if ($receipt->ongkir_expense_id && in_array($this->fungsi->user_login()->level, [1, 2])): ?>
                                    &mdash; <a href="<?= site_url('beban') ?>" title="Lihat di Beban Operasional">lihat di Beban Operasional</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
            <?php if ($receipt->notes): ?>
            <div style="margin-top:8px;padding:8px 12px;background:#f8f9fa;border-radius:4px;font-size:13px;color:#555">
                <i class="fa fa-comment-o"></i> <?= htmlspecialchars($receipt->notes) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Item diterima -->
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> Item yang Diterima</h3>
            <div class="box-tools pull-right" style="display:flex;align-items:center;gap:8px">
                <span class="badge" style="background:#3c8dbc;font-size:12px;padding:4px 10px"><?= count($items) ?> item</span>
                <button type="button" class="btn btn-warning btn-sm" id="btn-toggle-edit">
                    <i class="fa fa-pencil"></i> Koreksi Data
                </button>
            </div>
        </div>
        <div class="box-body table-responsive" style="padding:0">
            <div id="edit-warning" style="display:none;padding:10px 16px;background:#fff8e1;border-bottom:1px solid #ffe082;font-size:12px;color:#795548">
                <i class="fa fa-exclamation-triangle" style="color:#f39c12"></i>
                <strong>Mode Koreksi:</strong> Edit qty/harga lalu simpan per baris, atau tambah item dari PO yang sama.
            </div>

            <!-- Tambah item (mode koreksi) -->
            <?php if (!empty($available_items)): ?>
            <div id="add-item-row" style="display:none;padding:10px 16px;background:#e8f5e9;border-bottom:1px solid #c8e6c9;">
                <div style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap">
                    <div style="flex:2;min-width:200px">
                        <label style="font-size:11px;color:#555;font-weight:600;display:block;margin-bottom:3px">Item dari PO</label>
                        <select id="sel-add-item" class="form-control input-sm">
                            <option value="">— Pilih item —</option>
                            <?php foreach ($available_items as $ai): ?>
                            <option value="<?= $ai->id ?>"
                                    data-sisa="<?= $ai->qty_ordered - $ai->qty_received ?>"
                                    data-unit="<?= htmlspecialchars($ai->nama_unit ?? '') ?>"
                                    data-price="<?= (int) $ai->unit_price ?>">
                                <?= htmlspecialchars($ai->display_name) ?>
                                (sisa <?= $ai->qty_ordered - $ai->qty_received ?> <?= $ai->nama_unit ?? '' ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="width:80px">
                        <label style="font-size:11px;color:#555;font-weight:600;display:block;margin-bottom:3px">Qty</label>
                        <input type="number" id="inp-add-qty" class="form-control input-sm text-center" value="1" min="1">
                    </div>
                    <div style="width:130px">
                        <label style="font-size:11px;color:#555;font-weight:600;display:block;margin-bottom:3px">Harga Aktual (Rp)</label>
                        <input type="text" id="inp-add-price" class="form-control input-sm text-right" placeholder="0">
                    </div>
                    <div>
                        <button type="button" class="btn btn-success btn-sm" id="btn-do-add-item">
                            <i class="fa fa-plus"></i> Tambah
                        </button>
                    </div>
                </div>
                <div id="add-item-info" style="font-size:11px;color:#388e3c;margin-top:5px"></div>
            </div>
            <?php endif; ?>
            <table class="table table-condensed table-bordered" style="margin:0">
                <thead>
                    <tr style="background:#f4f6f9;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#555">
                        <th style="padding:8px 12px;width:40px">No</th>
                        <th style="padding:8px 12px">Nama Barang</th>
                        <th style="padding:8px 12px;width:100px;text-align:center">Qty Diterima</th>
                        <th style="padding:8px 12px;width:150px;text-align:right">Harga Aktual</th>
                        <th style="padding:8px 12px;width:80px;text-align:center">PK</th>
                        <th style="padding:8px 12px;width:150px;text-align:right">Subtotal</th>
                        <th style="padding:8px 12px;width:80px;text-align:center"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grand_total = 0;
                    foreach ($items as $i => $it):
                        $subtotal = $it->qty_received * $it->actual_price;
                        $grand_total += $subtotal;
                    ?>
                    <tr data-detail-id="<?= $it->id ?>" data-qty="<?= $it->qty_received ?>">
                        <td style="padding:9px 12px;text-align:center"><?= $i + 1 ?></td>
                        <td style="padding:9px 12px">
                            <?php if ($it->barcode): ?>
                            <div style="font-size:11px;color:#888;margin-bottom:2px"><?= htmlspecialchars($it->barcode) ?></div>
                            <?php endif; ?>
                            <strong><?= htmlspecialchars($it->nama_item ?? $it->item_name_temp ?? '—') ?></strong>
                            <?php if (!$it->item_id): ?>
                            <span class="label label-warning" style="font-size:10px;margin-left:4px">Belum Terdaftar</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:7px 12px;text-align:center" class="cell-qty">
                            <!-- View mode -->
                            <span class="view-qty" style="font-weight:700">
                                <?= $it->qty_received ?>
                                <?php if (!empty($it->nama_unit)): ?>
                                <span style="font-size:11px;color:#9ca3af;font-weight:400"><?= htmlspecialchars($it->nama_unit) ?></span>
                                <?php endif; ?>
                            </span>
                            <!-- Edit mode -->
                            <input type="number" class="form-control input-xs text-center edit-qty"
                                value="<?= $it->qty_received ?>" min="0" max="<?= $it->qty_ordered ?>"
                                data-unit="<?= htmlspecialchars($it->nama_unit ?? '') ?>"
                                data-max="<?= $it->qty_ordered ?>"
                                style="width:60px;margin:0 auto;display:none">
                            <div class="edit-qty-hint text-muted" style="font-size:10px;display:none">maks <?= $it->qty_ordered ?></div>
                        </td>
                        <td style="padding:7px 12px;text-align:right" class="cell-price">
                            <!-- View mode -->
                            <span class="view-price"><?= indo_currency($it->actual_price) ?></span>
                            <!-- Edit mode -->
                            <input type="text" class="form-control input-xs text-right edit-price"
                                value="<?= number_format((int)$it->actual_price, 0, ',', '.') ?>"
                                style="width:120px;margin-left:auto;display:none">
                        </td>
                        <td style="padding:7px 12px;text-align:center">
                            <span class="view-pk" style="font-family:monospace;font-size:12px;letter-spacing:1px">
                                <?= htmlspecialchars($it->item_pk ?? '—') ?>
                            </span>
                        </td>
                        <td style="padding:9px 12px;text-align:right;font-weight:600" class="cell-subtotal">
                            <?= indo_currency($subtotal) ?>
                        </td>
                        <td style="padding:7px 12px;text-align:center;white-space:nowrap" class="cell-action">
                            <!-- Simpan (muncul saat edit mode) -->
                            <button type="button" class="btn btn-success btn-xs btn-save-row" style="display:none">
                                <i class="fa fa-save"></i> Simpan
                            </button>
                            <!-- Hapus (muncul saat edit mode) -->
                            <button type="button" class="btn btn-danger btn-xs btn-del-detail" style="display:none"
                                data-detail-id="<?= $it->id ?>"
                                data-receipt-id="<?= $receipt->receipt_id ?>"
                                data-qty="<?= $it->qty_received ?>"
                                data-nama="<?= htmlspecialchars($it->nama_item ?? $it->item_name_temp ?? '') ?>"
                                title="Hapus dari penerimaan">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:#f8f9fa;font-weight:700">
                        <td colspan="5" style="padding:10px 12px;text-align:right;font-size:13px">Total Nilai Penerimaan</td>
                        <td style="padding:10px 12px;text-align:right;font-size:14px;color:#3c8dbc" id="grand-total-cell"><?= indo_currency($grand_total) ?></td>
                        <td class="cell-action"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="box-footer">
            <a href="<?= site_url('purchase-order/history') ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
            <a href="<?= site_url('purchase-order/' . $receipt->po_id) ?>" class="btn btn-primary btn-sm">
                <i class="fa fa-file-text-o"></i> Lihat PO
            </a>
        </div>
    </div>

<script>
$(function () {
    function toast(icon, msg) {
        Swal.fire({ toast:true, position:'top-end', icon:icon, title:msg,
            showConfirmButton:false, timer:3500, timerProgressBar:true });
    }
    function formatRp(val) {
        return String(val).replace(/[^0-9]/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.');
    }
    function unformatRp(val) {
        return parseInt(String(val).replace(/\./g,''),10) || 0;
    }

    var editMode = false;

    $('#btn-toggle-edit').on('click', function () {
        editMode = !editMode;
        if (editMode) {
            $(this).html('<i class="fa fa-times"></i> Selesai').removeClass('btn-warning').addClass('btn-default');
            $('#edit-warning').slideDown(150);
            $('#add-item-row').slideDown(150);
            $('.view-qty, .view-price').hide();
            $('.edit-qty, .edit-price, .btn-save-row, .btn-del-detail, .edit-qty-hint').show();
        } else {
            $(this).html('<i class="fa fa-pencil"></i> Koreksi Data').removeClass('btn-default').addClass('btn-warning');
            $('#edit-warning').slideUp(150);
            $('#add-item-row').slideUp(150);
            $('.edit-qty, .edit-price, .btn-save-row, .btn-del-detail, .edit-qty-hint').hide();
            $('.view-qty, .view-price').show();
        }
    });

    // Format harga saat edit
    $(document).on('input', '.edit-price', function () {
        $(this).val(formatRp($(this).val()));
    });

    // Update info sisa qty saat pilih item
    $('#sel-add-item').on('change', function () {
        var $opt = $(this).find('option:selected');
        var sisa = $opt.data('sisa') || 0;
        var unit = $opt.data('unit') || '';
        var price = $opt.data('price') || 0;
        if ($(this).val()) {
            $('#add-item-info').text('Sisa qty: ' + sisa + ' ' + unit);
            $('#inp-add-qty').attr('max', sisa).val(Math.min(parseInt($('#inp-add-qty').val()) || 1, sisa));
            if (price > 0) $('#inp-add-price').val(formatRp(price));
        } else {
            $('#add-item-info').text('');
        }
    });

    function formatRp(val) {
        return String(val).replace(/[^0-9]/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.');
    }
    function unformatRp(val) {
        return parseInt(String(val).replace(/\./g,''),10) || 0;
    }
    $(document).on('input', '#inp-add-price', function () {
        $(this).val(formatRp($(this).val()));
    });

    $('#btn-do-add-item').on('click', function () {
        var poDetailId = $('#sel-add-item').val();
        var qty        = parseInt($('#inp-add-qty').val()) || 0;
        var price      = unformatRp($('#inp-add-price').val());
        var sisa       = parseInt($('#sel-add-item').find('option:selected').data('sisa')) || 0;

        if (!poDetailId) { toast('warning', 'Pilih item terlebih dahulu.'); return; }
        if (qty < 1)     { toast('warning', 'Qty minimal 1.'); return; }
        if (qty > sisa)  { toast('warning', 'Melebihi sisa qty (' + sisa + ').'); return; }

        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.post('<?= site_url('purchase-order/history/add-detail') ?>', {
            receipt_id   : <?= $receipt->receipt_id ?>,
            po_detail_id : poDetailId,
            qty          : qty,
            actual_price : price,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>',
        }, function (res) {
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Tambah');
            if (res.status !== 'success') {
                toast('error', res.message || 'Gagal menambah item.');
                return;
            }
            toast('success', 'Item berhasil ditambahkan.');
            setTimeout(function () { location.reload(); }, 1000);
        }, 'json').fail(function () {
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Tambah');
            toast('error', 'Terjadi kesalahan server.');
        });
    });

    // Hapus baris dari receipt
    $(document).on('click', '.btn-del-detail', function () {
        var $btn      = $(this);
        var detailId  = $btn.data('detail-id');
        var receiptId = $btn.data('receipt-id');
        var qty       = parseInt($btn.data('qty')) || 0;
        var nama      = $btn.data('nama') || 'item ini';

        var bodyText = qty > 0
            ? '<b>' + nama + '</b> (qty: ' + qty + ') akan dihapus dari penerimaan.<br>Stok akan dikurangi <b>' + qty + '</b> kembali.'
            : '<b>' + nama + '</b> (qty: 0) akan dihapus dari catatan penerimaan.';

        Swal.fire({
            title: 'Hapus dari penerimaan?',
            html : bodyText,
            icon : 'warning',
            showCancelButton : true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText : 'Batal',
            confirmButtonColor: '#dd4b39',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $btn.prop('disabled', true);
            $.post('<?= site_url('purchase-order/history/delete-detail') ?>', {
                detail_id  : detailId,
                receipt_id : receiptId,
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>',
            }, function (res) {
                if (res.status !== 'success') {
                    toast('error', res.message || 'Gagal menghapus.');
                    $btn.prop('disabled', false);
                    return;
                }
                if (res.receipt_deleted) {
                    // Seluruh receipt kosong — redirect ke histori
                    toast('success', 'Baris dihapus. Penerimaan ini sudah kosong.');
                    setTimeout(function () {
                        window.location.href = '<?= site_url('purchase-order/history') ?>';
                    }, 1500);
                } else {
                    // Hapus hanya baris ini dari tabel
                    $btn.closest('tr').fadeOut(300, function () { $(this).remove(); });
                    toast('success', 'Baris berhasil dihapus.');
                }
            }, 'json').fail(function () {
                toast('error', 'Terjadi kesalahan.');
                $btn.prop('disabled', false);
            });
        });
    });

    // Simpan per baris
    $(document).on('click', '.btn-save-row', function () {
        var $tr      = $(this).closest('tr');
        var detailId = $tr.data('detail-id');
        var $qtyInp  = $tr.find('.edit-qty');
        var newQty   = parseInt($qtyInp.val()) || 0;
        var maxQty   = parseInt($qtyInp.data('max')) || 0;
        var newPrice = unformatRp($tr.find('.edit-price').val());

        if (newQty > maxQty) {
            toast('warning', 'Qty melebihi jumlah yang dipesan (' + maxQty + ').');
            $qtyInp.val(maxQty).focus();
            return;
        }

        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.post('<?= site_url('purchase-order/history/update-detail') ?>', {
            detail_id:    detailId,
            qty_received: newQty,
            actual_price: newPrice,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>',
        }, function (res) {
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Simpan');
            if (res.status !== 'success') {
                toast('error', res.message || 'Gagal menyimpan.');
                return;
            }
            // Update view-qty
            var unit = $tr.find('.edit-qty').data('unit');
            var unitHtml = unit ? ' <span style="font-size:11px;color:#9ca3af;font-weight:400">' + unit + '</span>' : '';
            $tr.find('.view-qty').html('<span style="font-weight:700">' + newQty + '</span>' + unitHtml);

            // Update view-price
            var priceFormatted = newPrice.toLocaleString('id-ID');
            $tr.find('.view-price').text('Rp ' + priceFormatted);

            // Update subtotal cell
            var newSubtotal = newQty * newPrice;
            $tr.find('.cell-subtotal').html('<strong>Rp ' + newSubtotal.toLocaleString('id-ID') + '</strong>');

            // Recalculate grand total
            var total = 0;
            $('tbody tr').each(function () {
                var st = $(this).find('.cell-subtotal').text().replace(/[^0-9]/g,'');
                total += parseInt(st) || 0;
            });
            $('#grand-total-cell').text('Rp ' + total.toLocaleString('id-ID'));

            var diffLabel = res.qty_diff > 0 ? '+' + res.qty_diff : res.qty_diff;
            toast('success', 'Disimpan. Stok disesuaikan ' + diffLabel + '.');
        }, 'json');
    });
});
</script>

</div>
