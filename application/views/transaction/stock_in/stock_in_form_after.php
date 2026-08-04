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
                <a href="<?=site_url('item') ?>" class="btn btn-warning btn-flat">
                    <i class="fa  fa-undo"></i> Kembali
                </a>
            </div>
        </div>

        <div class="box-body">
            <div class="row">
            <div class="col-md-4 col-md-offset-4">                        
                <form action="<?=site_url('stock/process')?>" method="post">
                    <div class="form-group">
                        <label>Tanggal *</label>
                        <input type="date" name="date" value="<?=date('Y-m-d') ?>" class="form-control" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="barcode">Barcode *</label>
                    </div>
                    <div class="form-group input-group">
                        <input type='hidden' name="item_id" id="item_id" value="<?=$row->item_id?>">
                        <input type='text' name="barcode" id="barcode" value="<?=$row->barcode?>" class="form-control" readonly > 
                    </div>
                    <div class="form-group">
                        <label for="nama_item">Nama Barang *</label>
                        <input type="text" name="nama_item" id="nama_item" value="<?=$row->nama_item?>" class="form-control" readonly>
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
                                <input type="text" name="modal" id="modal" class="form-control modalInput"
                                    value="<?= number_format($row->modal, 0, ',', '.') ?>">
                            </div>
                            <div class="col-md-6">
                                <label>PK</label>
                                <input type="text" name="pk" id="pk" class="form-control"
                                    value="<?= $row->pk ?>" style="font-family:monospace;font-weight:700" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="nama_unit">Satuan</label>
                                <input type="text" name="nama_unit" id="nama_unit" value="<?=$row->nama_unit?>" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="stock">Stok Awal</label>
                                <input type="text" name="stock" id="stock" value="<?=$row->stock?>" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Detail *</label>
                        <input type="text" name="detail" class="form-control" placeholder="Datang Barang" >
                    </div>
                    <div class="form-group">
                        <label>Qty *</label>
                        <input type="text" name="qty" class="form-control" required autofocus>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="in_add" class="btn btn-success btn-flat">
                            <i class="fa fa-paper-plane"></i> Simpan
                        </button>
                        <button type="Reset" class="btn btn-flat">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

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

    // Format modal & auto PK
    $('#modal').on('input', function () {
        var raw = $(this).val().replace(/\D/g,'');
        $(this).val(formatRp(raw));
        $('#pk').val(generatePK(raw));
    });

    // Auto-fill harga dari supplier yang dipilih
    $('#supplier_id').on('change', function () {
        var harga = parseInt($('option:selected', this).data('harga')) || 0;
        if (harga > 0) {
            $('#modal').val(formatRp(String(harga)));
            $('#pk').val(generatePK(String(harga)));
        }
    });

    // Unformat sebelum submit
    $('form').on('submit', function () {
        $('#modal').val($('#modal').val().replace(/\./g,''));
    });
});
</script>
