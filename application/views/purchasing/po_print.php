<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase Order <?= htmlspecialchars($po->po_number) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; background: #e8e8e8; padding: 20px 0; }
        .page { max-width: 560px; margin: 0 auto; background: #fff; padding: 24px 28px; box-shadow: 0 2px 8px rgba(0,0,0,.15); }
        .no-print { max-width: 560px; margin: 0 auto 12px; display: flex; gap: 8px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 14px; }
        .header h2 { font-size: 17px; font-weight: bold; letter-spacing: .5px; }
        .header h3 { font-size: 12px; color: #555; margin-top: 2px; }
        .po-info { display: flex; justify-content: space-between; margin-bottom: 14px; gap: 16px; }
        .po-info-left, .po-info-right { font-size: 12px; }
        .po-info-left tr td, .po-info-right tr td { padding: 2px 4px 2px 0; vertical-align: top; }
        .po-info-left .lbl, .po-info-right .lbl { font-weight: bold; white-space: nowrap; padding-right: 6px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.items th, table.items td { border: 1px solid #bbb; padding: 5px 7px; }
        table.items thead tr { background: #f0f0f0; font-size: 11px; text-transform: uppercase; letter-spacing: .3px; }
        table.items .text-center { text-align: center; }
        .sign-section { margin-top: 30px; }
        .sign-section table { width: 100%; }
        .sign-section td { text-align: center; width: 33%; vertical-align: bottom; font-size: 11px; }
        .sign-box { height: 50px; border-bottom: 1px solid #555; margin: 0 16px 4px; }
        .footer-note { text-align: center; color: #aaa; margin-top: 14px; font-size: 10px; border-top: 1px solid #eee; padding-top: 6px; }
        @media print {
            body { background: #fff; padding: 0; }
            .page { box-shadow: none; max-width: 100%; padding: 12px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" style="padding:6px 14px;background:#337ab7;color:#fff;border:none;border-radius:3px;cursor:pointer;font-size:13px">
        Print
    </button>
    <button onclick="window.close()" style="padding:6px 14px;background:#777;color:#fff;border:none;border-radius:3px;cursor:pointer;font-size:13px">
        Tutup
    </button>
</div>

<div class="page">

    <div class="header">
        <h2>PURCHASE ORDER</h2>
        <h3><?= htmlspecialchars($po->po_number) ?></h3>
    </div>

    <div class="po-info">
        <table class="po-info-left">
            <tr><td class="lbl">Supplier</td><td>: <?= htmlspecialchars($po->nama_supplier) ?></td></tr>
            <tr><td class="lbl">Telp</td><td>: <?= htmlspecialchars($po->phone ?? '-') ?></td></tr>
            <tr><td class="lbl">Alamat</td><td>: <?= htmlspecialchars($po->alamat ?? '-') ?></td></tr>
        </table>
        <table class="po-info-right">
            <tr><td class="lbl">Tanggal PO</td><td>: <?= indo_date($po->po_date) ?></td></tr>
            <tr><td class="lbl">Expected Tiba</td><td>: <?= $po->expected_date ? indo_date($po->expected_date) : '-' ?></td></tr>
            <tr><td class="lbl">Dibuat oleh</td><td>: <?= htmlspecialchars($po->created_by_name) ?></td></tr>
        </table>
    </div>

    <?php if ($po->notes): ?>
    <p style="margin-bottom:12px;padding:5px 9px;background:#fffbe6;border-left:3px solid #f0ad4e;font-size:11px">
        Catatan: <?= htmlspecialchars($po->notes) ?>
    </p>
    <?php endif; ?>

    <table class="items">
        <thead>
            <tr>
                <th width="28" class="text-center">#</th>
                <th>Nama Barang</th>
                <th width="50" class="text-center">Qty</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $i => $d): ?>
            <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($d->display_name) ?><?= $d->notes ? ' <em style="color:#888;font-size:11px">(' . htmlspecialchars($d->notes) . ')</em>' : '' ?></td>
                <td class="text-center"><?= $d->qty_ordered ?> <?= htmlspecialchars($d->nama_unit ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="sign-section">
        <table>
            <tr>
                <td>
                    <div class="sign-box"></div>
                    <div>Disiapkan oleh</div>
                    <div style="color:#888"><?= htmlspecialchars($po->created_by_name) ?></div>
                </td>
                <td>
                    <div class="sign-box"></div>
                    <div>Disetujui oleh</div>
                </td>
                <td>
                    <div class="sign-box"></div>
                    <div>Diterima (Supplier)</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-note">Cetak: <?= date('d/m/Y H:i') ?></div>

</div>
</body>
</html>
