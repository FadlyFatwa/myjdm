<section class="content-header">
    <h1>Produk
        <small>Deteksi Item Mirip</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-archive"></i></a></li>
        <li class="active">Produk</li>
        <li class="active">Deteksi Item Mirip</li>
    </ol>
</section>

<section class="content">

    <div class="box box-warning">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-info-circle"></i> Tentang Halaman Ini</h3>
        </div>
        <div class="box-body">
            <p>
                Halaman ini mendeteksi barang yang kemungkinan <strong>sama tetapi ke-input ulang</strong>
                dengan nama yang berbeda susunan kata, singkatan, atau typo — biasanya terjadi saat barang
                yang sama dibeli dari supplier yang berbeda. Akibatnya stock &amp; data harga jadi terpecah
                di beberapa baris, sehingga pengadaan jadi tidak akurat.
            </p>
            <p class="text-muted">
                Item dikelompokkan berdasarkan kemiripan nama (mengabaikan urutan kata) dan dibandingkan
                hanya dalam kategori yang sama. Grup yang melibatkan <strong>lebih dari satu supplier</strong>
                ditandai sebagai prioritas karena paling berpotensi menyebabkan duplikasi data.
            </p>
            <p class="text-muted">
                <strong>Grade tidak pernah dicampur:</strong> tag <code>'G'</code> (Original), <code>'B'</code> (KW),
                dan <code>'L'</code> (Lelangan) dideteksi dari nama, dan item dengan grade berbeda
                <strong>tidak akan digabung</strong> meskipun nama sisanya identik/mirip — karena itu memang
                produk berbeda (beda harga &amp; keaslian). Item tanpa tag tersebut (cuma nama merk aftermarket
                biasa) dikelompokkan sebagai <span class="label label-info">Aftermarket</span>.
            </p>

            <form method="get" class="form-inline" style="margin-top:10px;">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="fuzzy" value="1" <?=$include_fuzzy ? 'checked' : ''?>>
                        Sertakan deteksi typo/singkatan (mode lanjutan, lebih lambat)
                    </label>
                </div>
                <div class="form-group" style="<?=$include_fuzzy ? '' : 'opacity:0.5'?>">
                    <label for="threshold">Tingkat Kemiripan Minimal</label>
                    <select name="threshold" id="threshold" class="form-control" style="margin-left:5px;" <?=$include_fuzzy ? '' : 'disabled'?>>
                        <?php foreach ([95, 90, 85, 80, 75, 70, 60, 50] as $t): ?>
                            <option value="<?=$t?>" <?=$t == $threshold ? 'selected' : ''?>><?=$t?>%</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                &nbsp;
                <button type="submit" class="btn btn-primary btn-flat">
                    <i class="fa fa-refresh"></i> Jalankan Ulang
                </button>
                <p class="text-muted" style="margin-top:8px;">
                    Tanpa mode lanjutan: hanya mencocokkan item yang kata-katanya sama tapi urutannya beda
                    (instan, untuk <?=number_format($total_items)?> item aktif). Mode lanjutan menambah
                    deteksi typo/singkatan tapi prosesnya lebih berat.
                </p>
            </form>
        </div>
    </div>

    <?php if (empty($groups)): ?>
        <div class="alert alert-success">
            <i class="fa fa-check-circle"></i> Tidak ditemukan item dengan nama mirip pada ambang batas <?=$threshold?>%.
        </div>
    <?php else: ?>

        <div class="alert alert-info">
            <i class="fa fa-list"></i> Ditemukan <strong><?=count($groups)?></strong> grup item yang berpotensi duplikat.
        </div>

        <?php $is_admin = in_array($this->fungsi->user_login()->level, [1, 2]); ?>

        <?php foreach ($groups as $index => $group): ?>
            <?php
                $supplier_ids = array_unique(array_column($group, 'supplier_id'));
                $is_multi_supplier = count($supplier_ids) > 1;

                // default target = item dengan stok paling banyak
                $default_target = $group[0];
                foreach ($group as $item) {
                    if ($item->stock > $default_target->stock) $default_target = $item;
                }

                $grade_label_map = ['G' => 'Original', 'B' => 'KW', 'L' => 'Lelangan', 'AFTERMARKET' => 'Aftermarket'];
                $grade_tag       = $group[0]->grade ?? 'AFTERMARKET'; // satu grup pasti satu grade (sudah jadi gerbang union)
                $grade_label     = $grade_label_map[$grade_tag] ?? $grade_tag;
                $grade_badge_class = $grade_tag === 'G' ? 'label-success' : ($grade_tag === 'B' ? 'label-danger' : ($grade_tag === 'L' ? 'label-warning' : 'label-info'));
            ?>
            <div class="box <?= $is_multi_supplier ? 'box-danger' : 'box-default' ?> group-card" data-group-index="<?=$index?>">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        Grup #<?=$index + 1?>
                        <span class="label <?=$grade_badge_class?>"><i class="fa fa-tag"></i> <?=$grade_label?></span>
                        <?php if ($is_multi_supplier): ?>
                            <span class="label label-danger">Beda Supplier — Prioritas</span>
                        <?php else: ?>
                            <span class="label label-default">Supplier Sama</span>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <?php if ($is_admin): ?>
                                    <th width="60px" class="text-center">Target</th>
                                    <th width="60px" class="text-center">Merge?</th>
                                <?php endif; ?>
                                <th>Barcode</th>
                                <th>Nama Barang</th>
                                <th>Supplier</th>
                                <th>Kategori</th>
                                <?php if ($is_admin): ?>
                                    <th>Modal</th>
                                <?php endif; ?>
                                <th>Harga</th>
                                <th>Stock</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($group as $item): ?>
                                <?php $is_default_target = $item->item_id === $default_target->item_id; ?>
                                <tr>
                                    <?php if ($is_admin): ?>
                                        <td class="text-center">
                                            <input type="radio" class="radio-target" name="target_<?=$index?>" value="<?=$item->item_id?>" <?=$is_default_target ? 'checked' : ''?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" class="chk-merge" value="<?=$item->item_id?>" checked>
                                        </td>
                                    <?php endif; ?>
                                    <td><?=htmlspecialchars($item->barcode)?></td>
                                    <td><?=htmlspecialchars($item->nama_item)?></td>
                                    <td><?=htmlspecialchars($item->nama_supplier ?? '-')?></td>
                                    <td><?=htmlspecialchars($item->nama_category ?? '-')?></td>
                                    <?php if ($is_admin): ?>
                                        <td><?=indo_currency($item->modal)?></td>
                                    <?php endif; ?>
                                    <td><?=indo_currency($item->price)?></td>
                                    <td><?=$item->stock?></td>
                                    <td>
                                        <a href="<?=site_url('item/edit/' . $item->item_id)?>" class="btn btn-primary btn-xs">
                                            <i class="fa fa-pencil"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($is_admin): ?>
                    <div class="box-footer">
                        <button class="btn btn-warning btn-merge" data-group-index="<?=$index?>">
                            <i class="fa fa-compress"></i> Merge Grup Ini ke Item Target
                        </button>
                        <span class="text-muted" style="font-size:12px;margin-left:10px;">
                            Item lain akan dinonaktifkan, stoknya digabung ke item target, dan harga/suppliernya dipindahkan ke target.
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</section>

