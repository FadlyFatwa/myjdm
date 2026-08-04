<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Nota Pembayaran <?= $sale->invoice ?> </title>
    <style media="screen">
        body {
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
        }

        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: auto;
            padding: 0px;
        }

        .left-column {
            width: 70%;
            float: left;
        }

        .nama-barang-small {
            font-size: 12px;
            word-wrap: break-word;
            text-overflow: ellipsis;
            white-space: normal;
        }

        .right-column {
            width: 40%;
            float: right;
            text-align: left;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container clearfix">
        <div class="left-column">
            <strong>Menara Mas</strong>
            <br>
            <div class="row">
        <div class="col-md-5">
            NO. Nota : <?= $sale->invoice ?>
        </div>
    </div>
        </div>

        <div class="right-column">
            <?php
                $date = strtotime($sale->sale_date);
                $tglpj = date('d M Y', $date);
            ?>
             <div class="date" style="text-align: right; margin-bottom: 10px;">Bandung, <?= $tglpj ?></div>
        </div>
    </div>

    <h2>
        <center> INVOICE PENJUALAN </center>
    </h2>
    <hr style="height:1px;border-width:0;color:gray;background-color:gray">
    
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered" id="table1" width="100%" cellspacing="0">
			<thead>
				<tr>
					<td style="width: 5%; text-align: center;"><strong>No</strong></td>
					<td style="width: 60%; text-align: center;"><strong>Nama Barang</strong></td>
					<td style="width: 20%; text-align: center;"><strong>Harga Barang</strong></td>
					<td style="width: 10%; text-align: center;"><strong>Jumlah</strong></td>
					<td style="width: 20%; text-align: center;"><strong>Sub Total</strong></td>
				</tr>
			</thead>
				<tbody>
					<?php $no = 1;
					foreach ($sale_detail as $key => $value) {
						$nama_barang_class = (strlen($value->nama_barang_jual) > 50) ? 'nama-barang-small' : '';
					?>
						<tr>
							<td style="text-align: center;"><?= $no++ ?></td>
							<td class="<?= $nama_barang_class ?>" style="text-align: left;"><?= $value->nama_barang_jual ?></td>
							<td style="text-align: right;"><?= number_format(($value->price_sale), 0, ',', '.') ?></td>
							<td style="text-align: center;"><?= $value->qty ?></td>
							<td style="text-align: right;"><?= number_format($value->total, 0, ',', '.') ?></td>
						</tr>
					<?php } ?>
				</tbody>

                <tfoot>
                    <tr>
                        <td colspan="4" align="right"><strong> Grand Total : </strong></td>
                        <td align="right">Rp <?= number_format($sale->grand_total, 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="footer">
        <!-- Footer content -->
    </div>
</body>

</html>
