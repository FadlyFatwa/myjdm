<section class="content-header">
    <h1>Produk
        <small>Barang Pending</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-archive"></i></a></li>
        <li class="active">Produk</li>
        <li class="active">Barang Pending</li>
    </ol>
</section>

<section class="content">

    <!-- Flash Message -->
    <div id="flash" data-flash="<?= $this->session->flashdata('success'); ?>"></div>

    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Data Barang Pending</h3>
            <br>
            <small>
                <em>
                    Gunakan <kbd>Ctrl + F</kbd> atau <kbd>F2</kbd> untuk cepat mencari data.
                </em>
            </small>

            <?php if(in_array($this->fungsi->user_login()->level, [1,2,4])) { ?>
                <div class="pull-right">
                    <a href="<?= site_url('item_pending/add') ?>" 
                       class="btn btn-primary btn-flat">
                        <i class="fa fa-plus"></i> Ajukan Barang
                    </a>
                </div>
            <?php } ?>
        </div>

        <div class="box-body table-responsive">
            <table id="table-item-pending"
                   class="table table-striped table-bordered dt-responsive"
                   style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Supplier</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Modal</th>
                        <th>PK</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Tanggal Masuk</th>
                        <th>Pengaju</th>
                        <th>Status</th>
                        <?php if (in_array($this->fungsi->user_login()->level, [1,2,4])) { ?>
                            <th>Aksi</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data akan dimuat via DataTables -->
                </tbody>
            </table>
        </div>
    </div>


<div class="modal fade" id="modalDetail">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header bg-info">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Detail Barang Pending</h4>
</div>

<div class="modal-body">
    <div class="row">

        <div class="col-md-6">
            <p><strong>Nama Item :</strong> <span id="d_nama"></span></p>
            <p><strong>Supplier :</strong> <span id="d_supplier"></span></p>
            <p><strong>Kategori :</strong> <span id="d_kategori"></span></p>
            <p><strong>Unit :</strong> <span id="d_unit"></span></p>
            <p><strong>Modal :</strong> <span id="d_modal"></span></p>
            <p><strong>PK :</strong> <span id="d_pk"></span></p>
            <p><strong>Harga:</strong> <span id="d_price"></span></p>
        </div>

        <div class="col-md-6">
            <p><strong>Qty Masuk :</strong> <span id="d_qty"></span></p>
            <p><strong>Tanggal Masuk :</strong> <span id="d_date"></span></p>
            <p><strong>Status :</strong> <span id="d_status"></span></p>
            <hr>
            <p><strong>Diajukan Oleh :</strong> <span id="d_created_by"></span></p>
            <p><strong>Diajukan Pada :</strong> <span id="d_created_at"></span></p>
            <hr>
            <p><strong>Diprint Oleh :</strong> <span id="d_printed_by"></span></p>
            <p><strong>Diprint Pada :</strong> <span id="d_printed_at"></span></p>
            <hr>
            <p><strong>Ditempel Oleh:</strong> <span id="d_attached_by"></span></p>
            <p><strong>Ditempel Pada :</strong> <span id="d_attached_at"></span></p>
        </div>

    </div>

    <hr>
    <div class="text-center" id="d_photo"></div>

</div>

</div>
</div>
</div>

<div class="modal fade" id="modalEdit">
<div class="modal-dialog">
<div class="modal-content">

<form id="formEdit">
<div class="modal-header bg-primary">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Edit Barang Pending</h4>
</div>

<div class="modal-body">

    <input type="hidden" name="pending_id" id="e_id">

    <!-- Nama -->
    <div class="form-group">
        <label>Nama Barang</label>
        <input type="text" name="nama_item" id="e_nama"
               class="form-control" required>
    </div>

    <!-- SUPPLIER -->
    <div class="form-group">
        <label>Supplier *</label>
        <?= form_dropdown(
            'supplier',
            $supplier,
            null,
            [
                'class' => 'form-control',
                'id' => 'e_supplier',
                'required' => 'required'
            ]
        ) ?>
    </div>

    <!-- KATEGORI -->
    <div class="form-group">
        <label>Kategori *</label>
        <?= form_dropdown(
            'category',
            $category,
            null,
            [
                'class' => 'form-control',
                'id' => 'e_category',
                'required' => 'required'
            ]
        ) ?>
    </div>

    <!-- UNIT -->
    <div class="form-group">
        <label>Unit *</label>
        <?= form_dropdown(
            'unit',
            $unit,
            null,
            [
                'class' => 'form-control',
                'id' => 'e_unit',
                'required' => 'required'
            ]
        ) ?>
    </div>


    <!-- Modal & PK -->
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Modal *</label>
                <input type="text"
                    name="modal"
                    id="e_modal"
                    class="form-control modalInput"
                    required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label>PK *</label>
                <input type="text"
                    name="pk"
                    id="e_pk"
                    class="form-control"
                    readonly>
            </div>
        </div>
    </div>


    <!-- Harga (LOCKED) -->
    <div class="form-group">
        <label>Harga (Tidak Bisa Diubah)</label>
        <input type="text"
               id="e_price"
               class="form-control"
               readonly>
    </div>

    <!-- Qty -->
    <div class="form-group">
        <label>Qty</label>
        <input type="number"
               name="qty"
               id="e_qty"
               class="form-control"
               required>
    </div>

    <!-- Tanggal -->
    <div class="form-group">
        <label>Tanggal</label>
        <input type="date"
               name="stock_date"
               id="e_date"
               class="form-control"
               required>
    </div>

</div>

<div class="modal-footer">
    <button type="submit" class="btn btn-success">
        Simpan Perubahan
    </button>
</div>

</form>
</div>
</div>
</div>


<section>

<script>

$(document).ready(function() {
    let flash = $('#flash').data('flash');
    if(flash){
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: flash
    });
}

    $('#table-item-pending').DataTable({
        stateSave: true,
        stateDuration: 120,
        serverSide: true,
        processing: true,
        order: [],
        ajax: {
            url: "<?= site_url('item_pending/get_json') ?>",
            type: "POST"
        },
        columns: [
            { data: "no", className: "text-center", orderable:false },
            { data: "nama_item" },
            { data: "nama_supplier" },
            { data: "nama_category" },
            { data: "nama_unit" },
            { data: "modal", className: "text-right" },
            { data: "pk" },
            { data: "price", className: "text-right" },
            { data: "qty", className: "text-center" },
            { data: "stock_date" },
            { data: "user_name" },
            { data: "status", orderable:false },
            <?php if (in_array($this->fungsi->user_login()->level, [1,2,4])) { ?>
                { data: "action", orderable:false, searchable:false }
            <?php } ?>
        ]
    });

    // Shortcut Ctrl+F atau F2
    $(window).on('keydown', function(e) {
        if ((e.ctrlKey && e.keyCode === 70) || e.keyCode === 113) {
            e.preventDefault();
            var $searchInput = $('input[aria-controls="table-item-pending"]');
            if ($searchInput.length) {
                $searchInput.focus().select();
            }
        }
    });

    $(document).on('focus', 'input[aria-controls="table-item-pending"]', function () {
        $(this).select();
    });

});


