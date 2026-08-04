<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$total_items     = 0;
$total_suppliers = count($grouped_cart);
foreach ($grouped_cart as $g) { $total_items += count($g['items']); }
$is_superadmin = ($this->fungsi->user_login()->level == 1);
?>

<div class="content-header">
    <h1><i class="fa fa-shopping-basket" style="color:#00a65a"></i> Keranjang PO
        <small>dikelompok per supplier</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?= site_url('stock-review') ?>">Review Stok</a></li>
        <li class="active">Keranjang PO</li>
    </ol>
</div>

<style>
/* capsule dark mode — lebih terlihat */
body.dark-mode #sup-nav .sup-chip.btn-default {
    background:#2a2f45; border-color:#4a5070; color:#c5cbe0;
}
body.dark-mode #sup-nav .sup-chip.btn-default:hover {
    background:#343a56; border-color:#6070a0; color:#e8ecf5;
}
/* capsule light mode — border lebih tegas */
#sup-nav .sup-chip.btn-default {
    border-color:#bfc8d6;
}
</style>

<div class="content">

<?php if (empty($grouped_cart)): ?>

<div class="box box-default">
    <div class="box-body text-center" style="padding:60px 20px">
        <i class="fa fa-shopping-basket" style="font-size:52px;color:#ddd"></i>
        <p style="margin:18px 0 6px;font-size:16px;color:#888;font-weight:600">Keranjang PO kosong</p>
        <p class="text-muted">Tambah item dari <a href="<?= site_url('stock-review') ?>">Review Stok</a> atau gunakan tombol di bawah.</p>
        <div style="margin-top:16px;display:flex;gap:8px;justify-content:center">
            <a href="<?= site_url('stock-review') ?>" class="btn btn-default btn-sm">
                <i class="fa fa-plus"></i> Dari Stok
            </a>
            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-item-baru">
                <i class="fa fa-plus-circle"></i> Item Baru
            </button>
        </div>
    </div>
</div>

<?php else: ?>

<!-- Info strip -->
<div class="row" style="margin-bottom:10px">
    <div class="col-xs-12" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <span class="badge bg-green" style="font-size:13px;padding:5px 10px"><?= $total_items ?> item</span>
        <span class="badge bg-blue" style="font-size:13px;padding:5px 10px"><?= $total_suppliers ?> supplier</span>
        <div style="margin-left:auto;display:flex;gap:6px">
            <a href="<?= site_url('stock-review') ?>" class="btn btn-default btn-sm">
                <i class="fa fa-plus"></i> Dari Stok
            </a>
            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-item-baru">
                <i class="fa fa-plus-circle"></i> Item Baru
            </button>
        </div>
    </div>
</div>

