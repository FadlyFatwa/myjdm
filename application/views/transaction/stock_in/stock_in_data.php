<section class = "content-header">
    <h1>Update Stock
        <small>Stock Datang Barang</small>
</h1>
    <ol class="breadcrumb">
    <li><a href = "#"><i class="fa fa-archive"></i></a></li>
        <li class="active">Produk</li>
        <li class="active">Stock in</li>
    </ol>
</section>

<section class="content">
<div id="flash" data-flash="<?=$this->session->flashdata('success');?>"></div>
<div class="box">
        <div class="box-header">
            <div class="pull-left">
                <!-- Tombol "Pilih Beberapa" dipindahkan ke sebelah kiri -->
                <button id="btn-select-multiple" class="btn btn-info btn-flat">
                    <i class="fa fa-check-square-o"></i> Pilih Beberapa
                </button>
            </div>
            <div class="pull-right">
                <!-- Tombol "Tambah" tetap di sebelah kanan -->
                <a href="<?=site_url('stock/in/add') ?>" class="btn btn-primary btn-flat">
                    <i class="fa fa-plus"></i> Tambah
                </a>
            </div>
        </div>
        <small><em>Gunakan <kbd>Ctrl + F</kbd> atau <kbd>F2</kbd> untuk cepat mencari data.</em></small>
    <div class="box-body table-responsive">
        <div class="table-responsive">
            <table id="table-stock" class="table table-striped table-bordered dt-responsive " style="width:100%">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="check-all">
                    </th>
                    <th>#</th>
                    <th>Barcode</th>
                    <th>Nama Barang</th>
                    <th>Supplier</th>
                    <th>Qty</th>
                    <th>Info</th>
                    <th>Date</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <!-- <?php $no = 1;
            foreach($row as $key => $data) { ?>
            <tr>
                <td style="width:5%;"><?=$no++?>.</td>
                <td>
                    <?=$data->barcode?><br>
                    <a href="<?=site_url('stock/barcode_qrcode/'.$data->item_id.'/'.$data->stock_id.'? back_from=stock_in')?>" class="btn btn-default btn-xs">
                           Print Barcode <i class="fa fa-barcode"></i>
                        </a>
                </td>
                <td><?=$data->nama_item?></td>
                <td><?=$data->nama_supplier?></td>
                <td><?=$data->qty?></td>
                <td><?=$data->detail?></td>
                <td><?=indo_date($data->date)?></td>
                <td class="text-center" width="160px">        
                <a href="<?=site_url('stock/in/edit/'.$data->stock_id.'/'.$data->item_id)?>" class="btn btn-primary btn-xs">
                            <i class="fa fa-pencil"></i> Edit
                        </a>
                        <a href="<?=site_url('stock/in/del/'.$data->stock_id.'/'.$data->item_id)?>" id="btn-hapus" class="btn btn-danger btn-xs">
                            <i class="fa fa-trash"></i> Hapus
                        </a>
                </td>
            </tr>
            <?php
            } ?> -->
            </tbody>
        </table>
    </div>
 </div>

</section>
<script>    
$(document).ready(function () {
    let isSelectionMode = false;

    // Inisialisasi DataTables
    const table = $("#table-stock").DataTable({
        serverSide: true,
        responsive: true,
        stateSave: true, // Ini akan menyimpan state tabel termasuk pencarian
        stateDuration: 120, // Simpan state secara permanen
        autoWidth: false,
        order: [],
        ajax: {
            url: "<?=site_url('stock/get_json')?>",
            type: "POST"
        },
        columns: [
            { data: "checkbox", width: 40, searchable: false, orderable: false, className: "text-center checkbox-column", visible: false },
            { data: "no", width: 40, searchable: false, orderable: false, className: "text-center" },
            { data: "barcode", width: 20 },
            { data: "nama_item", width: 500 },
            { data: "nama_supplier", width: 200 },
            { data: "qty", width: 60 },
            { data: "detail", width: 40 },
            { data: "date", width: 100 },
            { data: "action", width: 150, searchable: false, orderable: false, className: "text-center" }
        ]
    });

    // Tombol "Pilih Beberapa"
    $('#btn-select-multiple').on('click', function () {
        isSelectionMode = !isSelectionMode;
        table.column(0).visible(isSelectionMode);

        if (isSelectionMode) {
            $(this)
                .html('<i class="fa fa-times"></i> Batal')
                .removeClass('btn-info')
                .addClass('btn-danger');

            $('.box-header .pull-left').append(`
                <button id="btn-print-barcode" class="btn btn-success btn-flat" style="margin-left: 10px;">
                    <i class="fa fa-barcode"></i> Print Barcode Terpilih
                </button>
            `);
        } else {
            $(this)
                .html('<i class="fa fa-check-square-o"></i> Pilih Beberapa')
                .removeClass('btn-danger')
                .addClass('btn-info');

            $('#btn-print-barcode').remove();
            $('#check-all').prop('checked', false);
            $('.check-item').prop('checked', false);
        }
    });

    // Event Handler untuk Checkbox Utama ("Pilih Semua")
    $(document).on('change', '#check-all', function () {
        $('.check-item').prop('checked', this.checked);
    });

    // Event Handler untuk Checkbox Baris
    $(document).on('change', '.check-item', function () {
        const allChecked = $('.check-item:checked').length === $('.check-item').length;
        $('#check-all').prop('checked', allChecked);
    });

    // Tombol "Print Barcode Terpilih"
    $(document).on('click', '#btn-print-barcode', function () {
        const selectedIds = $('.check-item:checked').map(function () {
            return this.value;
        }).get();

        if (selectedIds.length === 0) {
            alert('Silakan pilih minimal satu item untuk mencetak barcode.');
            return;
        }

        window.location.href = "<?=site_url('barcode/barcode_qrcode_multiple')?>?ids=" + selectedIds.join(',');
    });
});

$(document).ready(function() {
        // Tambahkan event listener untuk shortcut keyboard
        $(window).on('keydown', function(e) {
            // Ctrl + F = 70, F2 = 113
            if ((e.ctrlKey && e.keyCode === 70) || e.keyCode === 113) {
                e.preventDefault();

                // Cari input pencarian menggunakan atribut aria-controls
                var $searchInput = $('input[aria-controls="table-stock"]');

                if ($searchInput.length) {
                    $searchInput.focus().select(); // Fokus dan pilih teks
                    console.log("Input pencarian ditemukan dan difokuskan");
                } else {
                    console.warn("Input pencarian tidak ditemukan");
                }
            }
        });

        // Opsional: auto-select saat pengguna klik input pencarian
        $(document).on('focus', 'input[aria-controls="table-stock"]', function () {
            $(this).select();
        });
    });
</script>