// DETAIL
$(document).on('click','.btn-detail',function(){

    let id = $(this).data('id');

    $.get("<?= site_url('item_pending/get_detail/') ?>"+id,
        function(data){

        let d = JSON.parse(data);

        $('#d_nama').text(d.nama_item);
        $('#d_supplier').text(d.nama_supplier);
        $('#d_kategori').text(d.nama_category);
        $('#d_unit').text(d.nama_unit);
        $('#d_modal').text(d.modal);
        $('#d_pk').text(d.pk);
        $('#d_price').text(d.price);
        $('#d_qty').text(d.qty);
        $('#d_date').text(d.stock_date);

        $('#d_status').html('<span class="badge bg-info">'+d.status+'</span>');
        $('#d_created_by').text(d.created_name ?? '-');
        $('#d_created_at').text(d.created_at ?? '-');
        $('#d_printed_by').text(d.printed_name ?? '-');
        $('#d_printed_at').text(d.printed_at ?? '-');
        $('#d_attached_by').text(d.attached_name ?? '-');
        $('#d_attached_at').text(d.attached_at ?? '-');

        if(d.photo){
            $('#d_photo').html(
                '<img src="<?= base_url('uploads/item_pending/') ?>'
                + d.photo +
                '" class="img-responsive" style="max-height:250px;">'
            );
        } else {
            $('#d_photo').html('<em>Tidak ada foto</em>');
        }

        $('#modalDetail').modal('show');
    });

});


// EDIT
$(document).on('click','.btn-edit',function(){
    let id = $(this).data('id');

   $.get("<?= site_url('item_pending/get_edit/') ?>"+id,
        function(data){

        let d = JSON.parse(data);

        $('#e_id').val(d.pending_id);
        $('#e_nama').val(d.nama_item);
        $('#e_supplier').val(d.supplier_id).trigger('change');
        $('#e_category').val(d.category_id).trigger('change');
        $('#e_unit').val(d.unit_id).trigger('change');

        $('#e_modal').val(formatNumber(d.modal.toString()));
        $('#e_pk').val(d.pk);
        $('#e_price').val(formatNumber(d.price.toString()));
        $('#e_qty').val(d.qty);
        $('#e_date').val(d.stock_date);

        $('#modalEdit').modal('show');
    });

});