<div class="row">

    <!-- ── Kolom kiri: daftar supplier ──────────────────────── -->
    <div class="col-md-3 col-sm-4">
        <div class="box box-default">
            <div class="box-header with-border" style="padding:10px 12px">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <input type="text" id="sup-search" class="form-control" placeholder="Cari supplier...">
                </div>
            </div>
            <div class="box-body" style="max-height:calc(100vh - 340px);overflow-y:auto;display:grid;grid-template-columns:repeat(4,1fr);gap:5px" id="sup-nav">
                <?php foreach ($grouped_cart as $i => $group): ?>
                <a href="#" class="btn btn-sm btn-default sup-chip <?= $i === 0 ? 'btn-success' : '' ?>"
                   data-sid="<?= $group['supplier_id'] ?>"
                   data-name="<?= strtolower(htmlspecialchars($group['nama_supplier'])) ?>"
                   style="border-radius:20px;font-size:12px;display:flex;align-items:center;justify-content:center;gap:4px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;padding:4px 8px">
                    <span style="overflow:hidden;text-overflow:ellipsis;min-width:0"><?= htmlspecialchars($group['nama_supplier']) ?></span>
                    <span class="badge" style="flex-shrink:0"><?= count($group['items']) ?></span>
                </a>
                <?php endforeach; ?>
                <p class="text-muted" id="no-sup-msg" style="display:none;width:100%;font-size:12px;text-align:center;margin:10px 0">
                    <i class="fa fa-search"></i> Tidak ditemukan
                </p>
            </div>
            <div class="box-footer" style="padding:8px 12px;font-size:11px" id="sup-count-foot">
                <i class="fa fa-info-circle text-muted"></i>
                <span class="text-muted"><?= $total_suppliers ?> supplier</span>
            </div>
        </div>
    </div>

    <!-- ── Kolom kanan: item supplier aktif ─────────────────── -->
    <div class="col-md-9 col-sm-8">
        <?php foreach ($grouped_cart as $i => $group): ?>
        <div class="po-panel <?= $i === 0 ? '' : 'hidden' ?>" id="panel-<?= $group['supplier_id'] ?>">

            <div class="box box-success box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-truck"></i>
                        <?= htmlspecialchars($group['nama_supplier']) ?>
                        <small id="pcnt-<?= $group['supplier_id'] ?>" class="text-muted" style="font-size:12px">
                            &nbsp;· <?= count($group['items']) ?> item
                        </small>
                    </h3>
                </div>
                <div class="box-body no-padding" style="max-height:calc(100vh - 340px);overflow-y:auto">
                    <table class="table table-hover table-condensed" style="margin:0">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th class="text-center" width="70">Stok</th>
                                <th class="text-center" width="115">Qty</th>
                                <th class="text-right"  width="130">Harga Ref</th>
                                <th>Catatan</th>
                                <th width="40"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($group['items'] as $item): ?>
                            <tr class="item-row" data-id="<?= $item->id ?>">
                                <td style="vertical-align:middle">
                                    <?php if ($item->barcode): ?>
                                    <small class="text-muted" style="font-family:monospace"><?= htmlspecialchars($item->barcode) ?></small><br>
                                    <?php endif; ?>
                                    <span class="item-display-name" style="font-size:15px;font-weight:600"><?= htmlspecialchars($item->display_name) ?></span>
                                    <?php if (!$item->item_id): ?>
                                    <span class="label label-warning" style="font-size:9px">Baru</span>
                                    <?php endif; ?>
                                    <?php
                                    $others = [];
                                    if ($item->item_id && !empty($multi_supplier_map[$item->item_id])) {
                                        foreach ($multi_supplier_map[$item->item_id] as $s) {
                                            if ((int) $s['supplier_id'] !== (int) $group['supplier_id']) {
                                                $others[] = $s['nama_supplier'];
                                            }
                                        }
                                    }
                                    ?>
                                    <?php if (!empty($others)): ?>
                                    <span class="label label-info" style="font-size:9px" title="Juga disuplai oleh: <?= htmlspecialchars(implode(', ', $others)) ?>">
                                        <i class="fa fa-random"></i> Multi-Supplier (<?= count($others) ?> lainnya)
                                    </span>
                                    <br><small class="text-muted" style="font-size:10px">
                                        <i class="fa fa-truck"></i> Lainnya: <?= htmlspecialchars(implode(', ', $others)) ?>
                                    </small>
                                    <?php endif; ?>
                                    <?php if (!empty($item->created_at)): ?>
                                    <br><small class="text-muted">
                                        <i class="fa fa-clock-o"></i>
                                        <?= date('d M Y, H:i', strtotime($item->created_at)) ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center" style="vertical-align:middle">
                                    <?php
                                    $s = $item->stock ?? null;
                                    if ($s === null)  echo '<span class="text-muted">—</span>';
                                    elseif ($s <= 0)  echo '<span class="label label-danger">'.$s.'</span>';
                                    elseif ($s <= 3)  echo '<span class="label label-warning">'.$s.'</span>';
                                    else              echo '<span class="label label-success">'.$s.'</span>';
                                    ?>
                                </td>
                                <td class="text-center" style="vertical-align:middle;white-space:nowrap">
                                    <input type="number" class="form-control input-sm input-qty text-center"
                                        value="<?= $item->qty ?>" min="1" style="width:54px;display:inline-block">
                                    <?php if (!empty($item->nama_unit)): ?>
                                    <small class="text-muted"><?= htmlspecialchars($item->nama_unit) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right" style="vertical-align:middle">
                                    <input type="text" class="form-control input-sm input-ref_price text-right"
                                        value="<?= number_format((int)$item->ref_price, 0, ',', '.') ?>" style="width:108px;display:inline-block">
                                </td>
                                <td style="vertical-align:middle">
                                    <input type="text" class="form-control input-sm input-notes"
                                        value="<?= htmlspecialchars($item->notes ?? '') ?>"
                                        placeholder="—" style="min-width:80px">
                                </td>
                                <td class="text-center" style="vertical-align:middle">
                                    <a href="<?= site_url('po-cart/remove/' . $item->id) ?>"
                                       class="btn btn-danger btn-xs btn-remove-item"
                                       data-id="<?= $item->id ?>" title="Hapus">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="box-footer text-right">
                    <small class="text-muted hidden-xs" style="margin-right:10px"><i class="fa fa-info-circle"></i> Edit qty / harga lalu simpan</small>
                    <form action="<?= site_url('po-cart/clear') ?>" method="post" class="form-clear-supplier" style="display:inline;margin-right:4px">
                        <input type="hidden" name="supplier_id" value="<?= $group['supplier_id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm btn-clear-supplier">
                            <i class="fa fa-trash"></i> Kosongkan
                        </button>
                    </form>
                    <button class="btn btn-primary btn-sm btn-save-group" data-supplier_id="<?= $group['supplier_id'] ?>">
                        <i class="fa fa-save"></i> Simpan Perubahan
                    </button>
                    <?php if ($is_superadmin): ?>
                    <button class="btn btn-success btn-sm btn-buat-po" style="margin-left:4px"
                        data-supplier_id="<?= $group['supplier_id'] ?>"
                        data-supplier="<?= htmlspecialchars($group['nama_supplier']) ?>">
                        <i class="fa fa-file-text-o"></i> Buat PO
                    </button>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
    </div>

