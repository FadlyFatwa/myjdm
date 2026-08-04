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
            margin-top: 0.8cm;
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
            font-size: 14px;
            margin-left: 10px;
            margin-bottom : 0;
            text-align: center;
        }
        .barcode-item-mobil {
            font-size: 14px;
            margin-left: 10px;
            text-align: center;
            margin-bottom: 0px;
            margin-top: 0px;
        }

        .barcode-item-no_part {
            font-size: 48px;
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
    <div class="barcode-container">
        <table>
        <?php
        // Initialize barcode generator
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $width = 2;
        $height = 50;

        // Quantity and positioning
        $quantity = isset($quantity) ? (int) $quantity : 6;
        $start_col = isset($start_col) ? (int)$start_col - 1 : 0;
        $start_row = isset($start_row) ? (int)$start_row - 1 : 0;
        $current_date = date('d/m/y', strtotime($date)); // Use the provided date
        $max_characters = isset($max_characters) ? (int)$max_characters : 30; // Maximum characters for the first line
        
        $nama_item = isset($nama_item) ? strtoupper(strval($nama_item)) : ''; 
        $nama_mobil = isset($nama_mobil) ? strtoupper(strval($nama_mobil)) : ' '; 
        $no_part = isset($no_part) ? strtoupper(strval($no_part)) : ' ';


        $counter = 0;

        // Loop through 3 rows
        for ($i = 0; $i < 3; $i++) {
            echo '<tr>';
            // Loop through 3 columns
            for ($j = 0; $j < 2; $j++) {
                if ($i < $start_row || ($i == $start_row && $j < $start_col)) {
                    echo '<td></td>';
                } else {
                    if ($counter < $quantity) {
                        echo '<td>';
                        echo '<div class="barcode-item">';

                            // Split the item name into two lines based on length
                            echo '<div class="barcode-item-name">';
                            echo $nama_item;
                            echo '</div>';

                        
                            echo '<div class="barcode-text">';
                            echo '<div class="barcode-item-no_part">' . $no_part . '</div>';
                            echo '<div class="barcode-item-mobil">' . $nama_mobil . '</div>';
                            echo '<img class="barcode-img" src="data:image/png;base64,' . base64_encode($generator->getBarcode($row->barcode, $generator::TYPE_CODE_128, $width, $height)) . '"><br>';
                            echo $row->barcode;
                            echo '</div>';
                            echo '<div class="barcode-details">' . $row->pk . ' | ' . $row->nama_supplier . ' | ' . $current_date . '</div>';
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
