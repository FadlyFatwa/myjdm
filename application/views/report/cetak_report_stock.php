<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Pembelian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .report {
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #000;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .summary {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="report">
        <h1>Report Penjualan</h1>
        <p>Periode: <?=indo_date(@$post['date1'])?> s/d <?=indo_date(@$post['date2'])?></p>
        <?php if (!empty($row)): ?>
        <?php
// Get unique suppliers
$suppliers = [];
foreach ($row->result() as $data) {
    $suppliers[$data->supplier_id] = $data->nama_supplier;
}

// Display data conditionally based on the number of unique suppliers
if (count($suppliers) > 1) {  // Correct the variable name from $supplier to $suppliers
?>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Barcode</th>
            <th>Supplier</th>
            <th>Qty</th>
            <th>Modal</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1;
        foreach($row->result() as $data) { ?>
        <tr>
            <td><?=$no++?></td>
            <td><?=indo_date($data->date)?></td>
            <td><?=$data->barcode?></td>
            <td><?=$data->nama_supplier?></td>
            <td class="text-right"><?=$data->qty?></td>
            <td class="text-right"><?=indo_currency($data->modal)?></td>
            <td class="text-right"><?=indo_currency($data->subtotal)?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<?php } else { ?>
<p>Supplier: <?=$data->nama_supplier?></p> <!-- Correct the variable name from $supplier to $suppliers -->
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Barcode</th>
            <th>Qty</th>
            <th>Modal</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1;
        foreach($row->result() as $data) { ?>
        <tr>
            <td><?=$no++?></td>
            <td><?=indo_date($data->date)?></td>
            <td><?=$data->barcode?></td>
            <td class="text-right"><?=$data->qty?></td>
            <td class="text-right"><?=indo_currency($data->modal)?></td>
            <td class="text-right"><?=indo_currency($data->subtotal)?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<?php } ?>


        <?php else: ?>
        <p>No data available for the selected period and supplier.</p>
        <?php endif; ?>

        <div class="summary">
            <h3>Total Penjualan: <?= indo_currency($totalpembelian) ?></h3>
        </div>
    </div>
</body>
</html>
