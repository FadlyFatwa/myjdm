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
                    <option value="barcode_107">Label 107</option>
                    <option value="barcode_101">Label 101 (Label Besar)</option>
                    <option value="barcode_fanbelt">Label Fanbelt</option>
                </select>
            </div>
        <div class="box-body">
            <?php if ($items) { // Cek apakah ada item ?>
                <form id="form-thermal" action="<?=site_url('barcode/barcode_print_multiple_40x20')?>" method="post" target="_blank">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Barcode</th>
                                <th>Nama Barang</th>
                                <th width="10%">Karakter</th>
                                <th width="10%">Jumlah Print</th>
                                <th width="10%">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $index => $row): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                                        echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($row->barcode, $generator::TYPE_CODE_128)) . '">';
                                        echo '<br>' . $row->barcode;
                                        ?>
                                    </td>
                                    <td><?= $row->nama_item ?></td>
                                    <td><input type="number" name="max_characters[<?= $row->stock_id ?>]" class="form-control" required min="1"  value="30"></td>
                                    <td>
                                        <input type="number" name="quantity[<?= $row->stock_id ?>]" class="form-control" required min="1"  value="<?= $row->qty ?>">
                                    </td>
                                    <td>
                                        <input type="date" name="date[<?= $row->stock_id ?>]" class="form-control" required value="<?= date('Y-m-d', strtotime($row->date)) ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Print Barcode Terpilih</button>
                </form>
                <form id="form-107" action="<?=site_url('barcode/barcode_print_multiple')?>" method="post" target="_blank">
                    <div class="form-group">
                        <label for="start_col">Start Kolom (1-3)</label>
                        <input type="number" name="start_col" class="form-control" id="start_col" required min="1" max="3">
                    </div>
                    <div class="form-group">
                        <label for="start_row">Start Baris (1-10)</label>
                        <input type="number" name="start_row" class="form-control" id="start_row" required min="1" max="10">
                    </div>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Barcode</th>
                                <th>Nama Barang</th>
                                <th width="10%">Karakter</th>
                                <th width="10%">Jumlah Print</th>
                                <th width="10%">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $index => $row): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                                        echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($row->barcode, $generator::TYPE_CODE_128)) . '">';
                                        echo '<br>' . $row->barcode;
                                        ?>
                                    </td>
                                    <td><?= $row->nama_item ?></td>
                                    <td><input type="number" name="max_characters[<?= $row->stock_id ?>]" class="form-control" required min="1"  value="30"></td>
                                    <td>
                                        <input type="number" name="quantity[<?= $row->stock_id ?>]" class="form-control" required min="1"  value="<?= $row->qty ?>">
                                    </td>
                                    <td>
                                        <input type="date" name="date[<?= $row->stock_id ?>]" class="form-control" required value="<?= date('Y-m-d', strtotime($row->date)) ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Print Barcode Terpilih</button>
                </form>            
                <form id="form-101" action="<?=site_url('barcode/barcode_101_print_multiple')?>" method="post" target="_blank">
                    <div class="form-group">
                        <label for="start_col">Start Kolom (1-3)</label>
                        <input type="number" name="start_col" class="form-control" id="start_col" required min="1" max="3">
                    </div>
                    <div class="form-group">
                        <label for="start_row">Start Baris (1-10)</label>
                        <input type="number" name="start_row" class="form-control" id="start_row" required min="1" max="10">
                    </div>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="10%">Barcode</th>
                                <th>Nama Barang</th>
                                <th>Nama Mobil</th>
                                <th>Merk / No Part</th>
                                <th width="10%">Jumlah Print</th>
                                <th width="10%">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $index => $row): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                                        echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($row->barcode, $generator::TYPE_CODE_128)) . '">';
                                        echo '<br>' . $row->barcode;
                                        ?>
                                    </td>
                                    <td><input type="text" name="nama_item[<?= $row->stock_id ?>]" class="form-control" required  value="<?= $row->nama_item ?>"></td>
                                    <td><input type="text" name="nama_mobil[<?= $row->stock_id ?>]" class="form-control" required ></td>
                                    <td><input type="text" name="no_part[<?= $row->stock_id ?>]" class="form-control" required ></td>
                                    <td>
                                        <input type="number" name="quantity[<?= $row->stock_id ?>]" class="form-control" required min="1"  value="<?= $row->qty ?>">
                                    </td>
                                    <td>
                                        <input type="date" name="date[<?= $row->stock_id ?>]" class="form-control" required value="<?= date('Y-m-d', strtotime($row->date)) ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Print Barcode Terpilih</button>
                </form>
                
                <form id="form-fanbelt" action="<?=site_url('barcode/barcode_fanbelt_print_multiple')?>" method="post" target="_blank">
                    <div class="form-group">
                        <label for="start_col">Start Kolom (1-3)</label>
                        <input type="number" name="start_col" class="form-control" id="start_col" required min="1" max="3">
                    </div>
                    <div class="form-group">
                        <label for="start_row">Start Baris (1-10)</label>
                        <input type="number" name="start_row" class="form-control" id="start_row" required min="1" max="10">
                    </div>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="10%">Barcode</th>
                                <th>Nama Barang</th>
                                <th>Nama Mobil</th>
                                <th>Ukuran / Tipe Fanbelt</th>
                                <th width="10%">Jumlah Print</th>
                                <th width="10%">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $index => $row): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                                        echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($row->barcode, $generator::TYPE_CODE_128)) . '">';
                                        echo '<br>' . $row->barcode;
                                        ?>
                                    </td>
                                    <td><input type="text" name="nama_item[<?= $row->stock_id ?>]" class="form-control" required  value="<?= $row->nama_item ?>"></td>
                                    <td><input type="text" name="nama_mobil[<?= $row->stock_id ?>]" class="form-control" required ></td>
                                    <td><input type="text" name="no_part[<?= $row->stock_id ?>]" class="form-control" required ></td>
                                    <td>
                                        <input type="number" name="quantity[<?= $row->stock_id ?>]" class="form-control" required min="1"  value="<?= $row->qty ?>">
                                    </td>
                                    <td>
                                        <input type="date" name="date[<?= $row->stock_id ?>]" class="form-control" required value="<?= date('Y-m-d', strtotime($row->date)) ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Print Barcode Terpilih</button>
                </form>

            <?php } else { // Jika tidak ada item, tampilkan pesan error ?>
                <div class="alert alert-danger">
                    <strong>Error!</strong> Tidak ada data barang yang dipilih.
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<style>
    #form-107, #form-101, #form-fanbelt , #form-thermal {
        display: none;
    }
</style>


<script>
    $(document).ready(function() {
        $('#form').change(function() {
            $('#form-107, #form-101, #form-fanbelt, #form-thermal').hide();
            var selectedForm = $(this).val();
            if (selectedForm === 'barcode_thermal') {
                $('#form-thermal').show();
            } else if (selectedForm === 'barcode_101') {
                $('#form-101').show();
            } else if (selectedForm === 'barcode_fanbelt') {
                $('#form-fanbelt').show();
            }else if (selectedForm === 'barcode_107') {
                $('#form-107').show();
            }
        });
        $('#form').trigger('change');
    });
</script>

