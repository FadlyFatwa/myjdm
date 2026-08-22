<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Pembayaran <?= $sale->invoice ?></title>
    <style>
        /* ── Base (applies to PDF via DOMPDF & browser) ── */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Nunito', Arial, sans-serif;
            font-size: 13px;
            color: #000;
        }

        .container {
            width: 90%;
            padding-bottom: 170px;
        }

        .left-column  { width: 60%; float: left; }
        .right-column { width: 40%; float: right; text-align: right; }
        .clearfix::after { content: ""; clear: both; display: table; }

        /* ── Header ── */
        .company-name    { font-size: 20px; font-weight: 700; letter-spacing: .3px; }
        .company-address { font-size: 11px; margin-top: 3px; color: #444; }

        .invoice-badge {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        .invoice-meta { font-size: 11.5px; margin-top: 3px; }

        hr.thick { border: none; border-top: 2px solid #000; margin: 10px 0 8px; }
        hr.thin  { border: none; border-top: 1px solid #ccc; margin: 6px 0; }

        .info-table { border: none; width: auto; margin-bottom: 10px; }
        .info-table td { border: none; padding: 2px 4px 2px 0; font-size: 12px; vertical-align: top; }
        .info-table .lbl { width: 72px; color: #555; white-space: nowrap; }

        .info-left  { width: 58%; float: left; }
        .info-right { width: 40%; float: right; text-align: right; font-size: 12px; padding-top: 2px; }

        /* ── Footer signature (pinned to bottom of page, static) ── */
        .sig-footer {
            position: fixed;
            bottom: 80px;
            left: 0;
            right: 0;
            width: 90%;
            max-width: 820px;
            margin: 0 auto;
        }
        .sig-table { width: 100%; border-collapse: collapse; }
        .sig-table td { width: 50%; text-align: center; padding: 0; border: none; font-size: 12px; vertical-align: top; }
        .sig-line { margin: 44px auto 4px; width: 120px; border-top: 1px solid #000; }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; }

        table th, table td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 12px;
            vertical-align: middle;
        }

        table thead tr th {
            font-weight: 700;
            text-align: center;
            border-bottom: 2px solid #000;
        }

        table tfoot td { font-size: 12px; }
        table tfoot tr:first-child td {
            font-weight: 700;
            border-top: 2px solid #000;
        }

        @page { margin-top: 1.2cm; margin-bottom: 0.8cm; margin-left: 0.5cm; margin-right: 0.5cm; }

        /* ── Screen-only decorative styles ── */
        @media screen {
            body { background: #fff; }
            .container {
                max-width: 820px;
                margin: 0 auto;
                padding: 24px 28px;
            }
        }
    </style>
</head>
<body>
<div class="container clearfix">

    <!-- Header -->
    <div class="clearfix">
        <div class="left-column">
            <div class="company-name">JADI MOTOR</div>
            <div class="company-address">Jl.Banceuy Gg.Cikapundung No.18</div>
        </div>
        <div class="right-column">
            <?php
                $date  = strtotime($sale->date);
                $tglpj = date('d M Y', $date);
            ?>
            <div class="invoice-badge">Invoice Penjualan</div>
            <div class="invoice-meta">No. Nota &nbsp;: <strong><?= $sale->invoice ?></strong></div>
            <div class="invoice-meta">Tanggal &nbsp;&nbsp;: <?= $tglpj ?></div>
        </div>
    </div>

    <hr class="thick">

    <div class="clearfix">
        <div class="info-left">
            <table class="info-table">
                <tr>
                    <td class="lbl">Tuan/Toko</td>
                    <td>: <strong><?= htmlspecialchars($sale->nama_customer) ?></strong></td>
                </tr>
                <tr>
                    <td class="lbl">Kasir</td>
                    <td>: <?= htmlspecialchars($sale->nama) ?></td>
                </tr>
            </table>
        </div>
        <?php if ($sale->note): ?>
        <div class="info-right">
            Keterangan : <?= htmlspecialchars($sale->note) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Items table (max 12 per page) -->
    <?php
    $chunks    = array_chunk($sale_detail, 12);
    $last_idx  = count($chunks) - 1;
    $no        = 1;
    foreach ($chunks as $ci => $chunk):
    ?>
    <?php if ($ci > 0): ?>
    <div style="page-break-before:always; margin:0; padding:0; line-height:0;">&nbsp;</div>
    <?php endif; ?>
    <table cellspacing="0">
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:55%">Nama Barang</th>
                <th style="width:15%">Harga Barang</th>
                <th style="width:10%">Jumlah</th>
                <th style="width:15%">Sub Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($chunk as $value):
                $nama = $value->nama_barang_jual;
                if (mb_strlen($nama) > 55) {
                    $sub        = mb_substr($nama, 0, 55);
                    $pos_space  = mb_strrpos($sub, ' ');
                    $pos_hyphen = mb_strrpos($sub, '-');
                    if ($pos_space !== false && $pos_hyphen !== false) {
                        $last_break = max($pos_space, $pos_hyphen);
                    } elseif ($pos_space !== false) {
                        $last_break = $pos_space;
                    } elseif ($pos_hyphen !== false) {
                        $last_break = $pos_hyphen;
                    } else {
                        $last_break = false;
                    }
                    $nama = ($last_break > 0)
                        ? mb_substr($nama, 0, $last_break) . '...'
                        : mb_substr($nama, 0, 55) . '...';
                }
            ?>
            <tr>
                <td style="text-align:center"><?= $no++ ?></td>
                <td style="text-align:left">
                    <?php if (!empty($value->is_jasa)): ?>
                        <span style="font-size:9px;background:#17a2b8;color:#fff;padding:1px 4px;border-radius:3px;">JASA</span>
                    <?php endif; ?>
                    <?php if (!empty($value->barcode)): ?>
                        <span style="color:#555;">[<?= htmlspecialchars($value->barcode) ?>]</span>
                    <?php endif; ?>
                    <?= htmlspecialchars($nama) ?>
                </td>
                <td style="text-align:right"><?= number_format(($value->price_sale - $value->discount_item), 0, ',', '.') ?></td>
                <td style="text-align:center"><?= $value->qty ?> <?= $value->nama_unit ?></td>
                <td style="text-align:right"><?= number_format($value->total, 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <?php if ($ci === $last_idx): ?>
        <tfoot>
            <tr>
                <td colspan="4" align="right"><strong>Grand Total :</strong></td>
                <td align="right">Rp <?= number_format($sale->total_price, 0, ',', '.') ?></td>
            </tr>
            <?php if ($sale->payment_method == 'cash'): ?>
            <tr>
                <td colspan="4" align="right">Cash :</td>
                <td align="right">Rp <?= number_format($sale->cash, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td colspan="4" align="right">Kembalian :</td>
                <td align="right">Rp <?= number_format($sale->cash - $sale->total_price, 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
        </tfoot>
        <?php endif; ?>
    </table>
    <?php endforeach; ?>

    <!-- Footer signature -->
    <div class="sig-footer">
        <table class="sig-table">
            <tr>
                <td>
                    <div>Hormat kami,</div>
                    <div class="sig-line"></div>
                </td>
                <td>
                    <div>Tanda Terima,</div>
                    <div class="sig-line"></div>
                </td>
            </tr>
        </table>
    </div>

</div>
</body>
</html>
