<section class = "content-header">
    <h1>Kategori
        <small>Kategori Barang</small>
</h1>
    <ol class="breadcrumb">
    <li><a href = "#"><i class="fa fa-archive"></i></a></li>
        <li class="active">Produk</li>
        <li class="active">Kategori</li>
        <li class="active"><?=ucfirst($page)?></li>
    </ol>
</section>

<section class="content">
    <div class="box">
        <div class="box-header">
            <h3 class="box-title"><?=ucfirst($page)?> Kategori</h3>
            <div class="pull-right">
                <a href="<?=site_url('category') ?>" class="btn btn-warning btn-flat">
                    <i class="fa  fa-undo"></i> Kembali
                </a>
            </div>
        </div>

        <div class="box-body">
            <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <form action="<?=site_url('category/process')?>" method="post">
                    <div class="form-group">
                        <label>Nama category *</label>
                        <input type="hidden" name="id" value="<?=$row->category_id?>">
                        <input type="text" name="nama_category" value="<?=$row->nama_category?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="<?=$page?>" class="btn btn-success btn-flat">
                            <i class="fa fa-paper-plane"></i> Simpan
                        </button>
                        <button type="Reset" class="btn btn-flat">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
