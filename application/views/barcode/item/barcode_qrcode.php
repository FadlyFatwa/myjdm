<section class="content-header">
    <h1>Produk
        <small>Data Barang</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-archive"></i></a></li>
        <li class="active">Produk</li>
        <li class="active">Daftar Barang</li>
    </ol>
</section>

<section class="content">
    <?php $this->view('massage')?>
    <div class="box">
    <div class="box">
        <div class="pull-right">
            <?php
            // Cek parameter 'back_from' untuk menentukan URL back
            $back_from = $this->input->get('back_from');
            $back_url = ($back_from == 'item') ? 'item' : 'stock/in';
            ?>
            <a href="<?= site_url($back_url) ?>" class="btn btn-warning btn-flat">
                <i class="fa fa-undo"></i> Kembali
            </a>
        </div>

        <div class="box-body">
            <div class="form-group">
            <?php
            $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
            echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($row->barcode, $generator::TYPE_CODE_128)) . '">';
            ?>
            <br>
            <?=$row->barcode?>
            </div>
            <form action="<?=site_url('barcode/barcode_print/'.$row->item_id)?>" method="post" target="_blank">
            <div class="form-group">
                    <label for="max_characters">Print Nama Barang
                    <br>Jangan Sampai 2 Baris*</label>
                    <input type="number" name="max_characters" class="form-control" id="max_characters" required min="1" value="30">
                </div>
            <div class="form-group">
                    <label for="quantity">Jumlah Print (Maks 30)</label>
                    <input type="number" name="quantity" class="form-control" id="quantity" required>
                </div>
                <div class="form-group">
                    <label for="start_col">Start Kolom (1-3)</label>
                    <input type="number" name="start_col" class="form-control" id="start_col" required min="1" max="3">
                </div>
                <div class="form-group">
                    <label for="start_row">Start Baris (1-10)</label>
                    <input type="number" name="start_row" class="form-control" id="start_row" required min="1" max="10">
                </div>
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required>
                </div>
                <br>
                <button type="submit" class="btn btn-primary">Print Barcode</button>
            </form>
        </div>
    </div>
</section>
