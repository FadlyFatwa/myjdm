<!-- Hidden inputs - diakses JS di seluruh halaman -->
<input type="hidden" id="item_id">
<input type="hidden" id="nama_item">
<input type="hidden" id="price">
<input type="hidden" id="stock">
<input type="hidden" id="qty_cart">
<button type="button" id="add_cart_otomatis" hidden></button>

<style>
/* ── POS Header Strip ────────────────────────────────────────── */
.pos-strip {
    background: #222d32;
    color: #fff;
    padding: 10px 20px;
    margin: -15px -20px 15px;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    border-bottom: 3px solid #00a65a;
}
.pos-strip .strip-item { display: flex; align-items: center; gap: 8px; }
.pos-strip label { color: #aaa; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; margin: 0; }
.pos-strip .form-control {
    background: #2d3b41; border: 1px solid #3e5060;
    color: #fff; height: 32px; padding: 4px 10px;
    font-size: 13px; border-radius: 4px; width: auto;
}
.pos-strip .form-control:focus { border-color: #00a65a; outline: none; }
.pos-strip .badge-invoice {
    background: #00a65a; color: #fff;
    padding: 5px 12px; border-radius: 4px;
    font-size: 14px; font-weight: 700; letter-spacing: .5px;
}
.pos-strip .kasir-name { color: #fff; font-weight: 600; font-size: 14px; }

/* ── Right panel ─────────────────────────────────────────────── */
.pos-panel { position: sticky; top: 60px; }
.pos-section {
    border-bottom: 1px solid #e8e8e8;
    padding: 14px 15px;
}
.pos-section:last-child { border-bottom: none; }
.pos-label {
    font-size: 11px; font-weight: 700; color: #888;
    text-transform: uppercase; letter-spacing: .5px;
    margin-bottom: 8px; display: block;
}

/* ── Grand total display ─────────────────────────────────────── */
.grand-total-wrap {
    background: linear-gradient(135deg, #00a65a, #00823f);
    border-radius: 8px; padding: 12px 16px; margin: 8px 0;
    text-align: right;
}
.grand-total-wrap small { color: rgba(255,255,255,.8); font-size: 11px; display: block; }
.grand-total-wrap #grand_total2 {
    color: #fff; font-size: 1.9rem; font-weight: 800; line-height: 1.1;
}

/* ── Search result dropdown ──────────────────────────────────── */
#result_item {
    position: absolute; z-index: 9999;
    left: 0; right: 0;
    max-height: 360px; overflow-y: auto;
    box-shadow: 0 4px 16px rgba(0,0,0,.18);
    border-radius: 0 0 6px 6px; border: 1px solid #ddd; border-top: none;
    background: #fff;
}
#result_item .list-group-item { padding: 10px 14px; font-size: 15px; cursor: pointer; }
#result_item .list-group-item strong { font-size: 15px; }
#result_item .list-group-item small { font-size: 13px; }
#result_item .list-group-item .label { font-size: 12px; }
#result_item .list-group-item:hover { background: #f0f9f4; }
#search_item { color: #333; }

/* ── Cart table ──────────────────────────────────────────────── */
.cart-table thead th {
    position: sticky; top: 0;
    background: #f4f4f4; z-index: 2;
    box-shadow: 0 2px 2px rgba(0,0,0,.08);
    font-size: 12px; text-transform: uppercase;
    letter-spacing: .4px; padding: 8px 10px;
}
.cart-table tbody td { vertical-align: middle; padding: 7px 10px; font-size: 13px; }
.cart-box .box-body { max-height: calc(100vh - 300px); overflow-y: auto; padding: 0; }

/* ── Payment method tabs ─────────────────────────────────────── */
.pay-tabs { display: flex; gap: 5px; margin-bottom: 10px; flex-wrap: wrap; }
.pay-tab {
    flex: 1; min-width: 52px; padding: 6px 0; text-align: center; border-radius: 6px;
    border: 2px solid #ddd; background: #fff; cursor: pointer;
    font-size: 11px; font-weight: 600; color: #666; transition: all .2s;
}
.pay-tab.active-cash     { border-color: #00a65a; background: #e8f8f1; color: #00a65a; }
.pay-tab.active-transfer { border-color: #3c8dbc; background: #e8f3f9; color: #3c8dbc; }
.pay-tab.active-credit   { border-color: #dd4b39; background: #fdf0ee; color: #dd4b39; }
.pay-tab.active-qris     { border-color: #8e44ad; background: #f5eefb; color: #8e44ad; }
.pay-tab.active-debit    { border-color: #16a085; background: #e8f8f5; color: #16a085; }

/* ── Notif barang masuk ──────────────────────────────────────── */
#barang_masuk-wrap {
    background: #dff0d8; border: 1px solid #d6e9c6; border-radius: 4px;
    padding: 6px 10px; margin-top: 8px; font-size: 13px; min-height: 32px;
}

/* ── Process button ──────────────────────────────────────────── */
#process_payment {
    background: linear-gradient(135deg, #00a65a, #00823f);
    border: none; color: #fff; font-size: 16px; font-weight: 700;
    letter-spacing: .5px; padding: 12px; border-radius: 6px;
    width: 100%; transition: opacity .2s;
}
#process_payment:hover { opacity: .9; }
#cancel_payment {
    background: #fff; border: 2px solid #ddd; color: #666;
    font-weight: 600; padding: 10px; border-radius: 6px; width: 100%;
}
#cancel_payment:hover { border-color: #dd4b39; color: #dd4b39; background: #fff5f5; }
</style>

<section class="content">

<!-- ── POS Header Strip ─────────────────────────────────────── -->
<div class="pos-strip">
    <div class="strip-item">
        <i class="fa fa-file-text-o" style="color:#aaa"></i>
        <div>
            <label>Invoice</label>
            <span class="badge-invoice"><?= $invoice ?></span>
        </div>
    </div>

    <div class="strip-item">
        <i class="fa fa-calendar" style="color:#aaa"></i>
        <div>
            <label>Tanggal</label>
            <input type="date" id="date" value="<?= $today ?>" class="form-control">
        </div>
    </div>

    <div class="strip-item">
        <i class="fa fa-user-circle" style="color:#aaa"></i>
        <div>
            <label>Kasir</label>
            <span class="kasir-name"><?= $this->fungsi->user_login()->nama ?></span>
        </div>
    </div>

    <div class="strip-item" style="flex:1; min-width:200px;">
        <i class="fa fa-users" style="color:#aaa; flex-shrink:0"></i>
        <div style="flex:1">
            <label>Pembeli</label>
            <select id="customer" class="form-control" style="width:100%; min-width:180px;">
                <option value="0">— Umum —</option>
                <?php foreach ($customer as $cust => $value): ?>
                    <option value="<?= $value->customer_id ?>"><?= $value->nama_customer ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div id="customer_name_group" style="display:none; flex:1; min-width:160px;">
        <i class="fa fa-pencil" style="color:#aaa; flex-shrink:0; margin-right:8px;"></i>
        <div style="flex:1">
            <label>Nama Pembeli</label>
            <input type="text" id="customer_name" class="form-control" placeholder="Nama walk-in...">
        </div>
    </div>

    <?php if ((int) $this->fungsi->user_login()->level === LEVEL_ADMIN): ?>
    <div class="strip-item" style="margin-left:auto;">
        <a href="<?= site_url('sale/lite') ?>" class="btn btn-xs btn-warning" style="white-space:nowrap;">
            <i class="fa fa-magic"></i> Mode Lite
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- ── Main POS Layout ──────────────────────────────────────── -->
<div class="row">

    <!-- ═════════════ LEFT: Cari + Cart ═════════════ -->
    <div class="col-lg-8">

        <!-- Cart table -->
        <div class="box box-solid cart-box">
            <div class="box-header with-border" style="padding:10px 15px;">
                <h3 class="box-title" style="font-size:14px;">
                    <i class="fa fa-shopping-basket text-green"></i> Keranjang Belanja
                    <span id="cart-count"
                          style="background:#00a65a; color:#fff; font-size:11px; font-weight:700;
                                 padding:2px 10px; border-radius:10px; margin-left:8px;">0 item</span>
                </h3>
            </div>
            <div class="box-body">
                <table class="table table-hover table-bordered cart-table" style="margin:0;">
                    <thead>
                        <tr>
                            <th width="5%"  class="text-center">No</th>
                            <th width="12%">Barcode</th>
                            <th>Nama Barang</th>
                            <th width="12%" class="text-right">Harga</th>
                            <th width="7%"  class="text-center">Qty</th>
                            <th width="9%"  class="text-right">Diskon</th>
                            <th width="13%" class="text-right">Subtotal</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cart_table">
                        <?php $this->load->view('transaction/sale/cart_data') ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cart Jasa -->
        <div class="box box-solid" style="border-top:3px solid #3c8dbc;">
            <div class="box-header with-border" style="padding:10px 15px;">
                <h3 class="box-title" style="font-size:14px;">
                    <i class="fa fa-wrench" style="color:#3c8dbc;"></i> Keranjang Jasa
                    <span id="jasa-count"
                          style="background:#3c8dbc; color:#fff; font-size:11px; font-weight:700;
                                 padding:2px 10px; border-radius:10px; margin-left:8px;">0 jasa</span>
                </h3>
            </div>
            <div class="box-body">
                <!-- Input tambah jasa -->
                <div style="display:flex; gap:8px; align-items:center; margin-bottom:10px; flex-wrap:wrap;">
                    <select id="jasa_select" class="form-control" style="flex:2; min-width:200px;">
                        <option value="">— Pilih Jasa —</option>
                        <?php foreach ($jasa_list as $j): ?>
                        <option value="<?= $j->jasa_id ?>"
                                data-tarif="<?= $j->tarif ?>"
                                data-nama="<?= htmlspecialchars($j->nama_jasa) ?>">
                            <?= htmlspecialchars($j->nama_jasa) ?> — Rp <?= number_format($j->tarif, 0, ',', '.') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" id="jasa_qty" value="1" min="1"
                           style="width:70px; text-align:center; font-size:15px; font-weight:700;
                                  height:34px; border:1px solid #ccc; border-radius:4px; padding:4px 8px;">
                    <button type="button" id="btn_add_jasa" class="btn btn-info btn-sm"
                            style="height:34px; padding:0 16px; white-space:nowrap;">
                        <i class="fa fa-plus"></i> Tambah Jasa
                    </button>
                </div>
                <table class="table table-bordered table-hover" style="margin:0;">
                    <thead>
                        <tr style="background:#ecf5fb;">
                            <th width="5%" class="text-center">No</th>
                            <th>Nama Jasa</th>
                            <th width="15%" class="text-right">Tarif</th>
                            <th width="8%"  class="text-right">Qty</th>
                            <th width="15%" class="text-right">Total</th>
                            <th class="hidden"></th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="jasa_cart_table">
                        <?php $this->load->view('transaction/sale/cart_jasa_data', ['cart_jasa' => $cart_jasa]) ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ═════════════ RIGHT: Panel Kasir ═════════════ -->
    <div class="col-lg-4">
        <div class="box box-solid pos-panel" style="border-top:3px solid #00a65a;">

            <!-- CARI BARANG -->
            <div class="pos-section" style="position:relative;">
                <span class="pos-label"><i class="fa fa-search"></i> Cari Nama / Barang</span>
                <div style="display:flex; gap:6px; align-items:center;">
                    <div style="flex:1; display:flex; align-items:center; gap:8px;
                                background:#f4f6f8; border:1.5px solid #d0d5dd; border-radius:8px;
                                padding:0 12px; height:38px; transition:border-color .2s;"
                         id="search-wrap">
                        <i class="fa fa-search" style="color:#aaa; font-size:14px; flex-shrink:0;"></i>
                        <input type="text" id="search_item"
                               placeholder="Nama / barcode barang..."
                               style="border:none; outline:none; background:transparent;
                                      font-size:13px; width:100%;"
                               onfocus="document.getElementById('search-wrap').style.borderColor='#00a65a'"
                               onblur="document.getElementById('search-wrap').style.borderColor=document.body.classList.contains('dark-mode')?'#374151':'#d0d5dd'">
                    </div>
                    <button type="button" class="btn btn-warning btn-xs" id="btn_temporary"
                            style="height:38px; padding:0 10px; white-space:nowrap; font-size:12px; border-radius:8px; flex-shrink:0;">
                        <i class="fa fa-plus-circle"></i> Sementara
                    </button>
                </div>
                <div id="result_item"></div>
            </div>

            <!-- INPUT BARCODE + QTY -->
            <div class="pos-section">
                <span class="pos-label"><i class="fa fa-barcode"></i> Scan / Input Barcode</span>
                <div class="input-group" style="margin-bottom:8px;">
                    <input type="text" id="barcode" class="form-control"
                           placeholder="Scan barcode..." autofocus
                           style="font-size:15px; height:40px; font-family:monospace;">
                    <span class="input-group-addon" style="padding:0;">
                        <input type="number" id="qty" value="1" min="1"
                               style="width:60px; border:none; height:38px; padding:4px 8px;
                                      text-align:center; font-size:15px; font-weight:700;
                                      border-left:1px solid #ccc; background:#fafafa;"
                               title="Qty">
                    </span>
                    <span class="input-group-btn">
                        <button type="button" id="add_cart" class="btn btn-primary"
                                style="height:40px; padding: 0 14px;">
                            <i class="fa fa-plus"></i> Tambah
                        </button>
                    </span>
                </div>
                <div id="barang_masuk-wrap">
                    <span style="color:#888; font-size:12px;">
                        <i class="fa fa-check-circle text-green" style="display:none" id="barang_masuk-icon"></i>
                        <span id="barang_masuk"></span>
                        <span id="barang_masuk-placeholder" style="color:#bbb;">Belum ada barang ditambahkan</span>
                    </span>
                </div>
            </div>

            <!-- RINGKASAN HARGA -->
            <div class="pos-section">
                <span class="pos-label"><i class="fa fa-calculator"></i> Ringkasan</span>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="color:#666; font-size:13px;">Subtotal</span>
                    <input type="text" id="sub_total" value="0" readonly
                           style="text-align:right; border:none; background:transparent;
                                  font-size:14px; font-weight:600; width:150px; color:#333;">
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="color:#666; font-size:13px;">Diskon</span>
                    <div class="input-group" style="width:120px;">
                        <input type="text" id="discount" value="0"
                               style="text-align:right; height:30px; padding:4px 8px; font-size:13px;">
                        <span class="input-group-addon" style="padding:4px 8px; font-size:12px;">%</span>
                    </div>
                </div>
                <div class="grand-total-wrap">
                    <small>Grand Total</small>
                    <input type="text" id="grand_total" style="display:none;">
                    <div id="grand_total2">Rp 0</div>
                </div>
            </div>

            <!-- METODE PEMBAYARAN -->
            <div class="pos-section">
                <span class="pos-label"><i class="fa fa-credit-card"></i> Pembayaran</span>
                <div class="pay-tabs">
                    <div class="pay-tab" data-method="cash"     onclick="selectPayment(this,'cash')">
                        <i class="fa fa-money"></i><br>Cash
                    </div>
                    <div class="pay-tab" data-method="transfer" onclick="selectPayment(this,'transfer')">
                        <i class="fa fa-university"></i><br>Transfer
                    </div>
                    <div class="pay-tab" data-method="qris"     onclick="selectPayment(this,'qris')">
                        <i class="fa fa-qrcode"></i><br>QRIS
                    </div>
                    <div class="pay-tab" data-method="debit"    onclick="selectPayment(this,'debit')">
                        <i class="fa fa-credit-card"></i><br>Debit
                    </div>
                    <div class="pay-tab" data-method="credit"   onclick="selectPayment(this,'credit')">
                        <i class="fa fa-clock-o"></i><br>Kredit
                    </div>
                </div>
                <input type="hidden" id="payment_method" value="">

                <div id="cash_row" style="display:none;">
                    <div class="input-group" style="margin-bottom:6px;">
                        <span class="input-group-addon" style="font-size:12px;">Cash&nbsp;Rp</span>
                        <input type="text" id="cash" value="0" class="form-control"
                               style="text-align:right; font-size:14px; font-weight:600;">
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="color:#666; font-size:13px;">Kembalian</span>
                        <input type="text" id="change" readonly
                               style="text-align:right; border:none; background:transparent;
                                      font-size:14px; font-weight:700; color:#00a65a; width:150px;">
                    </div>
                </div>
            </div>

            <!-- CATATAN + TOMBOL -->
            <div class="pos-section">
                <span class="pos-label"><i class="fa fa-sticky-note-o"></i> Catatan</span>
                <textarea id="note" rows="2" class="form-control"
                          placeholder="Catatan transaksi (opsional)..."
                          style="font-size:13px; resize:none; margin-bottom:12px;"></textarea>

                <div style="display:flex; gap:8px;">
                    <button id="cancel_payment" style="flex:1;">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button id="process_payment" style="flex:2;">
                        <i class="fa fa-paper-plane"></i> PROSES
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>

</section>

<script>
// ── Payment tab selector ─────────────────────────────────────────
function selectPayment(el, method) {
    document.querySelectorAll('.pay-tab').forEach(t => {
        t.className = 'pay-tab';
    });
    el.classList.add('active-' + method);
    $('#payment_method').val(method).trigger('change');
}

// ── Barang masuk notif tweak ─────────────────────────────────────
var _origBM = '';
var _bmObserver = new MutationObserver(function() {
    var txt = $('#barang_masuk').text().trim();
    if (txt) {
        $('#barang_masuk-placeholder').hide();
        $('#barang_masuk-icon').show();
    } else {
        $('#barang_masuk-placeholder').show();
        $('#barang_masuk-icon').hide();
    }
});
$(document).ready(function() {
    _bmObserver.observe(document.getElementById('barang_masuk'), { childList: true, characterData: true, subtree: true });
});
</script>


<!-- Modal Edit Cart Item -->
<div class="modal fade" id="modal-item-edit">
    <div class="modal-dialog" style="max-width:460px; margin:60px auto;">
        <div class="modal-content" style="border:none; border-top:3px solid #00a65a; border-radius:8px; box-shadow:0 8px 32px rgba(0,0,0,.2);">

            <!-- Header -->
            <div class="modal-header" style="background:#222d32; border-radius:6px 6px 0 0; padding:12px 18px;">
                <button type="button" class="close" data-dismiss="modal"
                        style="color:#fff; opacity:.7; font-size:20px; margin-top:-2px;">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title" style="color:#fff; font-size:14px; font-weight:600;">
                    <i class="fa fa-pencil-square-o" style="color:#00a65a; margin-right:6px;"></i>
                    Edit Item Keranjang
                </h4>
            </div>

            <!-- Body -->
            <div class="modal-body" style="padding:18px;">
                <input type="hidden" id="cartid_item">
                <input type="hidden" id="status_item_edit">
                <input type="hidden" id="modal_item">

                <!-- Info: Barcode + PK + Nama -->
                <div class="edit-info-box" style="background:#f5f7f9; border-radius:6px;
                            padding:10px 14px; margin-bottom:14px;">
                    <div style="display:flex; gap:14px; align-items:center; margin-bottom:8px;">
                        <div style="flex-shrink:0;">
                            <div class="info-label" style="font-size:10px; color:#999; text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px;">Barcode</div>
                            <input type="text" id="barcode_item" readonly
                                   style="border:none; background:transparent; font-weight:700;
                                          font-size:13px; font-family:monospace; color:#444;
                                          padding:0; width:90px;">
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div class="info-label" style="font-size:10px; color:#999; text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px;">PK / Kode Beli</div>
                            <input type="text" id="pk_item" readonly
                                   style="border:none; background:transparent; font-weight:700;
                                          font-size:13px; font-family:monospace; color:#444;
                                          padding:0; width:100%;">
                        </div>
                    </div>
                    <div>
                        <div class="info-label" style="font-size:10px; color:#999; text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px;">Nama Barang</div>
                        <input type="text" id="product_item"
                               style="border:none; background:transparent; font-size:13px;
                                      color:#333; padding:0; width:100%; font-weight:600;">
                    </div>
                </div>

                <!-- Qty + Stok -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:11px; color:#555; font-weight:700; margin-bottom:4px; display:block; text-transform:uppercase; letter-spacing:.4px;">
                            <i class="fa fa-hashtag" style="color:#00a65a;"></i> Qty
                        </label>
                        <input type="number" id="qty_item" min="1" class="form-control"
                               style="font-size:22px; font-weight:800; text-align:center;
                                      height:50px; border:2px solid #00a65a; border-radius:6px;">
                    </div>
                    <div>
                        <label style="font-size:11px; color:#555; font-weight:700; margin-bottom:4px; display:block; text-transform:uppercase; letter-spacing:.4px;">
                            <i class="fa fa-cubes" style="color:#3c8dbc;"></i> Stok Ada
                        </label>
                        <input type="number" id="stock_item" readonly class="form-control"
                               style="font-size:22px; font-weight:800; text-align:center;
                                      height:50px; background:#eef4f9; color:#3c8dbc;
                                      border:2px solid #d0e6f3; border-radius:6px;">
                    </div>
                </div>

                <!-- Harga + Sebelum Diskon -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:11px; color:#555; font-weight:700; margin-bottom:4px; display:block; text-transform:uppercase; letter-spacing:.4px;">
                            <i class="fa fa-tag" style="color:#f39c12;"></i> Harga (Rp)
                        </label>
                        <input type="text" id="price_item" class="form-control"
                               style="font-size:15px; text-align:right; height:40px; border-radius:6px;">
                    </div>
                    <div>
                        <label style="font-size:11px; color:#aaa; font-weight:700; margin-bottom:4px; display:block; text-transform:uppercase; letter-spacing:.4px;">
                            <i class="fa fa-calculator"></i> Sebelum Diskon
                        </label>
                        <input type="text" id="total_before" readonly class="form-control"
                               style="font-size:15px; text-align:right; height:40px;
                                      background:#f8f9fa; color:#888; border-radius:6px;">
                    </div>
                </div>

                <!-- Diskon -->
                <div style="margin-bottom:14px;">
                    <label style="font-size:11px; color:#555; font-weight:700; margin-bottom:4px; display:block; text-transform:uppercase; letter-spacing:.4px;">
                        <i class="fa fa-scissors" style="color:#dd4b39;"></i> Diskon per Barang (Rp)
                    </label>
                    <div class="input-group">
                        <span class="input-group-addon" style="background:#fdf0ee; border-color:#f5c6c0; color:#dd4b39;">
                            <i class="fa fa-minus"></i>
                        </span>
                        <input type="text" id="discount_item" class="form-control"
                               style="font-size:15px; text-align:right; height:40px; border-radius:0 6px 6px 0;">
                    </div>
                </div>

                <!-- Total akhir -->
                <div style="background:linear-gradient(135deg,#00a65a,#008d4c); border-radius:8px;
                            padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:rgba(255,255,255,.85); font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">
                        Total Setelah Diskon
                    </span>
                    <input type="text" id="total_item" readonly
                           style="border:none; background:transparent; color:#fff;
                                  font-size:20px; font-weight:800; text-align:right; width:180px;">
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer" style="background:#f8f9fa; border-top:1px solid #eee;
                                             border-radius:0 0 6px 6px; padding:10px 18px;">
                <small class="text-muted" style="float:left; line-height:34px; font-size:12px;">
                    <i class="fa fa-keyboard-o"></i> Tekan <kbd>Enter</kbd> untuk simpan
                </small>
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Batal
                </button>
                <button type="button" id="edit_cart" class="btn btn-success btn-sm"
                        style="background:#00a65a; border-color:#008d4c; padding:6px 18px;">
                    <i class="fa fa-check"></i> Simpan
                </button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modal-temporary">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-warning">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-plus-circle"></i> Tambah Barang Sementara
                </h4>
            </div>

            <div class="modal-body">
            <input type="hidden" id="status_item">

                <div class="form-group">
                    <label>Nama Barang</label>
                    <input type="text" id="temp_nama" class="form-control">
                </div>

                <div class="form-group">
                    <label>Harga Jual</label>
                    <input type="text" id="temp_price" class="form-control">
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Batal</button>
                <button class="btn btn-success" id="save_temporary">
                    <i class="fa fa-save"></i> Simpan & Tambah
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Modal Edit Jasa Cart -->
<div class="modal fade" id="modal-jasa-edit">
    <div class="modal-dialog" style="max-width:400px; margin:60px auto;">
        <div class="modal-content" style="border:none; border-top:3px solid #3c8dbc; border-radius:8px; box-shadow:0 8px 32px rgba(0,0,0,.2);">
            <div class="modal-header" style="background:#222d32; border-radius:6px 6px 0 0; padding:12px 18px;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:.7;">&times;</button>
                <h4 class="modal-title" style="color:#fff; font-size:14px; font-weight:600;">
                    <i class="fa fa-wrench" style="color:#3c8dbc; margin-right:6px;"></i> Edit Item Jasa
                </h4>
            </div>
            <div class="modal-body" style="padding:18px;">
                <input type="hidden" id="jasa_edit_id">

                <div class="form-group">
                    <label style="font-size:11px; color:#555; font-weight:700; text-transform:uppercase; letter-spacing:.4px;">
                        Nama Jasa
                    </label>
                    <input type="text" id="jasa_edit_nama" class="form-control" style="font-size:14px;">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div class="form-group">
                        <label style="font-size:11px; color:#555; font-weight:700; text-transform:uppercase; letter-spacing:.4px;">
                            Tarif (Rp)
                        </label>
                        <input type="text" id="jasa_edit_tarif" class="form-control" style="font-size:15px; text-align:right;">
                    </div>
                    <div class="form-group">
                        <label style="font-size:11px; color:#555; font-weight:700; text-transform:uppercase; letter-spacing:.4px;">
                            Qty
                        </label>
                        <input type="number" id="jasa_edit_qty" min="1" class="form-control"
                               style="font-size:22px; font-weight:800; text-align:center; height:50px; border:2px solid #3c8dbc; border-radius:6px;">
                    </div>
                </div>

                <div style="background:linear-gradient(135deg,#3c8dbc,#2d6d99); border-radius:8px;
                            padding:10px 14px; display:flex; justify-content:space-between; align-items:center; margin-top:4px;">
                    <span style="color:rgba(255,255,255,.85); font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Total</span>
                    <span id="jasa_edit_total" style="color:#fff; font-size:20px; font-weight:800;">Rp 0</span>
                </div>
            </div>
            <div class="modal-footer" style="background:#f8f9fa; border-top:1px solid #eee; border-radius:0 0 6px 6px; padding:10px 18px;">
                <small class="text-muted" style="float:left; line-height:34px; font-size:12px;">
                    <i class="fa fa-keyboard-o"></i> Tekan <kbd>Enter</kbd> untuk simpan
                </small>
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Batal</button>
                <button type="button" id="btn_save_jasa_edit" class="btn btn-info btn-sm" style="padding:6px 18px;">
                    <i class="fa fa-check"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>

// Simpan keyword pencarian terakhir agar bisa di-restore saat klik search lagi
let lastSearchKeyword = '';

// Saat search diklik dan kosong → restore keyword terakhir + trigger pencarian
$('#search_item').on('focus', function() {
    if (!$(this).val().trim() && lastSearchKeyword) {
        $(this).val(lastSearchKeyword);
        $(this).trigger('keyup'); // langsung tampilkan hasil
    }
});

let delayTimer;
$('#search_item').on('keyup', function(){
    clearTimeout(delayTimer);
    let keyword = $(this).val().trim();

    delayTimer = setTimeout(function(){
        if(keyword.length >= 2){
            $.ajax({
                url: "<?= site_url('sale/search_item') ?>",
                type: "POST",
                data: { keyword: keyword },
                success: function(response){
                    $('#result_item').html(response);
                }
            });
        } else {
            $('#result_item').html('');
        }
    }, 300);
});


$(document).on('click', '.item-select', function(e){
    e.preventDefault();


    let item_id = $(this).data('id');
    let barcode = $(this).data('barcode');
    let price = $(this).data('price');
    let stock = $(this).data('stock');

    $('#item_id').val(item_id);
    $('#nama_item').val($(this).text().trim());
    $('#barcode').val(barcode);
    $('#price').val(price);
    $('#stock').val(stock);
    $('#nama_item').val($(this).data('name'));
    $('#status_item').val($(this).data('status'));

    // Simpan keyword sebelum dikosongkan, untuk restore saat klik search lagi
    lastSearchKeyword = $('#search_item').val().trim() || lastSearchKeyword;

    $('#result_item').html('');
    $('#search_item').val('');

    get_cart_qty(barcode, function(qtyInCart){
        $('#qty_cart').val(qtyInCart);
    });

    // trigger enter supaya logic lama jalan
    $('#barcode').focus();
});

function get_cart_qty(barcode, callback) {
    var totalQty = 0;
    $('#cart_table tr').each(function(){
        var rowBarcode = $(this).find("td:nth-child(2)").text().trim();
        if(rowBarcode === barcode) {
            var qty = parseInt($(this).find("td:nth-child(5)").text()) || 0;
            totalQty += qty;
        }
    });
    callback(totalQty);
}



$('#barcode').keypress(function(e){
    if(e.which != 13) return;
    
    var barcode = $(this).val().trim();
    if(!barcode) return;
    
    // Clear previous values
    $('#item_id').val('');
    $('#barcode').val('');
    $('#price').val('');
    $('#stock').val('');
    
    $.ajax({
        type: "POST",
        url: '<?=site_url('sale/get_item') ?>',
        data: {'barcode': barcode},
        dataType: 'json',
        success: function(result){
            if(result.success){
                // Check current stock from server
                $.ajax({
                    type: "POST",
                    url: '<?=site_url('sale/check_stock') ?>',
                    data: {'item_id': result.item.item_id},
                    dataType: 'json',
                    success: function(stockResult) {
                        if(stockResult.success) {
                            var currentStock = parseInt(stockResult.stock);
                            var qtyToAdd = parseInt($('#qty').val()) || 1;
                            
                            // Get current qty in cart
                            get_cart_qty(result.item.barcode, function(qtyInCart) {
                                var totalQty = qtyInCart + qtyToAdd;
                                
                                if(currentStock < totalQty) {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Stock Tidak Mencukupi",
                                        html: "Stok tersedia: <b>" + currentStock + "</b><br>" +
                                              "Sudah di keranjang: <b>" + qtyInCart + "</b><br>" +
                                              "Yang ingin ditambahkan: <b>" + qtyToAdd + "</b>",
                                        confirmButtonText: "OK"
                                    });
                                    $("#barcode").val('').focus();
                                    return;
                                }
                                
                                // Proceed with adding to cart
                                $('#item_id').val(result.item.item_id);
                                $('#barcode').val(result.item.barcode);
                                $('#price').val(result.item.price);
                                $('#stock').val(currentStock);
                                
                                // Show notification
                                $('#barang_masuk').text(result.item.nama_item + " (" + qtyToAdd + " pc/set)");
                                setTimeout(function(){
                                    $('#barang_masuk').fadeOut(3000, function(){
                                        $(this).text('').fadeIn(0);
                                    });
                                }, 3000);
                                
                                // Trigger add to cart
                                $('#add_cart_otomatis').trigger('click');
                            });
                        }
                    }
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Produk Tidak Ditemukan",
                });
                $("#barcode").val('').focus();
            }
        }
    });
});

// Enter di qty → trigger tambah
$('#qty').on('keypress', function(e){
    if(e.which == 13){
        e.preventDefault();
        $('#add_cart').trigger('click');
    }
});


$(document).on('click','#add_cart_otomatis', function(){

    var item_id = $('#item_id').val();
    var price   = $('#price').val();
    var qty     = $('#qty').val();
    var barcode = $('#barcode').val();
    var namaItem = $('#nama_item').val();

    if(!item_id){
        Swal.fire({
            icon: "error",
            title: "Produk Tidak Ditemukan",
        });
        $('#barcode').focus();
        return;
    }

    var qtyToAdd = parseInt(qty) || 1;

    // 🔥 CEK STOK KE SERVER (SAMA SEPERTI DI KEYPRESS)
    $.ajax({
        type: "POST",
        url: '<?=site_url('sale/check_stock')?>',
        data: { 'item_id': item_id },
        dataType: 'json',
        success: function(stockResult){

            if(!stockResult.success){
                Swal.fire({
                    icon: "error",
                    title: "Gagal Mengecek Stok",
                });
                return;
            }

            var currentStock = parseInt(stockResult.stock);

            // 🔥 Ambil qty di cart
            get_cart_qty(barcode, function(qtyInCart){

                var totalQty = qtyInCart + qtyToAdd;

                if(currentStock < totalQty){
                    Swal.fire({
                        icon: "error",
                        title: "Stock Tidak Mencukupi",
                        html: "Stok tersedia: <b>" + currentStock + "</b><br>" +
                              "Sudah di keranjang: <b>" + qtyInCart + "</b><br>" +
                              "Yang ingin ditambahkan: <b>" + qtyToAdd + "</b>",
                        confirmButtonText: "OK"
                    });
                    $('#qty').focus();
                    return;
                }

                // 🔥 PROCEED TAMBAH KE CART
                $.ajax({
                    type: "POST",
                    url: '<?=site_url('sale/process')?>',
                    data: {
                        'add_cart': true,
                        'item_id': item_id,
                        'price': price,
                        'qty': qtyToAdd
                    },
                    dataType: 'json',
                    success: function(result){

                        if(result.success){

                            $('#cart_table').load('<?=site_url('sale/cart_data')?>', function(){
                                calculate();
                            });

                            // Notif low stock adaptif
                            showLowStockToast(result.low_stock_info);

                            // Reset form
                            $('#item_id').val('');
                            $('#barcode').val('');
                            $('#qty').val('1');
                            $('#barcode').focus();

                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal Tambah Produk ke keranjang",
                            });
                        }
                    }
                });

            });

        }
    });

});

$(document).on('click','#add_cart', function(){

    var item_id   = $('#item_id').val();
    var price     = $('#price').val();
    var qty       = $('#qty').val();
    var barcode   = $('#barcode').val();
    var namaItem  = $('#nama_item').val();
    var statusItem = $('#status_item').val();

    var qtyToAdd = parseInt(qty) || 1;

    // =============================
    // VALIDASI BARANG BELUM DIPILIH
    // =============================
    if(!item_id){

        var barcodeInput = barcode.trim();

        if(!barcodeInput){
            Swal.fire({
                icon: "error",
                title: "Masukkan Barcode Terlebih Dahulu"
            });
            $('#barcode').focus();
            return;
        }


    }

    // =============================
    // CEK STOK KE SERVER (HANYA UNTUK ACTIVE)
    // =============================
    if(statusItem !== 'temporary'){

        $.ajax({
            type: "POST",
            url: '<?=site_url('sale/check_stock')?>',
            data: { 'item_id': item_id },
            dataType: 'json',
            success: function(stockResult){

                if(!stockResult.success){
                    Swal.fire({
                        icon: "error",
                        title: "Gagal Mengecek Stok"
                    });
                    return;
                }

                var currentStock = parseInt(stockResult.stock);

                get_cart_qty(barcode, function(qtyInCart){

                    var totalQty = qtyInCart + qtyToAdd;

                    if(currentStock < totalQty){
                        Swal.fire({
                            icon: "error",
                            title: "Stock Tidak Mencukupi",
                            html: "Stok tersedia: <b>" + currentStock + "</b><br>" +
                                  "Sudah di keranjang: <b>" + qtyInCart + "</b><br>" +
                                  "Yang ingin ditambahkan: <b>" + qtyToAdd + "</b>",
                            confirmButtonText: "OK"
                        });
                        $('#qty').focus();
                        return;
                    }

                    proceedAddToCart();

                });

            }
        });

    } else {

        // =============================
        // JIKA TEMPORARY → LANGSUNG TAMBAH
        // =============================
        proceedAddToCart();
    }

    // =============================
    // FUNCTION TAMBAH KE CART
    // =============================
    function proceedAddToCart(){

        $.ajax({
            type: "POST",
            url: '<?=site_url('sale/process')?>',
            data: {
                'add_cart': true,
                'item_id': item_id,
                'price': price,
                'qty': qtyToAdd
            },
            dataType: 'json',
            success: function(result){
                if(result.success){

                    $('#cart_table').load('<?=site_url('sale/cart_data')?>', function(){
                        calculate();
                    });

                    // Notif low stock adaptif
                    showLowStockToast(result.low_stock_info);

                    // Notifikasi sukses item masuk
                    $('#barang_masuk').text(namaItem + " (" + qtyToAdd + " pc/set)");
                    setTimeout(function(){
                        $('#barang_masuk').fadeOut(3000, function(){
                            $(this).text('').fadeIn(0);
                        });
                    }, 3000);

                    // Reset form
                    $('#item_id').val('');
                    $('#barcode').val('');
                    $('#qty').val('1');
                    $('#status_item').val('');
                    $('#barcode').focus();

                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal Tambah Produk ke keranjang"
                    });
                }
            }
        });
    }

});


