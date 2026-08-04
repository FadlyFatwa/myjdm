<section class="content-header">
    <h1>
        Tambah Barang
        <small>Input Multi-Item</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-archive"></i></a></li>
        <li class="active">Produk</li>
        <li class="active">Tambah Multiple</li>
    </ol>
</section>

<section class="content">
    <?php $this->view('massage') ?>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Form Input Barang</h3>
            <div class="pull-right">
                <a href="<?= site_url('item') ?>" class="btn btn-warning btn-flat btn-sm">
                    <i class="fa fa-undo"></i> Kembali
                </a>
            </div>
        </div>

        <div class="box-body clearfix">
            <form action="<?= site_url('item/process') ?>" method="post" id="addItemForm">
                <div class="row" id="itemRows">
                    </div>

                <div class="row">
                    <div class="col-md-12 text-center" style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed #ddd;">
                        <button type="button" class="btn btn-info btn-flat" id="addRow">
                            <i class="fa fa-plus"></i> Tambah Baris (Kartu) Baru
                        </button>
                        <button type="submit" name="add_multiple" class="btn btn-success btn-flat">
                            <i class="fa fa-save"></i> Simpan Semua Barang
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<style>
    /* Menghilangkan potensi double scroll */
    .content { overflow-x: hidden; }
    
    /* Styling Kartu Barang */
    .item-card-container {
        margin-bottom: 20px;
    }
    .box-item-custom {
        border: 1px solid #d2d6de;
        border-top: 3px solid #d2d6de;
        transition: all 0.3s ease;
    }
    .box-item-custom:hover {
        border-color: #3c8dbc;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .bg-custom-header {
        background-color: #f9f9f9;
    }
    /* Memastikan Select2 memenuhi lebar input */
    .select2-container {
        width: 100% !important;
    }
</style>

