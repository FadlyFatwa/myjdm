<section class = "content-header">
    <h1>Satuan
        <small>Daftar Satuan</small>
</h1>
    <ol class="breadcrumb">
    <li><a href = "#"><i class="fa fa-archive"></i></a></li>
        <li class="active">Produk</li>
        <li class="active">Satuan</li>
    </ol>
</section>

<section class="content">
<?php $this->view('massage')?>
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Data unit</h3>
        <div class="pull-right">
            <a href="<?=site_url('unit/add') ?>"class="btn btn-primary btn-flat">
                <i class="fa fa-plus"></i> Tambah
            </a>
        </div>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-bordered table-striped" id="table1">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1;
            foreach($row->result() as $key => $data) { ?>
            <tr>
                <td style="width:5%;"><?=$no++?>.</td>
                <td><?=$data->nama_unit?></td>
                <td class="text-center" width="160px">
                        <a href="<?=site_url('unit/edit/'.$data->unit_id)?>" class="btn btn-primary btn-xs">
                            <i class="fa fa-pencil"></i> Edit
                        </a>
                        <a href="<?=site_url('unit/del/'.$data->unit_id)?>" id="btn-hapus" class="btn btn-danger btn-xs">
                            <i class="fa fa-trash"></i> Hapus
                        </a>
                </td>
            </tr>
            <?php
            } ?>
            </tbody>
        </table>
    </div>
 </div>

</section>