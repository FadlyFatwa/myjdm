<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $receipt = $receipt ?? null; ?>

<div class="content-header">
    <?php if ($receipt): ?>
    <h1><i class="fa fa-pencil" style="color:#f39c12"></i> Edit Penerimaan
        <small>#<?= $receipt->receipt_id ?></small>
        <?php if ($receipt->is_direct): ?>
        <span class="label label-success" style="font-size:12px;vertical-align:middle">Langsung (Tanpa PO)</span>
        <?php endif; ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?= site_url('purchase-order/history') ?>">Histori Penerimaan</a></li>
        <li><a href="<?= site_url('purchase-order/history/' . $receipt->receipt_id) ?>">#<?= $receipt->receipt_id ?></a></li>
        <li class="active">Edit</li>
    </ol>
    <?php else: ?>
    <h1><i class="fa fa-inbox" style="color:#f39c12"></i> Penerimaan Barang
        <small><?= htmlspecialchars($po->po_number) ?></small>
        <?php if ($po->is_direct): ?>
        <span class="label label-success" style="font-size:12px;vertical-align:middle">Langsung (Tanpa PO)</span>
        <?php endif; ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?= site_url('purchase-order/receiving') ?>">Penerimaan</a></li>
        <li><a href="<?= site_url('purchase-order/' . $po->po_id) ?>"><?= htmlspecialchars($po->po_number) ?></a></li>
        <li class="active">Penerimaan</li>
    </ol>
    <?php endif; ?>
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

