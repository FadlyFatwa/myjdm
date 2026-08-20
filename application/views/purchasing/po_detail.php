<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$status_map = [
    'draft'     => ['label' => 'Draft',                  'class' => 'default', 'icon' => 'fa-pencil'],
    'sent'      => ['label' => 'Terkirim',               'class' => 'info',    'icon' => 'fa-paper-plane'],
    'partial'   => ['label' => 'Sebagian Diterima',      'class' => 'warning', 'icon' => 'fa-adjust'],
    'received'  => ['label' => 'Diterima Lengkap',       'class' => 'success', 'icon' => 'fa-check-circle'],
    'cancelled' => ['label' => 'Dibatalkan',             'class' => 'danger',  'icon' => 'fa-ban'],
    'closed'    => ['label' => 'Ditutup (Tidak Lengkap)','class' => 'default', 'icon' => 'fa-lock'],
];
$st = $status_map[$po->status] ?? ['label' => $po->status, 'class' => 'default', 'icon' => 'fa-circle'];
$can_receive   = in_array($po->status, ['sent', 'partial']);
$can_edit      = ($po->status === 'draft');
$is_superadmin = ($this->fungsi->user_login()->level == 1);
$can_modify    = $can_edit && $is_superadmin;

// qty_ordered=0 menandai baris "ekstra" (di luar rencana PO) — dikeluarkan dari
// progress keseluruhan supaya tidak lebih dari 100% gara-gara barang ekstra.
$planned_details = array_filter((array) $details, function ($d) { return (int) $d->qty_ordered > 0; });
$total_ordered   = array_sum(array_column($planned_details, 'qty_ordered'));
$total_received  = array_sum(array_column($planned_details, 'qty_received'));
$pct_overall     = $total_ordered > 0 ? round($total_received / $total_ordered * 100) : 0;
?>

<div class="content-header">
    <h1><i class="fa fa-file-text-o" style="color:#3c8dbc"></i> <?= htmlspecialchars($po->po_number) ?>
        <small><span class="label label-<?= $st['class'] ?>" style="font-size:11px;vertical-align:middle">
            <i class="fa <?= $st['icon'] ?>"></i> <?= $st['label'] ?>
        </span></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?= site_url('purchase-order') ?>">Purchase Order</a></li>
        <li class="active"><?= htmlspecialchars($po->po_number) ?></li>
    </ol>
</div>

<div class="content">

