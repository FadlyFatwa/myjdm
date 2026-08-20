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
    </style>
</head>
<body>
    <div class="header">
        <h2>Kartu Hutang</h2>
        <div><?= $supplier->nama_supplier ?> | Periode <?= tgl_finance($from) ?> s/d <?= tgl_finance($to) ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Ref</th>
                <th>Keterangan</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Kredit</th>
                <th class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
            <tr><td colspan="6" style="text-align:center;">Tidak ada transaksi</td></tr>
            <?php else: foreach ($rows as $r): ?>
            <tr>
                <td><?= tgl_finance($r->trx_date) ?></td>
                <td><?= $r->ref_no ?></td>
                <td><?= $r->description ?></td>
                <td class="text-right"><?= $r->debit > 0 ? number_format($r->debit, 0, ',', '.') : '' ?></td>
                <td class="text-right"><?= $r->kredit > 0 ? number_format($r->kredit, 0, ',', '.') : '' ?></td>
                <td class="text-right"><?= number_format($r->balance, 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</body>
</html>
