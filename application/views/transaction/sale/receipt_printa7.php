<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Struk Penjualan</title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }

        body {
            font-family: monospace;
            font-size: 10.5px;
            margin: 0;
            padding: 0;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .line {
            border-top: 1px dashed black;
            margin: 2px 0;
        }

        .footer {
            text-align: center;
            margin-top: 3px;
        }

        .item-line {
            line-height: 1.5;
        }
    </style>
</head>
<body>

<!-- HEADER TOKO -->
<div class="center bold">JADI MOTOR</div>
<div class="center">Jl. Banceuy Gg. Cikapundung No.18</div>
<div class="line"></div>

<?php
$tgl = date('d/m/Y', strtotime($sale->date));
$jam = date('H:i:s', strtotime($sale->create));
$invoice = $sale->invoice;
$customer = $sale->nama_customer ?? '-';
$kasir = $sale->nama;
$metode = strtoupper($sale->payment_method);
?>

<!-- INFO TRANSAKSI -->
<pre style="margin: 0; font-family: monospace;">
Tanggal  : <?= $tgl ?> <?= $jam ?>

Invoice  : <?= $invoice ?>

Pembeli  : <?= $customer . "  (" . $metode . ")"  ?> 

Kasir    : <?= $kasir ?>

</pre>
<div class="line"></div>


<!-- Header Kolom -->
No  Product
     Barcode Qty x Harga      Total
<div class="line"></div>

<pre class="item-line">
<?php
$no = 1;
foreach ($sale_detail as $item) {
    $nama = $item->nama_barang_jual;
    $qty = $item->qty;
    $unit = $item->nama_unit;
    $barcode = $item->barcode;
    $harga = number_format($item->price_sale - $item->discount_item, 0, ',', '.');
    $subtotal = number_format($item->total, 0, ',', '.');

    // Potong nama agar tidak lebih dari 36 karakter
    $nama_trim = substr($nama, 0, 36);

    // Tampilkan nama barang
    echo str_pad("{$no}.", 3) . "$nama_trim\n";

    // Kolom tetap: barcode (10) | qty+unit (5) | harga (10) | subtotal (10)
    $barcode_col = str_pad($barcode, 5);       // max 10 chars
    $qtyunit_col = str_pad("{$qty}{$unit}", 5); // max 5 chars
    $harga_col   = str_pad($harga, 10, ' ', STR_PAD_LEFT); // rata kanan
    $subtotal_col = str_pad($subtotal, 10, ' ', STR_PAD_LEFT); // rata kanan

    echo "    {$barcode_col} {$qtyunit_col} x {$harga_col} = {$subtotal_col}\n";
    $no++;
}
?>

</pre>

<div class="line"></div>

<?php
$total = number_format($sale->total_price, 0, ',', '.');
$cash = number_format($sale->cash ?? 0, 0, ',', '.');
$change = number_format(($sale->cash ?? 0) - $sale->total_price, 0, ',', '.');
?>

Subtotal : <?= str_pad($total, 20, ' ', STR_PAD_LEFT) ?><br>
<?php if ($sale->payment_method == 'cash'): ?>
Bayar    : <?= str_pad($cash, 20, ' ', STR_PAD_LEFT) ?><br>
Kembalian  : <?= str_pad($change, 20, ' ', STR_PAD_LEFT) ?><br>
<?php endif; ?>

<div class="line"></div>

<div class="footer">
:: Terima Kasih atas kunjungan Anda ::<br>
:: Barang yang sudah dibeli tidak dapat ditukar ::
</div>

</body>
</html>
