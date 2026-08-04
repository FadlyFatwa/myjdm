<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Print Barcode Multiple</title>
    <style>
        body {
            margin-left: 20px;
            margin-top: 1px;
            padding: 1.5px;
        }

        @page {
            size: 21cm 22cm; /* Ukuran kertas */
            margin: 8px; /* Remove margin from the @page rule */
        }

        .barcode-container {
            font-family: Arial, sans-serif;
            width: 50%; /* Ensure the container takes full width */
        }

        .barcode-item {
            margin-bottom: 3px; /* Adjust this value for spacing between barcodes */
            text-align: left;
        }

        .barcode-item-name {
            line-height: 0.8; /* Adjust line height for better readability */
            /* word-wrap: break-word; /* Allow long words to break to the next line - kita akan mengelola ini di PHP */
            max-width: 100%; /* Ensure the text doesn't overflow its container */
        }

        .barcode-item-name .line-1 {
            margin-top: 0px;
            font-size: 12px; /* Font size for the first line */
            margin-bottom: 0px; /* Small margin between line 1 and line 2 */
        }

        .barcode-item-name .line-2 {
            margin-top: 0px;
            font-size: 8px; /* Font size for the second line */
            margin-bottom: 0px; /* Small margin between line 2 and barcode */
        }

        .barcode-text {
            display: flex;
            justify-content: left;
            align-items: left;
            font-size: 12px; /* Adjust this value for the desired text size */
            margin-top: 2px; /* Small margin between line 2 and barcode */
            margin-bottom: 2px; /* Reduce bottom margin */
        }

        .barcode-img {
            margin-top: 0px;
            margin-right: 5px;
            vertical-align: bottom;
            margin-bottom: 0px; /* Remove bottom margin */
        }

        .barcode-details {
            font-size: 10px; /* Adjust this value for the barcode details size */
            margin-bottom: 0px; /* Remove bottom margin */
        }

        .highlight-pk {
            font-weight: bold;
            color: red;
            /* Atau gunakan background: yellow; untuk latar belakang */
        }

        .highlight-pk2 {
            font-weight: bold;
            color: blue;
            /* Atau gunakan background: yellow; untuk latar belakang */
        }

        table {
            width: 100%; /* Adjust the table width */
            margin: 0px; /* Remove any automatic margins */
            border-collapse: collapse;
        }

        td {
            width: 5.05cm; /* Set the width to 5 cm */
            height: 2.06cm; /* Set the height to 2 cm */
            padding: 1px; /* Reduce padding for more compact layout */
            vertical-align: top;
            box-sizing: border-box; /* Ensure padding is included in height */
        }
    </style>
