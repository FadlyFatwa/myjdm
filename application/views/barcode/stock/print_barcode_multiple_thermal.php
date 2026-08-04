<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Print Barcode Multiple</title>

<style>
@page {
    margin: 0;
}

p, div {
    margin: 0;
    padding: 0;
}

body {
    margin: 0;
    padding: 0;
}

.barcode-container {
    font-family: Arial, sans-serif;
    width: 100%;
}

table {
    border-collapse: collapse;
    margin-left: 0.5mm;   /* offset kiri */
    width: 8.5cm; 
}

td {
    width: 4.5cm;
    height: 2.02cm;
    padding: 1mm 2mm 1mm 2mm;
    vertical-align: middle;   /* 🔥 ini kuncinya */
    box-sizing: border-box;
}

.barcode-item-name {
    line-height: 1; /* Adjust line height for better readability */
            /* word-wrap: break-word; /* Allow long words to break to the next line - kita akan mengelola ini di PHP */
    max-width: 100%; /* Ensure the text doesn't overflow its container */
}
.barcode-item-name .line-1,
.barcode-item-name .line-2 {
    display: block;
    white-space: nowrap;      
    text-overflow: ellipsis;  
    max-width: 150%;
}
.barcode-item-name .line-1 {
    margin-top: 0px;
    font-size: 12px; /* Font size for the first line */
    margin-bottom: 0px; /* Small margin between line 1 and line 2 */
}

.barcode-item-name .line-2 {
    margin-top: 0px;
    font-size: 8px; /* Font size for the first line */
    margin-bottom: 0px; /* Small margin between line 1 and line 2 */
}

.barcode-text {
    font-size: 10px;
    margin-top: 1px;
    line-height: 1;
}
.barcode-img {
    height: 25px;
    margin-right: 4px;
    vertical-align: middle;
}

.barcode-details {
    font-size: 10px;
    line-height: 1.1;
    max-height: 18px;        /* batasi tinggi max 2 baris */
    word-wrap: break-word;   /* biar bisa turun */
}

.highlight-pk {
    font-weight: bold;
    color: red;
}

.highlight-pk2 {
    font-weight: bold;
    color: blue;
}
</style>
</head>

<body>
<div class="barcode-container">
<table>

<?php
$generator = new Picqer\Barcode\BarcodeGeneratorPNG();
$width = 1;
$height = 25;

$current_col = 1;
$start_col = isset($start_col) ? (int)$start_col : 1;

echo '<tr>';

// ===== OFFSET KOLOM AWAL =====
if ($start_col > 1) {
    for ($i = 1; $i < $start_col; $i++) {
        echo '<td></td>';
        $current_col++;
    }
}
        // helper fungsi potong tanpa putus kata
        function smart_cut($text, $max)
        {
            if (mb_strlen($text) <= $max) {
                return [$text, ''];
            }

            $cut = mb_substr($text, 0, $max);
            $last_space = mb_strrpos($cut, ' ');

            // kalau tidak ada spasi (kata sangat panjang)
            if ($last_space === false) {
                return [$cut, mb_substr($text, $max)];
            }

            $line = mb_substr($text, 0, $last_space);
            $remaining = trim(mb_substr($text, $last_space));

            return [$line, $remaining];
        }

foreach ($items as $item):
    for ($i = 0; $i < $item->quantity; $i++):

        // pindah baris setiap 2 kolom
        if ($current_col > 2) {
            echo '</tr><tr>';
            $current_col = 1;
        }

        echo '<td>';
        echo '<div class="barcode-item">';

        // ===== SMART WORD WRAP (anggap / sebagai spasi) =====
        $nama_item = trim($item->nama_item);

        // 🔧 MUDAH DIATUR
        $max_line1 = 28;
        $max_line2 = 40;

        // versi untuk proses wrapping (anggap / sebagai spasi)
        $wrap_text = str_replace('/', ' / ', $nama_item);
        $wrap_text = preg_replace('/\s+/', ' ', $wrap_text);

        

        // ===== LINE 1 =====
        list($line_1, $sisa) = smart_cut($wrap_text, $max_line1);

        // ===== LINE 2 =====
        list($line_2, $sisa2) = smart_cut($sisa, $max_line2);

        // ===== bersihkan spasi di sekitar slash =====
        $line_1 = preg_replace('/\s*\/\s*/', '/', $line_1);
        $line_2 = preg_replace('/\s*\/\s*/', '/', $line_2);

        // ===== TAMBAH ... JIKA MASIH ADA SISA =====
        if (!empty($sisa2)) {
            $line_2 = rtrim($line_2) . '...';
        }

        echo '<div class="barcode-item-name">';
        echo '<span class="line-1">' . htmlspecialchars($line_1) . '</span>';
        if ($line_2 !== '') {
            echo '<span class="line-2">' . htmlspecialchars($line_2) . '</span>';
        }   
        echo '</div>';

        // ===== BARCODE =====
        echo '<div class="barcode-text">';
        echo '<img class="barcode-img" src="data:image/png;base64,' .
            base64_encode($generator->getBarcode(
                $item->barcode,
                $generator::TYPE_CODE_128,
                $width,
                $height
            )) . '">';
        echo htmlspecialchars($item->barcode) . ' / ' . htmlspecialchars($item->nama_unit);
        echo '</div>';

        // ===== DETAIL =====
        $pk_raw = $item-> pk;
        $pk_display = htmlspecialchars($pk_raw);

        echo '<div class="barcode-details">';

        if (strpos($pk_raw, '/') !== false) {
            // 🔥 kalau ada slash
            echo '<div><strong>' . $pk_display . '</strong></div>';
            echo '<div>' .
                htmlspecialchars($item->nama_supplier) . ' | ' .
                date('d/m/y', strtotime($item->date)) .
                '</div>';
        } else {
            // normal satu baris
            echo '<div>' . $pk_display . ' | ' .
                htmlspecialchars($item->nama_supplier) . ' | ' .
                date('d/m/y', strtotime($item->date));
        }

        echo '</div>';

        echo '</div>';
        echo '</td>';

        $current_col++;

    endfor;
endforeach;

// isi sisa kolom kalau ganjil
while ($current_col <= 2) {
    echo '<td></td>';
    $current_col++;
}
?>

</table>
</div>
</body>
</html>