<section class = "content-header">
    <h1>Karyawan
        <small>Daftar Karyawan</small>
</h1>
    <ol class="breadcrumb">
    <li><a href = "#"><i class="fa fa-users"></i></a></li>
        <li class="active">SDM</li>
        <li class="active">Karyawan</li>
    </ol>
</section>

<section class="content">
<?php $this->view('massage')?>
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Data Karyawan</h3>
        <div class="pull-right">
            <a href="<?=site_url('karyawan/add') ?>"class="btn btn-primary btn-flat">
                <i class="fa fa-plus"></i> Tambah
            </a>
        </div>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-bordered table-striped" id="table1">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Karyawan</th>
                    <th>Akun Login Terhubung</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1;
            foreach($row->result() as $key => $data) { ?>
            <tr>
                <td style="width:5%;"><?=$no++?>.</td>
                <td><?=$data->nama?></td>
                <td><?=$data->user_nama ?: '-'?></td>
                <td class="text-center" width="160px">
                        <a href="<?=site_url('karyawan/edit/'.$data->karyawan_id)?>" class="btn btn-primary btn-xs">
                            <i class="fa fa-pencil"></i> Edit
                        </a>
                        <a href="<?=site_url('karyawan/del/'.$data->karyawan_id)?>" id="btn-hapus" class="btn btn-danger btn-xs">
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
