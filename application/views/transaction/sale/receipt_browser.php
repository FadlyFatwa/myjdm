<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Struk Penjualan - <?= $sale->invoice ?> </title>

<style>
/* ===== RESET ===== */
* {
    box-sizing: border-box;
}

/* ===== PRINT SETTING ===== */
@media print {
    @page {
        size: 76mm auto;
        margin: 3mm;
    }

    body {
        width: 72mm;
        margin: 0 auto;
        padding: 0 2mm;
        font-family: 'Nunito', sans-serif;
        font-size: 12px;
    }
}

/* SCREEN */
body {
    width: 72mm;   /* 🔥 samakan */
    margin: auto 3mm;
    font-family: 'Nunito', sans-serif;
    font-size: 12px;
}

.item-detail td {
    font-size: 12px;
    line-height: 1.2;
}



/* ===== UTIL ===== */
.center { text-align: center; }
.bold { font-weight: bold; }

.line {
    border-top: 1px dashed #000;
    margin: 6px 0;
}

/* ===== TABLE ANTI GESER ===== */
.table {
    width: 100%;
    border-collapse: collapse;
}

.table td {
    vertical-align: top;
    word-break: break-word;
}

.receipt {
    overflow: hidden;
}



.col-no   { width: 7%; }
.col-name { width: 49%; }
.col-qty  { width: 11%; text-align:right; }
.col-price{ width: 16%; text-align:right; }
.col-sub  { width: 17%; text-align:right; }


/* ===== FOOTER ===== */
.footer {
    text-align: center;
    margin-top: 6px;
}
</style>
</head>

<body>

<div class="receipt">

<!-- HEADER -->
<div class="center bold" style="font-size:18px">JADI MOTOR</div>
<div class="center">Jl. Banceuy Gg. Cikapundung No.18</div>
<div class="center">Telp 081392179943</div>
<div class="line"></div>

<?php
$tgl = date('d/m/Y', strtotime($sale->date));
$jam = date('H:i:s', strtotime($sale->create));
?>

<div>
Tanggal : <?= $tgl ?> <?= $jam ?><br>
Invoice : <?= $sale->invoice ?><br>
Pembeli : <?= $sale->customer_name ?? '-' ?> (<?= strtoupper($sale->payment_method) ?>)<br>
: <?= !empty($sale->note) ? htmlspecialchars($sale->note) : '-' ?> <br>
Kasir   : <?= $sale->nama ?>
</div>

<div class="line"></div>

<div style="font-size:14px">
<b>No  Product<br>
&nbsp;&nbsp;&nbsp;&nbsp;Barcode &nbsp;&nbsp; Qty x Harga &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Total
</b></div>

<div class="line"></div>

<!-- ITEMS -->
<table class="table">
<?php $no=1; foreach ($sale_detail as $item): ?>

<tr>
    <td colspan="5">
        <?= $no ?>. <?php
            $nama = mb_strimwidth($item->nama_barang_jual, 0, 40, '...');
            if (!empty($item->is_jasa)) echo '[Jasa] ';
            echo htmlspecialchars($nama);
            ?>

    </td>
</tr>
<tr class="item-detail">
    <td style="width:6%"></td>

    <!-- barcode -->
    <td style="width:15%">
        <small><?= $item->barcode ?></small>
    </td>

    <!-- qty -->
    <td style="width:15%; text-align:right">
        <?= $item->qty ?>  <?=$item->nama_unit?>
    </td>

    <!-- harga -->
    <td style="width:30%; text-align:right">
       x <?= number_format($item->price_sale - $item->discount_item,0,',','.') ?>
    </td>

    <!-- subtotal -->
    <td style="width:40%; text-align:right;">
      <?= number_format($item->total,0,',','.') ?>
    </td>
</tr>


<?php $no++; endforeach; ?>
</table>


<div class="line"></div>

<?php
$total = number_format($sale->total_price,0,',','.');
$cash = number_format($sale->cash ?? 0,0,',','.');
$change = number_format(($sale->cash ?? 0) - $sale->total_price,0,',','.');
?>

<div>
Subtotal : <span style="float:right; padding-right:1.5mm;"><?= $total ?></span><br>

<?php if ($sale->payment_method == 'cash'): ?>
Bayar    : <span style="float:right; padding-right:1.5mm;"><?= $cash ?></span><br>
Kembali  : <span style="float:right; padding-right:1.5mm;"><?= $change ?></span><br>
<?php endif; ?>
</div>


<div class="line"></div>


<div class="footer">
:: Terima Kasih atas kunjungan Anda ::<br>
</div>

</div>
</body>
</html>