</head>
<body>
    <div class="barcode-container">
        <table>
            <?php
            $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
            $width = 1;  // Width of a single bar
            $height = 30;  // Height of the barcode

            // Inisialisasi counter untuk menentukan posisi cetak
            // Kita akan mengatur current_col dan current_row setelah mencetak sel kosong
            $current_col = 1;
            $current_row = 1;

            // Start the first row
            echo '<tr>';

            // Loop untuk mencetak sel kosong sampai mencapai start_col dan start_row
            for ($r = 1; $r < $start_row; $r++) { // Iterate through rows before start_row
                for ($c = 1; $c <= 3; $c++) { // Fill all 3 columns in these rows
                    echo '<td></td>';
                }
                echo '</tr><tr>'; // Start a new row after filling the current one
                $current_row++;
            }

            // Now, we are at the $start_row. Fill empty cells up to $start_col.
            for ($c = 1; $c < $start_col; $c++) {
                echo '<td></td>';
                $current_col++;
            }

            // Loop untuk mencetak barcode
            foreach ($items as $item):
                for ($i = 0; $i < $item->quantity; $i++):
                    // Jika sudah mencapai akhir baris, mulai baris baru
                    if ($current_col > 3) {
                        echo '</tr><tr>';
                        $current_col = 1;
                        $current_row++;
                    }

                    // Print barcode
                    echo '<td>';
                    echo '<div class="barcode-item">';
                    // Split the item name into two lines based on length, intelligently
                    $nama_item = $item->nama_item; // Keep the original name for display
                    $max_characters = isset($item->max_characters) ? (int)$item->max_characters : 35; // Default to 30 if not set

                    $line_1 = '';
                    $line_2 = '';

                    if (mb_strlen($nama_item) > $max_characters) { // Use mb_strlen for multi-byte safe length
                        $break_point = false;
                        $temp_line_1 = mb_substr($nama_item, 0, $max_characters); // Get the substring up to max_characters

                        // Find the last space or '/' in temp_line_1
                        $last_space = mb_strrpos($temp_line_1, ' ');
                        $last_slash = mb_strrpos($temp_line_1, '/');

                        // Choose the later of the two as the break point
                        if ($last_space !== false && $last_slash !== false) {
                            $break_point = max($last_space, $last_slash);
                        } elseif ($last_space !== false) {
                            $break_point = $last_space;
                        } elseif ($last_slash !== false) {
                            $break_point = $last_slash;
                        }

                        if ($break_point !== false) { // If a space or '/' is found
                            $line_1 = mb_substr($nama_item, 0, $break_point);
                            $line_2 = mb_substr($nama_item, $break_point + 1); // +1 to skip the separator
                        } else { // No space or '/' found within the max_characters limit, fall back to simple substring
                            $line_1 = mb_substr($nama_item, 0, $max_characters);
                            $line_2 = mb_substr($nama_item, $max_characters);
                        }
                    } else {
                        $line_1 = $nama_item;
                        $line_2 = '';
                    }

                    echo '<div class="barcode-item-name">';
                    echo '<span class="line-1">' . htmlspecialchars($line_1) . '</span><br>';
                    if ($line_2 !== '') {
                        echo '<span class="line-2">' . htmlspecialchars($line_2) . '</span>';
                    }
                    echo '</div>';
                    
                    echo '<div class="barcode-text">';
                    echo '<img class="barcode-img" src="data:image/png;base64,' . base64_encode($generator->getBarcode($item->barcode, $generator::TYPE_CODE_128, $width, $height)) . '">';
                    echo htmlspecialchars($item->barcode);
                    echo '   /  ';
                    echo htmlspecialchars($item->nama_unit);
                    echo '</div>';
                    
                    // Highlight PK logic (mengikuti logika yang telah disepakati)
                    $pk_display = htmlspecialchars($item->pk);
                    $is_pk_highlighted = false; // Default: tidak di-highlight
                    $is_pk_highlighted2 = false; // Default: tidak di-highlight

                    // Kondisi pertama: Jika nama_item mengandung 'G' (tidak peduli huruf besar/kecil)
                    if (stripos($item->nama_item," 'G' ") !== false) {
                        $is_pk_highlighted = true;
                    }elseif (stripos($item->nama_item," 'L' ") !== false) {
                        $is_pk_highlighted2 = true;
                    }

                    if ($is_pk_highlighted) {
                        echo '<div class="barcode-details"><span class="highlight-pk">' . $pk_display . '</span> | ' . htmlspecialchars($item->nama_supplier) . ' | ' . date('d/m/y', strtotime($item->date)) . '</div>';
                    }elseif ($is_pk_highlighted2) {
                        echo '<div class="barcode-details"><span class="highlight-pk2">' . $pk_display . '</span> | ' . htmlspecialchars($item->nama_supplier) . ' | ' . date('d/m/y', strtotime($item->date)) . '</div>';
                    } else {
                        echo '<div class="barcode-details">' . $pk_display . ' | ' . htmlspecialchars($item->nama_supplier) . ' | ' . date('d/m/y', strtotime($item->date)) . '</div>';
                    }
                    echo '</div>';
                    echo '</td>';

                    // Update counter
                    $current_col++;
                endfor;
            endforeach;

            // Print empty cells to complete the last row
            while ($current_col <= 3) {
                echo '<td></td>';
                $current_col++;
            }
            ?>
        </table>
    </div>
</body>
</html>