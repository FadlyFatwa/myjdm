<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-header">
    <h1><i class="fa fa-history" style="color:#3c8dbc"></i> Detail Penerimaan
        <small>#<?= $receipt->receipt_id ?></small>
        <?php if ($receipt->is_direct): ?>
        <span class="label label-success" style="font-size:12px;vertical-align:middle">Langsung (Tanpa PO)</span>
        <?php endif; ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?= site_url('purchase-order/history') ?>">Histori Penerimaan</a></li>
        <li class="active">#<?= $receipt->receipt_id ?></li>
    </ol>
</div>

<div class="content">

<style>
body.dark-mode #totals-box            { background: #1a1d27 !important; color: #e5e7eb; }
body.dark-mode #totals-box span       { color: #e5e7eb; }
body.dark-mode .label-default         { background: #374151 !important; color: #d1d5db !important; }
</style>

    <!-- Info Card -->
    <div class="box box-primary">
        <div class="box-header with-border" style="background:#3c8dbc;color:#fff">
            <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi Penerimaan</h3>
            <div class="box-tools pull-right">
                <?php if ($receipt->label_status === 'labeled'): ?>
                <span class="label label-success" style="font-size:12px;padding:5px 10px" id="label-status-badge">
                    <i class="fa fa-check-circle"></i> Sudah Dilabeli
                    <?php if ($receipt->labeled_at): ?>
                        &mdash; <?= indo_date($receipt->labeled_at) ?><?= $receipt->labeled_by_name ? ' oleh ' . htmlspecialchars($receipt->labeled_by_name) : '' ?>
                    <?php endif; ?>
                </span>
                <?php else: ?>
                <span class="label label-warning" style="font-size:12px;padding:5px 10px" id="label-status-badge">
                    <i class="fa fa-clock-o"></i> Belum Dilabeli
                </span>
                <?php endif; ?>
            </div>
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

            <?php
                $subtotal_barang = array_sum(array_map(function ($it) { return $it->qty_received * $it->actual_price; }, $items));
                // Dihitung ulang dari item yang ada SEKARANG (bukan pakai po_receipt.total_amount
                // yang tersimpan) — soalnya subtotal barang bisa berubah lewat "Koreksi Data"
                // (tambah/ubah/hapus baris) tanpa total_amount ikut kehitung ulang otomatis.
                // diskon_invoice & ppn_nominal sendiri BISA diubah lewat "Pengaturan Invoice"
                // di bawah (khusus mode Koreksi, level 1) — begitu diubah, nilainya tersimpan
                // baru di po_receipt dan langsung dipakai di sini.
                $total_tampil = $subtotal_barang - (int) $receipt->diskon_invoice
                    + ($receipt->ppn_mode === 'add_distribute' ? (int) $receipt->ppn_nominal : 0);
            ?>
            <div style="margin-top:12px;background:#f4f6f9;border-radius:4px;padding:10px 14px;font-size:13px" id="totals-box">
                <div style="display:flex;justify-content:space-between"><span>Subtotal Barang</span><span><?= indo_currency($subtotal_barang) ?></span></div>
                <?php if ((int) $receipt->diskon_invoice > 0): ?>
                <div style="display:flex;justify-content:space-between"><span>Diskon Invoice</span><span>- <?= indo_currency($receipt->diskon_invoice) ?></span></div>
                <?php endif; ?>
                <?php if ($receipt->ppn_mode === 'add_distribute'): ?>
                <div style="display:flex;justify-content:space-between"><span>PPN (ditambah &amp; didistribusi ke harga beli)</span><span>+ <?= indo_currency($receipt->ppn_nominal) ?></span></div>
                <?php elseif ($receipt->ppn_mode === 'inclusive'): ?>
                <div style="display:flex;justify-content:space-between"><span>PPN (sudah termasuk harga beli, diekstrak)</span><span><?= indo_currency($receipt->ppn_nominal) ?></span></div>
                <?php endif; ?>
                <div style="display:flex;justify-content:space-between;border-top:1px solid #ddd;margin-top:4px;padding-top:4px;font-weight:700">
                    <span>Total Utang ke Supplier</span><span><?= indo_currency($total_tampil) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Item diterima -->
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> Item yang Diterima</h3>
            <div class="box-tools pull-right" style="display:flex;align-items:center;gap:8px">
                <span class="badge" style="background:#3c8dbc;font-size:12px;padding:4px 10px"><?= count($items) ?> item</span>
                <a href="<?= site_url('purchase-order/history/' . $receipt->receipt_id . '/edit') ?>" class="btn btn-warning btn-sm">
                    <i class="fa fa-pencil"></i> Edit Penerimaan
                </a>
            </div>
        </div>
        <div class="box-body table-responsive" style="padding:0">
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
                            <?php if ((int) $it->qty_ordered === 0): ?>
                            <span class="label label-success" style="font-size:10px;margin-left:4px">Ekstra</span>
                            <?php endif; ?>
                            <?php if (!$it->item_id): ?>
                            <br><span class="label label-warning" style="font-size:10px">Belum Terdaftar</span>
                            <button type="button" class="btn btn-xs btn-warning btn-register-item" style="margin-left:4px"
                                data-detail_id="<?= $it->id ?>"
                                data-name="<?= htmlspecialchars($it->item_name_temp ?? '') ?>"
                                data-modal_price="<?= (int) $it->actual_price ?>">
                                <i class="fa fa-plus-circle"></i> Daftarkan
                            </button>
                            <?php endif; ?>
                        </td>
                        <td style="padding:7px 12px;text-align:center" class="cell-qty">
                            <span class="view-qty" style="font-weight:700">
                                <?= $it->qty_received ?>
                                <?php if (!empty($it->nama_unit)): ?>
                                <span style="font-size:11px;color:#9ca3af;font-weight:400"><?= htmlspecialchars($it->nama_unit) ?></span>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td style="padding:7px 12px;text-align:right" class="cell-price">
                            <span class="view-price"><?= indo_currency($it->actual_price) ?></span>
                            <?php if ($it->harga_list): ?>
                            <div style="font-size:11px;color:#9ca3af">
                                List <?= indo_currency($it->harga_list) ?><?= $it->diskon_persen ? ' − ' . rtrim(rtrim(number_format($it->diskon_persen, 2, ',', ''), '0'), ',') . '%' : '' ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td style="padding:7px 12px;text-align:center">
                            <span class="view-pk" style="font-family:monospace;font-size:12px;letter-spacing:1px">
                                <?= htmlspecialchars($it->item_pk ?? '—') ?>
                            </span>
                        </td>
                        <td style="padding:9px 12px;text-align:right;font-weight:600" class="cell-subtotal">
                            <?= indo_currency($subtotal) ?>
                        </td>
                        <td class="cell-action"></td>
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
            <?php if (!empty($stock_ids)): ?>
            <a href="<?= site_url('barcode/barcode_qrcode_multiple') ?>?ids=<?= implode(',', $stock_ids) ?>"
               target="_blank" class="btn btn-info btn-sm">
                <i class="fa fa-print"></i> Print Barcode Item Ini
            </a>
            <?php endif; ?>
            <button type="button" id="btn-mark-labeled" class="btn btn-success btn-sm"
                    data-receipt-id="<?= $receipt->receipt_id ?>"
                    <?= $receipt->label_status === 'labeled' ? 'disabled' : '' ?>>
                <i class="fa fa-check"></i>
                <?= $receipt->label_status === 'labeled' ? 'Sudah Dilabeli' : 'Selesai Dilabeli' ?>
            </button>
        </div>
    </div>

<!-- Modal Daftarkan Barang ke Sistem — item belum terdaftar (satu mekanisme registrasi
     yang sama dipakai di seluruh modul pembelian: po_detail.php & po_receive.php). -->
<div class="modal fade" id="modal-register-item" tabindex="-1">
    <div class="modal-dialog" style="max-width:500px">
        <div class="modal-content">
            <div class="modal-header" style="background:#222d32;color:#fff;border-bottom:3px solid #f39c12">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff">&times;</button>
                <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Daftarkan Barang ke Sistem</h4>
            </div>
            <div class="modal-body" style="padding:20px">
                <div class="callout callout-info" style="margin-bottom:16px;padding:10px 14px">
                    <p style="margin:0;font-size:12px">
                        <i class="fa fa-info-circle"></i>
                        Barang ini belum ada di database. Lengkapi data berikut.
                        Stok akan diupdate otomatis sesuai qty yang sudah diterima.
                    </p>
                </div>
                <input type="hidden" id="reg-detail_id">
                <input type="hidden" id="reg-po_id" value="<?= $receipt->po_id ?>">
                <div class="form-group">
                    <label>Nama Barang <span class="text-red">*</span></label>
                    <input type="text" id="reg-nama_item" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label>Kode Beli (PK) <small class="text-muted">— otomatis dari modal</small></label>
                            <input type="text" id="reg-pk" class="form-control"
                                   placeholder="Otomatis" style="font-family:monospace;letter-spacing:1px;text-transform:uppercase"
                                   title="Diisi otomatis dari harga modal. Kosongkan untuk generate otomatis.">
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label>Barcode <small class="text-muted">(otomatis, bisa diedit)</small></label>
                            <input type="text" id="reg-barcode" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label>Kategori <span class="text-red">*</span></label>
                            <select id="reg-category_id" class="form-control">
                                <option value="">— Pilih —</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat->category_id ?>"><?= htmlspecialchars($cat->nama_category) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label>Satuan <span class="text-red">*</span></label>
                            <select id="reg-unit_id" class="form-control">
                                <option value="">— Pilih —</option>
                                <?php foreach ($units as $u): ?>
                                <option value="<?= $u->unit_id ?>"><?= htmlspecialchars($u->nama_unit) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Harga Modal (Rp) <span class="text-red">*</span></label>
                    <div class="input-group">
                        <span class="input-group-addon">Rp</span>
                        <input type="number" id="reg-modal" class="form-control" min="1" placeholder="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                <button type="button" class="btn btn-warning" id="btn-submit-register">
                    <i class="fa fa-check"></i> Daftarkan &amp; Update Stok
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    var nextBarcode = '<?= $next_barcode ?>';

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

    $(document).on('click', '.btn-register-item', function () {
        var modalPrice = $(this).data('modal_price') || 0;
        $('#reg-detail_id').val($(this).data('detail_id'));
        $('#reg-nama_item').val($(this).data('name'));
        $('#reg-modal').val(modalPrice);
        $('#reg-pk').val(modalPrice ? priceToK(modalPrice) : '');
        $('#reg-category_id, #reg-unit_id').val('');
        $('#reg-barcode').val(nextBarcode);
        $('#modal-register-item').data('trigger-row', $(this).closest('tr'));
        $('#modal-register-item').modal('show');
    });

    var regPkManual = false;
    $('#reg-pk').on('input', function () { regPkManual = $(this).val().trim() !== ''; });
    $('#reg-modal').on('input', function () {
        if (!regPkManual) $('#reg-pk').val(priceToK($(this).val()));
    });
    $('#modal-register-item').on('hidden.bs.modal', function () { regPkManual = false; });

    $('#btn-submit-register').on('click', function () {
        var nama  = $.trim($('#reg-nama_item').val());
        var cat   = $('#reg-category_id').val();
        var unit  = $('#reg-unit_id').val();
        var modal = $('#reg-modal').val();

        if (!nama || !cat || !unit || !modal) {
            Swal.fire({ icon: 'warning', title: 'Lengkapi Data', text: 'Semua field wajib diisi.' });
            return;
        }

        var pk   = $.trim($('#reg-pk').val()) || priceToK(modal);
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
        $.post('<?= site_url('purchase-order/register-item') ?>', {
            detail_id: $('#reg-detail_id').val(), po_id: $('#reg-po_id').val(),
            nama_item: nama, pk: pk, barcode: $('#reg-barcode').val(),
            category_id: cat, unit_id: unit, modal: modal,
        }, function (res) {
            $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Daftarkan & Update Stok');
            if (res.status !== 'success') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Terjadi kesalahan.' });
                return;
            }
            nextBarcode = String(parseInt(nextBarcode, 10) + 1).padStart(5, '0');

            var $row = $('#modal-register-item').data('trigger-row');
            if ($row && $row.length) {
                $row.find('.btn-register-item, .label-warning').remove();
                $row.find('td:first').append('<br><span class="label label-success" style="font-size:10px"><i class="fa fa-check"></i> Terdaftar</span>');
            }
            $('#modal-register-item').modal('hide');
            toast('success', 'Barang berhasil didaftarkan dan stok diupdate.');
        }, 'json');
    });

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

    <?php $flash = $this->session->flashdata('success'); if ($flash): ?>
    toast('success', '<?= addslashes($flash) ?>');
    <?php endif; ?>
    <?php $flash_err = $this->session->flashdata('error'); if ($flash_err): ?>
    toast('error', '<?= addslashes($flash_err) ?>');
    <?php endif; ?>

    // Selesai Dilabeli — kirim notifikasi WA setelah barang siap disimpan
    $('#btn-mark-labeled').on('click', function () {
        var $btn = $(this);
        var receiptId = $btn.data('receipt-id');

        Swal.fire({
            title: 'Selesai dilabeli?',
            html: 'Pastikan semua barang sudah ditempel label dan siap disimpan.<br>Notifikasi WA akan dikirim ke grup setelah ini.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, selesai',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#00a65a',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');

            $.post('<?= site_url('purchase-order/history/mark-labeled/') ?>' + receiptId, {
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>',
            }, function (res) {
                if (res.status !== 'success') {
                    toast('error', res.message || 'Gagal memproses.');
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Selesai Dilabeli');
                    return;
                }
                $btn.html('<i class="fa fa-check"></i> Sudah Dilabeli');
                $('#label-status-badge').replaceWith(
                    '<span class="label label-success" style="font-size:12px;padding:5px 10px" id="label-status-badge">' +
                    '<i class="fa fa-check-circle"></i> Sudah Dilabeli</span>'
                );
                toast('success', res.already_labeled ? 'Penerimaan ini sudah dilabeli sebelumnya.' : 'Notifikasi WA terkirim ke grup.');
            }, 'json').fail(function () {
                toast('error', 'Terjadi kesalahan server.');
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Selesai Dilabeli');
            });
        });
    });

});
</script>

</div>