.extra-box { border:1.5px dashed #00a65a; border-radius:4px; margin:16px 20px 0; overflow:hidden; }
.extra-box-head { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 14px; background:#f0fff6; border-bottom:1.5px dashed #00a65a; }
body.dark-mode .extra-box-head { background:#10301f; }
.extra-row td { vertical-align:middle !important; }
.total-box { background:#f4f6f9; border-radius:4px; padding:10px 14px; font-size:13px; }
.total-box .grand { font-size:16px; font-weight:700; }
body.dark-mode .total-box { background:#1a1d27 !important; }

.edit-thead { background:#f4f6f9; }
body.dark-mode .edit-thead { background:#1e2233 !important; color:#9ca3af !important; }
body.dark-mode .edit-thead th { color:#9ca3af !important; }
.edit-tfoot { background:#f8f9fa; }
body.dark-mode .edit-tfoot { background:#1e2233 !important; color:#e5e7eb !important; }
body.dark-mode .edit-tfoot td { color:#e5e7eb !important; }
.invoice-settings-body { border-top:1px solid #f4f6f9; background:#fffdf5; }
body.dark-mode .invoice-settings-body { background:#1a1d27 !important; border-top-color:#2d3148 !important; }
body.dark-mode .edit-qty,
body.dark-mode .edit-harga-list,
body.dark-mode .edit-diskon-persen,
body.dark-mode .edit-price,
body.dark-mode .edit-pk { background:#1a1d27 !important; border-color:#374151 !important; color:#e5e7eb !important; }
body.dark-mode .edit-pk:disabled { background:#15171e !important; color:#6b7280 !important; }
.prev-ppn, .prev-harga-final, .prev-pk-final { color:#555; }
body.dark-mode .prev-ppn,
body.dark-mode .prev-harga-final,
body.dark-mode .prev-pk-final { background:#15171e !important; border-color:#374151 !important; color:#9ca3af !important; }
body.dark-mode .edit-qty::placeholder,
body.dark-mode .edit-harga-list::placeholder,
body.dark-mode .edit-diskon-persen::placeholder,
body.dark-mode .edit-price::placeholder { color:#6b7280 !important; }
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
        <?php if ($receipt): ?>
        <a href="<?= site_url('purchase-order/history/' . $receipt->receipt_id) ?>" class="btn btn-default btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali ke Detail Penerimaan
        </a>
        <?php else: ?>
        <a href="<?= site_url('purchase-order/' . $po->po_id) ?>" class="btn btn-default btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali ke PO
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Form Penerimaan -->
<div class="box box-warning">
<?php if ($receipt): ?>
    <div class="box-header with-border" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <h3 class="box-title" style="flex:1;min-width:0"><i class="fa fa-pencil"></i> Edit Item Penerimaan Ini</h3>
        <span class="badge" style="background:#3c8dbc;font-size:12px;padding:4px 10px"><?= count($items) ?> item</span>
    </div>

    <div class="box-body table-responsive" style="padding:0">
        <?php if (!empty($available_items)): ?>
        <div id="add-item-row" style="padding:10px 16px;background:#e8f5e9;border-bottom:1px solid #c8e6c9;">
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

        <div style="padding:10px 16px;background:#e8f5e9;border-bottom:1px solid #c8e6c9;">
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-extra-item">
                <i class="fa fa-plus"></i> Tambah Barang Ekstra
            </button>
            <span class="text-muted" style="font-size:11px;margin-left:8px">Item di luar PO ini — sudah ada di sistem, atau belum terdaftar</span>
        </div>

        <table id="edit-items-table" class="table table-condensed table-bordered" style="margin:0">
            <thead>
                <tr class="edit-thead" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#555">
                    <th style="padding:8px 12px">Nama Barang</th>
                    <th style="padding:8px 12px;width:100px;text-align:center">Qty Diterima</th>
                    <th style="padding:8px 12px;width:130px;text-align:center">Harga List / Diskon %</th>
                    <th style="padding:8px 12px;width:165px;text-align:right">Harga Aktual / PK <span class="text-muted" style="text-transform:none;font-weight:400">(ditahan)</span></th>
                    <th class="ppn-prev-col" style="display:none;padding:8px 12px;width:150px;text-align:right">PPN / Harga Final / PK <span class="text-muted" style="text-transform:none;font-weight:400">(preview)</span></th>
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
                    $qty_max = (int) $it->qty_ordered > 0 ? (int) $it->qty_ordered : 99999;
                ?>
                <tr data-detail-id="<?= $it->id ?>" data-qty="<?= $it->qty_received ?>">
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
                        <input type="number" class="form-control input-xs text-center edit-qty"
                            value="<?= $it->qty_received ?>" min="0" max="<?= $qty_max ?>"
                            data-unit="<?= htmlspecialchars($it->nama_unit ?? '') ?>"
                            data-max="<?= $qty_max ?>"
                            style="width:60px;margin:0 auto">
                        <div class="text-muted" style="font-size:10px">
                            <?= (int) $it->qty_ordered > 0 ? 'maks ' . $it->qty_ordered : 'ekstra, tanpa batas order' ?>
                        </div>
                    </td>
                    <?php $level1 = (int) $this->fungsi->user_login()->level === 1; ?>
                    <td style="padding:7px 8px;text-align:center">
                        <input type="text" class="form-control input-xs text-right edit-harga-list"
                            value="<?= $it->harga_list ? number_format((int) $it->harga_list, 0, ',', '.') : '' ?>"
                            placeholder="Harga List" style="width:110px;margin-bottom:3px" title="Harga sebelum diskon (opsional)" <?= $level1 ? '' : 'disabled' ?>>
                        <input type="text" class="form-control input-xs text-right edit-diskon-persen"
                            value="<?= $it->diskon_persen ? rtrim(rtrim(number_format($it->diskon_persen, 2, ',', ''), '0'), ',') : '' ?>"
                            placeholder="Diskon %" style="width:110px" title="Diskon % dari Harga List (opsional)" <?= $level1 ? '' : 'disabled' ?>>
                    </td>
                    <td style="padding:7px 12px" class="cell-price">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon" style="font-size:11px">Rp</span>
                            <input type="text" class="form-control text-right edit-price"
                                value="<?= number_format((int)$it->actual_price, 0, ',', '.') ?>"
                                style="width:110px" <?= $level1 ? '' : 'disabled' ?>>
                        </div>
                        <input type="text" class="form-control input-xs edit-pk"
                            value="<?= htmlspecialchars($it->item_pk ?? '') ?>"
                            style="margin-top:4px;font-family:monospace;letter-spacing:1px;text-transform:uppercase;font-size:11px"
                            placeholder="PK (otomatis, bisa diedit)"
                            title="Auto dari harga aktual. Bisa ditambah price list, cth: MY3/UMY2"
                            <?= ($it->item_id && $level1) ? '' : 'disabled title="' . ($level1 ? 'Item belum terdaftar — PK diisi otomatis saat didaftarkan' : 'Hanya level 1 yang bisa mengubah PK') . '"' ?>>
                    </td>
                    <td class="ppn-prev-col" style="display:none;padding:7px 12px;text-align:right">
                        <input type="text" class="form-control input-xs text-right prev-ppn" readonly tabindex="-1"
                            placeholder="PPN" style="width:130px;margin-left:auto;margin-bottom:3px;background:#f4f6f9">
                        <input type="text" class="form-control input-xs text-right prev-harga-final" readonly tabindex="-1"
                            placeholder="Harga Final" style="width:130px;margin-left:auto;margin-bottom:3px;background:#f4f6f9">
                        <input type="text" class="form-control input-xs prev-pk-final" readonly tabindex="-1"
                            placeholder="PK Final" style="width:130px;margin-left:auto;font-family:monospace;letter-spacing:1px;text-transform:uppercase;font-size:11px;background:#f4f6f9">
                    </td>
                    <td style="padding:9px 12px;text-align:right;font-weight:600" class="cell-subtotal">
                        <?= indo_currency($subtotal) ?>
                    </td>
                    <td style="padding:7px 12px;text-align:center;white-space:nowrap" class="cell-action">
                        <button type="button" class="btn btn-danger btn-xs btn-del-detail"
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
                <tr class="edit-tfoot" style="font-weight:700">
                    <td colspan="4" style="padding:10px 12px;text-align:right;font-size:13px">Total Nilai Penerimaan</td>
                    <td class="ppn-prev-col" style="display:none"></td>
                    <td style="padding:10px 12px;text-align:right;font-size:14px;color:#3c8dbc" id="grand-total-cell"><?= indo_currency($grand_total) ?></td>
                    <td class="cell-action"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <?php if ((int) $this->fungsi->user_login()->level === 1): ?>
    <div class="box-body invoice-settings-body">
        <h4 style="font-size:14px;margin:0 0 12px"><i class="fa fa-sliders"></i> Pengaturan Invoice</h4>
        <div class="row" style="margin-bottom:0">
            <div class="col-sm-4">
                <div class="form-group">
                    <label style="font-size:12px;font-weight:600">No. Invoice Supplier</label>
                    <input type="text" id="inp-invoice-no" class="form-control"
                           value="<?= htmlspecialchars($receipt->supplier_invoice_no ?? '') ?>"
                           placeholder="cth: INV/2025/00123">
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label style="font-size:12px;font-weight:600">Tanggal Invoice</label>
                    <input type="date" id="inp-invoice-date" class="form-control"
                           value="<?= htmlspecialchars($receipt->invoice_date ?? '') ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label style="font-size:12px;font-weight:600">Diskon Invoice (Rp)</label>
                    <input type="text" id="inp-diskon-invoice" class="form-control input-rp-fmt"
                           value="<?= number_format((int) $receipt->diskon_invoice, 0, ',', '.') ?>">
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group" style="margin-bottom:0">
                    <label style="font-size:12px;font-weight:600">Ongkir (Rp)</label>
                    <input type="text" id="inp-ongkir" class="form-control input-rp-fmt"
                           value="<?= number_format((int) $receipt->ongkir, 0, ',', '.') ?>">
                    <small class="text-muted">Ongkir lama otomatis di-void, dicatat ulang sebagai Beban baru</small>
                </div>
            </div>
            <div class="col-sm-4">
                <label style="font-size:12px;font-weight:600;display:block">Mode PPN</label>
                <div class="radio" style="margin:3px 0 0">
                    <label style="font-weight:400;font-size:12.5px">
                        <input type="radio" name="inp-ppn-mode" value="none" <?= $receipt->ppn_mode === 'none' ? 'checked' : '' ?>> Tanpa PPN
                    </label>
                </div>
                <div class="radio" style="margin:3px 0">
                    <label style="font-weight:400;font-size:12.5px">
                        <input type="radio" name="inp-ppn-mode" value="add_distribute" <?= $receipt->ppn_mode === 'add_distribute' ? 'checked' : '' ?>> PPN ditambah &amp; didistribusi ke harga beli
                    </label>
                </div>
                <div class="radio" style="margin:3px 0 0">
                    <label style="font-weight:400;font-size:12.5px">
                        <input type="radio" name="inp-ppn-mode" value="inclusive" <?= $receipt->ppn_mode === 'inclusive' ? 'checked' : '' ?>> Harga beli sudah termasuk PPN
                    </label>
                </div>
            </div>
        </div>
        <div class="alert alert-info" style="padding:8px 12px;font-size:12px;margin:10px 0 14px">
            <i class="fa fa-info-circle"></i> Qty, Harga per baris, No. Invoice, Tanggal Invoice, Diskon Invoice, Ongkir, dan Mode PPN di atas
            <b>belum tersimpan</b> — semuanya ditahan dulu dan baru diproses bareng dalam satu transaksi saat tombol
            <b>"Simpan Semua Perubahan Harga &amp; Qty"</b> di bawah diklik. Kalau ada satu saja yang gagal disimpan,
            seluruh perubahan dibatalkan (rollback), tidak ada yang tersimpan sebagian. Kalau Mode PPN "ditambah &amp; didistribusi"
            aktif, harga beli tiap item <b>ikut dihitung ulang</b> saat itu juga sesuai porsi PPN-nya.
            <?php if ($receipt->ppn_mode === 'add_distribute'): ?>
            <br><i class="fa fa-info-circle"></i> Karena mode PPN resi ini sudah "ditambah &amp; didistribusi", kolom
            <b>Harga Aktual</b> di bawah menampilkan harga <b>sebelum PPN</b> (PPN sudah "ditarik keluar" lagi dari harga
            yang tersimpan) — biar tidak bingung dan tidak numpuk PPN di atas PPN tiap kali diedit ulang.
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="box-footer">
        <?php
            $subtotal_barang_edit = $grand_total;
            $total_tampil_edit = $subtotal_barang_edit - (int) $receipt->diskon_invoice
                + ($receipt->ppn_mode === 'add_distribute' ? (int) $receipt->ppn_nominal : 0);
        ?>
        <div class="total-box" style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between"><span>Subtotal Barang</span><span id="edit-t-barang"><?= indo_currency($subtotal_barang_edit) ?></span></div>
            <div id="edit-row-diskon" style="display:<?= (int) $receipt->diskon_invoice > 0 ? 'flex' : 'none' ?>;justify-content:space-between">
                <span>Diskon Invoice</span><span id="edit-t-diskon">- <?= indo_currency($receipt->diskon_invoice) ?></span>
            </div>
            <div id="edit-row-ppn" style="display:<?= $receipt->ppn_mode !== 'none' ? 'flex' : 'none' ?>;justify-content:space-between">
                <span id="edit-lbl-ppn"><?= $receipt->ppn_mode === 'inclusive' ? 'PPN (sudah termasuk harga beli, diekstrak)' : 'PPN (ditambah & didistribusi ke harga beli)' ?></span>
                <span id="edit-t-ppn"><?= $receipt->ppn_mode === 'add_distribute' ? '+ ' : '' ?><?= indo_currency($receipt->ppn_nominal) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;border-top:1px solid #ddd;margin-top:4px;padding-top:4px;font-weight:700" class="grand">
                <span>Total Utang ke Supplier</span><span id="edit-t-utang"><?= indo_currency($total_tampil_edit) ?></span>
            </div>
            <p class="text-muted" style="font-size:11px;margin:6px 0 0">
                <i class="fa fa-info-circle"></i> Angka di atas cuma perkiraan live — nilai final dihitung ulang di server saat disimpan.
            </p>
        </div>
        <button type="button" class="btn btn-warning" id="btn-save-prices">
            <i class="fa fa-save"></i>
            <?= (int) $this->fungsi->user_login()->level === 1 ? 'Simpan Semua Perubahan Harga & Qty' : 'Simpan Perubahan Qty' ?>
        </button>
    </div>
<?php else: ?>
    <?php if (!empty($details)): ?>
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
    <?php else: ?>
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-inbox"></i> Penerimaan Langsung — Tambahkan Barang di Bawah</h3>
    </div>
    <?php endif; ?>
    <form action="<?= site_url('purchase-order/receive') ?>" method="post" id="form-gr">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
        <input type="hidden" name="po_id" value="<?= $po->po_id ?>">

        <?php if (!empty($details)): ?>
        <div class="box-body table-responsive" style="padding:0">
            <table class="table table-bordered table-condensed table-recv" style="margin:0">
                <thead>
                    <tr>
                        <th style="min-width:200px;padding:9px 12px">Nama Barang</th>
                        <th width="70"  class="text-center" style="padding:9px 8px">Stok</th>
                        <th width="140" class="text-center" style="padding:9px 8px">Progress</th>
                        <th width="120" class="text-right"  style="padding:9px 12px">Harga PO</th>
                        <th width="90"  class="text-center" style="padding:9px 8px">Qty Terima</th>
                        <th width="130" class="text-center" style="padding:9px 8px">Harga List / Diskon %</th>
                        <th width="165" class="text-right"  style="padding:9px 12px">Harga Aktual / PK</th>
                        <th class="ppn-prev-col" width="150" style="display:none;text-align:right;padding:9px 12px">PPN / Harga Final / PK <span class="text-muted" style="font-weight:400">(preview)</span></th>
                        <th width="130" class="text-right" style="padding:9px 12px">Subtotal</th>
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
                        <?php if ($d->qty_received > 0): ?>
                        <button type="button" class="btn btn-xs btn-warning btn-register-item" style="margin-left:4px"
                            data-detail_id="<?= $d->id ?>"
                            data-name="<?= htmlspecialchars($d->item_name_temp ?? $d->display_name) ?>"
                            data-modal_price="<?= (int) ($d->actual_price ?: $d->unit_price) ?>">
                            <i class="fa fa-plus-circle"></i> Daftarkan
                        </button>
                        <?php else: ?>
                        <br><small class="text-muted">— terima dulu sebelum daftarkan</small>
                        <?php endif; ?>
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
                    <td class="text-center" style="padding:7px 8px">
                        <?php if ($done): ?>
                            <span class="text-muted">—</span>
                        <?php else: ?>
                            <input type="text" name="harga_list[]" class="form-control input-xs input-harga-list text-right"
                                   placeholder="Harga List" style="width:110px;margin-bottom:3px" title="Harga sebelum diskon (opsional)">
                            <input type="text" name="diskon_persen[]" class="form-control input-xs input-diskon-pct text-right"
                                   placeholder="Diskon %" style="width:110px" title="Diskon % dari Harga List (opsional)">
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
                    <td class="ppn-prev-col" style="display:none;padding:7px 12px;text-align:right">
                        <?php if ($done): ?>
                            <span class="text-muted">—</span>
                        <?php else: ?>
                            <input type="text" class="form-control input-xs text-right prev-ppn" readonly tabindex="-1"
                                placeholder="PPN" style="width:130px;margin-left:auto;margin-bottom:3px;background:#f4f6f9">
                            <input type="text" class="form-control input-xs text-right prev-harga-final" readonly tabindex="-1"
                                placeholder="Harga Final" style="width:130px;margin-left:auto;margin-bottom:3px;background:#f4f6f9">
                            <input type="text" class="form-control input-xs prev-pk-final" readonly tabindex="-1"
                                placeholder="PK Final" style="width:130px;margin-left:auto;font-family:monospace;letter-spacing:1px;text-transform:uppercase;font-size:11px;background:#f4f6f9">
                        <?php endif; ?>
                    </td>
                    <td class="text-right cell-subtotal" style="padding:9px 12px;font-weight:600">
                        <?= $done ? '<span class="text-muted">—</span>' : 'Rp 0' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Barang Ekstra: item di luar rencana PO, ditambahkan saat penerimaan -->
        <div class="extra-box">
            <div class="extra-box-head">
                <h3 class="box-title" style="font-size:14px;margin:0"><i class="fa fa-plus-circle"></i> Barang Ekstra <span class="label label-success" style="font-size:10px;vertical-align:middle">Di Luar PO</span></h3>
                <span class="badge" id="extra-count" style="display:none"></span>
            </div>
            <table class="table table-bordered table-condensed table-recv" style="margin:0" id="extra-table">
                <thead>
                    <tr>
                        <th style="min-width:200px;padding:9px 12px">Nama Barang</th>
                        <th width="90" class="text-center" style="padding:9px 8px">Qty Terima</th>
                        <th width="130" class="text-center" style="padding:9px 8px">Harga List / Diskon %</th>
                        <th width="165" class="text-right" style="padding:9px 12px">Harga Aktual / PK</th>
                        <th class="ppn-prev-col" width="150" style="display:none;text-align:right;padding:9px 12px">PPN / Harga Final / PK <span class="text-muted" style="font-weight:400">(preview)</span></th>
                        <th width="130" class="text-right" style="padding:9px 12px">Subtotal</th>
                        <th width="40"></th>
                    </tr>
                </thead>
                <tbody id="extra-tbody">
                    <tr id="extra-empty-row"><td colspan="7" class="text-center text-muted" style="padding:14px">Belum ada barang ekstra ditambahkan.</td></tr>
                </tbody>
            </table>
            <div style="padding:12px 16px">
                <button type="button" class="btn btn-success btn-sm" id="btn-open-extra">
                    <i class="fa fa-plus"></i> Tambah Barang Ekstra
                </button>
            </div>
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
                        <label style="font-size:12px;color:#555;font-weight:600">Cara Bayar Barang <span class="text-red">*</span></label>
                        <div class="radio" style="margin:3px 0 0">
                            <label style="font-weight:400;font-size:12.5px">
                                <input type="radio" name="payment_type" value="credit"> Kredit (Tempo)
                            </label>
                        </div>
                        <div class="radio" style="margin:3px 0 0">
                            <label style="font-weight:400;font-size:12.5px">
                                <input type="radio" name="payment_type" value="cash"> Cash (Lunas)
                            </label>
                        </div>
                        <small class="text-muted">Kredit tercatat sebagai hutang jatuh tempo; Cash langsung lunas otomatis. Wajib dipilih salah satu.</small>
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
                <div class="col-sm-4">
                    <div class="form-group" style="margin-bottom:0">
                        <label style="font-size:12px;color:#555;font-weight:600">Diskon Invoice (Rp)</label>
                        <input type="text" name="diskon_invoice" id="diskon-invoice" class="form-control" value="0" placeholder="0">
                        <small class="text-muted">Potongan nominal dari total invoice, opsional</small>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group" style="margin-bottom:0">
                        <label style="font-size:12px;color:#555;font-weight:600">PPN <span class="text-red">*</span> <small class="text-muted">(sesuai kebijakan supplier)</small></label>
                        <div class="radio" style="margin:3px 0 0">
                            <label style="font-weight:400;font-size:12.5px">
                                <input type="radio" name="ppn_mode" value="none"> Tanpa PPN
                            </label>
                        </div>
                        <div class="radio" style="margin:3px 0">
                            <label style="font-weight:400;font-size:12.5px">
                                <input type="radio" name="ppn_mode" value="add_distribute"> PPN ditambah &amp; didistribusi ke harga beli
                            </label>
                        </div>
                        <div class="radio" style="margin:3px 0 0">
                            <label style="font-weight:400;font-size:12.5px">
                                <input type="radio" name="ppn_mode" value="inclusive"> Harga beli sudah termasuk PPN
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div id="note-ppn-add" class="alert alert-warning" style="display:none;padding:8px 12px;font-size:12px;margin-bottom:14px">
                <i class="fa fa-info-circle"></i> Harga beli tiap item di atas akan otomatis naik (PPN didistribusi proporsional ke tiap baris).
            </div>
            <div id="note-ppn-inclusive" class="alert alert-info" style="display:none;padding:8px 12px;font-size:12px;margin-bottom:14px">
                <i class="fa fa-info-circle"></i> Harga beli tiap item di atas TIDAK berubah (dianggap sudah termasuk PPN). PPN cuma dicatat, tidak menambah total tagihan.
            </div>
            <div class="total-box" style="margin-bottom:14px">
                <div style="display:flex;justify-content:space-between"><span>Subtotal barang</span><span id="t-barang">Rp 0</span></div>
                <div style="display:flex;justify-content:space-between"><span>Diskon Invoice</span><span id="t-diskon-invoice">Rp 0</span></div>
                <div style="display:flex;justify-content:space-between;border-top:1px dashed #ddd;margin-top:4px;padding-top:4px"><span>Subtotal Setelah Diskon</span><span id="t-subtotal-setelah-diskon">Rp 0</span></div>
                <div style="display:flex;justify-content:space-between" id="row-ppn"><span id="lbl-ppn">PPN (11/12 &times; 12%)</span><span id="t-ppn">Rp 0</span></div>
                <div style="display:flex;justify-content:space-between;border-top:1px solid #ddd;margin-top:4px;padding-top:4px;font-weight:700"><span>Total Utang ke Supplier</span><span id="t-utang">Rp 0</span></div>
                <div style="display:flex;justify-content:space-between"><span>Ongkir (Tunai)</span><span id="t-ongkir">Rp 0</span></div>
                <div style="display:flex;justify-content:space-between;border-top:1px solid #ddd;margin-top:4px;padding-top:4px" class="grand"><span>Grand Total Diterima</span><span id="t-grand">Rp 0</span></div>
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
<?php endif; ?>
</div>

</div>

<!-- Modal Tambah Barang Ekstra -->
<div class="modal fade" id="modal-extra-item" tabindex="-1">
    <div class="modal-dialog" style="max-width:520px">
        <div class="modal-content">
            <div class="modal-header" style="background:#222d32;color:#fff;border-bottom:3px solid #00a65a">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff">&times;</button>
                <h4 class="modal-title"><i class="fa fa-plus"></i> Tambah Barang Ekstra</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:14px">
                    <label style="margin-right:16px"><input type="radio" name="extra-src" value="existing" checked> Item sudah ada di sistem</label>
                    <label><input type="radio" name="extra-src" value="new"> Item baru</label>
                </div>

                <!-- Sumber: item sudah ada -->
                <div id="extra-src-existing">
                    <div class="form-group" style="position:relative;margin-bottom:8px">
                        <div style="display:flex;align-items:center;gap:8px;background:#f4f6f8;border:1.5px solid #d0d5dd;border-radius:8px;padding:0 12px;height:38px"
                             id="extra-search-wrap">
                            <i class="fa fa-search" style="color:#aaa;font-size:14px;flex-shrink:0"></i>
                            <input type="text" id="extra-item-search" placeholder="Cari barcode / nama barang..." autocomplete="off"
                                   style="border:none;outline:none;background:transparent;font-size:13px;width:100%">
                        </div>
                        <div id="extra-search-results"
                             style="position:absolute;top:40px;left:0;right:0;z-index:9999;max-height:220px;overflow-y:auto;
                                    box-shadow:0 6px 18px rgba(0,0,0,.15);border-radius:0 0 8px 8px;
                                    border:1px solid #d0d5dd;border-top:none;background:#fff;display:none"></div>
                    </div>
                    <div id="extra-selected-info" style="display:none;background:#f8fffe;border-left:3px solid #00a65a;padding:10px 14px;border-radius:0 4px 4px 0;margin-bottom:12px">
                        <div style="font-size:11px;color:#888">Item dipilih</div>
                        <div id="extra-selected-name" style="font-weight:700;font-size:14px"></div>
                    </div>
                    <input type="hidden" id="extra-item-id">
                </div>

                <!-- Sumber: item baru — belum didaftarkan ke p_item, sama seperti pola
                     "Belum Terdaftar" yang sudah ada. Kategori/satuan/barcode diisi
                     belakangan lewat tombol "Daftarkan" setelah barang ini diterima. -->
                <div id="extra-src-new" style="display:none">
                    <div class="form-group">
                        <label>Nama Barang <span class="text-red">*</span></label>
                        <input type="text" id="extra-nama_item" class="form-control">
                        <small class="text-muted">Kategori, satuan, &amp; barcode diisi nanti lewat tombol "Daftarkan" setelah barang ini diterima.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label>Harga List <small class="text-muted">(opsional)</small></label>
                            <div class="input-group">
                                <span class="input-group-addon">Rp</span>
                                <input type="text" id="extra-harga-list" class="form-control input-rp-fmt" value="">
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label>Diskon % <small class="text-muted">(opsional)</small></label>
                            <input type="text" id="extra-diskon-pct" class="form-control" value="">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label>Qty <span class="text-red">*</span></label>
                            <input type="number" id="extra-qty" class="form-control" value="1" min="1">
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label>Harga Aktual (Rp) <span class="text-red">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon">Rp</span>
                                <input type="text" id="extra-price" class="form-control input-rp-fmt" value="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn-submit-extra">
                    <i class="fa fa-plus"></i> Tambahkan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Daftarkan Barang ke Sistem — untuk baris "Belum Terdaftar" (item_name_temp),
     baik yang direncanakan di PO maupun ditambahkan lewat Barang Ekstra. Satu mekanisme
     registrasi (register_temp_item) buat semua item belum terdaftar, apapun asalnya. -->
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
                <input type="hidden" id="reg-po_id" value="<?= $po->po_id ?>">
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
    function toast(icon, msg) {
        Swal.fire({ toast:true, position:'top-end', icon:icon, title:msg,
            showConfirmButton:false, timer:3500, timerProgressBar:true });
    }

    <?php $ferr = $this->session->flashdata('error'); if ($ferr): ?>
    toast('error', '<?= addslashes($ferr) ?>');
    <?php endif; ?>
    <?php $fsuc = $this->session->flashdata('success'); if ($fsuc): ?>
    toast('success', '<?= addslashes($fsuc) ?>');
    <?php endif; ?>

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

    // ── Preview per-baris PPN "ditambah & didistribusi" — mirror persis
    // algoritma distribusi proporsional di server (_redistribute_ppn /
    // receive()): baris terakhir menyerap sisa pembulatan. Cuma buat
    // preview di layar, TIDAK dikirim ke server (server hitung ulang
    // sendiri dari harga bersih yang tersimpan). ──
    function computePpnPreview(rows, diskonInvoice) {
        var subtotal = 0;
        rows.forEach(function (r) { subtotal += r.qty * r.price; });
        var subtotalSetelahDiskon = subtotal - diskonInvoice;
        var ppnNominal = Math.round(subtotalSetelahDiskon * (11 / 12) * 0.12);
        var count = rows.length;
        var remaining = ppnNominal;
        var out = [];
        if (subtotal <= 0 || ppnNominal <= 0) {
            rows.forEach(function (r) { out.push({ ppnUnit: 0, finalPrice: r.price }); });
            return out;
        }
        rows.forEach(function (r, i) {
            var rowSubtotal = r.qty * r.price;
            var share;
            if (i === count - 1) {
                share = remaining;
            } else {
                share = Math.round(ppnNominal * (rowSubtotal / subtotal));
                remaining -= share;
            }
            var ppnUnit = r.qty > 0 ? Math.round(share / r.qty) : 0;
            out.push({ ppnUnit: ppnUnit, finalPrice: r.price + ppnUnit });
        });
        return out;
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

    // Format harga ongkir & diskon invoice
    $(document).on('input', '#ongkir, #diskon-invoice', function () {
        var raw = String($(this).val()).replace(/[^0-9]/g, '');
        $(this).val(formatRp(raw));
    });

    // 3 mode PPN (saling eksklusif) — tampilkan catatan kontekstual sesuai mode
    $('input[name=ppn_mode]').on('change', function () {
        var mode = $('input[name=ppn_mode]:checked').val();
        $('#note-ppn-add').toggle(mode === 'add_distribute');
        $('#note-ppn-inclusive').toggle(mode === 'inclusive');
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

        if ($('input[name="payment_type"]:checked').length === 0) {
            Swal.fire({ icon:'warning', title:'Cara Bayar Barang belum dipilih',
                text:'Pilih Kredit (Tempo) atau Cash (Lunas) terlebih dahulu.' });
            return;
        }

        if ($('input[name="ppn_mode"]:checked').length === 0) {
            Swal.fire({ icon:'warning', title:'PPN belum dipilih',
                text:'Pilih salah satu opsi PPN terlebih dahulu.' });
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
                $('#diskon-invoice').val(unformatRp($('#diskon-invoice').val()));
                $('#btn-gr').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');
                $('#form-gr')[0].submit();
            }
        });
    });

    // ── Barang Ekstra ────────────────────────────────────────
    var supplierId  = <?= (int) $po->supplier_id ?>;
    var nextBarcode = '<?= $next_barcode ?>'; // saran barcode berikutnya (halaman reload tiap kali item baru berhasil didaftarkan, jadi nilainya selalu segar dari server)
    var extraSearchTimer;
    var extraRowSeq = 0;
    var isEditMode  = <?= $receipt ? 'true' : 'false' ?>;
    <?php if ($receipt): ?>
    var receiptId   = <?= (int) $receipt->receipt_id ?>;
    <?php endif; ?>

    function recalcTotal() {
        var subtotal = 0;
        $('#form-gr tr').each(function () {
            var $qty   = $(this).find('input[name="qty_received[]"]');
            var $price = $(this).find('input[name="actual_price[]"]');
            if (!$qty.length || !$price.length) return;
            subtotal += (parseInt($qty.val()) || 0) * unformatRp($price.val());
        });

        var ongkir        = unformatRp($('#ongkir').val());
        var diskonInvoice = unformatRp($('#diskon-invoice').val());
        var ppnMode       = $('input[name=ppn_mode]:checked').val() || 'none';
        var subtotalSetelahDiskon = subtotal - diskonInvoice;

        var ppn = 0, totalUtang = subtotalSetelahDiskon;
        if (ppnMode === 'add_distribute') {
            ppn = Math.round(subtotalSetelahDiskon * (11 / 12) * 0.12);
            totalUtang = subtotalSetelahDiskon + ppn;
        } else if (ppnMode === 'inclusive') {
            ppn = Math.round(subtotalSetelahDiskon * (0.11 / 1.11));
            totalUtang = subtotalSetelahDiskon; // sudah termasuk, tidak nambah
        }

        $('#t-barang').text('Rp ' + subtotal.toLocaleString('id-ID'));
        $('#t-diskon-invoice').text('Rp ' + diskonInvoice.toLocaleString('id-ID'));
        $('#t-subtotal-setelah-diskon').text('Rp ' + subtotalSetelahDiskon.toLocaleString('id-ID'));
        $('#lbl-ppn').text(ppnMode === 'inclusive' ? 'PPN (sudah termasuk, diekstrak)' : 'PPN (11/12 × 12%)');
        $('#t-ppn').text('Rp ' + ppn.toLocaleString('id-ID'));
        $('#t-utang').text('Rp ' + totalUtang.toLocaleString('id-ID'));
        $('#t-ongkir').text('Rp ' + ongkir.toLocaleString('id-ID'));
        $('#t-grand').text('Rp ' + (totalUtang + ongkir).toLocaleString('id-ID'));
        $('#row-ppn').toggle(ppnMode !== 'none');

        updateCreatePpnPreview(ppnMode, diskonInvoice);
    }

    // ── Preview per-baris "PPN / Harga Final / PK" (mode Create) — cuma
    // tampil saat Mode PPN "ditambah & didistribusi" dipilih, readonly,
    // murni informasi (nilai final sesungguhnya dihitung ulang di server).
    // Juga yang menghitung ulang kolom Subtotal per baris (qty × harga final:
    // harga + PPN kalau mode add_distribute aktif, atau harga polos kalau tidak). ──
    function updateCreatePpnPreview(ppnMode, diskonInvoice) {
        var show = ppnMode === 'add_distribute';
        $('.ppn-prev-col').toggle(show);

        var $allRows = $('#form-gr tr').filter(function () {
            var $tr = $(this);
            return $tr.find('input[name="qty_received[]"]').length && $tr.find('input[name="actual_price[]"]').length;
        });

        if (!show) {
            $allRows.each(function () {
                var $tr   = $(this);
                var $sub  = $tr.find('.cell-subtotal');
                if (!$sub.length) return; // baris "Selesai": tidak ada kolom subtotal aktif
                var qty   = parseInt($tr.find('input[name="qty_received[]"]').val()) || 0;
                var price = unformatRp($tr.find('input[name="actual_price[]"]').val());
                $tr.find('.prev-ppn, .prev-harga-final, .prev-pk-final').val('');
                $sub.text('Rp ' + (qty * price).toLocaleString('id-ID'));
            });
            return;
        }

        // baris qty 0 tidak ikut dihitung sama sekali (persis seperti server yang
        // skip baris qty<=0 saat receive()) supaya baris terakhir yang benar-benar
        // dapat "sisa pembulatan" cocok dengan yang dihitung ulang di server.
        var rowsData = [], rowRefs = [];
        $allRows.each(function () {
            var $tr  = $(this);
            var $sub = $tr.find('.cell-subtotal');
            $tr.find('.prev-ppn, .prev-harga-final, .prev-pk-final').val('');
            var qty = parseInt($tr.find('input[name="qty_received[]"]').val()) || 0;
            if (qty <= 0) {
                if ($sub.length) $sub.text('Rp 0');
                return;
            }
            rowsData.push({ qty: qty, price: unformatRp($tr.find('input[name="actual_price[]"]').val()) });
            rowRefs.push($tr);
        });
        var results = computePpnPreview(rowsData, diskonInvoice);
        rowRefs.forEach(function ($tr, i) {
            var r         = results[i];
            var qty       = parseInt($tr.find('input[name="qty_received[]"]').val()) || 0;
            var hargaList = unformatRp($tr.find('.input-harga-list').val());
            var pkFinal   = priceToK(String(r.finalPrice)) + (hargaList > 0 ? ' | PL ' + priceToK(String(hargaList)) : '');
            $tr.find('.prev-ppn').val('Rp ' + formatRp(String(r.ppnUnit)));
            $tr.find('.prev-harga-final').val('Rp ' + formatRp(String(r.finalPrice)));
            $tr.find('.prev-pk-final').val(pkFinal);
            var $sub = $tr.find('.cell-subtotal');
            if ($sub.length) $sub.text('Rp ' + (qty * r.finalPrice).toLocaleString('id-ID'));
        });
    }

    $('#btn-open-extra').on('click', function () {
        $('#modal-extra-item').modal('show');
    });

    $('input[name=extra-src]').on('change', function () {
        var isNew = $(this).val() === 'new';
        $('#extra-src-existing').toggle(!isNew);
        $('#extra-src-new').toggle(isNew);
    });

    $('#extra-item-search').on('keyup', function () {
        clearTimeout(extraSearchTimer);
        var kw = $.trim($(this).val());
        if (kw.length < 2) { $('#extra-search-results').hide().empty(); return; }
        extraSearchTimer = setTimeout(function () {
            $.post('<?= site_url('purchase-order/search-item') ?>', { keyword: kw, supplier_id: supplierId }, function (res) {
                var html = '';
                (res.items || []).forEach(function (it) {
                    var name    = $('<span>').text(it.nama_item).html();
                    var barcode = it.barcode ? '<small class="text-muted" style="margin-left:6px">' + it.barcode + '</small>' : '';
                    html += '<a href="#" class="list-group-item extra-item-pick" style="padding:8px 14px;font-size:13px;cursor:pointer"'
                          + ' data-id="' + it.item_id + '" data-name="' + name + '" data-price="' + it.ref_price + '">'
                          + '<strong>' + name + '</strong>' + barcode + '</a>';
                });
                if (!html) html = '<div class="list-group-item" style="padding:10px 14px;color:#999;font-size:13px">'
                                + '<i class="fa fa-search"></i> Tidak ada barang ditemukan</div>';
                $('#extra-search-results').html(html).show();
            }, 'json');
        }, 300);
    });

    $(document).on('click', '.extra-item-pick', function (e) {
        e.preventDefault();
        var price = parseInt($(this).data('price')) || 0;
        $('#extra-item-id').val($(this).data('id'));
        $('#extra-selected-name').text($(this).data('name'));
        $('#extra-price').val(formatRp(String(price)));
        $('#extra-selected-info').show();
        $('#extra-search-results').hide();
        $('#extra-item-search').val($(this).data('name'));
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#extra-src-existing').length) $('#extra-search-results').hide();
    });

    $('#modal-extra-item').on('hidden.bs.modal', function () {
        $('input[name=extra-src][value=existing]').prop('checked', true).trigger('change');
        $('#extra-item-search').val('');
        $('#extra-item-id').val('');
        $('#extra-selected-info').hide();
        $('#extra-search-results').hide().empty();
        $('#extra-nama_item').val('');
        $('#extra-qty').val(1);
        $('#extra-price').val('0').prop('readonly', false);
        $('#extra-harga-list').val('');
        $('#extra-diskon-pct').val('');
    });

    function recalcExtraModalPrice() {
        var hargaList = unformatRp($('#extra-harga-list').val());
        var diskon    = parseFloat($('#extra-diskon-pct').val()) || 0;
        if (hargaList > 0) {
            var actual = Math.round(hargaList * (1 - diskon / 100));
            $('#extra-price').val(formatRp(String(actual))).prop('readonly', true);
        } else {
            $('#extra-price').prop('readonly', false);
        }
    }
    $(document).on('input', '#extra-diskon-pct', recalcExtraModalPrice);
    $(document).on('input', '#extra-harga-list', function () {
        var raw = String($(this).val()).replace(/[^0-9]/g, '');
        $(this).val(formatRp(raw));
        recalcExtraModalPrice();
    });

    $('#btn-submit-extra').on('click', function () {
        var src       = $('input[name=extra-src]:checked').val();
        var qty       = parseInt($('#extra-qty').val()) || 0;
        var price     = unformatRp($('#extra-price').val());
        var hargaList = unformatRp($('#extra-harga-list').val());
        var diskonPct = $('#extra-diskon-pct').val();

        if (qty <= 0 || price <= 0) {
            Swal.fire({ icon: 'warning', title: 'Lengkapi Data', text: 'Qty dan harga harus lebih dari 0.' });
            return;
        }

        var payload = isEditMode
            ? { receipt_id: receiptId, source: src, harga_list: $('#extra-harga-list').val(), diskon_persen: diskonPct }
            : { po_id: $('input[name="po_id"]').val(), source: src };

        if (src === 'existing') {
            if (!$('#extra-item-id').val()) {
                Swal.fire({ icon: 'warning', title: 'Pilih Item', text: 'Cari & pilih item terlebih dahulu.' });
                return;
            }
            payload.item_id = $('#extra-item-id').val();
            if (isEditMode) {
                payload.actual_price = price;
                payload.qty = qty;
            } else {
                payload.unit_price = price;
            }
        } else {
            var nama = $.trim($('#extra-nama_item').val());
            if (!nama) {
                Swal.fire({ icon: 'warning', title: 'Lengkapi Data', text: 'Nama barang wajib diisi.' });
                return;
            }
            payload.nama_item = nama;
            payload.modal     = price;
            if (isEditMode) payload.qty = qty;
        }

        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        if (isEditMode) {
            // Resi sudah final — langsung efektif (stok kekredit / receipt_id ke-link),
            // reload supaya PK/badge/subtotal selalu akurat dari server (bukan dirakit di JS).
            $.post('<?= site_url('purchase-order/history/add-extra-item') ?>', payload, function (res) {
                $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Tambahkan');
                if (res.status !== 'success') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal menambahkan barang.' });
                    return;
                }
                toast('success', 'Barang ekstra berhasil ditambahkan.');
                setTimeout(function () { location.reload(); }, 900);
            }, 'json');
            return;
        }

        $.post('<?= site_url('purchase-order/receive-add-item') ?>', payload, function (res) {
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Tambahkan');
            if (res.status !== 'success') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal menambahkan barang.' });
                return;
            }
            $('#extra-empty-row').remove();
            var priceFmt     = formatRp(String(price));
            var hargaListFmt = hargaList > 0 ? formatRp(String(hargaList)) : '';
            var pk           = priceToK(String(price)) + (hargaList > 0 ? ' | PL ' + priceToK(String(hargaList)) : '');
            var badge        = res.is_temp
                ? '<span class="label label-warning" style="font-size:10px">Belum Terdaftar</span>'
                : '<span class="label label-success" style="font-size:10px">Ekstra</span>';
            var note         = res.is_temp
                ? '<br><small class="text-muted">Daftarkan dulu setelah diterima (lihat baris ini lagi di Daftar Item PO)</small>' : '';
            var row = '<tr class="extra-row" id="extra-row-' + (extraRowSeq++) + '">'
                + '<td style="padding:9px 12px"><strong>' + $('<span>').text(res.item_name).html() + '</strong> '
                + badge + note
                + '<input type="hidden" name="detail_id[]" value="' + res.detail_id + '"></td>'
                + '<td class="text-center" style="padding:7px 8px">'
                + '<input type="number" name="qty_received[]" class="form-control input-xs text-center" min="1" value="' + qty + '" style="width:55px;display:inline-block"></td>'
                + '<td class="text-center" style="padding:7px 8px">'
                + '<input type="text" name="harga_list[]" class="form-control input-xs input-harga-list text-right" value="' + hargaListFmt + '" placeholder="Harga List" style="width:110px;margin-bottom:3px">'
                + '<input type="text" name="diskon_persen[]" class="form-control input-xs input-diskon-pct text-right" value="' + (diskonPct || '') + '" placeholder="Diskon %" style="width:110px"></td>'
                + '<td style="padding:7px 12px">'
                + '<div class="input-group input-group-sm"><span class="input-group-addon" style="font-size:11px">Rp</span>'
                + '<input type="text" name="actual_price[]" class="form-control input-actual-price text-right" value="' + priceFmt + '"'
                + (hargaList > 0 ? ' readonly' : '') + ' style="width:110px"></div>'
                + '<input type="text" name="pk_new[]" class="form-control input-xs input-pk-new" value="' + pk + '" '
                + 'style="margin-top:4px;font-family:monospace;letter-spacing:1px;text-transform:uppercase;font-size:11px"></td>'
                + '<td class="ppn-prev-col" style="display:none;padding:7px 12px;text-align:right">'
                + '<input type="text" class="form-control input-xs text-right prev-ppn" readonly tabindex="-1" placeholder="PPN" style="width:130px;margin-left:auto;margin-bottom:3px;background:#f4f6f9">'
                + '<input type="text" class="form-control input-xs text-right prev-harga-final" readonly tabindex="-1" placeholder="Harga Final" style="width:130px;margin-left:auto;margin-bottom:3px;background:#f4f6f9">'
                + '<input type="text" class="form-control input-xs prev-pk-final" readonly tabindex="-1" placeholder="PK Final" style="width:130px;margin-left:auto;font-family:monospace;letter-spacing:1px;text-transform:uppercase;font-size:11px;background:#f4f6f9"></td>'
                + '<td class="text-right cell-subtotal" style="padding:9px 12px;font-weight:600">Rp ' + (qty * price).toLocaleString('id-ID') + '</td>'
                + '<td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-remove-extra"><i class="fa fa-times"></i></button></td>'
                + '</tr>';
            $('#extra-tbody').append(row);
            $('#modal-extra-item').modal('hide');
            recalcTotal();
        }, 'json');
    });

    $(document).on('click', '.btn-remove-extra', function () {
        $(this).closest('tr').remove();
        if ($('#extra-tbody tr').length === 0) {
            $('#extra-tbody').append('<tr id="extra-empty-row"><td colspan="7" class="text-center text-muted" style="padding:14px">Belum ada barang ekstra ditambahkan.</td></tr>');
        }
        recalcTotal();
    });

    // ── Harga List & Diskon % per baris (tabel PO utama & Barang Ekstra) ──
    function recalcRowHargaList($row) {
        var $hargaList = $row.find('.input-harga-list');
        var $diskon    = $row.find('.input-diskon-pct');
        var $actual    = $row.find('.input-actual-price');
        var $pk        = $row.find('.input-pk-new');
        if (!$hargaList.length || !$actual.length) return;

        var hargaList = unformatRp($hargaList.val());
        var diskon    = parseFloat($diskon.val()) || 0;

        if (hargaList > 0) {
            var actual = Math.round(hargaList * (1 - diskon / 100));
            $actual.val(formatRp(String(actual))).prop('readonly', true);
            $pk.val(priceToK(String(actual)) + ' | PL ' + priceToK(String(hargaList)));
        } else {
            $actual.prop('readonly', false);
            $pk.val(priceToK(unformatRp($actual.val())));
        }
    }

    $(document).on('input', '.input-diskon-pct', function () {
        recalcRowHargaList($(this).closest('tr'));
        recalcTotal();
    });
    $(document).on('input', '.input-harga-list', function () {
        var raw = String($(this).val()).replace(/[^0-9]/g, '');
        $(this).val(formatRp(raw));
        recalcRowHargaList($(this).closest('tr'));
        recalcTotal();
    });

    $(document).on('input', 'input[name="qty_received[]"], .input-actual-price, #ongkir, #diskon-invoice', recalcTotal);
    $(document).on('change', 'input[name=ppn_mode]', recalcTotal);

    // ── Mode Edit: harga List/Diskon %/PK per baris — SEMUA ditahan (tidak
    // langsung tersimpan), cuma live preview di layar sampai "Simpan Semua
    // Perubahan Harga" diklik. PK ikut dihitung ulang live mengikuti pola
    // Create (recalcRowHargaList), kecuali baris itu di-override manual. ──
    function recalcRowFromHargaList($tr) {
        var $hargaList = $tr.find('.edit-harga-list');
        var $diskon    = $tr.find('.edit-diskon-persen');
        var $price     = $tr.find('.edit-price');
        var $pk        = $tr.find('.edit-pk');
        var hargaList  = unformatRp($hargaList.val());
        var diskon     = parseFloat($diskon.val()) || 0;
        var actual;
        if (hargaList > 0) {
            actual = Math.round(hargaList * (1 - diskon / 100));
            $price.val(formatRp(String(actual))).prop('readonly', true);
        } else {
            actual = unformatRp($price.val());
            $price.prop('readonly', false);
        }
        if (!$pk.prop('disabled') && !$tr.data('pk-manual')) {
            $pk.val(hargaList > 0
                ? priceToK(String(actual)) + ' | PL ' + priceToK(String(hargaList))
                : priceToK(String(actual)));
        }
        recalcTotalEdit();
    }
    $(document).on('input', '.edit-diskon-persen', function () {
        recalcRowFromHargaList($(this).closest('tr'));
    });
    $(document).on('input', '.edit-harga-list', function () {
        $(this).val(formatRp($(this).val()));
        recalcRowFromHargaList($(this).closest('tr'));
    });
    $(document).on('input', '.edit-price', function () {
        $(this).val(formatRp($(this).val()));
        $(this).closest('tr').data('pk-manual', false); // harga diketik ulang -> PK ikut auto lagi
        recalcRowFromHargaList($(this).closest('tr'));
    });
    $(document).on('input', '.edit-pk', function () {
        $(this).val($(this).val().toUpperCase());
        $(this).closest('tr').data('pk-manual', true);
        recalcTotalEdit();
    });
    $(document).on('input', '.edit-qty', recalcTotalEdit);

    // ── Live preview total Edit (Subtotal/Diskon/PPN/Total Utang) — mirip
    // recalcTotal() di form Create, tapi baca dari tabel mode Edit & belum
    // tersimpan apa-apa sampai "Simpan Semua Perubahan Harga" diklik. ──
    function recalcTotalEdit() {
        var subtotal = 0;
        $('#edit-items-table tbody tr').each(function () {
            var $tr    = $(this);
            var qty    = parseInt($tr.find('.edit-qty').val()) || 0;
            var price  = unformatRp($tr.find('.edit-price').val());
            subtotal  += qty * price;
        });

        var diskonInvoice = unformatRp($('#inp-diskon-invoice').val());
        var ppnMode       = $('input[name=inp-ppn-mode]:checked').val() || 'none';
        var subtotalSetelahDiskon = subtotal - diskonInvoice;

        var ppn = 0, totalUtang = subtotalSetelahDiskon;
        if (ppnMode === 'add_distribute') {
            ppn = Math.round(subtotalSetelahDiskon * (11 / 12) * 0.12);
            totalUtang = subtotalSetelahDiskon + ppn;
        } else if (ppnMode === 'inclusive') {
            ppn = Math.round(subtotalSetelahDiskon * (0.11 / 1.11));
            totalUtang = subtotalSetelahDiskon;
        }

        $('#edit-t-barang').text('Rp ' + subtotal.toLocaleString('id-ID'));
        $('#edit-t-diskon').text('- Rp ' + diskonInvoice.toLocaleString('id-ID'));
        $('#edit-lbl-ppn').text(ppnMode === 'inclusive' ? 'PPN (sudah termasuk harga beli, diekstrak)' : 'PPN (ditambah & didistribusi ke harga beli)');
        $('#edit-t-ppn').text((ppnMode === 'add_distribute' ? '+ ' : '') + 'Rp ' + ppn.toLocaleString('id-ID'));
        $('#edit-t-utang').text('Rp ' + totalUtang.toLocaleString('id-ID'));
        $('#edit-row-diskon').toggle(diskonInvoice > 0);
        $('#edit-row-ppn').toggle(ppnMode !== 'none');

        var rowSubtotalSum = updateEditPpnPreview(ppnMode, diskonInvoice);
        $('#grand-total-cell').text('Rp ' + rowSubtotalSum.toLocaleString('id-ID'));
    }
    $(document).on('input', '#inp-diskon-invoice', recalcTotalEdit);
    $(document).on('change', 'input[name=inp-ppn-mode]', recalcTotalEdit);

    // ── Preview per-baris "PPN / Harga Final / PK" — cuma tampil saat Mode
    // PPN "ditambah & didistribusi" dipilih, readonly, murni informasi (nilai
    // final sesungguhnya tetap dihitung ulang di server saat disimpan). Juga
    // yang menghitung ulang kolom Subtotal per baris (qty × harga final: harga
    // + PPN kalau mode add_distribute aktif, atau harga polos kalau tidak). ──
    function updateEditPpnPreview(ppnMode, diskonInvoice) {
        var show = ppnMode === 'add_distribute';
        $('.ppn-prev-col').toggle(show);

        var $allRows = $('#edit-items-table tbody tr');
        var rowSubtotalSum = 0;

        if (!show) {
            $allRows.each(function () {
                var $tr    = $(this);
                var qty    = parseInt($tr.find('.edit-qty').val()) || 0;
                var price  = unformatRp($tr.find('.edit-price').val());
                $tr.find('.prev-ppn, .prev-harga-final, .prev-pk-final').val('');
                var rowSubtotal = qty * price;
                rowSubtotalSum += rowSubtotal;
                $tr.find('.cell-subtotal').text('Rp ' + rowSubtotal.toLocaleString('id-ID'));
            });
            return rowSubtotalSum;
        }

        // baris qty 0 tidak ikut dihitung sama sekali (persis seperti server yang
        // query WHERE qty_received > 0 di _redistribute_ppn) supaya baris terakhir
        // yang benar-benar dapat "sisa pembulatan" cocok dengan hitungan server.
        var rowsData = [], rowRefs = [];
        $allRows.each(function () {
            var $tr = $(this);
            $tr.find('.prev-ppn, .prev-harga-final, .prev-pk-final').val('');
            var qty = parseInt($tr.find('.edit-qty').val()) || 0;
            if (qty <= 0) {
                $tr.find('.cell-subtotal').text('Rp 0');
                return;
            }
            rowsData.push({ qty: qty, price: unformatRp($tr.find('.edit-price').val()) });
            rowRefs.push($tr);
        });
        var results = computePpnPreview(rowsData, diskonInvoice);
        rowRefs.forEach(function ($tr, i) {
            var r          = results[i];
            var qty        = parseInt($tr.find('.edit-qty').val()) || 0;
            var hargaList  = unformatRp($tr.find('.edit-harga-list').val());
            var pkFinal;
            if ($tr.data('pk-manual')) {
                pkFinal = String($tr.find('.edit-pk').val() || '').toUpperCase();
            } else {
                pkFinal = priceToK(String(r.finalPrice)) + (hargaList > 0 ? ' | PL ' + priceToK(String(hargaList)) : '');
            }
            $tr.find('.prev-ppn').val('Rp ' + formatRp(String(r.ppnUnit)));
            $tr.find('.prev-harga-final').val('Rp ' + formatRp(String(r.finalPrice)));
            $tr.find('.prev-pk-final').val(pkFinal);
            var rowSubtotal = qty * r.finalPrice;
            rowSubtotalSum += rowSubtotal;
            $tr.find('.cell-subtotal').text('Rp ' + rowSubtotal.toLocaleString('id-ID'));
        });
        return rowSubtotalSum;
    }

    // ── Mode Edit: tambah item dari PO yang sama (sisa qty belum masuk resi ini) ──
    $('#sel-add-item').on('change', function () {
        var $opt  = $(this).find('option:selected');
        var sisa  = $opt.data('sisa') || 0;
        var unit  = $opt.data('unit') || '';
        var price = $opt.data('price') || 0;
        if ($(this).val()) {
            $('#add-item-info').text('Sisa qty: ' + sisa + ' ' + unit);
            $('#inp-add-qty').attr('max', sisa).val(Math.min(parseInt($('#inp-add-qty').val()) || 1, sisa));
            if (price > 0) $('#inp-add-price').val(formatRp(price));
        } else {
            $('#add-item-info').text('');
        }
    });
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
            receipt_id   : receiptId,
            po_detail_id : poDetailId,
            qty          : qty,
            actual_price : price,
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

    // ── Mode Edit: hapus baris dari resi ──
    $(document).on('click', '.btn-del-detail', function () {
        var $btn      = $(this);
        var detailId  = $btn.data('detail-id');
        var recId     = $btn.data('receipt-id');
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
                receipt_id : recId,
            }, function (res) {
                if (res.status !== 'success') {
                    toast('error', res.message || 'Gagal menghapus.');
                    $btn.prop('disabled', false);
                    return;
                }
                if (res.receipt_deleted) {
                    toast('success', 'Baris dihapus. Penerimaan ini sudah kosong.');
                    setTimeout(function () {
                        window.location.href = '<?= site_url('purchase-order/history') ?>';
                    }, 1500);
                } else {
                    // Reload (bukan cuma fade-out baris ini) karena hapus baris bisa memicu
                    // redistribusi PPN di server yang menggeser harga baris LAIN juga.
                    toast('success', 'Baris berhasil dihapus.');
                    setTimeout(function () { location.reload(); }, 700);
                }
            }, 'json').fail(function () {
                toast('error', 'Terjadi kesalahan.');
                $btn.prop('disabled', false);
            });
        });
    });

    // ── Mode Edit: Pengaturan Invoice (level 1 saja) ──
    $(document).on('input', '#inp-diskon-invoice, #inp-ongkir', function () {
        $(this).val(formatRp($(this).val()));
    });

    // Qty tiap baris + Harga tiap baris + Diskon Invoice + Ongkir + Mode PPN
    // disimpan BARENG lewat satu tombol — full staging, semuanya ditahan di
    // browser sampai diklik, baru diproses jadi SATU transaksi di server
    // (kalau ada satu saja yang gagal, semuanya rollback, tidak ada partial save).
    var $btnSavePrices = $('#btn-save-prices');
    var btnSavePricesLabel = $btnSavePrices.html();
    $btnSavePrices.on('click', function () {
        var detailIds = [], qtys = [], prices = [], hargaLists = [], diskonPcts = [], pks = [];
        var qtyError = null;
        $('#edit-items-table tbody tr').each(function () {
            var $tr    = $(this);
            var $qtyInp = $tr.find('.edit-qty');
            var newQty  = parseInt($qtyInp.val()) || 0;
            var maxQty  = parseInt($qtyInp.data('max')) || 0;
            if (maxQty > 0 && newQty > maxQty) {
                qtyError = 'Qty melebihi jumlah yang dipesan (' + maxQty + ').';
            }
            detailIds.push($tr.data('detail-id'));
            qtys.push(newQty);
            prices.push(unformatRp($tr.find('.edit-price').val()));
            hargaLists.push(unformatRp($tr.find('.edit-harga-list').val()) || '');
            diskonPcts.push($tr.find('.edit-diskon-persen').val() || '');
            pks.push($tr.data('pk-manual') ? $tr.find('.edit-pk').val() : '');
        });

        if (qtyError) {
            toast('warning', qtyError);
            return;
        }

        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
        $.post('<?= site_url('purchase-order/history/update-prices') ?>', {
            receipt_id:      receiptId,
            detail_id:       detailIds,
            qty_received:    qtys,
            actual_price:    prices,
            harga_list:      hargaLists,
            diskon_persen:   diskonPcts,
            pk_new:          pks,
            supplier_invoice_no: $('#inp-invoice-no').length ? $('#inp-invoice-no').val() : '',
            invoice_date:    $('#inp-invoice-date').length ? $('#inp-invoice-date').val() : '',
            diskon_invoice:  $('#inp-diskon-invoice').length ? $('#inp-diskon-invoice').val() : '0',
            ongkir:          $('#inp-ongkir').length ? $('#inp-ongkir').val() : '0',
            ppn_mode:        $('input[name=inp-ppn-mode]:checked').val() || 'none',
        }, function (res) {
            $btn.prop('disabled', false).html(btnSavePricesLabel);
            if (res.status !== 'success') {
                toast('error', res.message || 'Gagal menyimpan.');
                return;
            }
            toast('success', 'Semua perubahan berhasil disimpan.');
            setTimeout(function () { location.reload(); }, 900);
        }, 'json').fail(function () {
            $btn.prop('disabled', false).html(btnSavePricesLabel);
            toast('error', 'Terjadi kesalahan server.');
        });
    });

    // ── Daftarkan Barang (item belum terdaftar — planned maupun ekstra) ──
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
            $('#modal-register-item').modal('hide');
            toast('success', 'Barang berhasil didaftarkan dan stok diupdate.');
            // Reload penuh (bukan update baris di tempat) supaya bagian lain yang
            // baca daftar item terdaftar — misalnya Print Barcode — ikut kebawa
            // item baru ini tanpa perlu di-refresh manual.
            setTimeout(function () { location.reload(); }, 900);
        }, 'json');
    });

    recalcTotal();
    if (isEditMode) recalcTotalEdit();
});
</script>