$('#btn_temporary').click(function(){
    manualTemporary = true;

    var barcode = $('#barcode').val();

    $('#temp_barcode').val(barcode);
    $('#temp_barcode_display').val(barcode);
    $('#status_item').val('temporary');

    $('#modal-temporary').modal('show');
});

$(document).on('keyup mouseup', '#temp_price', function() {
    var value = unformatNumber($(this).val());
    $(this).val(formatNumber(value));
});


$('#save_temporary').click(function(){

    var barcode = $('#temp_barcode').val();
    var nama    = $('#temp_nama').val();
    
    var price   = unformatNumber($('#temp_price').val());

    if(!nama){
        Swal.fire({
            icon: "error",
            title: "Nama barang wajib diisi"
        });
        return;
    }

    $.ajax({
        type: "POST",
        url: "<?=site_url('sale/create_temporary')?>",
        data: {
            barcode: barcode,
            nama_item: nama,
            price: price
        },
        dataType: "json",
        success: function(response){

            if(response.success){

                $('#modal-temporary').modal('hide');

                // isi field utama sale
                $('#item_id').val(response.item.item_id);
                $('#barcode').val(response.item.barcode);
                $('#price').val(response.item.price);
                $('#stock').val(0);

                $('#status_item').val('temporary');  // 🔥 WAJIB
                $('#nama_item').val($('#temp_nama').val()); // 🔥 WAJIB

                $('#add_cart').trigger('click');

            } else {
                Swal.fire({
                    icon: "error",
                    title: "Gagal menyimpan barang sementara"
                });
            }

        }
    });

});



