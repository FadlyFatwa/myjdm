<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Print - <?=$row->barcode?> - <?=$row->nama_item?></title>

<style>
/* ===============================
   🔥 LOCK THERMAL PAGE
================================ */
@page {
    margin: 0;
}

html, body {
    margin: 0;
    padding: 0;
    background: #fff;
    font-family: Arial, sans-serif;
}

p, div {
    margin: 0;
    padding: 0;
}

/* ===============================
   CONTAINER
================================ */
.barcode-container {
    width: 8cm;
    min-width: 8cm;
    max-width: 8.5cm;
}

/* ===============================
   TABLE (IDENTIK MULTIPLE)
================================ */
table {
    border-collapse: collapse;
    margin-left: 0.5mm;
    width: 8.5cm;
    table-layout: fixed; /* 🔥 penting */
}

td {
    width: 4cm;           /* 2 kolom */
    height: 2.02cm;
    padding: 1mm 2mm;
    vertical-align: middle;
    box-sizing: border-box;
}

/* ===============================
   FLEX LABEL (ANTI PECAH)
================================ */
.barcode-item {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* ===============================
   ITEM NAME
================================ */
.barcode-item-name {
    line-height: 1.2;
}

.barcode-item-name .line-1,
.barcode-item-name .line-2 {
    display: block;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

.barcode-item-name .line-1 {
    font-size: 12px;
}

.barcode-item-name .line-2 {
    font-size: 8px;
}

/* ===============================
   BARCODE
================================ */
.barcode-text {
    font-size: 10px;
    margin-top: 1px;
    line-height: 1;
}

.barcode-img {
    height: 25px;
    margin-right: 1px;
    vertical-align: middle;
}

/* ===============================
   DETAILS
================================ */
.barcode-details {
    font-size: 10px;
    line-height: 1.1;
    max-height: 18px;
    word-wrap: break-word;
}
</style>
</head>

<body><!-- 🔥 AUTO PRINT -->

<div class="barcode-container">
<table>

<?php
$generator = new Picqer\Barcode\BarcodeGeneratorPNG();
$width = 1;
$height = 25;

$quantity = isset($quantity) ? (int)$quantity : 1;
$current_date = date('d/m/y', strtotime($date ?? 'now'));

/* ===== SMART CUT ===== */
function smart_cut($text, $max)
{
    if (mb_strlen($text) <= $max) {
        return [$text, ''];
    }

    $cut = mb_substr($text, 0, $max);
    $last_space = mb_strrpos($cut, ' ');

    if ($last_space === false) {
        return [$cut, mb_substr($text, $max)];
    }

    $line = mb_substr($text, 0, $last_space);
    $remaining = trim(mb_substr($text, $last_space));

    return [$line, $remaining];
}

/* ===============================
   🔥 ENGINE IDENTIK MULTIPLE
================================ */
$current_col = 1;
echo '<tr>';

for ($i = 0; $i < $quantity; $i++) {

    if ($current_col > 2) {
        echo '</tr><tr>';
        $current_col = 1;
    }

    echo '<td>';
    echo '<div class="barcode-item">';

    /* ===== SMART WRAP ===== */
    $nama_item = trim($row->nama_item);

    $wrap_text = str_replace('/', ' / ', $nama_item);
    $wrap_text = preg_replace('/\s+/', ' ', $wrap_text);

    list($line_1, $sisa) = smart_cut($wrap_text, 28);
    list($line_2, $sisa2) = smart_cut($sisa, 40);

    $line_1 = preg_replace('/\s*\/\s*/', '/', $line_1);
    $line_2 = preg_replace('/\s*\/\s*/', '/', $line_2);

    if (!empty($sisa2)) {
        $line_2 = rtrim($line_2) . '...';
    }

    echo '<div class="barcode-item-name">';
    echo '<span class="line-1">'.htmlspecialchars($line_1).'</span>';
    if ($line_2 !== '') {
        echo '<span class="line-2">'.htmlspecialchars($line_2).'</span>';
    }
    echo '</div>';

    /* ===== BARCODE ===== */
    echo '<div class="barcode-text">';
    echo '<img class="barcode-img" src="data:image/png;base64,' .
        base64_encode($generator->getBarcode(
            $row->barcode,
            $generator::TYPE_CODE_128,
            $width,
            $height
        )) . '">';
    echo htmlspecialchars($row->barcode) . ' / ' . htmlspecialchars($row->nama_unit);
    echo '</div>';

    /* ===== DETAILS ===== */
    $pk_raw = $row->pk;
    $pk_display = htmlspecialchars($pk_raw);

    echo '<div class="barcode-details">';

    if (strpos($pk_raw, '/') !== false) {
        echo '<div><strong>'.$pk_display.'</strong></div>';
        echo '<div>'.
            htmlspecialchars($row->nama_supplier).' | '.
            $current_date.
            '</div>';
    } else {
        echo '<div>'.
            $pk_display.' | '.
            htmlspecialchars($row->nama_supplier).' | '.
            $current_date.
            '</div>';
    }

    echo '</div>';
    echo '</div>';
    echo '</td>';

    $current_col++;
}

/* isi kolom kosong kalau ganjil */
while ($current_col <= 2) {
    echo '<td></td>';
    $current_col++;
}

echo '</tr>';
?>

</table>
</div>

<!-- <script>
/* optional auto close */
window.onafterprint = function () {
    window.close();
};
</script> -->

</body>
</html>