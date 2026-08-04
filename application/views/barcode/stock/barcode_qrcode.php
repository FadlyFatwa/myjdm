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
        <div class="pull-right">
            <a href="<?= site_url('stock/in') ?>" class="btn btn-warning btn-flat">
                <i class="fa fa-undo"></i> Kembali
            </a>
        </div>
        <div class="box-body">
            <div class="form-group">
                <label for="form">Pilih Format Label</label>
                <select name="form" id="form" class="form-control" required>
                    <option value="barcode_thermal">Label Thermal</option>
                    <option value="barcode_107">Label 107 </option>
                    <option value="barcode_101">Label 101 (Label Besar)</option>
                    <option value="barcode_124">Label 124 (Merk FT)</option>
                    <option value="barcode_fanbelt">Label Fanbelt</option>
                </select>
            </div>
            <form id="form-thermal" action="<?=site_url('barcode/barcode_print_thermal/'.$row->item_id)?>" method="post" target="_blank">
                <div class="form-group">
                    <?php
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                    echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($row->barcode, $generator::TYPE_CODE_128)) . '">';
                    ?>
                    <br>
                    <?=$row->barcode?>
                </div>
                <div class="form-group">
                        <label for="max_characters">Print Nama Barang
                        <br>Jangan Sampai 2 Baris*</label>
                        <input type="number" name="max_characters" class="form-control" id="max_characters" required min="1" value="30">
                    </div>
                    <div class="form-group">
                        <label for="quantity">Jumlah Print</label>
                        <input type="number" name="quantity" class="form-control" id="quantity" required value="<?= isset($stock->qty) ? $stock->qty : '' ?>">
                    </div>

                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required value="<?= isset($stock->date) ? date('Y-m-d', strtotime($stock->date)) : '' ?>">
                    <br>
                    <button type="submit" class="btn btn-primary">Print Barcode</button>
            </form>
            <form id="form-107" action="<?=site_url('barcode/barcode_print/'.$row->item_id)?>" method="post" target="_blank">
                <div class="form-group">
                    <?php
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                    echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($row->barcode, $generator::TYPE_CODE_128)) . '">';
                    ?>
                    <br>
                    <?=$row->barcode?>
                </div>
                <div class="form-group">
                        <label for="max_characters">Print Nama Barang
                        <br>Jangan Sampai 2 Baris*</label>
                        <input type="number" name="max_characters" class="form-control" id="max_characters" required min="1" value="30">
                    </div>
                    <div class="form-group">
                        <label for="quantity">Jumlah Print (Maks 30)</label>
                        <input type="number" name="quantity" class="form-control" id="quantity" required value="<?= isset($stock->qty) ? $stock->qty : '' ?>">
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
                    <input type="date" id="date" name="date" required value="<?= isset($stock->date) ? date('Y-m-d', strtotime($stock->date)) : '' ?>">
                    <br>
                    <button type="submit" class="btn btn-primary">Print Barcode</button>
            </form>
            <form id="form-101" action="<?=site_url('barcode/barcode_print_101/'.$row->item_id)?>" method="post" target="_blank">
                <div class="form-group">
                    <?php
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                    echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($row->barcode, $generator::TYPE_CODE_128)) . '">';
                    ?>
                    <br>
                    <?=$row->barcode?>
                </div>
                <div class="form-group">
                    <label for="nama_item">Nama Barang</label>
                    <input type="text" name="nama_item" class="form-control" id="nama_item" required value="<?=isset($row->nama_item) ? $row->nama_item : '' ?>">
                </div>
                <div class="form-group">
                    <label for="nama_mobil">Nama Mobil</label>
                    <input type="text" name="nama_mobil" class="form-control" id="nama_mobil">
                </div>
                <div class="form-group">
                    <label for="no_part">Merk</label>
                    <input type="text" name="no_part" class="form-control" id="no_part">
                </div>
                <div class="form-group">
                    <label for="quantity">Jumlah Print (Maks 6)</label>
                    <input type="number" name="quantity" class="form-control" id="quantity" max="6" required value="<?= isset($stock->qty) ? $stock->qty : '' ?>">
                </div>
                <div class="form-group">
                    <label for="start_col">Start Kolom (1-2)</label>
                    <input type="number" name="start_col" class="form-control" id="start_col" required min="1" max="2">
                </div>
                <div class="form-group">
                    <label for="start_row">Start Baris (1-3)</label>
                    <input type="number" name="start_row" class="form-control" id="start_row" required min="1" max="3">
                </div>
                <br>
                <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required value="<?= isset($stock->date) ? date('Y-m-d', strtotime($stock->date)) : '' ?>">
                    <br>
                    <button type="submit" class="btn btn-primary">Print Barcode</button>
            </form>
            <form id="form-124" action="<?=site_url('barcode/barcode_print_124/'.$row->item_id)?>" method="post" target="_blank">
                    <div class="form-group">
                        <?php
                        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                        echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($row->barcode, $generator::TYPE_CODE_128)) . '">';
                        ?>
                        <br>
                        <?=$row->barcode?>
                    </div>
                
                
                    <div class="form-group">
                        <label for="nama_item">Print Nama Barang</label>
                        <input type="text" name="nama_item" class="form-control" id="nama_item" required value="<?=isset($row->nama_item) ? $row->nama_item : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="nama_mobil">Nama Mobil</label>
                        <input type="text" name="nama_mobil" class="form-control" id="nama_mobil">
                    </div>
                    <div class="form-group">
                        <label for="no_part">No Part</label>
                        <input type="text" name="no_part" class="form-control" id="no_part">
                    </div>
                    <div class="form-group">
                        <label for="quantity">Jumlah Print (Maks 9)</label>
                        <input type="number" name="quantity" class="form-control" id="quantity" required value="<?= isset($stock->qty) ? $stock->qty : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="start_col">Start Kolom (1-3)</label>
                        <input type="number" name="start_col" class="form-control" id="start_col" required min="1" max="3">
                    </div>
                    <div class="form-group">
                        <label for="start_row">Start Baris (1-3)</label>
                        <input type="number" name="start_row" class="form-control" id="start_row" required min="1" max="3">
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary">Print Barcode</button>
               
            </form>
            <form id="form-fanbelt" action="<?=site_url('barcode/barcode_print_fanbelt/'.$row->item_id)?>" method="post" target="_blank">
                 <div class="form-group">
                    <label for="nama_item">Print Nama Barang</label>
                    <input type="text" name="nama_item" class="form-control" id="nama_item" required value="<?=isset($row->nama_item) ? $row->nama_item : '' ?>">
                </div>
                <div class="form-group">
                    <label for="nama_mobil">Nama Mobil</label>
                    <input type="text" name="nama_mobil" class="form-control" id="nama_mobil">
                </div>
                <div class="form-group">
                    <label for="no_part">Ukuran / Tipe Fanbelt</label>
                    <input type="text" name="no_part" class="form-control" id="no_part">
                </div>
                <div class="form-group">
                    <label for="quantity">Jumlah Print (Maks 6)</label>
                    <input type="number" name="quantity" class="form-control" id="quantity" required value="<?= isset($stock->qty) ? $stock->qty : '' ?>">
                </div>
                <div class="form-group">
                    <label for="start_col">Start Kolom (1-2)</label>
                    <input type="number" name="start_col" class="form-control" id="start_col" required min="1" max="3">
                </div>
                <div class="form-group">
                    <label for="start_row">Start Baris (1-3)</label>
                    <input type="number" name="start_row" class="form-control" id="start_row" required min="1" max="3">
                </div>
                <br>
                <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required value="<?= isset($stock->date) ? date('Y-m-d', strtotime($stock->date)) : '' ?>">
                    <br>
                    <button type="submit" class="btn btn-primary">Print Barcode</button>
            </form>
                
            

            
        </div>
    </div>
</section>

<style>
        #form-thermal, #form-107, #form-101, #form-124, #form-fanbelt {
        display: none;
    }
</style>


<script>
    $(document).ready(function() {
        $('#form').change(function() {
            $('#form-thermal, #form-107,#form-124, #form-101, #form-fanbelt').hide();
            var selectedForm = $(this).val();
            if (selectedForm === 'barcode_thermal') {
                $('#form-thermal').show();
            } else if (selectedForm === 'barcode_101') {
                $('#form-101').show();
            } else if (selectedForm === 'barcode_124') {
                $('#form-124').show();
            } else if (selectedForm === 'barcode_fanbelt') {
                $('#form-fanbelt').show();
            } else if (selectedForm === 'barcode_107') {
                 $('#form-107').show();
            }
        });
        $('#form').trigger('change');
    });
</script>
