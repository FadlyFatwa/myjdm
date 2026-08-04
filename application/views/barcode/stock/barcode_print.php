<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Print - <?=$row->barcode?> - <?=$row->nama_item?></title>
    <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Print - <?=$row->barcode?> - <?=$row->nama_item?></title>
    <style>
        body {
            margin-left: 20px;
            margin-top: 1px;
            padding: 1.5px;
        }

        @page {
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
            word-wrap: break-word; /* Allow long words to break to the next line */
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
<body>
    <div class="barcode-container">
        <table>
            <?php
            $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
            $width = 1;  // Width of a single bar
            $height = 30;  // Height of the barcode

            $quantity = isset($quantity) ? (int)$quantity : 30;
            $start_col = isset($start_col) ? (int)$start_col - 1 : 0;
            $start_row = isset($start_row) ? (int)$start_row - 1 : 0;
            $current_date = date('d/m/y', strtotime($date)); // Use the provided date
            $max_characters = isset($max_characters) ? (int)$max_characters : 30; // Maximum characters for the first line

            $counter = 0;
            for ($i = 0; $i < 10; $i++) {  // 10 rows
                echo '<tr>';
                for ($j = 0; $j < 3; $j++) {  // 3 columns
                    if ($i < $start_row || ($i == $start_row && $j < $start_col)) {
                        echo '<td></td>'; // Print empty cells before start position
                    } else {
                        if ($counter < $quantity) {
                            echo '<td>';
                            echo '<div class="barcode-item">';

                            // Split the item name into two lines based on length
                            $nama_item = $row->nama_item;
                            if (strlen($nama_item) > $max_characters) {
                                $line_1 = substr($nama_item, 0, $max_characters);  // First line with max 30 characters
                                $line_2 = substr($nama_item, $max_characters);  // Remaining characters for the second line
                            } else {
                                $line_1 = $nama_item;  // If less than 30 characters, use the full name in the first line
                                $line_2 = '';  // No second line
                            }

                            echo '<div class="barcode-item-name">';
                            echo '<span class="line-1">' . $line_1 . '</span><br>';
                            if ($line_2 !== '') {
                                echo '<span class="line-2">' . $line_2 . '</span>';
                            }
                            echo '</div>';

                            echo '<div class="barcode-text">';
                            echo '<img class="barcode-img" src="data:image/png;base64,' . base64_encode($generator->getBarcode($row->barcode, $generator::TYPE_CODE_128, $width, $height)) . '">';
                            echo $row->barcode;
                            echo '                        ';
                            echo $row->nama_unit;
                            echo '</div>';

                            echo '<div class="barcode-details">' . $row->pk . ' | ' . $row->nama_supplier . ' | ' . $current_date . '</div>';
                            echo '</div>';
                            echo '</td>';
                            $counter++;
                        } else {
                            echo '<td></td>'; // Print empty cells after finishing the quantity
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
