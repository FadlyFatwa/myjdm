<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Invoice Pajak <?= $sale->invoice ?></title>

<style>
body {
    font-family: sans-serif;
    font-size: 12px;
}
table {
    border-collapse: collapse;
    width: 100%;
}
table, th, td {
    border: 1px solid #000;
}
th, td {
    padding: 5px;
}
.text-right { text-align: right; }
.text-center { text-align: center; }
</style>

</head>
<body>

<h2 style="text-align:center;">INVOICE PAJAK</h2>

<div style="margin-bottom:10px;">
    <strong>No Invoice:</strong> <?= $sale->invoice ?><br>
    <strong>Tanggal:</strong> <?= date('d-m-Y', strtotime($sale->sale_date)) ?><br>
    <strong>Periode Pajak:</strong> <?= $sale->tax_period ?><br>
</div>

<table>
    <thead>
        <tr>
            <th width="5%">No</th>
            <th>Nama Barang</th>
            <th width="15%">Harga</th>
            <th width="10%">Qty</th>
            <th width="15%">Subtotal</th>
            <th width="15%">DPP</th>
            <th width="15%">PPN</th>
        </tr>
    </thead>
    <tbody>
        <?php $no=1; foreach($sale_detail as $d): ?>
        <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td><?= $d->nama_barang_jual ?></td>
            <td class="text-right"><?= number_format($d->price_sale,0,',','.') ?></td>
            <td class="text-center"><?= $d->qty ?></td>
            <td class="text-right"><?= number_format($d->total,0,',','.') ?></td>
            <td class="text-right"><?= number_format($d->dpp,0,',','.') ?></td>
            <td class="text-right"><?= number_format($d->ppn,0,',','.') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>

    <tfoot>
        <tr>
            <td colspan="4" class="text-right"><strong>Subtotal</strong></td>
            <td class="text-right"><?= number_format($sale->grand_total,0,',','.') ?></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="4" class="text-right"><strong>DPP</strong></td>
            <td></td>
            <td class="text-right"><?= number_format($sale->dpp,0,',','.') ?></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="4" class="text-right"><strong>PPN</strong></td>
            <td></td>
            <td></td>
            <td class="text-right"><?= number_format($sale->ppn,0,',','.') ?></td>
        </tr>
        <tr>
            <td colspan="6" class="text-right"><strong>Total + PPN</strong></td>
            <td class="text-right">
                <?= number_format($sale->grand_total + $sale->ppn,0,',','.') ?>
            </td>
        </tr>
    </tfoot>
</table>

<br><br>

<div style="text-align:right;">
    Bandung, <?= date('d M Y') ?><br><br><br>
    ___________________________
</div>

</body>
</html>