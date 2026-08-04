<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print - <?=$row->barcode?> - <?=$row->nama_item?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }

        @page {
            margin-top: 1.3cm;
            margin-right: 0.5cm;
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
            width: 5.5cm;
            height: 4.2cm;
            padding: 2px;
            text-align: center;
            vertical-align: top;
            /* Ensure consistent size for all td */
            min-width: 5.5cm;
            min-height: 4.2cm;
        }

        td.spacer {
            width: 0.7cm;
            border: none;
        }

        /* Add right margin for the first and second columns */
        td.first-column, td.second-column {
            padding-right: 0.8cm;
        }

        .barcode-item {
            text-align: center;
            margin-bottom: 5px;
            margin-bottom: 0;
        }

        .barcode-item-name {
            line-height: 0.8;
            word-wrap: break-word;
            max-width: 100%;
            font-weight: bold;
            text-align: left;
            margin-top: 5px;
            margin-bottom: 0;
        }

        .barcode-item-name .line-1 {
            font-size: 14px;
            font-weight: bold;
            margin-left: 10px;
            margin-bottom: 0;
            text-align: left;
        }

        .barcode-item-name .line-2 {
            font-size: 14px;
            font-weight: bold;
            margin-left: 10px;
            margin-top: 0;
            margin-bottom: 0;
            text-align: left;
        }

        .barcode-item-mobil {
            font-size: 12px;
            margin-left: 10px;
            text-align: left;
            margin-bottom: 0px;
        }

        .barcode-item-no_part {
            font-size: 12px;
            margin-left: 10px;
            text-align: left;
            margin-bottom: 0px;
        }

        .barcode-text {
            margin-top: 3px;
            font-size: 12px;
            margin-bottom: 0px;
        }

        .logo-img {
            display: block;
            margin: 0 auto;
            margin-top: 0;
            width: 5cm;
            height: 0.7cm;
            border-bottom: 2px solid black;
            padding-bottom: 5px;
        }

        .barcode-img {
            width: 5cm;
            height: 0.7cm;
            margin-top: 0px;
        }
    </style>
</head>
<body>
    <div class="barcode-container">
        <table>
        <?php
        // Initialize barcode generator
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $width = 2;
        $height = 50;

        // Quantity and positioning
        $quantity = isset($quantity) ? (int) $quantity : 9;
        $start_col = isset($start_col) ? (int)$start_col - 1 : 0;
        $start_row = isset($start_row) ? (int)$start_row - 1 : 0;

        $nama_item = isset($nama_item) ? mb_strtoupper(strval($nama_item)) : ''; 
        $nama_mobil = isset($nama_mobil) ? mb_strtoupper(strval($nama_mobil)) : ''; 
        $no_part = isset($no_part) ? mb_strtoupper(strval($no_part)) : '';

        $counter = 0;

        // Loop through 3 rows
        for ($i = 0; $i < 3; $i++) {
            echo '<tr>';
            // Loop through 3 columns
            for ($j = 0; $j < 3; $j++) {
                if ($i < $start_row || ($i == $start_row && $j < $start_col)) {
                    // Empty cells that maintain the same size
                    echo '<td class="empty-cell"></td>';
                } else {
                    if ($counter < $quantity) {
                        // Add classes for first and second columns
                        $class = '';
                        if ($j == 0) {
                            $class = 'first-column';
                        } elseif ($j == 1) {
                            $class = 'second-column';
                        }

                        echo '<td class="' . $class . '">';
                        echo '<img class="logo-img" src="data:image/png;base64,' . base64_encode(file_get_contents('assets/dist/img/logo_ft.png')) . '" alt="Logo">';
                        echo '<div class="barcode-item">';

                        // Split the item name into two lines based on length
                        if (strlen($nama_item) > 25) {
                            $line_1 = substr($nama_item, 0, 25);
                            $line_2 = substr($nama_item, 25);
                        } else {
                            $line_1 = $nama_item;
                            $line_2 = '';
                        }

                        echo '<div class="barcode-item-name">';
                        echo '<span class="line-1">' . $line_1 . '</span><br>';
                        if ($line_2 !== '') {
                            echo '<span class="line-2">' . $line_2 . '</span>';
                        } else {
                            echo '<span class="line-2">&nbsp;</span>'; // Empty space if line 2 is empty
                        }
                        echo '</div>';

                        echo '<div class="barcode-text">';
                        echo '<div class="barcode-item-mobil">' . $nama_mobil . '</div>';
                        echo '<div class="barcode-item-no_part">' . $no_part . '</div>';
                        echo '<img class="barcode-img" src="data:image/png;base64,' . base64_encode($generator->getBarcode($row->barcode, $generator::TYPE_CODE_128, $width, $height)) . '"><br>';
                        echo $row->barcode;
                        echo '</div>';
                        echo '</div>';
                        echo '</td>';
                        
                        $counter++;
                    } else {
                        echo '<td class="spacer"></td>';
                    }
                }
            }
            echo '</tr>';
        }
        ?>
        </table>
    </div>
</body>
</html>
