<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Penjualan</title>
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

        <?php
        // Get unique customers
        $customers = [];
        foreach ($row->result() as $data) {
            $customers[$data->customer_id] = $data->nama_customer;
        }

        // Display data conditionally based on the number of unique customers
        if (count($customers) > 1) {
        ?>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Discount</th>
                    <th>Grand Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach($row->result() as $data) { ?>
                <tr>
                    <td><?=$no++?></td>
                    <td><?=$data->invoice?></td>
                    <td><?=indo_date($data->date)?></td>
                    <td><?=$data->customer_id == null ? "Umum" : $data->nama_customer?></td>
                    <td class="text-right"><?=indo_currency($data->total_price)?></td>
                    <td class="text-right"><?=indo_currency($data->discount)?></td>
                    <td class="text-right"><?=indo_currency($data->final_price)?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php } else { ?>
        
        <p>Customer: <?=reset($customers) ? reset($customers) : "Umum"?></p>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Discount</th>
                    <th>Grand Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach($row->result() as $data) { ?>
                <tr>
                    <td><?=$no++?></td>
                    <td><?=$data->invoice?></td>
                    <td><?=indo_date($data->date)?></td>
                    <td class="text-right"><?=indo_currency($data->total_price)?></td>
                    <td class="text-right"><?=indo_currency($data->discount)?></td>
                    <td class="text-right"><?=indo_currency($data->final_price)?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php } ?>

        <div class="summary">
            <h3>Total Penjualan: <?= indo_currency($total_penjualan) ?></h3>
        </div>
    </div>
</body>
</html>
