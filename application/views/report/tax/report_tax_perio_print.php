<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: sans-serif; font-size: 12px; }
table { border-collapse: collapse; width: 100%; }
table, th, td { border: 1px solid #000; }
th, td { padding: 5px; }
.text-right { text-align: right; }
.text-center { text-align: center; }
</style>
</head>
<body>

<h2 style="text-align:center;">LAPORAN PAJAK</h2>
<h4 style="text-align:center;">Periode: <?= $period ?></h4>

<table>
<thead>
<tr>
    <th>No</th>
    <th>Invoice</th>
    <th>Tanggal</th>
    <th>Subtotal</th>
    <th>DPP</th>
    <th>PPN</th>
</tr>
</thead>
<tbody>
<?php $no=1; foreach($rows as $r): ?>
<tr>
    <td class="text-center"><?= $no++ ?></td>
    <td><?= $r->invoice ?></td>
    <td><?= date('d-m-Y', strtotime($r->sale_date)) ?></td>
    <td class="text-right"><?= number_format($r->grand_total,0,',','.') ?></td>
    <td class="text-right"><?= number_format($r->dpp,0,',','.') ?></td>
    <td class="text-right"><?= number_format($r->ppn,0,',','.') ?></td>
</tr>
<?php endforeach; ?>
</tbody>

<tfoot>
<tr>
    <td colspan="3" class="text-right"><strong>TOTAL</strong></td>
    <td class="text-right"><strong><?= number_format($summary->subtotal,0,',','.') ?></strong></td>
    <td class="text-right"><strong><?= number_format($summary->total_dpp,0,',','.') ?></strong></td>
    <td class="text-right"><strong><?= number_format($summary->total_ppn,0,',','.') ?></strong></td>
</tr>
</tfoot>
</table>

</body>
</html>