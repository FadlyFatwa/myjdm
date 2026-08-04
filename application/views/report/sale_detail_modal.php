<?php if ($sale): ?>

<!-- ── Info Transaksi ────────────────────────────────── -->
<table class="table table-bordered" style="margin-bottom:16px;">
    <tbody>
        <tr>
            <th width="22%" class="bg-gray-light">Invoice</th>
            <td width="28%"><strong><?= $sale->invoice ?></strong></td>
            <th width="22%" class="bg-gray-light">Pembeli</th>
            <td><?= $sale->customer_id ? htmlspecialchars($sale->nama_customer) : 'Umum — ' . htmlspecialchars($sale->customer_name ?? '-') ?></td>
        </tr>
        <tr>
            <th class="bg-gray-light">Tanggal</th>
            <td><?= indo_date($sale->date) ?> <?= substr($sale->create, 11, 5) ?></td>
            <th class="bg-gray-light">Kasir</th>
            <td><?= ucfirst($sale->nama_user) ?></td>
        </tr>
        <tr>
            <th class="bg-gray-light">Metode</th>
            <?php
                $pm  = $sale->payment_method;
                $pmc = $pm === 'cash' ? 'success' : ($pm === 'transfer' ? 'info' : ($pm === 'qris' ? 'primary' : ($pm === 'debit' ? 'default' : 'warning')));
            ?>
            <td><span class="label label-<?= $pmc ?>"><?= strtoupper($pm) ?></span>
                &nbsp;
                <span class="label label-<?= $sale->payment_status === 'lunas' ? 'success' : 'danger' ?>">
                <?= ucfirst($sale->payment_status) ?></span>
            </td>
            <th class="bg-gray-light">Catatan</th>
            <td><?= $sale->note ? htmlspecialchars($sale->note) : '<span class="text-muted">—</span>' ?></td>
        </tr>
        <tr>
            <th class="bg-gray-light">Subtotal</th>
            <td>Rp <?= number_format($sale->total_price, 0, ',', '.') ?></td>
            <th class="bg-gray-light">Diskon Global</th>
            <td><?= $sale->discount ?>%</td>
        </tr>
        <?php if ($sale->payment_method === 'cash'): ?>
        <tr>
            <th class="bg-gray-light">Cash</th>
            <td>Rp <?= number_format($sale->cash, 0, ',', '.') ?></td>
            <th class="bg-gray-light">Kembalian</th>
            <td>Rp <?= number_format($sale->change, 0, ',', '.') ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th class="bg-gray-light text-primary">Grand Total</th>
            <td colspan="3"><strong class="text-primary" style="font-size:16px;">
                Rp <?= number_format($sale->final_price, 0, ',', '.') ?>
            </strong></td>
        </tr>
    </tbody>
</table>

<!-- ── Detail Barang ─────────────────────────────────── -->
<?php if (!empty($products)): ?>
<h5 style="margin:12px 0 6px; font-weight:700;">
    <i class="fa fa-cubes text-green"></i> Barang
    <span class="badge" style="background:#00a65a;"><?= count($products) ?></span>
</h5>
<table class="table table-bordered table-striped table-condensed" style="font-size:12px;">
    <thead class="bg-gray-light">
        <tr>
            <th>Barcode</th>
            <th>Nama Barang</th>
            <th class="text-right">Harga Jual</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Diskon/Item</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $sum_barang = 0; foreach ($products as $p): $sum_barang += $p->total; ?>
        <tr>
            <td><small><?= $p->barcode ?></small></td>
            <td><?= htmlspecialchars($p->nama_barang_jual) ?></td>
            <td class="text-right"><?= number_format($p->price_sale, 0, ',', '.') ?></td>
            <td class="text-center"><?= $p->qty ?> <?= $p->nama_unit ?? '' ?></td>
            <td class="text-right"><?= $p->discount_item > 0 ? number_format($p->discount_item, 0, ',', '.') : '<span class="text-muted">—</span>' ?></td>
            <td class="text-right"><strong><?= number_format($p->total, 0, ',', '.') ?></strong></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr style="background:#f9f9f9;">
            <td colspan="5" class="text-right"><strong>Subtotal Barang</strong></td>
            <td class="text-right"><strong><?= number_format($sum_barang, 0, ',', '.') ?></strong></td>
        </tr>
    </tfoot>
</table>
<?php endif; ?>

<!-- ── Detail Jasa ───────────────────────────────────── -->
<?php if (!empty($jasa)): ?>
<h5 style="margin:14px 0 6px; font-weight:700;">
    <i class="fa fa-wrench" style="color:#3c8dbc;"></i> Jasa
    <span class="badge" style="background:#3c8dbc;"><?= count($jasa) ?></span>
</h5>
<table class="table table-bordered table-striped table-condensed" style="font-size:12px;">
    <thead style="background:#ecf5fb;">
        <tr>
            <th>Nama Jasa</th>
            <th class="text-right">Tarif</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $sum_jasa = 0; foreach ($jasa as $j): $sum_jasa += $j->total; ?>
        <tr>
            <td><?= htmlspecialchars($j->nama_jasa) ?></td>
            <td class="text-right"><?= number_format($j->tarif, 0, ',', '.') ?></td>
            <td class="text-center"><?= $j->qty ?></td>
            <td class="text-right"><strong><?= number_format($j->total, 0, ',', '.') ?></strong></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr style="background:#f9f9f9;">
            <td colspan="3" class="text-right"><strong>Subtotal Jasa</strong></td>
            <td class="text-right"><strong><?= number_format($sum_jasa, 0, ',', '.') ?></strong></td>
        </tr>
    </tfoot>
</table>
<?php endif; ?>

<?php if (empty($products) && empty($jasa)): ?>
    <div class="alert alert-warning">Tidak ada detail item untuk transaksi ini.</div>
<?php endif; ?>

<?php else: ?>
    <div class="alert alert-danger">Data tidak ditemukan.</div>
<?php endif; ?>
