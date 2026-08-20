<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h2, h4 { margin: 0 0 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 5px 8px; }
        th { background: #eee; text-align: left; }
        .text-right { text-align: right; }
        .header { text-align: center; margin-bottom: 15px; }
        .info { margin-bottom: 10px; }
        .info td { border: none; padding: 2px 8px; }
        .total-row { font-weight: bold; background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="header">
        <h2>KONTRA BON</h2>
        <div>No. <?= $kb->kontra_bon_no ?></div>
    </div>

    <table class="info">
        <tr>
            <td width="120"><strong>Customer</strong></td>
            <td>: <?= $kb->nama_customer ?> (<?= $kb->phone ?>)</td>
            <td width="120"><strong>Periode</strong></td>
            <td>: <?= tgl_finance($kb->period_start) ?> s/d <?= tgl_finance($kb->period_end) ?></td>
        </tr>
        <tr>
            <td><strong>Jatuh Tempo</strong></td>
            <td>: <?= tgl_finance($kb->due_date) ?></td>
            <td><strong>Total Tagihan</strong></td>
            <td>: Rp <?= number_format($kb->total_amount, 0, ',', '.') ?></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>No. Invoice</th>
                <th>Tanggal</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($invoices as $inv): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $inv->sale_invoice ?: '-' ?></td>
                <td><?= tgl_finance($inv->invoice_date) ?></td>
                <td class="text-right"><?= number_format($inv->gross_amount ?: $inv->amount, 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="3" class="text-right">SUBTOTAL</td>
                <td class="text-right">Rp <?= number_format($subtotal_brutto, 0, ',', '.') ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="text-right">GRAND TOTAL</td>
                <td class="text-right">Rp <?= number_format($kb->total_amount, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top:20px;">Mohon pelunasan tagihan ini paling lambat tanggal <strong><?= tgl_finance($kb->due_date) ?></strong>.</p>
</body>
</html>