<style>
/* ── Info card ── */
.po-info-card { border-left:4px solid #3c8dbc; background:#fff; border-radius:4px; box-shadow:0 1px 3px rgba(0,0,0,.1); padding:16px 20px; margin-bottom:16px; }
.po-info-card.status-sent     { border-color:#00c0ef; }
.po-info-card.status-partial  { border-color:#f39c12; }
.po-info-card.status-received { border-color:#00a65a; }
.po-info-card.status-cancelled{ border-color:#dd4b39; }
.po-info-card.status-closed   { border-color:#777; }
.po-meta { display:flex; flex-wrap:wrap; gap:20px 40px; margin-top:8px; }
.po-meta-item { min-width:140px; }
.po-meta-item label { font-size:11px; text-transform:uppercase; color:#888; margin:0; letter-spacing:.5px; }
.po-meta-item p { margin:0; font-weight:600; font-size:14px; color:#333; }
.gr-progress { height:6px; border-radius:3px; background:#e9ecef; overflow:hidden; margin-top:3px; }
.gr-progress-bar { height:100%; border-radius:3px; transition:width .3s; }
.action-bar { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.table-gr th { background:#f4f6f9; font-size:12px; text-transform:uppercase; letter-spacing:.4px; color:#555; white-space:nowrap; }
.table-gr td { vertical-align:middle !important; }
.qty-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:12px; font-weight:600; }
#modal-add-item .modal-body { overflow:visible; }
#search-results .list-group-item { border-left:none; border-right:none; border-radius:0; font-size:13px; padding:9px 14px; cursor:pointer; }
#search-results .list-group-item:hover { background:#f0f9f4; }
#search-results .list-group-item:first-child { border-top:none; }
#search-results .list-group-item:last-child { border-radius:0 0 8px 8px; }

/* ── Dark mode ── */
body.dark-mode .po-info-card { background:#222537; box-shadow:0 1px 6px rgba(0,0,0,.4); }
body.dark-mode .po-meta-item label { color:#6b7280; }
body.dark-mode .po-meta-item p { color:#e5e7eb; }
body.dark-mode .po-meta-item small { color:#9ca3af !important; }
body.dark-mode .gr-progress { background:#374151; }

body.dark-mode .box           { background:#222537; border-color:#2d3148; }
body.dark-mode .box-header    { background:#1e2233 !important; border-color:#2d3148 !important; color:#e5e7eb !important; }
body.dark-mode .box-title     { color:#e5e7eb !important; }
body.dark-mode .box-body      { background:#222537; }
body.dark-mode .box-footer    { background:#1e2233; border-color:#2d3148; }

body.dark-mode .table-gr th   { background:#1e2233 !important; color:#9ca3af !important; border-color:#2d3148 !important; }
body.dark-mode .table-gr td   { border-color:#2d3148 !important; color:#e5e7eb; }
body.dark-mode .table-bordered{ border-color:#2d3148 !important; }
body.dark-mode .table-gr tbody tr           { background:#222537 !important; }
body.dark-mode .table-gr tbody tr:hover    { background:#252836 !important; }
body.dark-mode .table-gr tbody tr.row-full { background:#0d2e1d !important; }
body.dark-mode .table-gr tbody tr.row-full:hover { background:#12382a !important; }
body.dark-mode .table-gr tbody tr.row-partial { background:#2a2000 !important; }
body.dark-mode .table-gr tbody tr.row-partial:hover { background:#332600 !important; }

body.dark-mode .form-control  { background:#1a1d27 !important; border-color:#374151 !important; color:#e5e7eb !important; }
body.dark-mode .input-group-addon { background:#252836 !important; border-color:#374151 !important; color:#9ca3af !important; }

body.dark-mode .callout        { background:#1e2233; border-color:#2d3148; color:#e5e7eb; }
body.dark-mode .callout-warning{ border-left-color:#f39c12 !important; }
body.dark-mode .callout-info   { border-left-color:#3c8dbc !important; }

body.dark-mode #search-results { background:#1e2233; border-color:#2d3148; }
body.dark-mode #search-results .list-group-item { background:#1e2233; color:#e5e7eb; border-color:#2d3148; }
body.dark-mode #search-results .list-group-item:hover { background:#252836; }
body.dark-mode #selected-item-info { background:#0d2e1d; border-color:#00a65a; color:#e5e7eb; }

body.dark-mode .modal-content  { background:#222537; border-color:#2d3148; }
body.dark-mode .modal-header   { background:#1e2233 !important; border-color:#2d3148 !important; }
body.dark-mode .modal-footer   { background:#1e2233; border-color:#2d3148; }
body.dark-mode .modal-body     { color:#e5e7eb; }
body.dark-mode select.form-control option { background:#1a1d27; }
</style>

    <!-- Info PO Card -->
    <div class="po-info-card status-<?= $po->status ?>">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px">
            <div>
                <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.5px">Purchase Order</div>
                <div style="font-size:20px;font-weight:700;color:#222"><?= htmlspecialchars($po->po_number) ?></div>
                <div style="margin-top:4px">
                    <span class="label label-<?= $st['class'] ?>" style="font-size:12px;padding:4px 10px">
                        <i class="fa <?= $st['icon'] ?>"></i> <?= $st['label'] ?>
                    </span>
                    <?php if ($po->is_direct): ?>
                    <span class="label label-success" style="font-size:12px;padding:4px 10px" title="Dibuat otomatis dari penerimaan langsung tanpa PO formal">
                        <i class="fa fa-inbox"></i> Langsung (Tanpa PO)
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="action-bar">
                <?php if ($can_modify): ?>
                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-add-item">
                    <i class="fa fa-plus"></i> Tambah Item
                </button>
                <button class="btn btn-info btn-sm btn-update-status" data-status="sent">
                    <i class="fa fa-paper-plane"></i> Kirim PO
                </button>
                <?php endif; ?>
                <?php if ($can_receive): ?>
                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-close-po">
                    <i class="fa fa-lock"></i> Tutup PO
                </button>
                <?php endif; ?>
                <?php if ($is_superadmin && in_array($po->status, ['draft','sent'])): ?>
                <button class="btn btn-danger btn-sm btn-update-status" data-status="cancelled">
                    <i class="fa fa-times"></i> Batalkan
                </button>
                <?php endif; ?>
                <a href="<?= site_url('purchase-order/print/' . $po->po_id) ?>" target="_blank" class="btn btn-default btn-sm">
                    <i class="fa fa-print"></i> Print
                </a>
                <a href="<?= site_url('purchase-order') ?>" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="po-meta" style="margin-top:16px">
            <div class="po-meta-item">
                <label>Supplier</label>
                <p><?= htmlspecialchars($po->nama_supplier) ?></p>
                <?php if ($po->phone): ?><small class="text-muted"><i class="fa fa-phone"></i> <?= htmlspecialchars($po->phone) ?></small><?php endif; ?>
            </div>
            <div class="po-meta-item">
                <label>Tanggal PO</label>
                <p><?= indo_date($po->po_date) ?></p>
            </div>
            <div class="po-meta-item">
                <label>Expected Tiba</label>
                <p><?= $po->expected_date ? indo_date($po->expected_date) : '<span class="text-muted">—</span>' ?></p>
            </div>
            <div class="po-meta-item">
                <label>Dibuat Oleh</label>
                <p><?= htmlspecialchars($po->created_by_name) ?></p>
            </div>
        </div>

        <?php if ($total_ordered > 0 && in_array($po->status, ['sent','partial','received','closed'])): ?>
        <div style="margin-top:16px;border-top:1px solid #eee;padding-top:12px">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                <small style="color:#555"><i class="fa fa-cube"></i> Progress Penerimaan</small>
                <small style="font-weight:600;color:#<?= $pct_overall >= 100 ? '00a65a' : ($pct_overall > 0 ? 'f39c12' : '888') ?>">
                    <?= $total_received ?> / <?= $total_ordered ?> item (<?= $pct_overall ?>%)
                </small>
            </div>
            <div class="gr-progress">
                <div class="gr-progress-bar" style="width:<?= $pct_overall ?>%;background:<?= $pct_overall >= 100 ? '#00a65a' : ($pct_overall > 0 ? '#f39c12' : '#ccc') ?>"></div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($po->notes): ?>
        <div style="margin-top:12px;padding:8px 12px;background:#eaf4fb;border-radius:4px;font-size:13px;color:#31708f">
            <i class="fa fa-info-circle"></i> <?= htmlspecialchars($po->notes) ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($po->status === 'closed' && $po->close_note): ?>
    <div class="callout callout-warning" style="margin-bottom:16px">
        <h4><i class="fa fa-lock"></i> PO Ditutup</h4>
        <p><?= htmlspecialchars($po->close_note) ?></p>
    </div>
    <?php endif; ?>

    <!-- Detail Items -->
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> Daftar Item PO</h3>
            <?php if ($can_receive): ?>
            <div class="box-tools pull-right">
                <a href="<?= site_url('purchase-order/receive/' . $po->po_id) ?>" class="btn btn-warning btn-sm">
                    <i class="fa fa-inbox"></i> Mulai Penerimaan
                </a>
            </div>
            <?php endif; ?>
        </div>
        <form action="<?= site_url('purchase-order/receive') ?>" method="post" id="form-gr">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <input type="hidden" name="po_id" value="<?= $po->po_id ?>">
            <div class="box-body table-responsive" style="padding:0">
                <table class="table table-bordered table-condensed table-gr" style="margin-bottom:0">
                    <thead>
                        <tr>
                            <th style="min-width:180px">Nama Barang</th>
                            <th width="70" class="text-center">Stok</th>
                            <?php if (!$can_edit): ?>
                            <th width="110" class="text-center">Progress Terima</th>
                            <th width="120" class="text-right">Harga PO</th>
                            <th width="120" class="text-right">Harga Aktual</th>
                            <th width="110" class="text-right">Selisih</th>
                            <?php else: ?>
                            <th width="100" class="text-right">Harga PO</th>
                            <th width="60" class="text-center">Qty</th>
                            <?php endif; ?>
                            <?php if ($can_modify): ?>
                            <th width="40" class="text-center"></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $d):
                            $pct = $d->qty_ordered > 0 ? round($d->qty_received / $d->qty_ordered * 100) : 0;
                            $bar_class = $pct >= 100 ? '#00a65a' : ($pct > 0 ? '#f39c12' : '#ddd');
                            $remaining = $d->qty_ordered - $d->qty_received;
                        ?>
                        <?php
                            $row_class = $pct >= 100 ? 'row-full' : ($pct > 0 ? 'row-partial' : '');
                            $row_style = $pct >= 100 ? 'background:#f0fff4' : ($pct > 0 ? 'background:#fffdf0' : '');
                        ?>
                        <tr class="<?= $row_class ?>" style="<?= $row_style ?>">
                            <td>
                                <?php if ($d->barcode): ?>
                                <div style="font-size:11px;color:#888;margin-bottom:2px"><?= htmlspecialchars($d->barcode) ?></div>
                                <?php endif; ?>
                                <strong><?= htmlspecialchars($d->display_name) ?></strong>
                                <?php if (!$d->item_id): ?>
                                <br><span class="label label-warning" style="font-size:11px">Belum Terdaftar</span>
                                <?php if ($d->qty_received > 0): ?>
                                <button type="button" class="btn btn-xs btn-warning btn-register-item"
                                    style="margin-left:4px"
                                    data-detail_id="<?= $d->id ?>"
                                    data-name="<?= htmlspecialchars($d->item_name_temp ?? $d->display_name) ?>"
                                    data-modal_price="<?= (int)($d->actual_price ?: $d->unit_price) ?>">
                                    <i class="fa fa-plus-circle"></i> Daftarkan
                                </button>
                                <?php else: ?>
                                <small class="text-muted"> — terima dulu sebelum daftarkan</small>
                                <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php
                                $s = $d->stock ?? null;
                                if ($s === null) { echo '<span class="text-muted">—</span>'; }
                                elseif ($s <= 0) { echo '<span class="text-danger"><b>'.$s.'</b></span>'; }
                                elseif ($s <= 3) { echo '<span class="text-warning"><b>'.$s.'</b></span>'; }
                                else { echo $s; }
                                ?>
                            </td>
                            <?php if (!$can_edit): ?>
                            <td class="text-center">
                                <div style="font-size:12px;font-weight:600;color:<?= $pct >= 100 ? '#00a65a' : ($pct > 0 ? '#e08e0b' : '#999') ?>">
                                    <?= $d->qty_received ?>/<?= $d->qty_ordered ?>
                                    <?php if (!empty($d->nama_unit)): ?>
                                    <span style="font-size:10px;color:#9ca3af;font-weight:400"><?= htmlspecialchars($d->nama_unit) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="gr-progress">
                                    <div class="gr-progress-bar" style="width:<?= $pct ?>%;background:<?= $bar_class ?>"></div>
                                </div>
                            </td>
                            <td class="text-right"><?= indo_currency($d->unit_price) ?></td>
                            <td class="text-right"><?= $d->actual_price ? indo_currency($d->actual_price) : '<span class="text-muted">—</span>' ?></td>
                            <td class="text-right">
                                <?php
                                $v = $d->price_variance ?? null;
                                if ($v === null || $v == 0) { echo '<span class="text-muted">—</span>'; }
                                elseif ($v > 0) { echo '<span class="text-danger">+' . indo_currency($v) . '</span>'; }
                                else { echo '<span class="text-success">' . indo_currency($v) . '</span>'; }
                                ?>
                            </td>
                            <?php elseif ($can_modify): ?>
                            <td class="text-right">
                                <input type="text" class="form-control input-xs input-rp-edit text-right"
                                    data-id="<?= $d->id ?>"
                                    value="<?= number_format((int)$d->unit_price, 0, ',', '.') ?>"
                                    style="width:110px;margin-left:auto">
                            </td>
                            <td class="text-center" style="white-space:nowrap">
                                <input type="number" class="form-control input-xs input-qty-edit text-center"
                                    data-id="<?= $d->id ?>"
                                    value="<?= $d->qty_ordered ?>" min="1"
                                    style="width:55px;display:inline-block">
                                <?php if (!empty($d->nama_unit)): ?>
                                <span style="font-size:11px;color:#9ca3af;margin-left:2px"><?= htmlspecialchars($d->nama_unit) ?></span>
                                <?php endif; ?>
                            </td>
                            <?php elseif ($can_edit): ?>
                            <td class="text-right"><?= indo_currency($d->unit_price) ?></td>
                            <td class="text-center">
                                <?= $d->qty_ordered ?>
                                <?php if (!empty($d->nama_unit)): ?>
                                <span style="font-size:11px;color:#9ca3af"><?= htmlspecialchars($d->nama_unit) ?></span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                            <?php if ($can_modify): ?>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-xs btn-remove-detail"
                                    data-id="<?= $d->id ?>">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($can_modify): ?>
            <div class="box-footer" style="text-align:right">
                <button type="button" class="btn btn-primary" id="btn-save-details">
                    <i class="fa fa-save"></i> Simpan Perubahan
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>

</div>

<!-- Modal Daftarkan Item Baru -->
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
                            <label>Barcode <small class="text-muted">(opsional)</small></label>
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

<!-- Modal Tambah Item (Draft only, Super Admin) -->
<?php if ($can_modify): ?>
<div class="modal fade" id="modal-add-item" tabindex="-1">
    <div class="modal-dialog" style="max-width:520px">
        <div class="modal-content">
            <div class="modal-header" style="background:#222d32;color:#fff;border-bottom:3px solid #00a65a">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff">&times;</button>
                <h4 class="modal-title"><i class="fa fa-plus"></i> Tambah Item ke PO
                    <small style="font-size:12px;opacity:.75">— <?= htmlspecialchars($po->nama_supplier) ?></small>
                </h4>
            </div>
            <div class="modal-body">
                <div class="form-group" style="position:relative;margin-bottom:8px">
                    <div style="display:flex;align-items:center;gap:8px;background:#f4f6f8;border:1.5px solid #d0d5dd;border-radius:8px;padding:0 12px;height:38px;transition:border-color .2s"
                         id="add-item-search-wrap">
                        <i class="fa fa-search" style="color:#aaa;font-size:14px;flex-shrink:0"></i>
                        <input type="text" id="add-item-search"
                               placeholder="Cari barcode / nama barang..."
                               autocomplete="off"
                               style="border:none;outline:none;background:transparent;font-size:13px;width:100%"
                               onfocus="document.getElementById('add-item-search-wrap').style.borderColor='#00a65a'"
                               onblur="document.getElementById('add-item-search-wrap').style.borderColor='#d0d5dd'">
                    </div>
                    <div id="search-results"
                         style="position:absolute;top:40px;left:0;right:0;z-index:9999;max-height:220px;overflow-y:auto;
                                box-shadow:0 6px 18px rgba(0,0,0,.15);border-radius:0 0 8px 8px;
                                border:1px solid #d0d5dd;border-top:none;background:#fff;display:none"></div>
                </div>
                <div id="selected-item-info" style="display:none;background:#f8fffe;border-left:3px solid #00a65a;padding:10px 14px;border-radius:0 4px 4px 0;margin-bottom:12px">
                    <div style="font-size:11px;color:#888">Item dipilih</div>
                    <div id="selected-item-name" style="font-weight:700;font-size:14px"></div>
                    <div id="selected-item-barcode" style="font-size:11px;color:#888"></div>
                </div>
                <input type="hidden" id="add-item-id">
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label>Qty <span class="text-red">*</span></label>
                            <input type="number" id="add-item-qty" class="form-control" value="1" min="1">
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label>Harga Beli (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-addon">Rp</span>
                                <input type="text" id="add-item-price" class="form-control input-rp-fmt" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Catatan</label>
                    <input type="text" id="add-item-notes" class="form-control" placeholder="Opsional">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn-submit-add-item">
                    <i class="fa fa-plus"></i> Tambah
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Tutup PO -->
<div class="modal fade" id="modal-close-po" tabindex="-1">
    <div class="modal-dialog" style="max-width:440px">
        <div class="modal-content">
            <div class="modal-header" style="background:#222d32;color:#fff;border-bottom:3px solid #f39c12">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff">&times;</button>
                <h4 class="modal-title"><i class="fa fa-lock"></i> Tutup Purchase Order</h4>
            </div>
            <form action="<?= site_url('purchase-order/close') ?>" method="post">
                <input type="hidden" name="po_id" value="<?= $po->po_id ?>">
                <div class="modal-body">
                    <div class="callout callout-warning" style="margin-bottom:16px">
                        <p style="margin:0;font-size:13px">
                            <i class="fa fa-exclamation-triangle"></i>
                            Item yang <strong>belum diterima</strong> akan otomatis dikembalikan ke
                            <strong>Keranjang PO</strong> untuk diorder ulang.
                        </p>
                    </div>
                    <div class="form-group">
                        <label>Keterangan Penutupan <span class="text-red">*</span></label>
                        <textarea name="close_note" class="form-control" rows="3"
                            placeholder="Contoh: Supplier tidak bisa kirim sisa item minggu ini." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-lock"></i> Tutup PO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function () {
    var poId = <?= $po->po_id ?>;

    function toast(icon, msg) {
        Swal.fire({
            toast: true, position: 'top-end', icon: icon, title: msg,
            showConfirmButton: false, timer: 3000, timerProgressBar: true,
        });
    }

    <?php $flash = $this->session->flashdata('success'); if ($flash): ?>
    toast('success', '<?= addslashes($flash) ?>');
    <?php endif; ?>

    // Update status (kirim/batalkan)
    $(document).on('click', '.btn-update-status', function () {
        var $btn   = $(this);
        if ($btn.prop('disabled')) return;
        $btn.prop('disabled', true);

        var status = $btn.data('status');
        var label  = status === 'sent' ? 'mengirim' : 'membatalkan';
        var title  = status === 'sent' ? 'PO berhasil dikirim ke supplier' : 'PO berhasil dibatalkan';

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Yakin ingin ' + label + ' PO ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post('<?= site_url('purchase-order/status') ?>', { po_id: poId, status: status }, function (res) {
                    if (res.status === 'success') {
                        toast('success', title);
                        setTimeout(function () { location.reload(); }, 1600);
                    } else {
                        $btn.prop('disabled', false);
                    }
                }, 'json');
            } else {
                $btn.prop('disabled', false);
            }
        });
    });

    // Konversi harga → kode PK (sama dengan server-side _price_to_pk)
    function priceToKJS(price) {
        var map = {'0':'Y','1':'S','2':'I','3':'T','4':'O','5':'M','6':'P','7':'U','8':'L','9':'X'};
        var s = String(price).replace(/[^0-9]/g, '');
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

    // Daftarkan item baru
    $(document).on('click', '.btn-register-item', function () {
        var modalPrice = $(this).data('modal_price') || 0;
        $('#reg-detail_id').val($(this).data('detail_id'));
        $('#reg-nama_item').val($(this).data('name'));
        $('#reg-modal').val(modalPrice);
        $('#reg-pk').val(modalPrice ? priceToKJS(modalPrice) : '');
        $('#reg-category_id, #reg-unit_id').val('');
        $('#reg-barcode').val('<?= $next_barcode ?>');
        $('#modal-register-item').modal('show');
    });

    // Auto-update PK saat modal diubah (hanya jika user belum edit PK manual)
    var pkManual = false;
    $('#reg-pk').on('input', function () { pkManual = $(this).val().trim() !== ''; });
    $('#reg-modal').on('input', function () {
        if (!pkManual) $('#reg-pk').val(priceToKJS($(this).val()));
    });
    $('#modal-register-item').on('hidden.bs.modal', function () { pkManual = false; });

    $('#btn-submit-register').on('click', function () {
        var nama  = $.trim($('#reg-nama_item').val());
        var cat   = $('#reg-category_id').val();
        var unit  = $('#reg-unit_id').val();
        var modal = $('#reg-modal').val();

        if (!nama || !cat || !unit || !modal) {
            toast('warning', 'Lengkapi semua field yang wajib diisi.');
            return;
        }

        var pk   = $.trim($('#reg-pk').val()) || priceToKJS(modal);
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
        $.post('<?= site_url('purchase-order/register-item') ?>', {
            detail_id: $('#reg-detail_id').val(), po_id: $('#reg-po_id').val(),
            nama_item: nama, pk: pk, barcode: $('#reg-barcode').val(),
            category_id: cat, unit_id: unit, modal: modal,
        }, function (res) {
            $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Daftarkan & Update Stok');
            if (res.status === 'success') {
                $('#modal-register-item').modal('hide');
                toast('success', 'Barang berhasil didaftarkan dan stok diupdate!');
                setTimeout(function () { location.reload(); }, 1600);
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Terjadi kesalahan.' });
            }
        }, 'json');
    });

    // ── Harga formatting ─────────────────────────────────────
    function formatRp(val) {
        var s = String(val).replace(/[^0-9]/g, '');
        return s.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function unformatRp(val) {
        return parseInt(String(val).replace(/\./g, ''), 10) || 0;
    }
    function priceToK(rawStr) {
        var s = String(rawStr).replace(/[^0-9]/g, '');
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

    // Init pk values on load
    $('.input-actual-price').each(function () {
        var raw = String(unformatRp($(this).val()));
        $(this).closest('td').find('.input-pk-new').val(priceToK(raw));
    });

    $(document).on('input', '.input-actual-price', function () {
        var raw = String($(this).val()).replace(/[^0-9]/g, '');
        $(this).val(formatRp(raw));
        $(this).closest('td').find('.input-pk-new').val(priceToK(raw));
    });
    $(document).on('click', '.input-actual-price', function () {
        $(this).select();
    });

    <?php if ($can_modify): ?>
    // ── Edit Draft: Format & Simpan Perubahan ───────────────
    var supplierId = <?= (int) $po->supplier_id ?>;

    $(document).on('input', '.input-rp-edit', function () {
        var raw = String($(this).val()).replace(/[^0-9]/g, '');
        $(this).val(raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
    });
    $(document).on('click', '.input-rp-edit', function () { $(this).select(); });
    $(document).on('click', '.input-qty-edit', function () { $(this).select(); });

    $('#btn-save-details').on('click', function () {
        var $btn      = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
        var detailIds = [], qtys = [], prices = [];

        $('.input-qty-edit').each(function () {
            detailIds.push($(this).data('id'));
            qtys.push($(this).val());
        });
        $('.input-rp-edit').each(function () {
            prices.push(parseInt($(this).val().replace(/\./g, ''), 10) || 0);
        });

        $.post('<?= site_url('purchase-order/update-detail') ?>', {
            po_id:      poId,
            'detail_id[]': detailIds,
            'qty[]':       qtys,
            'unit_price[]': prices,
        }, function (res) {
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Simpan Perubahan');
            if (res.status === 'success') {
                toast('success', 'Perubahan disimpan.');
            } else {
                toast('error', res.message || 'Gagal menyimpan.');
            }
        }, 'json');
    });

    // ── Edit Draft: Tambah Item ──────────────────────────────

    $(document).on('input', '.input-rp-fmt', function () {
        var raw = String($(this).val()).replace(/[^0-9]/g, '');
        $(this).val(raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
    });
    $(document).on('click', '.input-rp-fmt', function () { $(this).select(); });

    // ── Item search — sale_form style ──────────────────────────
    var searchTimer;
    $('#add-item-search').on('keyup', function () {
        clearTimeout(searchTimer);
        var kw = $.trim($(this).val());
        if (kw.length < 2) { $('#search-results').hide().empty(); return; }
        searchTimer = setTimeout(function () {
            $.post('<?= site_url('purchase-order/search-item') ?>', { keyword: kw, supplier_id: supplierId }, function (res) {
                var html = '';
                (res.items || []).forEach(function (it) {
                    var name    = $('<span>').text(it.nama_item).html();
                    var barcode = it.barcode ? '<small class="text-muted" style="margin-left:6px">' + it.barcode + '</small>' : '';
                    var stok    = '<span class="pull-right text-muted" style="font-size:11px">Stok: ' + it.stock + '</span>';
                    html += '<a href="#" class="list-group-item item-pick" style="padding:8px 14px;font-size:13px;cursor:pointer"'
                          + ' data-id="' + it.item_id + '"'
                          + ' data-name="' + $('<span>').text(it.nama_item).html() + '"'
                          + ' data-barcode="' + (it.barcode || '') + '"'
                          + ' data-price="' + it.ref_price + '">'
                          + stok + '<strong>' + name + '</strong>' + barcode
                          + '</a>';
                });
                if (!html) html = '<div class="list-group-item" style="padding:10px 14px;color:#999;font-size:13px">'
                                + '<i class="fa fa-search"></i> Tidak ada barang ditemukan</div>';
                $('#search-results').html(html).show();
            }, 'json');
        }, 300);
    });

    $(document).on('click', '.item-pick', function (e) {
        e.preventDefault();
        var price = parseInt($(this).data('price')) || 0;
        $('#add-item-id').val($(this).data('id'));
        $('#selected-item-name').text($(this).data('name'));
        $('#selected-item-barcode').text($(this).data('barcode') || '');
        $('#add-item-price').val(price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
        $('#selected-item-info').show();
        $('#search-results').hide();
        $('#add-item-search').val($(this).data('name'));
    });

    // Close dropdown when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#modal-add-item .form-group').length) {
            $('#search-results').hide();
        }
    });

    $('#modal-add-item').on('hidden.bs.modal', function () {
        $('#add-item-search').val('');
        $('#add-item-id').val('');
        $('#selected-item-info').hide();
        $('#search-results').hide().empty();
        $('#add-item-qty').val(1);
        $('#add-item-price').val(0);
        $('#add-item-notes').val('');
    });

    $('#btn-submit-add-item').on('click', function () {
        var item_id = $('#add-item-id').val();
        if (!item_id) { toast('warning', 'Pilih item terlebih dahulu.'); return; }
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        $.post('<?= site_url('purchase-order/add-detail') ?>', {
            po_id:      poId,
            item_id:    item_id,
            qty:        $('#add-item-qty').val(),
            unit_price: parseInt($('#add-item-price').val().replace(/\./g, ''), 10) || 0,
            notes:      $('#add-item-notes').val(),
        }, function (res) {
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Tambah');
            if (res.status === 'success') {
                $('#modal-add-item').modal('hide');
                toast('success', 'Item berhasil ditambahkan.');
                setTimeout(function () { location.reload(); }, 1200);
            } else {
                toast('error', res.message || 'Gagal menambahkan item.');
            }
        }, 'json');
    });

    // ── Edit Draft: Hapus Item ───────────────────────────────
    $(document).on('click', '.btn-remove-detail', function () {
        var detail_id = $(this).data('id');
        Swal.fire({
            title: 'Hapus item ini dari PO?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dd4b39',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.post('<?= site_url('purchase-order/remove-detail') ?>', { detail_id: detail_id, po_id: poId }, function (res) {
                if (res.status === 'success') {
                    toast('success', 'Item dihapus.');
                    setTimeout(function () { location.reload(); }, 900);
                } else {
                    toast('error', res.message || 'Gagal menghapus.');
                }
            }, 'json');
        });
    });
    <?php endif; ?>
});
</script>