$(document).on('click','#del_cart',function(){
    Swal.fire({
        title: "Apakah Anda Yakin?",
        text: "Data Akan Terhapus!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, Hapus",
        cancelButtonText: "Batal"
    }).then((result) => {
        if (result.isConfirmed) {
            var cart_id = $(this).data('cartid');
        $.ajax({
            type: 'POST',
            url: '<?=site_url('sale/cart_del')?>',
            dataType: 'json',
            data: {'cart_id' : cart_id},
            success: function(result){
                if(result.success == true){
                        $('#cart_table').load('<?=site_url('sale/cart_data')?>', function(){
                            calculate()
                        })
                        $('#item_id').val('')
                        $('#barcode').val('')
                        $('#qty').val('1')
                        $('#barcode').focus()

                        Swal.fire(
                            'Dihapus!',
                            'Item telah dihapus dari keranjang.',
                            'success'
                        );
                        
                    } else {
                        Swal.fire(
                            'Gagal!',
                            'Gagal menghapus item dari keranjang.',
                            'error'
                        );
                    }        
                }
            });
        }
    });
});

 
$(document).on('click', '#update_cart', function() {
    // Ambil data dari elemen yang diklik
    var cartid = $(this).data('cartid');
    var barcode = $(this).data('barcode');
    var pk = $(this).data('pk');
    var product = $(this).data('nama_barang_jual');
    var stock = $(this).data('stock');
    var price = parseFloat($(this).data('price')) || 0;
    var modal = parseFloat($(this).data('modal')) || 0;
    var qty = parseFloat($(this).data('qty')) || 0;
    var discount = parseFloat($(this).data('discount')) || 0;
    var total = parseFloat($(this).data('total')) || 0;
    var status = $(this).data('status');

    // Hitung total sebelum diskon
    var total_before = price * qty;

    // Format angka sebelum ditampilkan di input
    $('#cartid_item').val(cartid);
    $('#barcode_item').val(barcode);
    $('#pk_item').val(pk);
    $('#product_item').val(product);
    $('#stock_item').val(stock);
    $('#status_item_edit').val(status);
    $('#modal_item').val(modal);
    $('#price_item').val(formatNumber(price)); // Format harga
    $('#qty_item').val(formatNumber(qty)); // Format kuantitas
    $('#total_before').val(formatNumber(total_before)); // Format total sebelum diskon
    $('#discount_item').val(formatNumber(discount)); // Format diskon
    $('#total_item').val(formatNumber(total)); // Format total akhir
});
function count_edit_modal() {
    // Ambil nilai dari input dan hapus pemisah ribuan untuk perhitungan
    var price = parseFloat($('#price_item').val().replace(/\D/g, '')) || 0;
    var qty = parseFloat($('#qty_item').val().replace(/\D/g, '')) || 0;
    var discount = parseFloat($('#discount_item').val().replace(/\D/g, '')) || 0;

    // Hitung total sebelum diskon
    var total_before = price * qty;

    // Hitung total setelah diskon
    var total = (price - discount) * qty;

    // Format angka untuk ditampilkan di input
    $('#total_before').val(formatNumber(total_before)); // Total sebelum diskon
    $('#total_item').val(formatNumber(total)); // Total setelah diskon

    // Jika diskon kosong, atur ke 0
    if ($('#discount_item').val() == '') {
        $('#discount_item').val(0);
    }

    // Format ulang input untuk harga, kuantitas, dan diskon
    $('#price_item').val(formatNumber(price));
    $('#qty_item').val(formatNumber(qty));
    $('#discount_item').val(formatNumber(discount));
}

