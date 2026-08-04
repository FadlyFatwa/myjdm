<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-header">
    <h1><i class="fa fa-bar-chart" style="color:#3c8dbc"></i> Review Stok
        <small>pantau stok dan buat keputusan pembelian</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Review Stok</li>
    </ol>
</div>

<div class="content">

<style>
.stock-danger  { color:#c0392b; font-weight:700; }
.stock-warning { color:#e08e0b; font-weight:700; }
.stock-ok      { color:#27ae60; }
.btn-add-cart  { white-space:nowrap; }
</style>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-table"></i> Daftar Barang &amp; Status Stok</h3>
            <div class="box-tools pull-right">
                <a href="<?= site_url('po-cart') ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-shopping-basket"></i> Keranjang PO
                    <?php
                    $cart_count = $this->db->count_all('po_cart');
                    if ($cart_count > 0): ?>
                    <span class="badge" style="background:#fff;color:#00a65a"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        <div class="box-body" style="padding:0">
            <table id="tbl-stock-review" class="table table-bordered table-hover" style="width:100%;margin:0">
                <thead>
                    <tr style="background:#f4f6f9;font-size:12px;text-transform:uppercase;letter-spacing:.4px;color:#555">
                        <th width="40" class="text-center">#</th>
                        <th width="90">Barcode</th>
                        <th>Nama Barang</th>
                        <th>Supplier</th>
                        <th width="80" class="text-center">Stok</th>
                        <th width="110" class="text-center">Avg Jual/Bln</th>
                        <th width="130" class="text-right">Harga Beli</th>
                        <th width="160" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add to Cart -->
<div class="modal fade" id="modal-add-cart" tabindex="-1">
    <div class="modal-dialog" style="max-width:500px">
        <div class="modal-content">
            <div class="modal-header" style="background:#222d32;color:#fff;border-bottom:3px solid #00a65a">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff">&times;</button>
                <h4 class="modal-title"><i class="fa fa-cart-plus"></i> Tambah ke Keranjang PO</h4>
            </div>
            <div class="modal-body">
                <div style="background:#f8fffe;border-left:3px solid #00a65a;padding:10px 14px;border-radius:0 4px 4px 0;margin-bottom:16px">
                    <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.4px">Barang</div>
                    <div id="cart-nama" style="font-size:15px;font-weight:700;color:#222"></div>
                </div>
                <input type="hidden" id="cart-item_id">

                <!-- Loading state -->
                <div id="supplier-loading" class="text-center" style="padding:16px;display:none">
                    <i class="fa fa-spinner fa-spin"></i> Memuat supplier...
                </div>

                <!-- Mode: 1 supplier -->
                <div id="single-supplier-mode">
                    <div class="form-group">
                        <label>Supplier <span class="text-red">*</span></label>
                        <select class="form-control select2" id="cart-supplier_id" style="width:100%">
                            <option value="">— Pilih Supplier —</option>
                            <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s->supplier_id ?>"><?= htmlspecialchars($s->nama_supplier) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label>Qty Order <span class="text-red">*</span></label>
                                <input type="number" class="form-control" id="cart-qty" min="1" value="1">
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label>Harga Beli Ref</label>
                                <div class="input-group">
                                    <span class="input-group-addon">Rp</span>
                                    <input type="number" class="form-control" id="cart-ref_price" min="0" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mode: multi supplier -->
                <div id="multi-supplier-mode" style="display:none">
                    <div class="callout callout-info" style="padding:8px 12px;margin-bottom:10px;font-size:12px">
                        <i class="fa fa-info-circle"></i>
                        Item ini terdaftar di <strong id="supplier-count"></strong> supplier.
                        Centang supplier yang ingin dipesan.
                    </div>
                    <div id="supplier-checklist" style="margin-bottom:10px"></div>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group" style="margin-bottom:0">
                                <label>Qty Order (per supplier) <span class="text-red">*</span></label>
                                <input type="number" class="form-control" id="cart-qty-multi" min="1" value="1">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top:12px;margin-bottom:0">
                    <label>Catatan</label>
                    <input type="text" class="form-control" id="cart-notes" placeholder="Opsional">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                <button type="button" class="btn btn-success" id="btn-save-cart">
                    <i class="fa fa-cart-plus"></i> Tambah ke Keranjang
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    function toast(icon, msg) {
        Swal.fire({
            toast: true, position: 'top-end', icon: icon, title: msg,
            showConfirmButton: false, timer: 3000, timerProgressBar: true,
        });
    }

    var table = $('#tbl-stock-review').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: '<?= site_url('stock-review/get_json') ?>', type: 'POST' },
        columns: [
            { data: 'no', orderable: false, className: 'text-center' },
            { data: 'barcode' },
            { data: 'nama_item' },
            { data: 'nama_supplier' },
            { data: 'stock', className: 'text-center' },
            { data: 'avg_sales', orderable: false, className: 'text-center' },
            { data: 'ref_price', className: 'text-right' },
            { data: 'action', orderable: false, className: 'text-center' },
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        stateSave: true,
    });

    var isMultiMode = false;

    // Open modal — cek jumlah supplier item
    $(document).on('click', '.btn-add-cart', function () {
        var $btn = $(this);
        var itemId      = $btn.data('item_id');
        var defaultSid  = $btn.data('supplier_id');
        var defaultQty  = Math.max(1, Math.ceil($btn.data('avg') || 1));
        var defaultPrice= $btn.data('ref_price');

        $('#cart-item_id').val(itemId);
        $('#cart-nama').text($btn.data('nama'));
        $('#cart-notes').val('');
        $('#cart-qty').val(defaultQty);
        $('#cart-qty-multi').val(defaultQty);
        $('#single-supplier-mode').hide();
        $('#multi-supplier-mode').hide();
        $('#supplier-loading').show();
        $('#modal-add-cart').modal('show');

        // Fetch daftar supplier item ini
        $.post('<?= site_url('stock-review/get_item_suppliers') ?>', { item_id: itemId }, function (suppliers) {
            $('#supplier-loading').hide();

            if (suppliers.length >= 2) {
                // ── Multi-supplier mode ──
                isMultiMode = true;
                $('#supplier-count').text(suppliers.length);
                var html = '';
                suppliers.forEach(function (s) {
                    var priceStr = s.harga_beli > 0
                        ? ' — <span style="color:#888;font-size:11px">Rp ' + parseInt(s.harga_beli).toLocaleString('id-ID') + '</span>'
                        : '';
                    html += '<label style="display:flex;align-items:center;gap:8px;margin:6px 0;font-weight:normal;cursor:pointer">'
                          + '<input type="checkbox" class="supplier-check" value="' + s.supplier_id + '"'
                          + ' data-price="' + s.harga_beli + '"'
                          + ' data-nama="' + $('<span>').text(s.nama_supplier).html() + '"'
                          + ' checked style="width:15px;height:15px">'
                          + '<span>' + $('<span>').text(s.nama_supplier).html() + priceStr + '</span>'
                          + '</label>';
                });
                $('#supplier-checklist').html(html);
                $('#multi-supplier-mode').show();
            } else {
                // ── Single-supplier mode ──
                isMultiMode = false;
                $('#cart-ref_price').val(defaultPrice);
                if (defaultSid) { $('#cart-supplier_id').val(defaultSid).trigger('change'); }
                else            { $('#cart-supplier_id').val('').trigger('change'); }
                $('#single-supplier-mode').show();
            }
        }, 'json');
    });

    // Auto-fetch ref_price saat supplier berubah (single mode)
    $('#cart-supplier_id').on('change', function () {
        var item_id     = $('#cart-item_id').val();
        var supplier_id = $(this).val();
        if (!item_id || !supplier_id) return;
        $.post('<?= site_url('stock-review/get_ref_price') ?>', { item_id: item_id, supplier_id: supplier_id }, function (res) {
            if (res.ref_price !== undefined && res.ref_price > 0) {
                $('#cart-ref_price').val(res.ref_price);
            }
        }, 'json');
    });

    // Fungsi inti: langsung tambah ke cart (sudah melewati konfirmasi)
    function doAddToCart() {
        var item_id = $('#cart-item_id').val();
        var notes   = $('#cart-notes').val();
        var $btn    = $('#btn-save-cart');

        if (isMultiMode) {
            var checked = [];
            $('.supplier-check:checked').each(function () {
                checked.push({ supplier_id: $(this).val(), ref_price: $(this).data('price') });
            });
            if (checked.length === 0) { toast('warning', 'Centang minimal satu supplier.'); return; }
            var qty = parseInt($('#cart-qty-multi').val()) || 1;
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            var done = 0;
            checked.forEach(function (s) {
                $.post('<?= site_url('po-cart/add') ?>', {
                    item_id: item_id, supplier_id: s.supplier_id, qty: qty,
                    ref_price: s.ref_price || 0, notes: notes,
                }, function () {
                    done++;
                    if (done === checked.length) {
                        $btn.prop('disabled', false).html('<i class="fa fa-cart-plus"></i> Tambah ke Keranjang');
                        $('#modal-add-cart').modal('hide');
                        toast('success', 'Ditambahkan ke ' + checked.length + ' keranjang supplier.');
                    }
                }, 'json');
            });
        } else {
            var supplier_id = $('#cart-supplier_id').val();
            if (!supplier_id) { toast('warning', 'Pilih supplier terlebih dahulu.'); return; }
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            $.post('<?= site_url('po-cart/add') ?>', {
                item_id: item_id, supplier_id: supplier_id,
                qty: parseInt($('#cart-qty').val()) || 1,
                ref_price: parseInt($('#cart-ref_price').val()) || 0, notes: notes,
            }, function (res) {
                $btn.prop('disabled', false).html('<i class="fa fa-cart-plus"></i> Tambah ke Keranjang');
                $('#modal-add-cart').modal('hide');
                toast(res.status === 'success' ? 'success' : 'info',
                    res.status === 'success' ? 'Item berhasil ditambahkan.' : (res.message || 'Item diperbarui.'));
            }, 'json');
        }
    }

    // Submit — cek dulu apakah sudah dipesan
    $('#btn-save-cart').on('click', function () {
        var item_id = $('#cart-item_id').val();
        var $btn    = $(this);

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.post('<?= site_url('stock-review/check_item_ordered') ?>', { item_id: item_id }, function (res) {
            $btn.prop('disabled', false).html('<i class="fa fa-cart-plus"></i> Tambah ke Keranjang');

            if (res.already_ordered) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Barang sudah dipesan',
                    html: 'Item ini ' + res.context + '.<br><br>Tetap ingin menambahkan ke keranjang PO lagi?',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa fa-cart-plus"></i> Ya, tambah lagi',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#f39c12',
                }).then(function (result) {
                    if (result.isConfirmed) doAddToCart();
                });
            } else {
                doAddToCart();
            }
        }, 'json').fail(function () {
            $btn.prop('disabled', false).html('<i class="fa fa-cart-plus"></i> Tambah ke Keranjang');
            doAddToCart(); // fallback: langsung add jika check gagal
        });
    });


    // Reset modal saat tutup
    $('#modal-add-cart').on('hidden.bs.modal', function () {
        isMultiMode = false;
        $('#supplier-checklist').empty();
    });

    // Init Select2
    $('#cart-supplier_id').select2({ dropdownParent: $('#modal-add-cart') });
});
</script>
