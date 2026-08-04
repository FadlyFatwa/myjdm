<?php if(!$header): ?>
<div class="alert alert-danger">Data tidak ditemukan</div>
<?php return; endif; ?>

<div class="mb-3">
    <strong>Invoice:</strong> <?= $header->invoice ?><br>
    <strong>Tanggal:</strong> <?= date('d-m-Y', strtotime($header->sale_date)) ?><br>
    <strong>Total Qty:</strong> <?= number_format($header->total_qty) ?><br>
    <strong>Grand Total:</strong> <?= number_format($header->grand_total) ?><br>
    <strong>DPP:</strong> <?= number_format($header->dpp) ?><br>
    <strong>PPN:</strong> <?= number_format($header->ppn) ?><br>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm">
        <thead class="bg-info">
            <tr>
                <th>No</th>
                <th>Item</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Total</th>
                <th class="text-right">DPP</th>
                <th class="text-right">PPN</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; foreach($items as $it): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $it->nama_barang_jual ?></td>
                <td class="text-center"><?= number_format($it->qty) ?></td>
                <td class="text-right"><?= number_format($it->price_sale) ?></td>
                <td class="text-right"><?= number_format($it->total) ?></td>
                <td class="text-right"><?= number_format($it->dpp) ?></td>
                <td class="text-right"><?= number_format($it->ppn) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>