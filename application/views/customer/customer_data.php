<section class = "content-header">
    <h1>Pembeli
        <small>Daftar Pembeli</small>
</h1>
    <ol class="breadcrumb">
        <li><a href = "#"><i class="fa fa-users"></i></a></li>
        <li class="active">Pembeli</li>
    </ol>
</section>

<section class="content">
<div id="flash" data-flash="<?=$this->session->flashdata('success');?>"></div>
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Data Pembeli</h3>
        <div class="pull-right">
            <a href="<?=site_url('customer/add') ?>"class="btn btn-primary btn-flat">
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
                    <th>Limit Kredit</th>
                    <th>Tempo (hari)</th>
                    <th>Diskon Brutto/Netto</th>
                    <th>Saldo Piutang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1;
            foreach($row->result() as $key => $data) { ?>
            <tr>
                <td style="width:5%;"><?=$no++?>.</td>
                <td><?=$data->nama_customer?></td>
                <td><?=$data->phone?></td>
                <td><?=$data->alamat?></td>
                <td class="text-right">Rp <?=number_format($data->credit_limit,0,',','.')?></td>
                <td class="text-center"><?=$data->payment_term_days?></td>
                <td class="text-center"><?=$data->gross_discount_percent !== null ? number_format($data->gross_discount_percent,2,',','.').' %' : '-'?></td>
                <td class="text-right">Rp <?=number_format($data->ar_balance,0,',','.')?></td>
                <td class="text-center" width="160px">
                        <a href="<?=site_url('customer/edit/'.$data->customer_id)?>" class="btn btn-primary btn-xs">
                            <i class="fa fa-pencil"></i> Edit
                        </a>
                        <a href="<?=site_url('customer/del/'.$data->customer_id)?>" id="btn-hapus" class="btn btn-danger btn-xs">
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