<script>
$(document).ready(function () {
    $('input[name="fuzzy"]').on('change', function () {
        const enabled = $(this).is(':checked');
        $('#threshold').prop('disabled', !enabled).closest('.form-group').css('opacity', enabled ? 1 : 0.5);
    });

    $(document).on('click', '.btn-merge', function () {
        var card      = $(this).closest('.group-card');
        var targetId  = card.find('.radio-target:checked').val();
        var itemIds   = [];
        card.find('.chk-merge:checked').each(function () { itemIds.push($(this).val()); });

        if (!targetId) {
            Swal.fire({ icon: 'warning', title: 'Pilih item target terlebih dahulu.', timer: 1800, showConfirmButton: false });
            return;
        }
        var loserCount = itemIds.filter(function (id) { return id !== targetId; }).length;
        if (loserCount === 0) {
            Swal.fire({ icon: 'warning', title: 'Pilih minimal satu item lain (selain target) untuk di-merge.', timer: 2200, showConfirmButton: false });
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: 'Merge ' + loserCount + ' item ke item target?',
            html: 'Item selain target akan <b>dinonaktifkan</b>, stoknya digabung ke target, dan data harga/suppliernya dipindahkan ke target. Aksi ini tidak bisa dibatalkan otomatis.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Merge',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#f39c12'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var btn = card.find('.btn-merge').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');
            $.post('<?= site_url('item/merge') ?>', {
                target_id: targetId,
                item_ids:  itemIds
            }, function (res) {
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: res.message, timer: 2200, showConfirmButton: false })
                        .then(function () { location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fa fa-compress"></i> Merge Grup Ini ke Item Target');
                    Swal.fire({ icon: 'error', title: res.message || 'Gagal melakukan merge.' });
                }
            }, 'json').fail(function (xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-compress"></i> Merge Grup Ini ke Item Target');
                Swal.fire({ icon: 'error', title: 'Gagal melakukan merge.', text: xhr.responseText ? xhr.responseText.substring(0, 300) : '' });
            });
        });
    });
});
</script>
