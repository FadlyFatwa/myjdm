<?php if (!empty($cart_jasa)): $no = 1; foreach ($cart_jasa as $j): ?>
<tr>
    <td class="text-center"><?= $no++ ?></td>
    <td><?= htmlspecialchars($j->nama_jasa) ?></td>
    <td class="text-right"><?= number_format($j->tarif, 0, ',', '.') ?></td>
    <td class="text-right"><?= $j->qty ?></td>
    <td class="text-right jasa-total"><?= number_format($j->total, 0, ',', '.') ?></td>
    <td class="hidden jasa-total-raw"><?= $j->total ?></td>
    <td class="text-center">
        <button class="btn btn-xs btn-primary btn-edit-jasa"
                data-id="<?= $j->id ?>"
                data-nama="<?= htmlspecialchars($j->nama_jasa) ?>"
                data-tarif="<?= $j->tarif ?>"
                data-qty="<?= $j->qty ?>">
            <i class="fa fa-pencil"></i> Edit
        </button>
        <button class="btn btn-xs btn-danger btn-del-jasa" data-id="<?= $j->id ?>">
            <i class="fa fa-trash"></i> Hapus
        </button>
    </td>
</tr>
<?php endforeach; else: ?>
<tr id="jasa-empty-row">
    <td colspan="7" class="text-center text-muted">Belum ada jasa</td>
</tr>
<?php endif; ?>
