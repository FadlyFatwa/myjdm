<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        h2, h4 { margin: 0 0 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; }
        th { background: #eee; text-align: left; }
        .text-right { text-align: right; }
        .header { text-align: center; margin-bottom: 15px; }
        .bg-gray { background: #f0f0f0; font-weight: bold; }
        .void-row { color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Operasional</h2>
        <div>Periode <?= tgl_finance($from) ?> s/d <?= tgl_finance($to) ?></div>
    </div>

    <h4>Ringkasan per Kategori</h4>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($summary as $s): ?>
            <tr>
                <td><?= $s->coa_name ?></td>
                <td class="text-right"><?= number_format($s->total, 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="bg-gray">
                <td>Total</td>
                <td class="text-right"><?= number_format($total, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <h4>Detail Transaksi</h4>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Beban</th>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th class="text-right">Jumlah</th>
                <th>Cara Bayar</th>
                <th>Keterangan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($rows as $r): ?>
            <tr<?= $r->is_void ? ' class="void-row"' : '' ?>>
                <td><?= $no++ ?></td>
                <td><?= $r->expense_no ?></td>
                <td><?= tgl_finance($r->expense_date) ?></td>
                <td><?= $r->coa_name ?></td>
                <td class="text-right"><?= number_format($r->amount, 0, ',', '.') ?></td>
                <td><?= $r->payment_method === 'cash' ? 'Cash' : 'Transfer' ?></td>
                <td><?= htmlspecialchars($r->description) ?></td>
                <td><?= $r->is_void ? 'Dibatalkan' : 'Aktif' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