// Event listener untuk input harga, kuantitas, dan diskon
$(document).on('keyup mouseup', '#price_item, #qty_item, #discount_item', function() {
    count_edit_modal();
});

// Enter di dalam modal edit → langsung simpan
$(document).on('keypress', '#modal-item-edit input', function(e) {
    if (e.which === 13) {
        e.preventDefault();
        $('#edit_cart').trigger('click');
    }
});
 // Fungsi untuk menghapus pemisah ribuan
function unformatNumber(value) {
    return value.toString().replace(/\D/g, ''); // Hapus semua karakter non-digit
}

function submitEditCart(cart_id, nama_barang_jual, price, qty, discount, total) {
    $.ajax({
        type: 'POST',
        url: '<?=site_url('sale/process')?>',
        data: {
            'edit_cart': true,
            'cart_id': cart_id,
            'nama_barang_jual': nama_barang_jual,
            'price': price,
            'qty': qty,
            'discount': discount,
            'total': total
        },
        dataType: 'json',
        success: function(result) {

            if(result.success == true) {

                $('#cart_table').load('<?=site_url('sale/cart_data')?>', function() {
                    calculate();
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Barang di keranjang berhasil diedit'
                });

                $('#modal-item-edit').modal('hide');

            } else {

                Swal.fire({
                    icon: "error",
                    title: result.message || "Data item cart tidak ter-update"
                });

                $('#modal-item-edit').modal('hide');
            }
        }
    });
}

