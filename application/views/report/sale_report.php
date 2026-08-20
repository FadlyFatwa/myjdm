<section class="content-header">
    <h1>
        <i class="fa fa-chart-line text-primary"></i> Laporan Penjualan
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-history"></i> Riwayat</a></li>
        <li class="active">Penjualan</li>
    </ol>
</section>

<section class="content">
    <div id="flash" data-flash="<?=$this->session->flashdata('success');?>" data-flash-error="<?=$this->session->flashdata('error');?>"></div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filter Data</h3>
        </div>
        <div class="box-body">
            <form method="post">
                <div class="row">
                    <div class="col-md-2">
                        <label>Tgl Awal</label>
                        <input type="date" name="date1" value="<?=@$post['date1'] ?? date('Y-m-d')?>" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label>Tgl Akhir</label>
                        <input type="date" name="date2" value="<?=@$post['date2'] ?? date('Y-m-d')?>" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>Pembeli</label>
                        <select name="customer" class="form-control select2">
                            <option value="">- Semua -</option>
                            <option value="null" <?=@$post['customer']=='null'?'selected':''?>>Umum</option>
                            <?php foreach($customer as $data) { ?>
                                <option value="<?=$data->customer_id?>" <?=@$post['customer']==$data->customer_id?'selected':''?>>
                                    <?=$data->nama_customer?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Invoice</label>
                        <input type="text" name="invoice" value="<?=@$post['invoice']?>" class="form-control" placeholder="Cari invoice...">
                    </div>
                    <div class="col-md-2" style="margin-top:25px">
                        <button type="submit" name="filter" class="btn btn-primary btn-block">
                            <i class="fa fa-search"></i> Filter
                        </button>
                    </div>
                </div>
                <div class="text-right" style="margin-top:10px;">
                    <button type="submit" name="reset" class="btn btn-default btn-sm">
                        <i class="fa fa-refresh"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <?php 
            $total_omzet = 0;
            foreach($row->result() as $r){ $total_omzet += $r->final_price; }
        ?>
        <div class="col-md-6">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3><?=$row->num_rows()?></h3>
                    <p>Total Transaksi</p>
                </div>
                <div class="icon"><i class="fa fa-receipt"></i></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3><?=indo_currencyex($total_omzet)?></h3>
                    <p>Total</p>
                </div>
                <div class="icon"><i class="fa fa-money"></i></div>
            </div>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> Data Penjualan</h3>
            <div class="pull-right">
                <a href="<?= site_url('report/print_pdf') ?>" target="_blank" class="btn btn-danger btn-sm">
                    <i class="fa fa-file-pdf-o"></i> Export PDF
                </a>
            </div>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-striped" id="salesTable">
                <thead class="bg-primary">
                    <tr>
                        <th width="4%">No</th>
                        <th>Invoice</th>
                        <th width="10%">Tanggal</th>
                        <th>Pembeli</th>
                        <th class="text-right">Grand Total</th>
                        <th width="9%" class="text-center">Metode</th>
                        <th class="text-center" width="10%">Status</th>
                        <th class="text-center" width="9%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($row->result() as $data) : ?>
                    <tr<?= !empty($data->is_cancelled) ? ' style="opacity:.55"' : '' ?>>
                        <td class="text-center"></td>
                        <td><?=$data->invoice?></td>
                        <td><?=indo_date($data->date)?></td>
                       <td>
                            <?php 
                                // 1. Cek apakah ada nama member dari JOIN (p_customer)
                                if (!empty($data->nama_member)) {
                                    echo $data->nama_member; 
                                } else {
                                    // 2. Jika tidak ada member, tampilkan "Umum" diikuti nama_customer dari t_sale
                                    // Kita gunakan !empty untuk memastikan jika customer_name ada isinya
                                    echo "Umum | " . $data->customer_name;
                                }
                            ?>
                        </td>
                        <td class="text-right"><?=indo_currencyex($data->final_price)?></td>
                        <td class="text-center">
                            <?php
                                $m  = $data->payment_method;
                                $mc = $m === 'cash' ? 'success' : ($m === 'transfer' ? 'info' : ($m === 'qris' ? 'primary' : ($m === 'debit' ? 'default' : 'warning')));
                            ?>
                            <span class="label label-<?= $mc ?>"><?= strtoupper($m) ?></span>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($data->is_cancelled)): ?>
                            <span style="display:inline-block; padding:4px 10px; border-radius:12px;
                                         font-size:11px; font-weight:700; background:#6b7280; color:#fff"
                                  title="<?= $data->cancel_reason ? htmlspecialchars($data->cancel_reason) : 'Transaksi dibatalkan' ?>">
                                <i class="fa fa-ban"></i> Dibatalkan
                            </span>
                            <?php else: $lunas = $data->payment_status === 'lunas'; ?>
                            <span class="badge-status"
                                  data-id="<?= $data->sale_id ?>"
                                  data-status="<?= $data->payment_status ?>"
                                  style="cursor:pointer; display:inline-block; padding:4px 10px;
                                         border-radius:12px; font-size:11px; font-weight:700;
                                         background:<?= $lunas ? '#00a65a' : '#f39c12' ?>;
                                         color:#fff;"
                                  title="Klik untuk ubah status">
                                <i class="fa fa-<?= $lunas ? 'check' : 'clock-o' ?>"></i>
                                <?= $lunas ? 'Lunas' : 'Belum Lunas' ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                    Aksi <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li>
                                        <a href="#" class="btn-detail-sale" data-id="<?=$data->sale_id?>">
                                            <i class="fa fa-eye"></i> Detail
                                        </a>
                                    </li>
                                    <li><a href="<?=site_url('sale/preview/'.$data->sale_id)?>?from=report"><i class="fa fa-print"></i> Print</a></li>
                                    <?php if (empty($data->is_cancelled)): ?>
                                    <li><a href="<?=site_url('retur/add/'.$data->sale_id)?>"><i class="fa fa-undo"></i> Retur</a></li>
                                    <?php endif; ?>

                                    <?php if(in_array($this->fungsi->user_login()->level, ['1','2'])) { ?>
                                        <li class="divider"></li>
                                        <?php if (empty($data->is_cancelled)): ?>
                                        <li><a href="<?=site_url('sale/edit/'.$data->sale_id)?>"><i class="fa fa-edit"></i> Edit</a></li>
                                        <li>
                                            <a href="<?=site_url('sale/del/'.$data->sale_id)?>" class="btn-cancel-sale text-red">
                                                <i class="fa fa-ban"></i> Batalkan Transaksi
                                            </a>
                                        </li>
                                        <?php else: ?>
                                        <li>
                                            <a href="<?=site_url('sale/reactivate/'.$data->sale_id)?>" class="btn-reactivate-sale" style="color:#00a65a">
                                                <i class="fa fa-undo"></i> Aktifkan Kembali
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    <?php } ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="modal-detail">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-info-circle"></i> Detail Penjualan</h4>
            </div>
            <div id="modal-content-detail" class="modal-body">
                </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inisialisasi DataTable
    var t = $('#salesTable').DataTable({
        "columnDefs": [{
            "searchable": false,
            "orderable": false,
            "targets": 0
        }],
        "order": [[1, 'desc']],
        "pageLength": 10
    });

    // Nomor urut dinamis
    t.on('order.dt search.dt', function() {
        t.column(0, {search:'applied', order:'applied'}).nodes().each(function(cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();

    // Dropdown "Aksi" — buka ke atas otomatis kalau ruang di bawah tidak cukup,
    // supaya baris paling bawah tidak perlu di-scroll untuk lihat semua opsi
    $(document).on('show.bs.dropdown', '.btn-group', function () {
        var $group  = $(this);
        var $toggle = $group.find('[data-toggle="dropdown"]');
        var $menu   = $group.find('.dropdown-menu');

        $group.removeClass('dropup');

        $menu.css('display', 'block');
        var menuHeight = $menu.outerHeight();
        $menu.css('display', '');

        var spaceBelow = window.innerHeight - $toggle.get(0).getBoundingClientRect().bottom;

        if (spaceBelow < menuHeight + 10) {
            $group.addClass('dropup');
        }
    });

    // Klik badge status → toggle lunas/belum lunas
    $(document).on('click', '.badge-status', function() {
        var $badge    = $(this);
        var sale_id   = $badge.data('id');
        var curStatus = $badge.data('status');
        var newStatus = curStatus === 'lunas' ? 'belum lunas' : 'lunas';
        var label     = newStatus === 'lunas' ? 'Lunas' : 'Belum Lunas';

        Swal.fire({
            title  : 'Ubah Status Pembayaran?',
            html   : 'Ganti ke <b>' + label + '</b>?',
            icon   : 'question',
            showCancelButton    : true,
            confirmButtonText   : 'Ya, Ubah',
            cancelButtonText    : 'Batal',
            confirmButtonColor  : newStatus === 'lunas' ? '#00a65a' : '#f39c12'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $.post('<?= site_url('sale/update_status') ?>', {
                sale_id        : sale_id,
                payment_status : newStatus
            }, function(res) {
                if (res.redirect) {
                    // Transaksi ini sudah dikelola modul Piutang (AR) — arahkan ke sana
                    window.location.href = res.redirect;
                    return;
                }
                if (res.success) {
                    var isLunas = newStatus === 'lunas';
                    $badge.data('status', newStatus)
                          .css('background', isLunas ? '#00a65a' : '#f39c12')
                          .html('<i class="fa fa-' + (isLunas ? 'check' : 'clock-o') + '"></i> ' + label);

                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success',
                        title: 'Status diperbarui', showConfirmButton: false, timer: 2000
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal mengubah status', text: res.message || '' });
                }
            }, 'json');
        });
    });

    // Batalkan transaksi — stok dikembalikan, piutang terkait (kalau ada) di-void
    $(document).on('click', '.btn-cancel-sale', function(e) {
        e.preventDefault();
        var link = $(this).attr('href');

        Swal.fire({
            title: 'Batalkan transaksi ini?',
            html : 'Stok barang akan <b>dikembalikan</b>.<br>Kalau transaksi ini punya piutang yang belum dibayar, piutangnya juga otomatis di-void.<br>Tindakan ini tidak bisa dibatalkan.',
            icon : 'warning',
            showCancelButton  : true,
            confirmButtonText : 'Ya, batalkan',
            cancelButtonText  : 'Tidak',
            confirmButtonColor: '#dd4b39',
        }).then(function(result) {
            if (!result.isConfirmed) return;
            var $form = $('<form>', {method: 'POST', action: link});
            $form.append($('<input>', {type: 'hidden', name: 'csrf_token', value: $('meta[name="csrf-token"]').attr('content')}));
            $('body').append($form);
            $form.submit();
        });
    });

    // Aktifkan kembali transaksi yang sudah dibatalkan
    $(document).on('click', '.btn-reactivate-sale', function(e) {
        e.preventDefault();
        var link = $(this).attr('href');

        Swal.fire({
            title: 'Aktifkan kembali transaksi ini?',
            html : 'Stok barang akan <b>dikurangi lagi</b> sesuai isi transaksi.<br>Kalau transaksi ini punya piutang yang sempat di-void otomatis, piutangnya juga akan diaktifkan kembali.',
            icon : 'question',
            showCancelButton  : true,
            confirmButtonText : 'Ya, aktifkan',
            cancelButtonText  : 'Tidak',
            confirmButtonColor: '#00a65a',
        }).then(function(result) {
            if (!result.isConfirmed) return;
            var $form = $('<form>', {method: 'POST', action: link});
            $form.append($('<input>', {type: 'hidden', name: 'csrf_token', value: $('meta[name="csrf-token"]').attr('content')}));
            $('body').append($form);
            $form.submit();
        });
    });

    // AJAX Detail Lengkap
    $(document).on('click', '.btn-detail-sale', function(e) {
        e.preventDefault();
        var sale_id = $(this).data('id');
        
        $('#modal-detail').modal('show');
        $('#modal-content-detail').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Loading Detail...</div>');

        // Memanggil controller yang me-load view sale_detail_modal
        $.get("<?=site_url('report/sale_detail_ajax/')?>" + sale_id, function(data) {
            $('#modal-content-detail').html(data);
        });
    });
});
</script>