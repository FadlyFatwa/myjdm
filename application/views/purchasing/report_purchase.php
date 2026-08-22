<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-bar-chart text-primary"></i> Laporan Pembelian</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Pembelian</a></li>
        <li class="active">Laporan</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Laporan Pembelian per Periode</h3>
        </div>
        <div class="box-body">
            <?php
            $qs = http_build_query(['from' => $from, 'to' => $to, 'status' => $status, 'supplier_id' => $supplier_id]);
            ?>
            <form method="get" action="<?= site_url('report-purchase') ?>" class="form-inline" style="margin-bottom:15px;">
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Supplier</label>
                    <select name="supplier_id" class="form-control select2" style="width:200px;">
                        <option value="">- Semua Supplier -</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s->supplier_id ?>" <?= $supplier_id == $s->supplier_id ? 'selected' : '' ?>><?= $s->nama_supplier ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Dari</label>
                    <input type="date" name="from" value="<?= $from ?>" class="form-control">
                </div>
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Sampai</label>
                    <input type="date" name="to" value="<?= $to ?>" class="form-control">
                </div>
                <div class="form-group" style="margin-right:10px;">
                    <label style="margin-right:5px;">Status</label>
                    <select name="status" class="form-control">
                        <option value="" <?= $status === '' ? 'selected' : '' ?>>Semua</option>
                        <option value="lunas" <?= $status === 'lunas' ? 'selected' : '' ?>>Lunas</option>
                        <option value="belum_lunas" <?= $status === 'belum_lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                        <option value="void" <?= $status === 'void' ? 'selected' : '' ?>>Void</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button>
                <a href="<?= site_url('report-purchase/cetak?' . $qs) ?>" target="_blank" class="btn btn-default">
                    <i class="fa fa-print"></i> Cetak PDF
                </a>
                <?php if (in_array($this->session->userdata('level'), [1, 2])): ?>
                <a href="<?= site_url('report-purchase/export?' . $qs) ?>" class="btn btn-success">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </a>
                <?php endif; ?>
            </form>

            <p>
                Total Barang: <strong>Rp <?= number_format($totals['barang'], 0, ',', '.') ?></strong>
            </p>

            <div class="table-responsive">
            <table id="purchaseTable" class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th>No</th>
                        <th>No. PO</th>
                        <th>Supplier</th>
                        <th>No. Invoice Supplier</th>
                        <th>Tgl Terima</th>
                        <th class="text-right">Diskon Invoice</th>
                        <th class="text-right">PPN</th>
                        <th class="text-right">Total Barang</th>
                        <th class="text-center">Cara Bayar</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $status_label = [
                        'outstanding' => ['Belum Lunas', 'warning'],
                        'partial'     => ['Belum Lunas', 'warning'],
                        'paid'        => ['Lunas', 'success'],
                        'void'        => ['Void', 'default'],
                    ];
                    ?>
                    <?php $no = 1; foreach ($rows as $r): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $r->po_number ?></td>
                        <td><?= $r->nama_supplier ?></td>
                        <td><?= $r->supplier_invoice_no ?: '-' ?></td>
                        <td><?= tgl_finance($r->receive_date) ?></td>
                        <td class="text-right"><?= number_format($r->diskon_invoice, 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($r->ppn_nominal, 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($r->total_amount, 0, ',', '.') ?></td>
                        <td class="text-center">
                            <?php if ($r->payment_type): ?>
                            <span class="label label-<?= $r->payment_type === 'cash' ? 'success' : 'default' ?>">
                                <?= $r->payment_type === 'cash' ? 'Cash' : 'Kredit' ?>
                            </span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($r->ap_status): [$label, $class] = $status_label[$r->ap_status] ?? [$r->ap_status, 'default']; ?>
                            <span class="label label-<?= $class ?>"><?= $label ?></span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if (!empty($rows)): ?>
                <tfoot>
                    <tr class="bg-gray">
                        <th colspan="7" class="text-right">Total</th>
                        <th class="text-right"><?= number_format($totals['barang'], 0, ',', '.') ?></th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('.select2').select2();

    var t = $('#purchaseTable').DataTable({
        "columnDefs": [{
            "searchable": false,
            "orderable": false,
            "targets": 0
        }],
        "order": [[4, 'desc']],
        "pageLength": 25
    });

    t.on('order.dt search.dt', function() {
        t.column(0, {search:'applied', order:'applied'}).nodes().each(function(cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();
});
</script>
