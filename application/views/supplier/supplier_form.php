<section class="content-header">
    <h1>Tambah Supplier 
        <small>Data Supplier</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href='#'><i class="fa fa-dashboard"></i></a></li>
        <li classs ="active">Supplier</li>
    </ol>
</section>

<section class="content">
    <div class="box">
        <div class="box-header">
            <h3 class="box-title"><?=ucfirst($page)?> Supplier</h3>
            <div class="pull-right">
                <a href="<?=site_url('supplier') ?>" class="btn btn-warning btn-flat">
                    <i class="fa  fa-undo"></i> Kembali
                </a>
            </div>
        </div>

        <div class="box-body">
            <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <form action="<?=site_url('supplier/process')?>" method="post">
                    <div class="form-group">
                        <label>Nama Supplier *</label>
                        <input type="hidden" name="id" value="<?=$row->supplier_id?>">
                        <input type="text" name="nama_supplier" value="<?=$row->nama_supplier?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>No Telepon *</label>
                        <input type="text" name="telp" value="<?=$row->phone?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <input type="text" name="alamat" value="<?=$row->alamat?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" value="<?=$row->keterangan?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Termin Pembayaran (hari)</label>
                        <input type="number" name="payment_term_days" value="<?=$row->payment_term_days?>" class="form-control" min="0">
                    </div>
                    <div class="form-group">
                        <button type="submit" name="<?=$page?>" class="btn btn-success btn-flat">
                            <i class="fa fa-paper-plane"></i> Save
                        </button>
                        <button type="Reset" class="btn btn-flat">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
