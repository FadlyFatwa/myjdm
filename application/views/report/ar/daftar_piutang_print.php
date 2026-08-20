<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        h2 { margin: 0 0 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; }
        th { background: #eee; text-align: left; }
        .text-right { text-align: right; }
        .header { text-align: center; margin-bottom: 15px; }
        .bg-gray { background: #f0f0f0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Daftar Piutang</h2>
        <div>Periode <?= tgl_finance($from) ?> s/d <?= tgl_finance($to) ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Invoice</th>
                <th>Customer</th>
                <th>Tgl Invoice</th>
                <th>Jatuh Tempo</th>
                <th class="text-right">Jumlah</th>
                <th class="text-right">Dibayar</th>
                <th class="text-right">Sisa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $status_label = [
                'outstanding' => 'Belum Lunas',
                'partial'     => 'Belum Lunas',
                'paid'        => 'Lunas',
                'void'        => 'Void',
            ];
            $no = 1;
            foreach ($rows as $r): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $r->ar_no ?></td>
                <td><?= $r->nama_customer ?></td>
                <td><?= tgl_finance($r->invoice_date) ?></td>
                <td><?= tgl_finance($r->due_date) ?></td>
                <td class="text-right"><?= number_format($r->amount, 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($r->paid_amount, 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($r->outstanding_amount, 0, ',', '.') ?></td>
                <td><?= $status_label[$r->status] ?? $r->status ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="bg-gray">
                <td colspan="5" class="text-right">Total</td>
                <td class="text-right"><?= number_format($totals['amount'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($totals['paid'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($totals['outstanding'], 0, ',', '.') ?></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
