<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-header">
    <h1><i class="fa fa-inbox" style="color:#f39c12"></i> Penerimaan Barang
        <small><?= htmlspecialchars($po->po_number) ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?= site_url('purchase-order') ?>">Purchase Order</a></li>
        <li><a href="<?= site_url('purchase-order/' . $po->po_id) ?>"><?= htmlspecialchars($po->po_number) ?></a></li>
        <li class="active">Penerimaan</li>
    </ol>
</div>

<div class="content">

<style>
.recv-card { background:#fff; border-radius:6px; border:1px solid #e0e6ed; box-shadow:0 1px 4px rgba(0,0,0,.06); padding:16px 20px; margin-bottom:16px; display:flex; flex-wrap:wrap; gap:20px 40px; }
body.dark-mode .recv-card { background:#222537; border-color:#2d3148; }
.recv-meta label { font-size:11px; text-transform:uppercase; color:#888; margin:0; letter-spacing:.5px; display:block; }
.recv-meta p { margin:2px 0 0; font-weight:600; font-size:14px; color:#333; }
body.dark-mode .recv-meta p { color:#e5e7eb; }
body.dark-mode .recv-meta label { color:#6b7280; }

.gr-progress { height:5px; border-radius:3px; background:#e9ecef; overflow:hidden; margin-top:3px; }
.gr-progress-bar { height:100%; border-radius:3px; transition:width .3s; }

.table-recv th { background:#f4f6f9; font-size:12px; text-transform:uppercase; letter-spacing:.4px; color:#555; white-space:nowrap; vertical-align:middle; }
.table-recv td { vertical-align:middle !important; }
body.dark-mode .table-recv th { background:#1e2233; color:#9ca3af; border-color:#2d3148; }
body.dark-mode .table-recv td { border-color:#2d3148; color:#e5e7eb; }
body.dark-mode .table-recv tbody tr { background:#222537; }
body.dark-mode .table-recv tbody tr.row-done { background:#0d2e1d !important; }

.recv-row.row-search-match { outline:2px solid #f39c12; outline-offset:-2px; }
.recv-row.row-search-match td:first-child { border-left:4px solid #f39c12 !important; }
.recv-row.row-search-dim { opacity:.25; }
body.dark-mode .recv-row.row-search-match { outline-color:#f39c12; }
mark.search-hl { background:#fff176; color:inherit; border-radius:2px; padding:0 1px; }
body.dark-mode mark.search-hl { background:#7c6d00; color:#ffe; }
body.dark-mode .box        { background:#222537; border-color:#2d3148; }
body.dark-mode .box-header { background:#1e2233 !important; border-color:#2d3148 !important; color:#e5e7eb !important; }
body.dark-mode .box-footer { background:#1e2233; border-color:#2d3148; }
body.dark-mode .form-control { background:#1a1d27 !important; border-color:#374151 !important; color:#e5e7eb !important; }
body.dark-mode .input-group-addon { background:#252836 !important; border-color:#374151 !important; color:#9ca3af !important; }
</style>

<!-- Info PO -->
<div class="recv-card">
    <div class="recv-meta">
        <label>No. PO</label>
        <p><?= htmlspecialchars($po->po_number) ?></p>
    </div>
    <div class="recv-meta">
        <label>Supplier</label>
        <p><?= htmlspecialchars($po->nama_supplier) ?></p>
        <?php if ($po->phone): ?><small class="text-muted"><i class="fa fa-phone"></i> <?= htmlspecialchars($po->phone) ?></small><?php endif; ?>
    </div>
    <div class="recv-meta">
        <label>Tanggal PO</label>
        <p><?= indo_date($po->po_date) ?></p>
    </div>
    <div class="recv-meta">
        <label>Status</label>
        <p>
            <?php if ($po->status === 'partial'): ?>
                <span class="label label-warning">Sebagian Diterima</span>
            <?php else: ?>
                <span class="label label-info">Terkirim</span>
            <?php endif; ?>
        </p>
    </div>
    <div style="margin-left:auto;display:flex;align-items:center">
        <a href="<?= site_url('purchase-order/' . $po->po_id) ?>" class="btn btn-default btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali ke PO
        </a>
    </div>
</div>

<!-- Form Penerimaan -->
<div class="box box-warning">
    <div class="box-header with-border" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <h3 class="box-title" style="flex:1;min-width:0"><i class="fa fa-inbox"></i> Daftar Item — Isi Qty &amp; Harga Aktual</h3>
        <div style="display:flex;align-items:center;gap:6px">
            <div class="input-group input-group-sm" style="width:220px">
                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                <input type="text" id="item-search" class="form-control" placeholder="Cari nama / kode barang...">
                <span class="input-group-btn">
                    <button type="button" id="btn-clear-search" class="btn btn-default" title="Hapus pencarian" style="display:none">
                        <i class="fa fa-times"></i>
                    </button>
                </span>
            </div>
            <span id="search-count" class="badge bg-blue" style="display:none;white-space:nowrap"></span>
        </div>
    </div>
    <form action="<?= site_url('purchase-order/receive') ?>" method="post" id="form-gr">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
        <input type="hidden" name="po_id" value="<?= $po->po_id ?>">

        <div class="box-body table-responsive" style="padding:0">
            <table class="table table-bordered table-condensed table-recv" style="margin:0">
                <thead>
                    <tr>
                        <th style="min-width:200px;padding:9px 12px">Nama Barang</th>
                        <th width="70"  class="text-center" style="padding:9px 8px">Stok</th>
                        <th width="140" class="text-center" style="padding:9px 8px">Progress</th>
                        <th width="120" class="text-right"  style="padding:9px 12px">Harga PO</th>
                        <th width="90"  class="text-center" style="padding:9px 8px">Qty Terima</th>
                        <th width="165" class="text-right"  style="padding:9px 12px">Harga Aktual / PK</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($details as $d):
                    $pct       = $d->qty_ordered > 0 ? round($d->qty_received / $d->qty_ordered * 100) : 0;
                    $remaining = $d->qty_ordered - $d->qty_received;
                    $bar_color = $pct >= 100 ? '#00a65a' : ($pct > 0 ? '#f39c12' : '#ddd');
                    $done      = $remaining <= 0;
                ?>
                <tr class="recv-row <?= $done ? 'row-done' : '' ?>"
                    data-search="<?= strtolower(htmlspecialchars($d->display_name . ' ' . ($d->barcode ?? ''))) ?>"
                    style="<?= $done ? 'background:#f0fff4;opacity:.8' : '' ?>">
                    <td style="padding:9px 12px">
                        <?php if ($d->barcode): ?>
                        <div style="font-size:11px;color:#888;margin-bottom:2px"><?= htmlspecialchars($d->barcode) ?></div>
                        <?php endif; ?>
                        <strong><?= htmlspecialchars($d->display_name) ?></strong>
                        <?php if (!$d->item_id): ?>
                        <br><span class="label label-warning" style="font-size:10px">Belum Terdaftar</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center" style="padding:9px 8px">
                        <?php
                        $s = $d->stock ?? null;
                        if ($s === null)    echo '<span class="text-muted">—</span>';
                        elseif ($s <= 0)    echo '<span class="text-danger"><b>' . $s . '</b></span>';
                        elseif ($s <= 3)    echo '<span class="text-warning"><b>' . $s . '</b></span>';
                        else                echo $s;
                        ?>
                    </td>
                    <td class="text-center" style="padding:9px 8px">
                        <div style="font-size:12px;font-weight:600;color:<?= $pct >= 100 ? '#00a65a' : ($pct > 0 ? '#e08e0b' : '#999') ?>">
                            <?= $d->qty_received ?>/<?= $d->qty_ordered ?>
                            <?php if (!empty($d->nama_unit)): ?>
                            <span style="font-size:10px;color:#9ca3af;font-weight:400"><?= htmlspecialchars($d->nama_unit) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="gr-progress">
                            <div class="gr-progress-bar" style="width:<?= $pct ?>%;background:<?= $bar_color ?>"></div>
                        </div>
                    </td>
                    <td class="text-right" style="padding:9px 12px"><?= indo_currency($d->unit_price) ?></td>
                    <td class="text-center" style="padding:7px 8px;white-space:nowrap">
                        <input type="hidden" name="detail_id[]" value="<?= $d->id ?>">
                        <?php if ($done): ?>
                            <input type="number" value="0" disabled
                                   style="width:55px;display:inline-block;background:#f5f5f5;border:1px solid #ddd;border-radius:4px;text-align:center">
                            <input type="hidden" name="qty_received[]" value="0">
                            <span class="label label-success" style="font-size:10px;margin-left:3px">Selesai</span>
                        <?php else: ?>
                            <input type="number" name="qty_received[]" class="form-control input-xs text-center"
                                   min="0" max="<?= $remaining ?>" value="0"
                                   style="width:55px;display:inline-block">
                            <?php if (!empty($d->nama_unit)): ?>
                            <span style="font-size:11px;color:#9ca3af;margin-left:2px"><?= htmlspecialchars($d->nama_unit) ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td style="padding:7px 12px">
                        <?php if ($done): ?>
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon" style="font-size:11px">Rp</span>
                                <input type="text" class="form-control text-right"
                                       value="<?= number_format((int)$d->unit_price, 0, ',', '.') ?>"
                                       style="width:110px" disabled>
                            </div>
                            <input type="hidden" name="actual_price[]" value="<?= (int)$d->unit_price ?>">
                            <input type="hidden" name="pk_new[]" value="">
                        <?php else: ?>
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon" style="font-size:11px">Rp</span>
                                <input type="text" name="actual_price[]" class="form-control input-actual-price text-right"
                                       value="<?= number_format((int)$d->unit_price, 0, ',', '.') ?>"
                                       style="width:110px">
                            </div>
                            <input type="text" name="pk_new[]" class="form-control input-xs input-pk-new"
                                   style="margin-top:4px;font-family:monospace;letter-spacing:1px;text-transform:uppercase;font-size:11px"
                                   placeholder="PK (otomatis, bisa diedit)"
                                   title="Auto dari harga aktual. Bisa ditambah price list, cth: MY3/UMY2">
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="box-footer">
            <div class="row" style="margin-bottom:14px">
                <div class="col-sm-4">
                    <div class="form-group" style="margin-bottom:0">
                        <label style="font-size:12px;color:#555;font-weight:600">
                            No. Invoice Supplier <span class="text-red">*</span>
                        </label>
                        <input type="text" name="supplier_invoice_no" class="form-control"
                               placeholder="cth: INV/2025/00123" required>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group" style="margin-bottom:0">
                        <label style="font-size:12px;color:#555;font-weight:600">
                            Tanggal Invoice <span class="text-red">*</span>
                            <small class="text-muted">(→ tanggal stok masuk)</small>
                        </label>
                        <input type="date" name="invoice_date" class="form-control" required>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group" style="margin-bottom:0">
                        <label style="font-size:12px;color:#555;font-weight:600">Tanggal Penerimaan Aktual</label>
                        <input type="date" name="receive_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
            </div>
            <div class="row" style="margin-bottom:14px">
                <div class="col-sm-4">
                    <div class="form-group" style="margin-bottom:0">
                        <label style="font-size:12px;color:#555;font-weight:600">Ongkir (Rp)</label>
                        <input type="text" name="ongkir" id="ongkir" class="form-control" value="0" placeholder="0">
                        <small class="text-muted">Dibayar tunai dari Kas</small>
                    </div>
                </div>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center">
                <span class="text-muted" style="font-size:12px">
                    <i class="fa fa-info-circle"></i>
                    Input 0 pada qty untuk melewati item. Harga aktual wajib diisi untuk item yang diterima.
                </span>
                <div style="display:flex;gap:8px">
                    <a href="<?= site_url('purchase-order/' . $po->po_id) ?>" class="btn btn-default">
                        <i class="fa fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-success" id="btn-gr">
                        <i class="fa fa-check-circle"></i> Proses Penerimaan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

</div>

<script>
$(function () {
    function toast(icon, msg) {
        Swal.fire({ toast:true, position:'top-end', icon:icon, title:msg,
            showConfirmButton:false, timer:3500, timerProgressBar:true });
    }

    function formatRp(val) {
        var s = String(val).replace(/[^0-9]/g, '');
        return s.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function unformatRp(val) {
        return parseInt(String(val).replace(/\./g, ''), 10) || 0;
    }
    function priceToK(rawStr) {
        var s   = String(rawStr).replace(/[^0-9]/g, '');
        var map = {'0':'Y','1':'S','2':'I','3':'T','4':'O','5':'M','6':'P','7':'U','8':'L','9':'X'};
        var out = '', zeros = 0;
        for (var i = 0; i < s.length; i++) {
            if (s[i] === '0') { zeros++; }
            else {
                if (zeros > 1) out += 'Y' + zeros;
                else if (zeros === 1) out += 'Y';
                zeros = 0;
                out += map[s[i]] || s[i];
            }
        }
        if (zeros > 1) out += 'Y' + zeros;
        else if (zeros === 1) out += 'Y';
        return out.toUpperCase();
    }

    // ── Search item ─────────────────────────────────────────────
    var $rows       = $('.recv-row');
    var totalRows   = $rows.length;
    var origNameMap = {};   // simpan HTML asli nama barang agar highlight bisa di-reset

    $rows.each(function (i) {
        origNameMap[i] = $(this).find('td:first strong').html();
    });

    function doSearch(kw) {
        var $btn   = $('#btn-clear-search');
        var $badge = $('#search-count');

        if (!kw) {
            $btn.hide(); $badge.hide();
            $rows.each(function (i) {
                $(this).removeClass('row-search-match row-search-dim');
                $(this).find('td:first strong').html(origNameMap[i]);
            });
            return;
        }

        $btn.show();
        var re    = new RegExp('(' + kw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        var found = 0;
        var $first = null;

        $rows.each(function (i) {
            var haystack = $(this).data('search') || '';
            var match    = haystack.indexOf(kw.toLowerCase()) !== -1;

            $(this).removeClass('row-search-match row-search-dim');

            if (match) {
                found++;
                $(this).addClass('row-search-match');
                if (!$first) $first = $(this);
                // highlight teks dalam nama barang
                var hl = origNameMap[i].replace(re, '<mark class="search-hl">$1</mark>');
                $(this).find('td:first strong').html(hl);
            } else {
                $(this).addClass('row-search-dim');
                $(this).find('td:first strong').html(origNameMap[i]);
            }
        });

        $badge.text(found + ' / ' + totalRows).show();

        // scroll ke baris pertama yang cocok
        if ($first) {
            var $wrap = $('.box-body.table-responsive');
            var offset = $first.position().top + $wrap.scrollTop() - 20;
            $wrap.animate({ scrollTop: offset }, 220);
        }
    }

    var searchTimer;
    $('#item-search').on('input', function () {
        clearTimeout(searchTimer);
        var kw = $.trim($(this).val());
        searchTimer = setTimeout(function () { doSearch(kw); }, 200);
    });

    $('#btn-clear-search').on('click', function () {
        $('#item-search').val('').trigger('input').focus();
    });

    // Format harga & generate PK
    $(document).on('input', '.input-actual-price', function () {
        var raw = String($(this).val()).replace(/[^0-9]/g, '');
        $(this).val(formatRp(raw));
        $(this).closest('td').find('.input-pk-new').val(priceToK(raw));
    });
    $(document).on('click', '.input-actual-price', function () { $(this).select(); });

    // Format harga ongkir
    $(document).on('input', '#ongkir', function () {
        var raw = String($(this).val()).replace(/[^0-9]/g, '');
        $(this).val(formatRp(raw));
    });
    $(document).on('click', '#ongkir', function () { $(this).select(); });

    // Init PK dari harga yang sudah ada
    $('.input-actual-price').each(function () {
        var raw = String(unformatRp($(this).val()));
        $(this).closest('td').find('.input-pk-new').val(priceToK(raw));
    });

    // Validasi & konfirmasi submit
    $('#form-gr').on('submit', function (e) {
        e.preventDefault();

        var totalQty = 0;
        $('input[name="qty_received[]"]').each(function () {
            if (!$(this).prop('disabled')) totalQty += parseInt($(this).val()) || 0;
        });
        if (totalQty === 0) {
            Swal.fire({ icon:'warning', title:'Tidak ada barang diterima',
                text:'Minimal satu item harus memiliki qty lebih dari 0.' });
            return;
        }

        Swal.fire({
            title: 'Proses penerimaan barang?',
            text : 'Stok akan diupdate. Harga beli & kode PK diperbarui jika ada perubahan.',
            icon : 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-check"></i> Ya, proses',
            cancelButtonText : 'Batal',
        }).then(function (result) {
            if (result.isConfirmed) {
                $('.input-actual-price').each(function () {
                    $(this).val(unformatRp($(this).val()));
                });
                $('#ongkir').val(unformatRp($('#ongkir').val()));
                $('#btn-gr').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');
                $('#form-gr')[0].submit();
            }
        });
    });
});
</script>
