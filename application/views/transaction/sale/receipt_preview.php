<style>
/* ── Receipt Preview — dark mode overrides ── */
body.dark-mode .preview-nota-wrap          { background: #222537 !important; }

/* Tombol Lewati */
body.dark-mode .btn-lewati                 { background: #252836 !important; color: #d1d5db !important; }
body.dark-mode .btn-lewati:hover           { background: #2a2d40 !important; }

/* Info grid label */
body.dark-mode .nota-label                 { color: #6b7280 !important; }

/* Thead row (override inline background:#f4f6f8) */
body.dark-mode .nota-table thead tr        { background: #1a1d27 !important; }
body.dark-mode .nota-table thead th        { background: #1a1d27 !important; color: #9ca3af !important; }

/* Grand Total row (override inline background:#f0f9f4) */
body.dark-mode .nota-table .row-grandtotal { background: #0d2e1d !important; }

/* Catatan box (override inline background:#fffde7) */
body.dark-mode .nota-catatan               { background: #241e0a !important; border-color: #b45309 !important; color: #fcd34d !important; }

/* Divider invoice */
body.dark-mode .invoice-divider            { border-top-color: #9ca3af !important; border-bottom-color: #374151 !important; }
</style>

<section class="content-header">
    <h1>Preview Cetak <small><?= $sale->invoice ?></small></h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?= site_url('sale') ?>">Penjualan</a></li>
        <li class="active">Preview Cetak</li>
    </ol>
</section>

<section class="content">
<div class="row">
<div class="col-md-8 col-md-offset-2">

    <!-- Status sukses -->
    <div class="text-center" style="padding:20px 0 16px;">
        <div style="width:60px; height:60px; background:#00a65a; border-radius:50%;
                    display:inline-flex; align-items:center; justify-content:center; margin-bottom:12px;">
            <i class="fa fa-check" style="color:#fff; font-size:28px;"></i>
        </div>
        <h3 style="margin:0 0 4px; font-weight:700;">Transaksi Berhasil!</h3>
        <p class="text-muted" style="margin:0;">Pilih format nota untuk mencetak</p>
    </div>

    <!-- Tombol Print -->
    <div style="display:flex; gap:10px; margin-bottom:20px;">
        <a href="<?= site_url('sale/cetak/' . $sale->sale_id) ?>" target="_blank"
           class="btn-print" data-url="<?= site_url('sale/cetak/' . $sale->sale_id) ?>"
           style="flex:1; background:#2c3e50; color:#fff; border-radius:8px; padding:14px 16px;
                  display:flex; align-items:center; gap:12px; text-decoration:none; cursor:pointer;">
            <div style="width:40px; height:40px; background:rgba(255,255,255,.15); border-radius:8px;
                        display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fa fa-file-pdf-o" style="font-size:20px;"></i>
            </div>
            <div>
                <div style="font-weight:700; font-size:14px;">Print Nota Besar</div>
                <div style="font-size:11px; opacity:.75;">Format A4 — PDF</div>
            </div>
            <i class="fa fa-chevron-right" style="margin-left:auto; opacity:.5;"></i>
        </a>

        <a href="<?= site_url('sale/cetak_kecil/' . $sale->sale_id) ?>" target="_blank"
           class="btn-print" data-url="<?= site_url('sale/cetak_kecil/' . $sale->sale_id) ?>"
           style="flex:1; background:#00a65a; color:#fff; border-radius:8px; padding:14px 16px;
                  display:flex; align-items:center; gap:12px; text-decoration:none; cursor:pointer;">
            <div style="width:40px; height:40px; background:rgba(255,255,255,.15); border-radius:8px;
                        display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fa fa-print" style="font-size:20px;"></i>
            </div>
            <div>
                <div style="font-weight:700; font-size:14px;">Print Nota Kecil</div>
                <div style="font-size:11px; opacity:.75;">Format thermal — printer kasir</div>
            </div>
            <i class="fa fa-chevron-right" style="margin-left:auto; opacity:.5;"></i>
        </a>

        <?php if (($from ?? 'sale') !== 'report'): ?>
        <a href="<?= site_url('sale/edit/' . $sale->sale_id) ?>?from=preview"
           style="background:#f39c12; color:#fff; border-radius:8px; padding:14px 16px;
                  display:flex; align-items:center; gap:8px; text-decoration:none; flex-shrink:0;">
            <i class="fa fa-pencil"></i>
            <span style="font-size:13px; font-weight:600;">Edit</span>
        </a>
        <?php endif; ?>

        <?php $back_url = ($from ?? 'sale') === 'report' ? site_url('report/sale') : site_url('sale'); ?>
        <a href="<?= $back_url ?>" class="btn-lewati"
           style="background:#f4f6f8; color:#555; border-radius:8px; padding:14px 16px;
                  display:flex; align-items:center; gap:8px; text-decoration:none; flex-shrink:0;">
            <i class="fa fa-times"></i>
            <span style="font-size:13px; font-weight:600;">Lewati</span>
        </a>
    </div>

    <!-- Preview Nota -->
    <div class="box box-solid" style="border-top:3px solid #00a65a;">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-file-text-o"></i> Preview Nota</h3>
        </div>
        <div class="box-body preview-nota-wrap" style="padding:24px 28px;">

            <!-- Header Nota -->
            <div class="text-center" style="margin-bottom:20px;">
                <h3 style="margin:0; font-weight:800; letter-spacing:1px;">JADI MOTOR BANDUNG</h3>
                <p style="margin:4px 0 0; color:#666; font-size:13px;">Jl. Banceuy Gg.Cikapundung No.18 , Bandung</p>
                <div class="invoice-divider" style="border-top:2px solid #333; border-bottom:1px dashed #ccc;
                            padding:6px 0; margin:12px 0;">
                    <strong style="font-size:15px; letter-spacing:2px;"><?= $sale->invoice ?></strong>
                </div>
            </div>

            <!-- Info Transaksi -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:4px; margin-bottom:16px; font-size:13px;">
                <div><span class="nota-label" style="color:#888;">Tanggal</span></div>
                <div><?= indo_date($sale->date) ?></div>

                <div><span class="nota-label" style="color:#888;">Pembeli</span></div>
                <div><?= $sale->customer_name ?? $sale->nama_customer ?? 'Umum' ?></div>

                <div><span class="nota-label" style="color:#888;">Kasir</span></div>
                <div><?= $sale->nama ?? $sale->user_name ?></div>

                <div><span class="nota-label" style="color:#888;">Pembayaran</span></div>
                <div style="text-transform:capitalize;"><?= $sale->metode ?? $sale->payment_method ?>
                    <?php if (($sale->payment_status ?? '') === 'lunas'): ?>
                        <span class="label label-success" style="margin-left:4px;">Lunas</span>
                    <?php else: ?>
                        <span class="label label-warning" style="margin-left:4px;">Belum Lunas</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabel Item -->
            <table class="table table-bordered nota-table" style="font-size:13px; margin-bottom:0;">
                <thead>
                    <tr style="background:#f4f6f8;">
                        <th width="5%">#</th>
                        <th>Nama Barang</th>
                        <th width="10%" class="text-center">Qty</th>
                        <th width="15%" class="text-right">Harga</th>
                        <th width="10%" class="text-right">Diskon</th>
                        <th width="15%" class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($sale_detail as $d): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td>
                            <?= htmlspecialchars($d->nama_barang_jual) ?>
                            <br><small class="text-muted"><?= $d->barcode ?></small>
                        </td>
                        <td class="text-center"><?= $d->qty ?></td>
                        <td class="text-right"><?= indo_currency($d->price_sale) ?></td>
                        <td class="text-right"><?= $d->discount_item > 0 ? indo_currency($d->discount_item) : '-' ?></td>
                        <td class="text-right"><strong><?= indo_currency($d->total) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right" style="font-weight:600;">Subtotal</td>
                        <td class="text-right"><?= indo_currency($sale->total_price) ?></td>
                    </tr>
                    <?php if ($sale->discount > 0): ?>
                    <tr>
                        <td colspan="5" class="text-right" style="color:#888;">Diskon <?= $sale->discount ?>%</td>
                        <td class="text-right" style="color:#dd4b39;">
                            - <?= indo_currency($sale->total_price * $sale->discount / 100) ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr class="row-grandtotal" style="background:#f0f9f4;">
                        <td colspan="5" class="text-right" style="font-weight:800; font-size:15px;">Grand Total</td>
                        <td class="text-right" style="font-weight:800; font-size:15px; color:#00a65a;">
                            <?= indo_currency($sale->final_price) ?>
                        </td>
                    </tr>
                    <?php if (($sale->payment_method ?? '') === 'cash'): ?>
                    <tr>
                        <td colspan="5" class="text-right" style="color:#888;">Cash</td>
                        <td class="text-right"><?= indo_currency($sale->cash) ?></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right" style="color:#888;">Kembalian</td>
                        <td class="text-right"><?= indo_currency($sale->change) ?></td>
                    </tr>
                    <?php endif; ?>
                </tfoot>
            </table>

            <?php if (!empty($sale->note)): ?>
            <div class="nota-catatan" style="margin-top:12px; padding:10px 14px; background:#fffde7;
                        border-left:3px solid #f39c12; border-radius:4px; font-size:13px;">
                <strong>Catatan:</strong> <?= htmlspecialchars($sale->note) ?>
            </div>
            <?php endif; ?>

        </div>
    </div>

</div>
</div>
</section>

<script>
// Setelah pilih print → buka di tab baru, lalu kembali ke halaman asal setelah 1.5 detik
document.querySelectorAll('.btn-print').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        window.open(this.getAttribute('href'), '_blank');
        setTimeout(function() {
            window.location.href = '<?= ($from ?? 'sale') === 'report' ? site_url('report/sale') : site_url('sale') ?>';
        }, 1500);
    });
});
</script>
