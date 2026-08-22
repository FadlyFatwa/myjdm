<section class = "content-header">
    <h1>Supplier
        <small>Daftar Supplier</small>
</h1>
    <ol class="breadcrumb">
        <li><a href = "#"><i class="fa fa-truck"></i></a></li>
        <li class="active">Supplier</li>
    </ol>
</section>

<section class="content">
<div id="flash" data-flash="<?=$this->session->flashdata('success');?>"></div>
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Data Supplier</h3>
        <div class="pull-right">
            <a href="<?=site_url('supplier/add') ?>"class="btn btn-primary btn-flat">
                <i class="fa fa-user-plus"></i> Tambah
            </a>
        </div>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-bordered table-striped" id="table1">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Phone</th>
                    <th>Alamat</th>
                    <th>Keterangan</th>
                    <th>Tempo (hari)</th>
                    <th>Saldo Hutang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1;
            foreach($row->result() as $key => $data) { ?>
            <tr>
                <td style="width:5%;"><?=$no++?>.</td>
                <td><?=$data->nama_supplier?></td>
                <td><?=$data->phone?></td>
                <td><?=$data->alamat?></td>
                <td><?=$data->keterangan?></td>
                <td class="text-center"><?=$data->payment_term_days?></td>
                <td class="text-right">Rp <?=number_format($data->ap_balance,0,',','.')?></td>
                <td class="text-center" width="160px">
                        <a href="<?=site_url('supplier/edit/'.$data->supplier_id)?>" class="btn btn-primary btn-xs">
                            <i class="fa fa-pencil"></i> Edit
                        </a>
                        <a href="<?=site_url('supplier/del/'.$data->supplier_id)?>" id="btn-hapus" class="btn btn-danger btn-xs">
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