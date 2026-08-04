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
        <h2>Aging Piutang</h2>
        <div>Per Tanggal <?= tgl_finance($as_of) ?></div>
    </div>

    <h4>Ringkasan per Customer</h4>
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th class="text-right">0-30 hari</th>
                <th class="text-right">31-60 hari</th>
                <th class="text-right">61-90 hari</th>
                <th class="text-right">&gt;90 hari</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grand = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '>90' => 0, 'total' => 0];
            foreach ($summary as $s):
                foreach ($grand as $k => $v) $grand[$k] += $s[$k];
            ?>
            <tr>
                <td><?= $s['nama_customer'] ?></td>
                <td class="text-right"><?= number_format($s['0-30'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($s['31-60'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($s['61-90'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($s['>90'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($s['total'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="bg-gray">
                <td>Total</td>
                <td class="text-right"><?= number_format($grand['0-30'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($grand['31-60'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($grand['61-90'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($grand['>90'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($grand['total'], 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <h4>Detail Invoice</h4>
    <table>
        <thead>
            <tr>
                <th>No. Invoice</th>
                <th>Customer</th>
                <th>Tgl Invoice</th>
                <th>Jatuh Tempo</th>
                <th class="text-right">Hari Terlambat</th>
                <th class="text-right">Sisa</th>
                <th>Bucket</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= $r->ar_no ?></td>
                <td><?= $r->nama_customer ?></td>
                <td><?= tgl_finance($r->invoice_date) ?></td>
                <td><?= tgl_finance($r->due_date) ?></td>
                <td class="text-right"><?= (int) $r->days_overdue ?></td>
                <td class="text-right"><?= number_format($r->outstanding_amount, 0, ',', '.') ?></td>
                <td><?= $r->bucket ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
