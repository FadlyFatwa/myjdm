<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1>
        <i class="fa fa-file-invoice text-primary"></i> Laporan Pajak (PPN)
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Report</a></li>
        <li class="active">Tax Report</li>
    </ol>
</section>

<section class="content">

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filter Periode</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <form method="get" action="<?= site_url('report_tax/data') ?>" class="form-inline">
                        <div class="form-group">
                            <label class="mr-2">Pilih Bulan </label>
                            <input type="month" name="period" value="<?= $period ?>" class="form-control mx-sm-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> Tampilkan
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 text-right">
                    <a href="<?= site_url('report_tax/cetak_laporan/'.$period) ?>" target="_blank" class="btn btn-danger">
                        <i class="fa fa-file-pdf-o"></i> Export PDF
                    </a>
                    <a href="<?= site_url('report_tax/export_excel/'.$period) ?>" class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> Export Excel
                    </a>
                    <a href="<?= site_url('report_tax/export_xml/'.$period) ?>" class="btn btn-info">
                        <i class="fa fa-file-code"></i> Export XML
                    </a>
                    <button type="button" class="btn btn-danger" id="btn-hapus-semua">
                        <i class="fa fa-trash"></i> Hapus Semua
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php 
        $total_dpp = 0;
        $total_ppn = 0;
        $total_grand_total = 0;
        foreach($rows as $r) {
            $total_dpp += $r->dpp;
            $total_ppn += $r->ppn;
            $total_grand_total += $r->grand_total;
        }
        ?>
        <div class="col-md-3">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3><?= count($rows) ?></h3>
                    <p>Total Faktur</p>
                </div>
                <div class="icon"><i class="fa fa-list-alt"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3>Rp <?= number_format($total_grand_total) ?></h3>
                    <p>Total Grand Total</p>
                </div>
                <div class="icon"><i class="fa fa-calculator"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3>Rp <?= number_format($total_dpp) ?></h3>
                    <p>Total DPP</p>
                </div>
                <div class="icon"><i class="fa fa-calculator"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>Rp <?= number_format($total_ppn) ?></h3>
                    <p>Total PPN</p>
                </div>
                <div class="icon"><i class="fa fa-money"></i></div>
            </div>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-table"></i> Data Pajak Periode <?= $period ?></h3>
        </div>
        <div class="box-body table-responsive">
            <?php if($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-check"></i> Sukses!</h4>
                    <?= $this->session->flashdata('success') ?>
                </div>
            <?php endif; ?>

            <table class="table table-bordered table-striped" id="table-tax">
                <thead class="bg-primary">
                    <tr>
                        <th width="5%">No</th>
                        <th>Invoice</th>
                        <th>Tanggal</th>
                        <th class="text-center">Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Total Transaksi</th>
                        <th class="text-right">DPP</th>
                        <th class="text-right">PPN (11%)</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; foreach($rows as $r): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><span class="text-bold"><?= $r->invoice ?></span></td>
                        <td><?= date('d-m-Y', strtotime($r->sale_date)) ?></td>
                        <td class="text-center"><?= number_format($r->total_item) ?></td>
                        <td class="text-center"><?= number_format($r->total_qty) ?></td>
                        <td class="text-right"><?= number_format($r->grand_total) ?></td>
                        <td class="text-right text-blue"><?= number_format($r->dpp) ?></td>
                        <td class="text-right text-green"><strong><?= number_format($r->ppn) ?></strong></td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button class="btn btn-xs btn-info btn-detail-tax" data-id="<?= $r->tax_id ?>" title="Detail">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <a href="<?= site_url('report_tax/cetak/'.$r->tax_id) ?>" target="_blank" class="btn btn-xs btn-warning" title="Cetak">
                                    <i class="fa fa-print"></i>
                                </a>
                                <form method="post" action="<?= site_url('report_tax/delete/'.$r->tax_id) ?>" style="display:inline;" class="form-delete-item">
                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                                    <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="modal-tax-detail">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-info-circle"></i> Detail Rincian Pajak</h4>
            </div>
            <div class="modal-body" id="tax-detail-body">
                </div>
        </div>
    </div>
</div>

<form method="post" id="form-hapus-semua" action="<?= site_url('report_tax/delete_all/'.$period) ?>" style="display:none;">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
</form>

<script>
$(document).ready(function(){
    $('#table-tax').DataTable({
        pageLength: 25,
        ordering: true,
        order: [[1, 'asc']],
        language: {
            search: "Cari Faktur:",
            lengthMenu: "Tampilkan _MENU_ data"
        }
    });

    // Konfirmasi hapus per item
    $(document).on('submit', '.form-delete-item', function(e) {
        if (!confirm('Yakin hapus data pajak ini?')) e.preventDefault();
    });

    // Hapus semua periode
    $('#btn-hapus-semua').on('click', function() {
        if (confirm('Yakin hapus SEMUA data pajak periode <?= $period ?>?\nTindakan ini tidak bisa dibatalkan.')) {
            $('#form-hapus-semua').submit();
        }
    });

    $(document).on('click', '.btn-detail-tax', function() {
        let id = $(this).data('id');
        $('#modal-tax-detail').modal('show');
        $('#tax-detail-body').html('<div class="text-center p-3"><i class="fa fa-refresh fa-spin fa-2x"></i><br>Sedang mengambil data...</div>');

        $.get("<?= site_url('report_tax/detail_ajax/') ?>" + id, function(res){
            $('#tax-detail-body').html(res);
        });
    });
});
</script>