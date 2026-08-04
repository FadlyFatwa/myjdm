<section class="content-header">
    <h1>Stock In Report
        <small>Laporan Stok Masuk</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i></a></li>
        <li><a href="#">Laporan</a></li>
    </ol>
</section>
 
<section class="content">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Filter Data</h3>
        </div>
        <div class="box-body">
            <form action="" method="post">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Tanggal</label>
                                <div class="col-sm-9">
                                    <input type="date" name="date1" value="<?=@$post['date1']?>" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">s/d</label>
                                <div class="col-sm-9">
                                    <input type="date" name="date2" value="<?=@$post['date2']?>" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Supplier</label>
                                <div class="col-sm-9">
                                    <select name="supplier" class="form-control">
                                        <option value="">- All -</option>
                                        <?php foreach($supplier as $spl => $data) { ?>
                                            <option value="<?=$data->supplier_id?>" <?=@$post['supplier'] == $data->supplier_id ? 'selected' : ''?>><?=$data->nama_supplier?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="pull-right">
                            <button type="submit" name="reset" class="btn btn-flat">Reset</button>
                            <button type="submit" name="filter" class="btn btn-info btn-flat">
                                <i class="fa fa-search"></i> Filter
                            </button>
                            <button> <a href="<?= site_url('report/print_stock_pdf') ?>" target="_blank" class="btn btn-info btn-flat">
                                <i class="fa fa-print"></i> Print PDF
                            </a>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Data Stok Masuk</h3>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Detail</th>
                        <th>Qty</th>
                        <th>Cost Price</th>
                        <th>Subtotal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $no = $this->uri->segment(3) ? $this->uri->segment(3) + 1 : 1;
                $grand_total = 0;
                foreach($row->result() as $key => $data) { 
                    $grand_total += $data->subtotal;
                    ?>
                    <tr>
                        <td style="width:5%;"><?=$no++?>.</td>
                        <td><?=indo_date($data->date)?></td>
                        <td class="hidden"><?=$data->nama_item?></td>
                        <td><?=$data->nama_supplier?></td>
                        <td><?=$data->detail?></td>
                        <td class="text-right"><?=$data->qty?></td>
                        <td class="text-right"><?=indo_currency($data->modal)?></td>
                        <td class="text-right"><?=indo_currency($data->subtotal)?></td>
                        <td class="text-center" width="200px">
                            <button id="detail" data-target="#modal-detail" data-toggle="modal" class="btn btn-default btn-xs"
                            data-date="<?=indo_date($data->date)?>"
                            data-nama_supplier="<?=$data->nama_supplier?>"
                            data-nama_item="<?=$data->nama_item?>"
                            data-detail="<?=$data->detail?>"
                            data-qty="<?=$data->qty?>"
                            data-costprice="<?=indo_currency($data->modal)?>"
                            data-subtotal="<?=indo_currency($data->subtotal)?>">Detail</button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="pull-right">
                <h3>Grand Total: <?=indo_currency($grand_total)?></h3>
            </div>
        </div>
    </div>
</section>
 
<div class="modal fade" id="modal-detail">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Stock In Detail</h4>
            </div>
            <div class="modal-body table-responsive">
                <table class="table table-bordered no-margin">
                    <tbody>
                        <tr>
                            <th style="width:20%">Date</th>
                            <td style="width:30%"><span id="date"></span></td>
                            <th style="width:20%">Supplier</th>
                            <td style="width:30%"><span id="nama_supplier"></span></td>
                        </tr>
                        <tr>
                            <th>Nama Barang</th>
                            <td><span id="nama_item"></span></td>
                            <th>Detail</th>
                            <td><span id="detail"></span></td>
                        </tr>
                        <tr>
                            <th>Qty</th>
                            <td><span id="qty"></span></td>
                            <th>Cost Price</th>
                            <td><span id="costprice"></span></td>
                        </tr>
                        <tr>
                            <th>Subtotal</th>
                            <td colspan="3"><span id="subtotal"></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
 
<script>
$(document).on('click', '#detail', function() {
    $('#date').text($(this).data('date'))
    $('#nama_supplier').text($(this).data('nama_supplier'))
    $('#detail').text($(this).data('detail'))
    $('#nama_item').text($(this).data('nama_item'))
    $('#qty').text($(this).data('qty'))
    $('#costprice').text($(this).data('costprice'))
    $('#subtotal').text($(this).data('subtotal'))
})
</script>