$('#formEdit').submit(function(e){

    e.preventDefault();

    let raw=$('#e_modal').val().replace(/\./g,'');
    $('#e_modal').val(raw);

    $.post("<?= site_url('item_pending/update') ?>",
        $(this).serialize(),
        function(){

            $('#modalEdit').modal('hide');

            Swal.fire({
                icon:'success',
                title:'Berhasil',
                text:'Data diperbarui'
            }).then(()=>{
                $('#table-item-pending').DataTable().ajax.reload();
            });
        });
});



function formatNumber(input) {
    return input.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function unformatNumber(input) {
    return input.replace(/\./g, "");
}

// Auto format modal
$(document).on('input', '#e_modal', function () {

    let value = $(this).val();
    let raw = unformatNumber(value);
    $(this).val(formatNumber(raw));

    // Generate PK
    $('#e_pk').val(generatePK(raw));
});

// Function generate PK (SAMA seperti form utama)
function generatePK(value) {

    let mapping = {
        '0': 'Y', '1': 'S', '2': 'I', '3': 'T', '4': 'O',
        '5': 'M', '6': 'P', '7': 'U', '8': 'L', '9': 'X'
    };

    let zeroCount = 0;
    let pkOutput = '';

    for (let i = 0; i < value.length; i++) {
        let char = value.charAt(i);

        if (char === '0') {
            zeroCount++;
        } else {
            if (zeroCount > 1) {
                pkOutput += 'Y' + zeroCount;
            } else if (zeroCount === 1) {
                pkOutput += 'Y';
            }
            zeroCount = 0;
            pkOutput += mapping[char] || char;
        }
    }

    if (zeroCount > 1) {
        pkOutput += 'Y' + zeroCount;
    } else if (zeroCount === 1) {
        pkOutput += 'Y';
    }

    return pkOutput;
}

// Init Select2 saat modal dibuka
$('#modalEdit').on('shown.bs.modal', function () {

    $('#e_supplier').select2({
        dropdownParent: $('#modalEdit'),
        placeholder: 'Pilih Supplier',
        allowClear: true,
        width: '100%'
    });

    $('#e_category').select2({
        dropdownParent: $('#modalEdit'),
        placeholder: 'Pilih Kategori',
        allowClear: true,
        width: '100%'
    });

    $('#e_unit').select2({
        dropdownParent: $('#modalEdit'),
        placeholder: 'Pilih Unit',
        allowClear: true,
        width: '100%'
    });

});

// $('#e_supplier').val(d.supplier_id).trigger('change');
// $('#e_category').val(d.category_id).trigger('change');
// $('#e_unit').val(d.unit_id).trigger('change');
// $('#e_modal').val(d.modal_raw);
// $('#e_pk').val(d.pk);
// $('#e_price').val(d.price);
// $('#e_qty').val(d.qty);
// $('#e_date').val(d.stock_date_raw);

// APPROVE
$(document).on('click','.btn-approve',function(){

    let id = $(this).data('id');

    Swal.fire({
        title: 'Approve Barang?',
        text: "Barang akan masuk ke data item & stok awal.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Approve'
    }).then((result) => {

        if (result.isConfirmed) {

            $.post("<?= site_url('item_pending/approve/') ?>"+id,
                function(){
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Barang berhasil di-approve'
                    }).then(() => {
                        $('#table-item-pending').DataTable().ajax.reload();
                    });
                });

        }
    });

});


// PRINT
$(document).on('click','.btn-print',function(){
    let id = $(this).data('id');

    Swal.fire({
        title: 'Sudah Print Barcode?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya'
    }).then((result) => {

        if(result.isConfirmed){

            $.post("<?= site_url('item_pending/mark_printed/') ?>"+id,
                function(res){

                    Swal.fire('Berhasil', res.message, 'success')
                        .then(() => {
                            $('#table-item-pending').DataTable().ajax.reload();
                        });

                }, 'json');

        }
    });
});




// ATTACH
$(document).on('click','.btn-attach',function(){

    let id = $(this).data('id');

    Swal.fire({
        title: 'Barcode Sudah Ditempel?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Sudah Ditempel'
    }).then((result) => {

        if (result.isConfirmed) {

            $.post("<?= site_url('item_pending/mark_attached/') ?>"+id,
                function(){
                    Swal.fire('Berhasil','Status menjadi Sudah Ditempel','success');
                    $('#table-item-pending').DataTable().ajax.reload();
                });

        }
    });

});

</script>