<script>
$(document).ready(function () {
    let initialBarcode = <?= $new_barcode ?>; 
    let rowCount = 0;

    // Helper: Format Angka Ribuan
    function formatNumber(n) {
        return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Helper: Hapus Titik (Unformat)
    function unformatNumber(n) {
        return n.replace(/\./g, "");
    }

    // Template Generator Kartu Barang
    function generateRow(barcode) {
        rowCount++;
        return `
            <div class="col-md-6 col-lg-4 item-card-container">
                <div class="box box-default box-item-custom">
                    <div class="box-header with-border bg-custom-header">
                        <h3 class="box-title text-blue"><b>#${rowCount}</b></h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool text-red remove-row" title="Hapus Baris">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label>Barcode</label>
                            <input type="text" name="barcode[]" class="form-control barcodeInput" 
                                value="${String(barcode).padStart(5, '0')}" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Nama Barang</label>
                            <input type="text" name="nama_item[]" class="form-control" placeholder="Contoh: Oli MPX 2" required>
                        </div>

                        <div class="form-group">
                            <label>Supplier <span class="text-red">*</span></label>
                            ${<?php echo json_encode(form_dropdown('supplier[]', $supplier, '', ['class' => 'form-control select2 supplierDropdown', 'required' => 'required'])); ?>}
                        </div>

                        <!-- Ref supplier tambahan -->
                        <div class="supplier-ref-list" id="ref-list-${rowCount}"></div>
                        <div style="margin-bottom:10px">
                            <button type="button" class="btn btn-xs btn-default btn-add-ref" data-card="${rowCount}">
                                <i class="fa fa-plus"></i> Tambah Ref Supplier
                            </button>
                        </div>

                        <div class="form-group">
                            <label>Kategori <span class="text-red">*</span></label>
                            ${<?php echo json_encode(form_dropdown('category[]', $category, '', ['class' => 'form-control select2 categoryDropdown', 'required' => 'required'])); ?>}
                        </div>

                        <div class="form-group">
                            <label>Unit (Satuan)</label>
                            ${<?php echo json_encode(form_dropdown('unit[]', $unit, '', ['class' => 'form-control select2 unitDropdown', 'required' => 'required'])); ?>}
                        </div>


                        <div class="row">
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <label>Modal (Beli)</label>
                                    <input type="text" name="modal[]" class="form-control modalInput" placeholder="0" required>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <label>PK (Kode Toko)</label>
                                    <input type="text" name="pk[]" class="form-control pkOutput"  
                                        style="background-color: #eeeeee; font-weight:bold;" tabindex="-1">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Harga Jual</label>
                            <input type="text" name="price[]" class="form-control priceInput" placeholder="0" required>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Inisialisasi Select2
    function initSelect2() {
        $('.select2').select2({
            placeholder: "- Pilih -",
            allowClear: true
        });
    }

    // Tambah Baris Pertama
    $('#itemRows').append(generateRow(initialBarcode));
    initSelect2();

    // Supplier dropdown HTML (untuk ref rows)
    var supplierOptions = <?php
        $opts = '<option value="">— Pilih —</option>';
        foreach ($this->supplier_m->get()->result() as $s) {
            $opts .= '<option value="' . $s->supplier_id . '">' . htmlspecialchars($s->nama_supplier) . '</option>';
        }
        echo json_encode($opts);
    ?>;

    // Tambah ref supplier ke kartu tertentu
    $(document).on('click', '.btn-add-ref', function () {
        var cardId  = $(this).data('card');
        var refHtml = '<div class="ref-row input-group" style="margin-bottom:5px">'
                    + '<select name="supplier_refs[' + cardId + '][]" class="form-control select2-ref">'
                    + supplierOptions + '</select>'
                    + '<span class="input-group-btn">'
                    + '<button type="button" class="btn btn-danger btn-xs btn-remove-ref" style="height:34px">'
                    + '<i class="fa fa-times"></i></button></span></div>';
        $('#ref-list-' + cardId).append(refHtml);
        // Init Select2 pada dropdown yang baru ditambahkan
        $('#ref-list-' + cardId + ' .select2-ref').last().select2({ placeholder:'— Pilih —', allowClear:true });
    });

    // Hapus satu baris ref
    $(document).on('click', '.btn-remove-ref', function () {
        $(this).closest('.ref-row').remove();
    });

    // Event: Klik Tambah Baris
    $('#addRow').on('click', function () {
        // Ambil barcode terakhir di input, tambahkan 1
        let lastBarcodeStr = $('.barcodeInput').last().val() || initialBarcode;
        let lastBarcodeInt = parseInt(lastBarcodeStr);
        let newBarcode = String(lastBarcodeInt + 1).padStart(5, '0');
        
        $('#itemRows').append(generateRow(newBarcode));
        initSelect2();
        
        // Scroll halus ke baris baru
        $('html, body').animate({ scrollTop: $(document).height() }, 500);
    });

    // Event: Hapus Baris
    $(document).on('click', '.remove-row', function () {
        if ($('.item-card-container').length > 1) {
            $(this).closest('.item-card-container').fadeOut(300, function() {
                $(this).remove();
            });
        } else {
            alert("Minimal harus ada satu barang.");
        }
    });

    // Format Rupiah saat Input
    $(document).on('keyup', '.modalInput, .priceInput', function() {
        $(this).val(formatNumber($(this).val()));
    });

    // Otomatisasi PK (Kode Unik Toko)
    $(document).on('input', '.modalInput', function () {
        let rawValue = unformatNumber($(this).val());
        let modalStr = rawValue.toString();
        let pkResult = '';
        
        // Mapping: J-A-D-I-M-O-T-O-R (Sesuaikan mapping Anda)
        // Berdasarkan code Anda: 0:Y, 1:S, 2:I, 3:T, 4:O, 5:M, 6:P, 7:U, 8:L, 9:X
        let mapping = {
            '0': 'Y', '1': 'S', '2': 'I', '3': 'T', '4': 'O',
            '5': 'M', '6': 'P', '7': 'U', '8': 'L', '9': 'X'
        };

        let zeroCount = 0;
        for (let i = 0; i < modalStr.length; i++) {
            let char = modalStr.charAt(i);
            if (char === '0') {
                zeroCount++;
            } else {
                if (zeroCount > 1) pkResult += 'Y' + zeroCount;
                else if (zeroCount === 1) pkResult += 'Y';
                
                zeroCount = 0;
                pkResult += mapping[char] || char;
            }
        }
        if (zeroCount > 1) pkResult += 'Y' + zeroCount;
        else if (zeroCount === 1) pkResult += 'Y';

        $(this).closest('.item-card-container').find('.pkOutput').val(pkResult);
    });

    // Bersihkan format + renumber supplier_refs sebelum submit
    $('#addItemForm').on('submit', function () {
        // Renumber supplier_refs agar urutan index cocok dengan barcode[]
        var cardIdx = 0;
        $('.item-card-container').each(function () {
            $(this).find('.ref-row select').each(function () {
                $(this).attr('name', 'supplier_refs[' + cardIdx + '][]');
            });
            cardIdx++;
        });

        $('.modalInput, .priceInput').each(function () {
            $(this).val(unformatNumber($(this).val()));
        });
    });
});
</script>