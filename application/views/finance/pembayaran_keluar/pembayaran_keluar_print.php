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
        <h2>Pembayaran Keluar</h2>
        <div>Periode <?= tgl_finance($from) ?> s/d <?= tgl_finance($to) ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Bukti</th>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Referensi</th>
                <th>Supplier</th>
                <th class="text-right">Jumlah</th>
                <th>Cara Bayar</th>
                <th>Dicatat Oleh</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($rows as $r): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $r->payment_no ?></td>
                <td><?= tgl_finance($r->payment_date) ?></td>
                <td><?= $r->jenis === 'kontra_bon' ? 'Kontra Bon' : 'Invoice' ?></td>
                <td><?= $r->reference_no ?></td>
                <td><?= $r->nama_supplier ?></td>
                <td class="text-right"><?= number_format($r->amount, 0, ',', '.') ?></td>
                <td><?= $r->payment_method === 'cash' ? 'Cash' : 'Bank' ?></td>
                <td><?= $r->paid_by_name ?></td>
                <td><?= $r->is_void ? 'Void' : 'Aktif' ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="bg-gray">
                <td colspan="6" class="text-right">Total (aktif)</td>
                <td class="text-right"><?= number_format($total, 0, ',', '.') ?></td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
