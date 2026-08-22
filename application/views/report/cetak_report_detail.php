<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan Detail</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Nunito', Arial, sans-serif;
            font-size: 13px;
            color: #000;
        }

        .container { width: 95%; }

        .company-name    { font-size: 20px; font-weight: 700; letter-spacing: .3px; }
        .company-address { font-size: 11px; margin-top: 3px; color: #444; }

        .report-badge {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        .report-meta { font-size: 11.5px; margin-top: 3px; }

        /* Header pakai table (bukan float) + table-layout:fixed, supaya rendering DOMPDF stabil dan
           kolom tidak melar ikut isi (auto layout DOMPDF bisa bikin tabel meluber ke luar halaman) */
        .header-table { width: 100%; table-layout: fixed; border-collapse: collapse; }
        .header-table td { border: none; padding: 0; vertical-align: top; overflow: hidden; }
        .header-table .col-left  { width: 60%; }
        .header-table .col-right { width: 40%; text-align: right; }

        hr.thick { border: none; border-top: 2px solid #000; margin: 10px 0 8px; }

        .data-table { width: 100%; table-layout: fixed; border-collapse: collapse; }

        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 7px 8px;
            font-size: 12px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .data-table thead tr th {
            font-weight: 700;
            text-align: center;
            border-bottom: 2px solid #000;
        }

        .text-right { text-align: right; }

        .data-table tfoot td { font-size: 12px; }
        .data-table tfoot tr td {
            font-weight: 700;
            border-top: 2px solid #000;
        }

        @page { margin: 1.5cm; }

        @media screen {
            body { background: #fff; }
            .container { max-width: 960px; margin: 0 auto; padding: 24px 28px; }
        }
    </style>
</head>
<body>
<div class="container">

    <table class="header-table">
        <tr>
            <td class="col-left">
                <div class="company-name">JADI MOTOR</div>
                <div class="company-address">Jl.Banceuy Gg.Cikapundung No.18</div>
            </td>
            <td class="col-right">
                <div class="report-badge">Laporan Penjualan Detail</div>
                <div class="report-meta">Periode &nbsp;: <?=indo_date(@$post['date1'])?> s/d <?=indo_date(@$post['date2'])?></div>
            </td>
        </tr>
    </table>

    <hr class="thick">

    <?php
    // Get unique customers
    $customers = [];
    foreach ($row->result() as $data) {
        $customers[$data->customer_id] = $data->nama_customer;
    }

    // Group item rows (barang + barcode) per invoice, supaya bisa ditampilkan dengan rowspan
    $grouped = [];
    foreach ($detail->result() as $d) {
        if (!isset($grouped[$d->sale_id])) {
            $grouped[$d->sale_id] = [
                'invoice'       => $d->invoice,
                'date'          => $d->date,
                'customer_id'   => $d->customer_id,
                'nama_customer' => $d->nama_customer,
                'items'         => [],
            ];
        }
        $grouped[$d->sale_id]['items'][] = $d;
    }

    $multi_customer = count($customers) > 1;

    // Display data conditionally based on the number of unique customers
    if ($multi_customer) {
    ?>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:11%">Invoice</th>
                <th style="width:9%">Tanggal</th>
                <th style="width:13%">Customer</th>
                <th style="width:12%">Barcode</th>
                <th style="width:22%">Nama Barang</th>
                <th style="width:6%">Qty</th>
                <th style="width:11%">Harga</th>
                <th style="width:12%">Sub Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            foreach ($grouped as $g):
                $item_count = count($g['items']);
                foreach ($g['items'] as $idx => $item): ?>
            <tr>
                <?php if ($idx === 0): ?>
                <td rowspan="<?=$item_count?>" style="text-align:center"><?=$no?></td>
                <td rowspan="<?=$item_count?>"><?=$g['invoice']?></td>
                <td rowspan="<?=$item_count?>"><?=indo_date($g['date'])?></td>
                <td rowspan="<?=$item_count?>"><?=$g['customer_id'] == null ? "Umum" : $g['nama_customer']?></td>
                <?php endif; ?>
                <td><?=htmlspecialchars((string)$item->barcode)?></td>
                <td><?=htmlspecialchars((string)$item->nama_item)?></td>
                <td style="text-align:center"><?=$item->qty?></td>
                <td class="text-right"><?=indo_currency($item->price_sale)?></td>
                <td class="text-right"><?=indo_currency($item->total)?></td>
            </tr>
                <?php endforeach;
                $no++;
            endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" class="text-right">Total Penjualan :</td>
                <td class="text-right">Rp <?= number_format($total_penjualan, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <?php } else { ?>

    <p style="margin-bottom:6px;">Customer: <strong><?=reset($customers) ? reset($customers) : "Umum"?></strong></p>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:13%">Invoice</th>
                <th style="width:10%">Tanggal</th>
                <th style="width:14%">Barcode</th>
                <th style="width:25%">Nama Barang</th>
                <th style="width:7%">Qty</th>
                <th style="width:12%">Harga</th>
                <th style="width:14%">Sub Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            foreach ($grouped as $g):
                $item_count = count($g['items']);
                foreach ($g['items'] as $idx => $item): ?>
            <tr>
                <?php if ($idx === 0): ?>
                <td rowspan="<?=$item_count?>" style="text-align:center"><?=$no?></td>
                <td rowspan="<?=$item_count?>"><?=$g['invoice']?></td>
                <td rowspan="<?=$item_count?>"><?=indo_date($g['date'])?></td>
                <?php endif; ?>
                <td><?=htmlspecialchars((string)$item->barcode)?></td>
                <td><?=htmlspecialchars((string)$item->nama_item)?></td>
                <td style="text-align:center"><?=$item->qty?></td>
                <td class="text-right"><?=indo_currency($item->price_sale)?></td>
                <td class="text-right"><?=indo_currency($item->total)?></td>
            </tr>
                <?php endforeach;
                $no++;
            endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right">Total Penjualan :</td>
                <td class="text-right">Rp <?= number_format($total_penjualan, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <?php } ?>

</div>
</body>
</html>
