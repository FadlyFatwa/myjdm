<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
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
        <h2>Laporan Pembelian</h2>
        <div>Periode <?= tgl_finance($from) ?> s/d <?= tgl_finance($to) ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. PO</th>
                <th>Supplier</th>
                <th>No. Invoice Supplier</th>
                <th>Tgl Terima</th>
                <th class="text-right">Diskon Invoice</th>
                <th class="text-right">PPN</th>
                <th class="text-right">Total Barang</th>
                <th>Cara Bayar</th>
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
                <td><?= $r->po_number ?></td>
                <td><?= $r->nama_supplier ?></td>
                <td><?= $r->supplier_invoice_no ?: '-' ?></td>
                <td><?= tgl_finance($r->receive_date) ?></td>
                <td class="text-right"><?= number_format($r->diskon_invoice, 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($r->ppn_nominal, 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($r->total_amount, 0, ',', '.') ?></td>
                <td><?= $r->payment_type === 'cash' ? 'Cash' : ($r->payment_type === 'credit' ? 'Kredit' : '-') ?></td>
                <td><?= $r->ap_status ? ($status_label[$r->ap_status] ?? $r->ap_status) : '-' ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="bg-gray">
                <td colspan="7" class="text-right">Total</td>
                <td class="text-right"><?= number_format($totals['barang'], 0, ',', '.') ?></td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