$(document).on('click', '#edit_cart', function() {

    var cart_id = $('#cartid_item').val();
    var nama_barang_jual = $('#product_item').val();

    var price = unformatNumber($('#price_item').val());
    var qty = unformatNumber($('#qty_item').val());
    var discount = unformatNumber($('#discount_item').val());
    var total = unformatNumber($('#total_item').val());
    var stock = $('#stock_item').val();
    var statusItem = $('#status_item_edit').val();
    var modalItem = parseFloat($('#modal_item').val()) || 0;

    if(price == '' || parseInt(price) < 1) {

        Swal.fire({
            icon: "error",
            title: "Harga Tidak Boleh Kosong"
        });
        $('#price_item').focus();
        return;

    } else if(parseInt(price) === 1) {

        Swal.fire({
            icon: "error",
            title: "Harga Rp 1 Tidak Bisa Dijual",
            text: "Rp 1 adalah harga sementara (khusus untuk pengurangan stok). Silahkan masukkan harga jual yang sebenarnya."
        });
        $('#price_item').focus();
        return;

    } else if(qty == '' || parseInt(qty) < 1) {

        Swal.fire({
            icon: "error",
            title: "Qty Tidak Boleh Kosong"
        });
        $('#qty_item').focus();
        return;

    }
    // 🔥 CEK STOK HANYA JIKA ACTIVE
    else if(statusItem !== 'temporary'
            && parseInt(qty) > parseInt(stock)) {

        Swal.fire({
            icon: "error",
            title: "Stock Tidak Mencukupi"
        });
        $('#qty_item').focus();
        return;

    } else {

        // 🔥 HARGA DIBAWAH MODAL → TIDAK BISA DIPROSES
        if(modalItem > 0 && parseInt(price) < modalItem) {
            Swal.fire({
                icon: "error",
                title: "Harga Dibawah Modal",
                html: "Harga jual <b>Rp " + formatNumber(price) + "</b> berada di bawah harga modal (Rp " +
                      formatNumber(modalItem) + ").<br>Transaksi tidak dapat diproses dengan harga ini."
            });
            $('#price_item').focus();
            return;
        }

        // 🔥 CEK MARGIN MINIMAL 25% DARI HARGA MODAL (peringatan saja)
        if(modalItem > 0) {
            var margin = ((parseInt(price) - modalItem) / modalItem) * 100;
            if(margin < 25) {
                Swal.fire({
                    icon: "warning",
                    title: "Margin Harga Dibawah 25%",
                    html: "Harga jual <b>Rp " + formatNumber(price) + "</b> hanya memiliki margin <b>" +
                          margin.toFixed(1) + "%</b> dari harga modal (Rp " + formatNumber(modalItem) + ").<br>" +
                          "Margin minimal yang disarankan adalah <b>25%</b>.<br><br>Lanjutkan menyimpan harga ini?",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Lanjutkan",
                    cancelButtonText: "Batal, Ubah Harga"
                }).then((result) => {
                    if(result.isConfirmed) {
                        submitEditCart(cart_id, nama_barang_jual, price, qty, discount, total);
                    } else {
                        $('#price_item').focus();
                    }
                });
                return;
            }
        }

        submitEditCart(cart_id, nama_barang_jual, price, qty, discount, total);

    }

});