</div><!-- /.row -->
<?php endif; ?>
</div>

<!-- Modal Item Baru -->
<div class="modal fade" id="modal-item-baru" tabindex="-1">
    <div class="modal-dialog" style="max-width:460px">
        <div class="modal-content">
            <div class="modal-header" style="background:#222d32;color:#fff;border-bottom:3px solid #f39c12">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff">&times;</button>
                <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Item Baru (Belum Terdaftar)</h4>
            </div>
            <div class="modal-body">
                <div class="callout callout-info" style="margin-bottom:16px">
                    <p style="margin:0;font-size:12px"><i class="fa fa-info-circle"></i>
                    Untuk item yang <b>belum ada</b> di database. Bisa didaftarkan lengkap saat penerimaan GR.</p>
                </div>
                <div class="form-group">
                    <label>Nama Barang <span class="text-red">*</span></label>
                    <input type="text" id="new-item-name" class="form-control" placeholder="cth: Kampas Rem Honda Beat 2019">
                </div>
                <div class="form-group">
                    <label>Supplier <span class="text-red">*</span></label>
                    <select id="new-item-supplier" class="form-control" style="width:100%">
                        <option value="">— Pilih Supplier —</option>
                        <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s->supplier_id ?>"><?= htmlspecialchars($s->nama_supplier) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label>Qty Order <span class="text-red">*</span></label>
                            <input type="number" id="new-item-qty" class="form-control" value="1" min="1">
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label>Harga Ref (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-addon">Rp</span>
                                <input type="text" id="new-item-price" class="form-control input-ref_price" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Catatan</label>
                    <input type="text" id="new-item-notes" class="form-control" placeholder="Opsional">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                <button type="button" class="btn btn-warning" id="btn-add-new-item">
                    <i class="fa fa-plus"></i> Tambah ke Keranjang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buat PO -->
<div class="modal fade" id="modal-buat-po" tabindex="-1">
    <div class="modal-dialog" style="max-width:500px">
        <div class="modal-content">
            <div class="modal-header" style="background:#222d32;color:#fff;border-bottom:3px solid #00a65a">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff">&times;</button>
                <h4 class="modal-title"><i class="fa fa-file-text-o"></i> Buat Purchase Order</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="po-supplier_id">
                <div class="form-group">
                    <label>Supplier</label>
                    <p class="form-control-static" id="po-supplier-name" style="font-weight:700;font-size:15px"></p>
                </div>
                <div class="form-group">
                    <label>Item yang akan dipesan <small class="text-muted">(uncheck untuk tunda)</small></label>
                    <div id="po-item-checklist" style="border:1px solid #ddd;border-radius:4px;max-height:180px;overflow-y:auto;padding:6px 10px"></div>
                    <div style="margin-top:4px;font-size:11px;color:#888">
                        <a href="#" id="po-check-all">Pilih Semua</a> &nbsp;|&nbsp;
                        <a href="#" id="po-uncheck-all">Batal Semua</a>
                    </div>
                </div>
                <div class="form-group">
                    <label>Tanggal PO <span class="text-red">*</span></label>
                    <input type="date" id="po-date" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Expected Tiba</label>
                    <input type="date" id="po-expected_date" class="form-control">
                </div>
                <div class="form-group">
                    <label>Catatan PO</label>
                    <textarea id="po-notes" class="form-control" rows="2" placeholder="Opsional"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                <button type="button" class="btn btn-success" id="btn-submit-po">
                    <i class="fa fa-check"></i> Buat PO
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    function toast(icon, msg) {
        Swal.fire({ toast:true, position:'top-end', icon:icon, title:msg,
            showConfirmButton:false, timer:3000, timerProgressBar:true });
    }
    function formatRp(val) {
        return String(val).replace(/[^0-9]/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.');
    }
    function unformatRp(val) {
        return parseInt(String(val).replace(/\./g,''), 10) || 0;
    }

    $(document).on('input',  '.input-ref_price', function () { $(this).val(formatRp($(this).val())); });
    $(document).on('click',  '.input-ref_price', function () { $(this).select(); });

    <?php $flash = $this->session->flashdata('success'); if ($flash): ?>
    toast('success', '<?= addslashes($flash) ?>');
    <?php endif; ?>
    <?php $ferr = $this->session->flashdata('error'); if ($ferr): ?>
    toast('error', '<?= addslashes($ferr) ?>');
    <?php endif; ?>

    // ── Pilih supplier ───────────────────────────────────────────
    $('#sup-nav').on('click', '.sup-chip', function (e) {
        e.preventDefault();
        var sid = $(this).data('sid');
        $('.sup-chip').removeClass('btn-success').addClass('btn-default');
        $(this).removeClass('btn-default').addClass('btn-success');
        $('.po-panel').addClass('hidden');
        $('#panel-' + sid).removeClass('hidden');
    });

    // ── Search supplier ──────────────────────────────────────────
    $('#sup-search').on('input', function () {
        var kw = $(this).val().toLowerCase().trim();
        var vis = 0;
        $('.sup-chip').each(function () {
            var match = !kw || $(this).data('name').indexOf(kw) !== -1;
            $(this).toggle(match);
            if (match) vis++;
        });
        $('#no-sup-msg').toggle(vis === 0);
        $('#sup-count-foot span').text(vis + ' supplier');
    });

    // ── Buat PO ──────────────────────────────────────────────────
    $(document).on('click', '.btn-buat-po', function () {
        var sid = $(this).data('supplier_id');
        $('#po-supplier_id').val(sid);
        $('#po-supplier-name').text($(this).data('supplier'));
        var $cl = $('#po-item-checklist').empty();
        $('#panel-' + sid + ' tbody tr.item-row').each(function () {
            var id   = $(this).data('id');
            var name = $(this).find('.item-display-name').text().trim();
            var qty  = $(this).find('.input-qty').val();
            $cl.append(
                '<label style="display:flex;align-items:center;gap:8px;margin:4px 0;font-weight:normal;cursor:pointer">' +
                '<input type="checkbox" name="selected_ids[]" value="' + id + '" checked style="width:15px;height:15px"> ' +
                '<span>' + $('<span>').text(name).html() + ' <span class="text-muted">× ' + qty + '</span></span></label>'
            );
        });
        $('#modal-buat-po').modal('show');
    });

    $(document).on('click','#po-check-all',   function(e){ e.preventDefault(); $('#po-item-checklist input').prop('checked',true); });
    $(document).on('click','#po-uncheck-all', function(e){ e.preventDefault(); $('#po-item-checklist input').prop('checked',false); });

    $('#btn-submit-po').on('click', function () {
        var po_date = $('#po-date').val();
        if (!po_date) { toast('warning','Tanggal PO wajib diisi.'); return; }
        var selected = [];
        $('#po-item-checklist input:checked').each(function () { selected.push($(this).val()); });
        if (!selected.length) { toast('warning','Pilih minimal satu item.'); return; }
        var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Membuat...');
        $.post('<?= site_url('po-cart/create_po') ?>', {
            csrf_token:    $('meta[name="csrf-token"]').attr('content'),
            supplier_id:   $('#po-supplier_id').val(),
            po_date:       po_date,
            expected_date: $('#po-expected_date').val(),
            notes:         $('#po-notes').val(),
            'selected_ids[]': selected,
        }, function (res) {
            $btn.prop('disabled',false).html('<i class="fa fa-check"></i> Buat PO');
            if (res.status === 'success') {
                $('#modal-buat-po').modal('hide');
                window.location.href = '<?= site_url('purchase-order') ?>/' + res.po_id;
            } else { toast('error', res.message || 'Gagal membuat PO.'); }
        }, 'json').fail(function () {
            $btn.prop('disabled',false).html('<i class="fa fa-check"></i> Buat PO');
            toast('error','Terjadi kesalahan. Coba lagi.');
        });
    });

    // ── Item baru ────────────────────────────────────────────────
    $('#btn-add-new-item').on('click', function () {
        var nama     = $.trim($('#new-item-name').val());
        var supplier = $('#new-item-supplier').val();
        if (!nama)     { toast('warning','Nama barang wajib diisi.'); return; }
        if (!supplier) { toast('warning','Supplier wajib dipilih.'); return; }
        var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
        $.post('<?= site_url('po-cart/add') ?>', {
            item_name_temp: nama, supplier_id: supplier,
            qty: parseInt($('#new-item-qty').val()) || 1,
            ref_price: unformatRp($('#new-item-price').val()),
            notes: $('#new-item-notes').val(),
        }, function (res) {
            $btn.prop('disabled',false).html('<i class="fa fa-plus"></i> Tambah ke Keranjang');
            if (res.status === 'success' || res.status === 'info') {
                $('#modal-item-baru').modal('hide');
                toast('success', nama + ' ditambahkan.');
                setTimeout(function(){ location.reload(); }, 1200);
            } else { toast('error', res.message || 'Gagal menambahkan.'); }
        }, 'json');
    });

    $('#modal-item-baru').on('shown.bs.modal', function () {
        if (!$('#new-item-supplier').data('select2')) {
            $('#new-item-supplier').select2({ placeholder:'— Pilih Supplier —', allowClear:true,
                dropdownParent:$('#modal-item-baru'), width:'100%' });
        }
    });

    // ── Hapus item ───────────────────────────────────────────────
    $(document).on('click', '.btn-remove-item', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        Swal.fire({ title:'Hapus item ini dari keranjang?', icon:'warning',
            showCancelButton:true, confirmButtonText:'Ya, hapus', cancelButtonText:'Batal',
            confirmButtonColor:'#dd4b39'
        }).then(function(r){ if(r.isConfirmed) window.location.href = href; });
    });

    // ── Kosongkan supplier ───────────────────────────────────────
    $(document).on('click', '.btn-clear-supplier', function (e) {
        e.preventDefault();
        var $form = $(this).closest('form');
        Swal.fire({ title:'Kosongkan semua item supplier ini?', icon:'warning',
            showCancelButton:true, confirmButtonText:'Ya, kosongkan', cancelButtonText:'Batal',
            confirmButtonColor:'#dd4b39'
        }).then(function(r){ if(r.isConfirmed) $form.submit(); });
    });

    // ── Simpan ───────────────────────────────────────────────────
    $(document).on('click', '.btn-save-group', function () {
        var sid  = $(this).data('supplier_id');
        var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
        var rows = [];
        $('#panel-' + sid + ' tbody tr').each(function () {
            rows.push({ id:$(this).data('id'), qty:$(this).find('.input-qty').val(),
                ref_price:unformatRp($(this).find('.input-ref_price').val()),
                notes:$(this).find('.input-notes').val() });
        });
        var done = 0;
        rows.forEach(function (r) {
            $.post('<?= site_url('po-cart/update') ?>', r, function () {
                if (++done === rows.length) {
                    $btn.prop('disabled',false).html('<i class="fa fa-save"></i> Simpan');
                    toast('success','Perubahan berhasil disimpan.');
                }
            });
        });
    });
});
</script>
