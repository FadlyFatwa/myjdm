<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tagihan Penjualan</title>
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
        .header-table .col-left  { width: 42%; }
        .header-table .col-right { width: 34%; text-align: right; padding-right: 16px; }
        .header-table .col-total {
            width: 24%;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #000;
            padding: 6px 8px;
        }
        .grand-total-label { font-size: 11px; color: #444; }
        .grand-total-value { font-size: 17px; font-weight: 700; }

        hr.thick { border: none; border-top: 2px solid #000; margin: 10px 0 8px; }

        .data-table { width: 100%; table-layout: fixed; border-collapse: collapse; }

        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 7px 8px;
            font-size: 12px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .data-table thead tr th {
            font-weight: 700;
            text-align: center;
            border-bottom: 2px solid #000;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .data-table tfoot td { font-size: 12px; }
        .data-table tfoot tr td {
            font-weight: 700;
            border-top: 2px solid #000;
        }

        @page { margin: 1.5cm; }

        @media screen {
            body { background: #fff; }
            .container { max-width: 1100px; margin: 0 auto; padding: 24px 28px; }
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
                <div class="report-badge">Tagihan Penjualan</div>
                <div class="report-meta">Periode &nbsp;: <?=indo_date(@$post['date1'])?> s/d <?=indo_date(@$post['date2'])?></div>
            </td>
            <td class="col-total">
                <div class="grand-total-label">Grand Total</div>
                <div class="grand-total-value"><?= indo_currency($total_penjualan) ?></div>
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

    // Display data conditionally based on the number of unique customers
    if (count($customers) > 1) {
    ?>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:14%">No Invoice</th>
                <th style="width:11%">Tanggal</th>
                <th style="width:18%">Pembeli</th>
                <th style="width:38%">Keterangan</th>
                <th style="width:15%">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            foreach($row->result() as $data) { ?>
            <tr>
                <td class="text-center"><?=$no++?></td>
                <td><?=$data->invoice?></td>
                <td><?=indo_date($data->date)?></td>
                <td><?=$data->customer_id == null ? "Umum" : $data->nama_customer?></td>
                <td><?=nl2br(htmlspecialchars($data->note ?? ''))?></td>
                <td class="text-right"><?=indo_currency($data->final_price)?></td>
            </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right">Grand Total :</td>
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
                <th style="width:16%">No Invoice</th>
                <th style="width:13%">Tanggal</th>
                <th style="width:49%">Keterangan</th>
                <th style="width:17%">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            foreach($row->result() as $data) { ?>
            <tr>
                <td class="text-center"><?=$no++?></td>
                <td><?=$data->invoice?></td>
                <td><?=indo_date($data->date)?></td>
                <td><?=nl2br(htmlspecialchars($data->note ?? ''))?></td>
                <td class="text-right"><?=indo_currency($data->final_price)?></td>
            </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Grand Total :</td>
                <td class="text-right">Rp <?= number_format($total_penjualan, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <?php } ?>

</div>
</body>
</html>
