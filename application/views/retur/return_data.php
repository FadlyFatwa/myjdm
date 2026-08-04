<section class="content-header">
    <h1>Return List
        <small>List of all returns</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-undo"></i></a></li>
        <li class="active">Return</li>
    </ol>
</section>

<section class="content">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Return Data</h3>
        </div>
        <div class="box-body table-responsive">
            <!-- Tabel Return -->
            <table class="table table-bordered table-striped" id="tabel1">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Invoice</th>
                        <th>Tanggal Retur</th>
                        <th>Pembeli</th>
                        <th>Total Return</th>
                        <th>Note</th>
                        <th>User</th>
                        <th>Actions</th>
                        <th style="display: none;">Detail ID</th> <!-- Kolom detail_id tersembunyi -->
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    foreach ($returns as $data): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $data->invoice ?></td>
                        <td><?= indo_date($data->date) ?></td>
                        <td><?=$data->nama_customer == null ? "Umum" : $data->nama_customer?></td>
                        <td><?= indo_currency($data->total_return_amount) ?></td>
                        <td><?= $data->note ?></td>
                        <td><?= ucfirst($data->nama) ?></td>
                        <td>
                            <button id="detail" data-target="#modal-detail" data-toggle="modal" class="btn btn-default btn-xs"
                                data-invoice="<?=$data->invoice?>"
                                data-date="<?=indo_date($data->date)?>"
                                data-time="<?=substr($data->create, 11, 5)?>"
                                data-customer="<?=$data->nama_customer == null ? "Umum" : $data->nama_customer?>"
                                data-total="<?=indo_currency($data->total_return_amount)?>"
                                data-note="<?=$data->note?>"
                                data-kasir="<?=ucfirst($data->nama)?>"
                                data-return_id="<?=$data->return_id?>">Detail</button>
                                <?php if(in_array($this->fungsi->user_login()->level, [1,2])) { ?>
                                    <a href="<?= site_url('retur/del/' . $data->return_id) ?>" 
                                    id="btn-hapus"
                                    class="btn btn-danger btn-xs">Delete</a>
                                    <?php } ?>
                        </td>
                        <td style="display: none;"><?= $data->detail_id ?></td> <!-- Data detail_id tersembunyi -->
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal Detail -->
<div class="modal fade" id="modal-detail">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Return Details</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body table-responsive">
                <table class="table table-bordered no-margin">
                    <tbody>
                        <tr>
                            <th style="width:20%">Invoice</th>
                            <td style="width:30%"><span id="invoice"></span></td>
                            <th style="width:20%">Customer</th>
                            <td style="width:30%"><span id="cust"></span></td>
                        </tr>
                        <tr>
                            <th>Date Time</th>
                            <td><span id="date"></span></td>
                            <th>Cashier</th>
                            <td><span id="kasir"></span></td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td><span id="total"></span></td>
                            <th>Note</th>
                            <td><span id="note"></span></td>
                        </tr>
                        <tr>
                            <th>Product</th>
                            <td colspan="3"><span id="product"></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inisialisasi DataTables
    $('#tabel1').DataTable({
        "lengthMenu": [5, 10, 25, 50], // Pilihan jumlah data per halaman
        "pageLength": 10, // Default jumlah data per halaman
        "order": [[8, 'asc']], // Urutkan berdasarkan kolom Detail ID (index 8) secara ascending
        "columnDefs": [
            { "targets": [0, 7], "orderable": false }, // Nonaktifkan pengurutan untuk kolom No dan Actions
            { "targets": [8], "visible": false } // Sembunyikan kolom Detail ID
        ]
    });

    // Logika untuk modal detail
    $(document).on('click', '#detail', function() {
        $('#invoice').text($(this).data('invoice'));
        $('#date').text($(this).data('date'));
        $('#cust').text($(this).data('customer'));
        $('#total').text($(this).data('total'));
        $('#note').text($(this).data('note'));
        $('#kasir').text($(this).data('kasir'));

        var product = '<table class="table no-margin">';
        product += '<tr><th>Item</th><th>Price</th><th>Qty</th><th>Total</th></tr>';
        $.getJSON('<?=site_url('retur/sale_product/')?>' + $(this).data('return_id'), function(data) {
            $.each(data, function(key, val) {
                product += '<tr><td>' + val.nama_item + '</td><td>' + val.price_retur + '</td><td>' + val.qty + '</td><td>' + val.total + '</td></tr>';
            });
            product += '</table>';
            $('#product').html(product);
        });
    });
});
</script>