$(document).ready(function () {

    function toggleCustomerName() {
        let customerId = $('#customer').val();

        if (customerId == '0') {
            // UMUM
            $('#customer_name_group').show();
        } else {
            // MEMBER
            $('#customer_name_group').hide();
            $('#customer_name').val('');
        }
    }

    $('#customer').change(toggleCustomerName);

    // auto saat load
    toggleCustomerName();

});


$(document).on('click', '#process_payment', function() {
    var customer_id = $('#customer').val();
    var customer_name = $('#customer_name').val();
    var subtotal = unformatNumber($('#sub_total').val()); // Unformat subtotal
    var discount = unformatNumber($('#discount').val()); // Unformat diskon
    var grandtotal = unformatNumber($('#grand_total').val()); // Unformat grandtotal
    var cash = unformatNumber($('#cash').val()); // Unformat cash
    var change = unformatNumber($('#change').val()); // Unformat kembalian
    var note = $('#note').val()
    var date = $('#date').val()
    var payment_method = $('#payment_method').val();

    // 🔥 CEK HARGA RP 1 / DIBAWAH MODAL / MARGIN < 25% UNTUK SEMUA ITEM DI KERANJANG
    var hasPriceOne = false;
    var belowCostItems = [];
    var lowMarginItems = [];
    $('#cart_table button[id=update_cart]').each(function() {
        var itemPrice = parseFloat($(this).data('price')) || 0;
        var itemModal = parseFloat($(this).data('modal')) || 0;
        var itemNama  = $(this).data('nama_barang_jual');

        if (itemPrice === 1) {
            hasPriceOne = true;
        } else if (itemModal > 0 && itemPrice > 0) {
            if (itemPrice < itemModal) {
                belowCostItems.push({ nama: itemNama, price: itemPrice, modal: itemModal });
            } else {
                var margin = ((itemPrice - itemModal) / itemModal) * 100;
                if (margin < 25) {
                    lowMarginItems.push({ nama: itemNama, price: itemPrice, modal: itemModal, margin: margin });
                }
            }
        }
    });

    function doFinalConfirm() {
        Swal.fire({
            title: "Yakin proses transaksi ini?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Proses"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                type: 'POST',
                url: '<?=site_url('sale/process')?>',
                data: {'process_payment': true, 'customer_id': customer_id, 'customer_name': customer_name, 'subtotal': subtotal,
                    'discount': discount, 'grandtotal': grandtotal, 'cash': cash, 'change': change,
                    'note': note, 'date': date,'payment_method':payment_method},
                dataType: 'json',
                success: function(result) {
                    if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Penjualan Berhasil dibuat',
                            }).then(() => {
                                if (result.po_alerts && result.po_alerts.length > 0) {
                                    var names = result.po_alerts.map(function(a) {
                                        return '<li>' + a.nama + ' — <b>' + (a.status === 'habis' ? 'HABIS' : 'Sisa ' + a.sisa_stok) + '</b></li>';
                                    }).join('');
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Stok Menipis / Habis',
                                        html: '<p>Item berikut sudah ditambahkan ke <strong>Keranjang PO</strong>:</p><ul style="text-align:left">' + names + '</ul>',
                                        confirmButtonText: 'OK',
                                    }).then(() => {
                                        location.href = '<?=site_url('sale/preview/')?>' + result.sale_id;
                                    });
                                } else {
                                    location.href = '<?=site_url('sale/preview/')?>' + result.sale_id;
                                }
                            });
                    } else {
                        Swal.fire({
                                icon: 'error',
                                title: 'Transaksi gagal',
                                text: result.message || 'Terjadi kesalahan saat memproses transaksi.',

                            });
                        }
                    },
                });
            }
        });
    }

    if(subtotal < 1) {
        Swal.fire({
            icon: "error",
            title: "Belum ada product item yang diinput",
        });
        $('#barcode').focus()
    } else if(hasPriceOne) {
        Swal.fire({
            icon: "error",
            title: "Ada Barang Dengan Harga Rp 1",
            text: "Rp 1 adalah harga sementara (khusus untuk pengurangan stok) dan tidak bisa dijual. Silahkan edit harga jual barang tersebut terlebih dahulu.",
        });
    } else if(belowCostItems.length > 0) {
        var belowListHtml = belowCostItems.map(function(it) {
            return '<li>' + it.nama + ' — Rp ' + formatNumber(it.price) +
                   ' (modal Rp ' + formatNumber(it.modal) + ')</li>';
        }).join('');
        Swal.fire({
            icon: 'error',
            title: 'Ada Harga Dibawah Modal',
            html: '<p style="text-align:left">Barang berikut dijual dibawah harga modal, transaksi tidak dapat diproses:</p>' +
                  '<ul style="text-align:left">' + belowListHtml + '</ul>',
        });
    } else if(payment_method == "cash" && cash < 1) {
        Swal.fire({
            icon: "error",
            title: "Uang Cash belum dimasukkan",
            text: "SILAHKAN MASUKKAN TERLEBIH DAHULU",
        });
        $('#cash').focus()

    }else if (payment_method == null || payment_method.trim() === "") {
        Swal.fire({
            icon: "error",
            title: "Metode Pembayaran Belum Dipilih",
            text: "SILAHKAN PILIH TERLEBIH DAHULU",
        });
    } else if (payment_method == "credit" && (!customer_id || customer_id == "0")) {
        Swal.fire({
            icon: "error",
            title: "Customer Wajib Dipilih",
            text: "Transaksi kredit wajib memilih customer terdaftar (bukan Umum), supaya piutang bisa dilacak di modul Finance.",
        });
    } else if(lowMarginItems.length > 0) {
        var listHtml = lowMarginItems.map(function(it) {
            return '<li>' + it.nama + ' — Rp ' + formatNumber(it.price) +
                   ' (margin ' + it.margin.toFixed(1) + '%, modal Rp ' + formatNumber(it.modal) + ')</li>';
        }).join('');
        Swal.fire({
            icon: 'warning',
            title: 'Margin Harga Dibawah 25%',
            html: '<p style="text-align:left">Barang berikut memiliki margin keuntungan di bawah 25% dari harga modal:</p>' +
                  '<ul style="text-align:left">' + listHtml + '</ul><p>Lanjutkan proses transaksi?</p>',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal, Cek Harga'
        }).then((result) => {
            if (result.isConfirmed) {
                doFinalConfirm();
            }
        });
    } else {
        doFinalConfirm();
    }
});

