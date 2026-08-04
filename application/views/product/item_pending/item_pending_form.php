<section class="content-header">
    <h1>Form Pengajuan Barang
        <small>Barang Pending</small>
    </h1>
</section>

<section class="content">
<div class="box">
<div class="box-header">
    <h3 class="box-title">Ajukan Barang</h3>
    <div class="pull-right">
        <a href="<?= site_url('item_pending') ?>" class="btn btn-warning btn-flat">
            <i class="fa fa-undo"></i> Kembali
        </a>
    </div>
</div>

<div class="box-body">
<div class="row">
<div class="col-md-4 col-md-offset-4">

<form action="<?= site_url('item_pending/process') ?>"
      method="post"
      enctype="multipart/form-data"
      id="pendingItemForm">

    <!-- Nama -->
    <div class="form-group">
        <label>Nama Barang *</label>
        <input type="text" name="nama_item" class="form-control" required>
    </div>

    <!-- SUPPLIER -->
    <div class="form-group">
        <label>Supplier *</label>
        <?= form_dropdown(
            'supplier',
            $supplier,
            null,
            ['class' => 'form-control', 'id' => 'supplierDropdown', 'required' => 'required']
        ) ?>
    </div>

    <!-- KATEGORI -->
    <div class="form-group">
        <label>Kategori *</label>
        <?= form_dropdown(
            'category',
            $category,
            null,
            ['class' => 'form-control', 'id' => 'categoryDropdown', 'required' => 'required']
        ) ?>
    </div>

    <!-- UNIT -->
    <div class="form-group">
        <label>Unit *</label>
        <?= form_dropdown(
            'unit',
            $unit,
            null,
            ['class' => 'form-control', 'id' => 'unitDropdown', 'required' => 'required']
        ) ?>
    </div>

    <!-- MODAL & PK -->
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <label>Modal *</label>
                <input type="text"
                        name="modal"
                        id="modal"
                        class="form-control modalInput"
                        required>
            </div>
            <div class="col-md-6">
                <label>PK *</label>
                <input type="text"
                        name="pk"
                        class="form-control"
                        readonly
                        required>
            </div>
        </div>
    </div>

    <!-- Harga -->
    <div class="form-group">
        <label>Harga *</label>
        <input type="text"
               name="price"
               class="form-control price"
               value="1"
               readonly
               required>
    </div>

    <!-- Qty -->
    <div class="form-group">
        <label>Qty Barang Masuk *</label>
        <input type="number"
               name="qty"
               class="form-control"
               required>
    </div>

    <!-- Tanggal -->
    <div class="form-group">
        <label>Tanggal Barang Masuk *</label>
        <input type="date"
               name="stock_date"
               class="form-control"
               value="<?= date('Y-m-d') ?>"
               required>
    </div>

    <!-- Foto -->
    <div class="form-group">
        <label>Foto Barang</label>
        <input type="file"
               name="photo"
               class="form-control"
               accept="image/*">
    </div>

    <div class="form-group">
        <button class="btn btn-success btn-flat">
            <i class="fa fa-paper-plane"></i> Ajukan
        </button>
        <button type="reset" class="btn btn-flat">Reset</button>
    </div>

</form>
</div>
</div>
</div>
</div>
</section>


<script>
$(document).ready(function () {

    function formatNumber(input) {
        return input.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function unformatNumber(input) {
        return input.replace(/\./g, "");
    }

    // Auto-format modal & price
    $(document).on('input', '.modalInput, .price', function () {
        let value = $(this).val();
        let unformattedValue = unformatNumber(value);
        $(this).val(formatNumber(unformattedValue));
    });

    // Generate PK otomatis
    $(document).on('input', '#modal', function () {
        let modalValue = unformatNumber($(this).val());
        let pkOutput = generatePK(modalValue);
        $('input[name="pk"]').val(pkOutput);
    });

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
                if (zeroCount > 1) pkOutput += 'Y' + zeroCount;
                else if (zeroCount === 1) pkOutput += 'Y';
                zeroCount = 0;
                pkOutput += mapping[char] || char;
            }
        }

        if (zeroCount > 1) pkOutput += 'Y' + zeroCount;
        else if (zeroCount === 1) pkOutput += 'Y';

        return pkOutput;
    }

    // Unformat sebelum submit
    $('#pendingItemForm').on('submit', function () {
        $('.modalInput, .price').each(function () {
            $(this).val(unformatNumber($(this).val()));
        });
    });
});
</script>
