<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Multiple Barcode - 101</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }
        @page {
            margin-top: 0.8cm;
            margin-right: 0cm;
        }
        .barcode-container {
            font-family: Arial, sans-serif;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: flex-start;
            justify-content: flex-end;
        }
        table {
            border-collapse: collapse;
            margin-left: auto;
            margin-right: 0;
            margin-top: 0;
            margin-bottom: 0;
        }
        td {
            width: 10cm;
            height: 5cm;
            padding: 2px;
            text-align: center;
            vertical-align: top;
        }
        td.spacer {
            width: 0.4cm;
            border: none;
        }
        .barcode-item {
            text-align: center;
            margin-bottom: 5px;
            margin-bottom : 0;
        }

        .barcode-item-name {
            line-height: 0.8;
            word-wrap: break-word;
            max-width: 100%;
            text-align: center;
            margin-top: 5px;
            margin-bottom : 0;
            font-size: 24px;
            font-weight: bold;
            margin-left: 10px;
            margin-bottom : 0;
            text-align: center;
        }
        .barcode-item-mobil {
            font-size: 24px;
            margin-left: 10px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 0px;
            margin-top: 0px;
        }

        .barcode-item-no_part {
            font-size: 22px;
            font-weight: bold;
            margin-left: 10px;
            text-align: center;
            margin-bottom: 0px;
            margin-top: 0px;
        }

        .barcode-text {
            margin-top: 3px;
            font-size: 12px;
            margin-bottom : 0px;
        }

        .barcode-details {
            font-size: 16px; /* Adjust this value for the barcode details size */
            margin-bottom: 0px; /* Remove bottom margin */
        }

        .barcode-img {
            width: 3cm;
            height: 0.7cm;
            margin-top: 10px; /* Margin antara no_part dan barcode 2px */
        }
    </style>
</head>
<body>
<?php
// Initialize barcode generator
$generator = new Picqer\Barcode\BarcodeGeneratorPNG();
$width = 2;
$height = 50;

// Start column and row (1-based index from user input)
$start_col = isset($start_col) ? max(0, (int)$start_col - 1) : 0; // Kolom awal (0-based)
$start_row = isset($start_row) ? max(0, (int)$start_row - 1) : 0; // Baris awal (0-based)

// Flatten all items into a single array of labels
$labels = [];
foreach ($items as $item) {
    $quantity = (int)$item->quantity;
    $current_date = date('d/m/y', strtotime($item->date));
    $nama_item = isset($item->nama_item) ? htmlspecialchars(strtoupper(strval($item->nama_item))) : '';
    $nama_mobil = isset($item->nama_mobil) ? htmlspecialchars(strtoupper(strval($item->nama_mobil))) : '';
    $no_part = isset($item->no_part) ? htmlspecialchars(strtoupper(strval($item->no_part))) : '';

    for ($q = 0; $q < $quantity; $q++) {
        $labels[] = [
            'barcode' => $item->barcode,
            'nama_item' => $nama_item,
            'nama_mobil' => $nama_mobil,
            'no_part' => $no_part,
            'pk' => $item->pk,
            'nama_unit' => $item->nama_unit,
            'nama_supplier' => $item->nama_supplier,
            'date' => $current_date,
        ];
    }
}

// **Hitung offset untuk halaman pertama** (karena ada sel kosong)
$first_page_offset = ($start_row * 2) + $start_col;

// **Pisahkan label menjadi halaman-halaman**
$first_page_labels = array_slice($labels, 0, 6 - $first_page_offset); // Halaman pertama (dengan offset)
$remaining_labels = array_slice($labels, 6 - $first_page_offset);     // Sisa label setelah halaman pertama
$pages = array_merge([$first_page_labels], array_chunk($remaining_labels, 6)); // Gabungkan jadi halaman

// Loop untuk mencetak setiap halaman
foreach ($pages as $page_index => $page_labels) {
    echo '<div class="barcode-container">';
    echo '<table>';
    $counter = 0;

    for ($row = 0; $row < 3; $row++) {
        echo '<tr>';

        for ($col = 0; $col < 2; $col++) {
            // **Tambahkan sel kosong di halaman pertama sesuai start_row & start_col**
            if ($page_index == 0 && ($row < $start_row || ($row == $start_row && $col < $start_col))) {
                echo '<td class="spacer"></td>';
            } 
            // **Cek apakah masih ada label yang perlu dicetak**
            elseif (isset($page_labels[$counter])) {
                $label = $page_labels[$counter];
                echo '<td>';
                echo '<div class="barcode-item">';
                    echo '<div class="barcode-item-name">' . $label['nama_item'] . '</div>';
                    echo '<div class="barcode-item-mobil">' . $label['nama_mobil'] . '</div>';
                    echo '<div class="barcode-item-no_part">' . $label['no_part'] . '</div>';
                    
                    // Barcode image and text
                    echo '<div class="barcode-text">';
                    echo '<img class="barcode-img" src="data:image/png;base64,' . base64_encode($generator->getBarcode($label['barcode'], $generator::TYPE_CODE_128, $width, $height)) . '"><br>';
                    echo $label['barcode'] . " / " . $label['nama_unit'];
                    echo '</div>';
                    
                    // Additional details
                    echo '<div class="barcode-details">' . $label['pk'] . ' | ' . $label['nama_supplier'] . ' | ' . $label['date'] . '</div>';
                echo '</div>';
                echo '</td>';
                $counter++;
            } 
            // **Jika label habis, tambahkan sel kosong**
            else {
                echo '<td class="spacer"></td>';
            }
        }

        echo '</tr>';
    }

    echo '</table>';
    echo '</div>';

    // **Tambah page break kecuali di halaman terakhir**
    if ($page_index < count($pages) - 1) {
        echo '<div style="page-break-after: always;"></div>';
    }
}
?>


</body>
</html>