$(document).ready(function() {
    $('#payment_method').change(function() {
        var paymentMethod = $(this).val();

        if (paymentMethod === 'cash') {
            // Tampilkan kolom cash dan kembalian
            $('#cash_row').show();
            $('#change_row').show();(
            function() {
                        calculate()
                    })
            
        } else {
            // Sembunyikan kolom cash dan kembalian, reset nilainya
            $('#cash_row').hide();
            $('#change_row').hide();
            $('#cash').val();
            $('#change').val(0);
        }
    });
});



$(document).on('click', '#cancel_payment', function(){
    Swal.fire({
            title: "Apakah Anda Yakin?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Proses"
        }).then((result) => {
            if (result.isConfirmed) {
        $.ajax({
            type : 'POST',
            url : '<?=site_url('sale/cart_del')?>',
            dataType : 'json',
            data : {'cancel_payment': true},
            success : function(result){
                if(result.success == true){
                    $('#cart_table').load('<?=site_url('sale/cart_data')?>', function(){
                        reloadJasaCart();
                    })
                }
            }
        })
        // Hapus juga cart jasa
        $.post('<?= site_url('sale/del_jasa_cart') ?>', { clear_all: 1 });
        $('#grand_total').val('')
        $('#grand_total2').text(0)
        $('#sub_total').val('')
        $('#discount').val('')
        $('#cash').val('')
        $('#customer').val('').change()
        $('#barcode').val('')
        lastSearchKeyword = '' // reset keyword saat transaksi dibatalkan
        $('#barcode').focus()
    }
})
})


// Tampilkan SweetAlert2 Toast untuk notif low stock (non-blocking)
function showLowStockToast(info) {
    if (!info || !info.show) return;
    var isHabis = info.type === 'habis';
    Swal.fire({
        toast             : true,
        position          : 'top-end',
        icon              : isHabis ? 'error' : 'warning',
        title             : info.message,
        showConfirmButton : false,
        timer             : 4500,
        timerProgressBar  : true,
    });
}

// Function to format numbers with thousand separators (for display purposes)
function formatNumber(value) {
    let numberString = value.toString().replace(/\D/g, ''); // Remove non-digit characters
    let formattedValue = numberString.replace(/\B(?=(\d{3})+(?!\d))/g, "."); // Add thousand separators
    return formattedValue;
}

// Function to calculate totals and update the UI
function calculate() {
    var subtotal = 0;

    // Calculate subtotal from barang cart rows
    $('#cart_table tr').each(function () {
        let totalText = $(this).find('#total').text().replace(/\D/g, '');
        subtotal += parseInt(totalText) || 0;
    });

    // Add jasa cart totals
    $('#jasa_cart_table .jasa-total-raw').each(function () {
        subtotal += parseInt($(this).text()) || 0;
    });

    // Update subtotal field
    $('#sub_total').val(formatNumber(subtotal));
    $('#sub_total').data('raw', subtotal);

    // Get discount value
    let discount = parseFloat($('#discount').val().replace(/\D/g, '')) || 0;

    // Calculate grand total
    let grandtotal = subtotal - (subtotal * discount / 100);
    if (isNaN(grandtotal)) {
        $('#grand_total').val(0);
        $('#grand_total2').text('Rp. 0');
    } else {
        $('#grand_total').val(formatNumber(grandtotal));
        $('#grand_total2').text('Rp. ' + formatNumber(grandtotal));
        $('#grand_total').data('raw', grandtotal);
    }

    // Handle cash input and calculate change
    let cash = parseFloat($('#cash').val().replace(/\D/g, '')) || 0;
    let change = cash - grandtotal;
    $('#change').val(change >= 0 ? formatNumber(change) : 0);

    // Update badge jumlah item di keranjang
    var itemCount = $('#cart_table tr').filter(function() {
        return $(this).find('td').length > 0;
    }).length;
    $('#cart-count').text(itemCount + ' item');
    $('#cart-count').css('background', itemCount > 0 ? '#00a65a' : '#aaa');

    // Update badge jasa
    var jasaCount = $('#jasa_cart_table tr').filter(function() {
        return $(this).find('.jasa-total-raw').length > 0;
    }).length;
    $('#jasa-count').text(jasaCount + ' jasa');
    $('#jasa-count').css('background', jasaCount > 0 ? '#3c8dbc' : '#aaa');
}

