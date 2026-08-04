<section class = "content-header">
    <h1>Update Stock
        <small>Tambah Stock Barang Masuk</small>
</h1>
    <ol class="breadcrumb">
        <li><a href = "#"><i class="fa fa-dashboard"></i></a></li>
        <li class="active">Stock in</li>
    </ol>
</section>

<section class="content">
<?php $this->view('massage')?>
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Tambah Stock</h3>
            <div class="pull-right">
                <a href="<?=site_url('stock/in') ?>" class="btn btn-warning btn-flat">
                    <i class="fa  fa-undo"></i> Kembali
                </a>
            </div>
        </div>

        <div class="box-body">
            <div class="row">
            <div class="col-md-4 col-md-offset-4">                        
                <form action="<?=site_url('stock/process')?>" method="post">
                    <input type="hidden" name="stock_id" value="<?=$row->stock_id?>">
                    <div class="form-group">
                        <label>Tanggal *</label>
                        <input type="date" name="date" value="<?= date('Y-m-d', strtotime($row->date)) ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="barcode">Barcode *</label>
                    </div>
                    <div class="form-group input-group">
                        <input type='hidden' name="item_id" id="item_id" value="<?=$row->item_id?>">
                        <input type='text' name="barcode" id="barcode" class="form-control" value="<?=$row->barcode?>" required autofocus readonly>
                        <span class="input-group-btn">
                            <!-- <button type="button" class="btn btn-info btn-flat" data-toggle="modal" data-target="#modal-item">
                                <i class = "fa fa-search"></i>
                            </button> -->
                        </span>
                    </div>
                    <div class="form-group">
                        <label for="nama_item">Nama Barang *</label>
                        <input type="text" name="nama_item" id="nama_item" class="form-control" value="<?=$row->nama_item?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Supplier</label>
                        <select name="supplier_id" id="supplier_id" class="form-control">
                            <option value="">— Pilih Supplier —</option>
                            <?php
                            $suppliers_item = $this->db
                                ->select('sb.supplier_id, s.nama_supplier, sb.harga_beli', false)
                                ->from('supplier_barang sb')
                                ->join('supplier s', 'sb.supplier_id = s.supplier_id')
                                ->where('sb.item_id', $row->item_id)
                                ->order_by('s.nama_supplier', 'ASC')
                                ->get()->result();
                            foreach ($suppliers_item as $sp): ?>
                            <option value="<?= $sp->supplier_id ?>"
                                <?= $sp->supplier_id == $row->supplier_id ? 'selected' : '' ?>
                                data-harga="<?= $sp->harga_beli ?>">
                                <?= htmlspecialchars($sp->nama_supplier) ?>
                                <?= $sp->harga_beli ? '(Rp ' . number_format($sp->harga_beli, 0, ',', '.') . ')' : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Modal (Harga Beli)</label>
                                <input type="text" name="modal" id="modal" class="form-control"
                                    value="<?= isset($item_row) ? number_format($item_row->modal, 0, ',', '.') : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label>PK</label>
                                <input type="text" name="pk" id="pk" class="form-control"
                                    value="<?= isset($item_row) ? $item_row->pk : '' ?>"
                                    style="font-family:monospace;font-weight:700" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-8">
                                <label for="nama_unit">Satuan</label>
                                <input type="text" name="nama_unit" id="nama_unit" value="<?= isset($item_row) ? $item_row->nama_unit ?? '-' : '-' ?>" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="stock">Stok Awal</label>
                                <input type="text" name="stock" id="stock" value="<?=$row->stock?>" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Detail *</label>
                        <input type="text" name="detail" class="form-control" value="<?=$row->detail?>" placeholder="Datang Barang">
                    </div>
                    <div class="form-group">
                        <label>Qty *</label>
                        <input type="text" name="qty" class="form-control" value="<?=$row->qty?>" required autofocus>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="in_edit" class="btn btn-success btn-flat">
                            <i class="fa fa-paper-plane"></i> Simpan
                        </button>
                        <button type="Reset" class="btn btn-flat">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modal-item">
    <div class="modal-dialog" style="width:60%">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title"> Pilih Barang </h4>
            <div>
        </div>
<div class="modal-body table-responsive">
                <table id="table1" class="table table-striped table-bordered dt-responsive" style="width:100%">
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Nama Barang</th>
                            <th>Supplier</th>
                            <th>Unit</th>
                            <th>Modal</th>
                            <th>PK</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($item as $data): ?>
                        <tr>
                            <td><?= $data->barcode ?></td>
                            <td><?= $data->nama_item ?></td>
                            <td><?= $data->nama_supplier ?></td>
                            <td><?= $data->nama_unit ?></td>
                            <td class="text-right"><?=indo_currency($data->modal) ?></td>
                            <td class="text-right"><?= $data->pk ?></td>
                            <td class="text-right"><?= $data->stock ?></td>
                            <td class="text-right">
                                <button class="btn btn-xs btn-info" id="select"
                                    data-id="<?= $data->item_id ?>"
                                    data-barcode="<?= $data->barcode ?>"
                                    data-modal="<?= $data->modal ?>"
                                    data-pk="<?= $data->pk ?>"
                                    data-nama_item="<?= $data->nama_item ?>"
                                    data-supplier_id="<?= $data->supplier_id ?>"
                                    data-nama_supplier="<?= $data->nama_supplier ?>"
                                    data-nama_unit="<?= $data->nama_unit ?>"
                                    data-stock="<?= $data->stock ?>">
                                    <i class="fa fa-check"></i> Pilih
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function () {
    function formatRp(v) { return String(v).replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.'); }
    function generatePK(v) {
        var map={'0':'Y','1':'S','2':'I','3':'T','4':'O','5':'M','6':'P','7':'U','8':'L','9':'X'};
        var s=String(v).replace(/\D/g,''),out='',z=0;
        for(var i=0;i<s.length;i++){
            if(s[i]==='0'){z++;}else{
                if(z>1)out+='Y'+z;else if(z===1)out+='Y';
                z=0;out+=map[s[i]]||s[i];
            }
        }
        if(z>1)out+='Y'+z;else if(z===1)out+='Y';
        return out.toUpperCase();
    }

    $('#modal').on('input', function () {
        var raw = $(this).val().replace(/\D/g,'');
        $(this).val(formatRp(raw));
        $('#pk').val(generatePK(raw));
    });

    $('#supplier_id').on('change', function () {
        var harga = parseInt($('option:selected', this).data('harga')) || 0;
        if (harga > 0) {
            $('#modal').val(formatRp(String(harga)));
            $('#pk').val(generatePK(String(harga)));
        }
    });

    $('form').on('submit', function () {
        $('#modal').val($('#modal').val().replace(/\./g,''));
    });
});
</script>