// ── Jasa Cart JS ────────────────────────────────────────────────────

function reloadJasaCart() {
    $('#jasa_cart_table').load('<?= site_url('sale/cart_jasa_data') ?>', function() {
        calculate();
    });
}

$(document).on('click', '#btn_add_jasa', function() {
    var jasa_id = $('#jasa_select').val();
    var qty     = parseInt($('#jasa_qty').val()) || 1;

    if (!jasa_id) {
        Swal.fire({ icon: 'warning', title: 'Pilih jasa terlebih dahulu' });
        return;
    }

    $.ajax({
        type: 'POST',
        url: '<?= site_url('sale/add_jasa_cart') ?>',
        data: { jasa_id: jasa_id, qty: qty },
        dataType: 'json',
        success: function(result) {
            if (result.success) {
                reloadJasaCart();
                $('#jasa_select').val('');
                $('#jasa_qty').val(1);
            } else {
                Swal.fire({ icon: 'error', title: result.message || 'Gagal menambah jasa' });
            }
        }
    });
});

$(document).on('click', '.btn-del-jasa', function() {
    var id = $(this).data('id');
    Swal.fire({
        title: 'Hapus jasa dari keranjang?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then(function(r) {
        if (r.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: '<?= site_url('sale/del_jasa_cart') ?>',
                data: { id: id },
                dataType: 'json',
                success: function() { reloadJasaCart(); }
            });
        }
    });
});

// ── Edit Jasa ─────────────────────────────────────────────────────

function updateJasaEditTotal() {
    var tarif = parseInt($('#jasa_edit_tarif').val().replace(/\D/g, '')) || 0;
    var qty   = parseInt($('#jasa_edit_qty').val()) || 0;
    var total = tarif * qty;
    $('#jasa_edit_total').text('Rp ' + formatNumber(total));
}

$(document).on('click', '.btn-edit-jasa', function() {
    $('#jasa_edit_id').val($(this).data('id'));
    $('#jasa_edit_nama').val($(this).data('nama'));
    $('#jasa_edit_tarif').val(formatNumber($(this).data('tarif')));
    $('#jasa_edit_qty').val($(this).data('qty'));
    updateJasaEditTotal();
    $('#modal-jasa-edit').modal('show');
});

$(document).on('keyup mouseup', '#jasa_edit_tarif, #jasa_edit_qty', function() {
    if (this.id === 'jasa_edit_tarif') {
        var raw = this.value.replace(/\D/g, '');
        this.value = raw ? formatNumber(raw) : '';
    }
    updateJasaEditTotal();
});

$(document).on('keypress', '#modal-jasa-edit input', function(e) {
    if (e.which === 13) { e.preventDefault(); $('#btn_save_jasa_edit').trigger('click'); }
});

$(document).on('click', '#btn_save_jasa_edit', function() {
    var id    = $('#jasa_edit_id').val();
    var nama  = $('#jasa_edit_nama').val().trim();
    var tarif = parseInt($('#jasa_edit_tarif').val().replace(/\D/g, '')) || 0;
    var qty   = parseInt($('#jasa_edit_qty').val()) || 0;

    if (!nama) {
        Swal.fire({ icon: 'warning', title: 'Nama jasa tidak boleh kosong' });
        return;
    }
    if (tarif < 1) {
        Swal.fire({ icon: 'warning', title: 'Tarif tidak boleh 0' });
        return;
    }
    if (qty < 1) {
        Swal.fire({ icon: 'warning', title: 'Qty minimal 1' });
        return;
    }

    $.ajax({
        type: 'POST',
        url: '<?= site_url('sale/update_jasa_cart') ?>',
        data: { id: id, nama_jasa: nama, tarif: tarif, qty: qty, total: tarif * qty },
        dataType: 'json',
        success: function(result) {
            if (result.success) {
                $('#modal-jasa-edit').modal('hide');
                reloadJasaCart();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal menyimpan' });
            }
        }
    });
});

// Event listener for cash input
$(document).on('keyup mouseup', '#cash', function () {
    $(this).val(formatNumber($(this).val())); // Format the input value
    calculate();
});

// Event listener for discount input
$(document).on('keyup mouseup', '#discount', function () {
    $(this).val(formatNumber($(this).val())); // Format the input value
    calculate();
});

// Initialize calculations on page load
$(document).ready(function () {
    calculate();
});

  $(document).ready(function(){
  $(document).ready(function () {
    $('#supplierDropdown').select2({
        placeholder: 'Pilih Supplier',
        allowClear: true
    });
    $('#categoryDropdown').select2({
        placeholder: 'Pilih Kategori',
        allowClear: true
    });
    $('#unitDropdown').select2({
        placeholder: 'Pilih Unit',
        allowClear: true
    });
    $('#customer').select2({
        placeholder: 'Pilih customer',
        allowClear: true
    });
    $('#table1').DataTable()
    })
    $('#table2').DataTable()
  })
  
   let idleTime = 0;
    const idleLimit = 60; // dalam detik (misal: 60 detik)

    // Reset idle time ketika ada aktivitas
    function resetIdleTime() {
        idleTime = 0;
    }

    // Tambahkan event listener untuk aktivitas user
    window.onload = function () {
        document.onmousemove = resetIdleTime;
        document.onkeypress = resetIdleTime;
        document.onclick = resetIdleTime;
        document.onscroll = resetIdleTime;
    };

    // Hitung waktu idle setiap detik
    setInterval(function () {
        idleTime++;
        if (idleTime >= idleLimit) {
            idleTime = 0;
            // Refresh cart saja (barang + jasa), field lain (customer, catatan, metode bayar, dll) tidak ikut ter-reset
            $('#cart_table').load('<?=site_url('sale/cart_data')?>', function(){
                calculate();
                $('#barcode').focus();
            });
            $('#jasa_cart_table').load('<?= site_url('sale/cart_jasa_data') ?>', function() {
                calculate();
            });
        }
    }, 1000); // setiap 1 detik



// $(document).ready(function() {
//     // Fungsi untuk fokus ke input pencarian berdasarkan tabel
//     function focusSearchBox(tableAriaControl) {
//         var $searchInput = $('input[aria-controls="' + tableAriaControl + '"]');
//         if ($searchInput.length) {
//             $searchInput.focus().select();
//             console.log("Input pencarian ditemukan dan difokuskan pada tabel:", tableAriaControl);
//         } else {
//             console.warn("Input pencarian tidak ditemukan untuk tabel:", tableAriaControl);
//         }
//     }

//     // Shortcut Keyboard: Ctrl + F atau F2
//     $(window).on('keydown', function(e) {
//         // Ctrl + F = 70, F2 = 113
//         if ((e.ctrlKey && e.keyCode === 70) || e.keyCode === 113) {
//             e.preventDefault();

//             // Jika modal sedang terbuka, fokus ke pencarian modal (table1)
//             if ($('#modal-item').hasClass('in')) {
//                 focusSearchBox('table1');
//             } else {
//                 // Jika tidak di modal, fokus ke pencarian utama (table-item)
//                 focusSearchBox('table-item');
//             }
//         }
//     });

//     // Opsional: Saat klik input pencarian, auto-select teks
//     $(document).on('focus', 'input[aria-controls]', function () {
//         $(this).select();
//     });
// });
</script>
