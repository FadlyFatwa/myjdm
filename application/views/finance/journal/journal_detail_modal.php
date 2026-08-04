<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-header <?= $journal->status == 'posted' ? 'bg-primary' : 'bg-default' ?>">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Jurnal <?= $journal->journal_no ?></h4>
</div>
<div class="modal-body">
    <p>
        <strong>Tanggal:</strong> <?= tgl_finance($journal->journal_date) ?><br>
        <strong>Sumber:</strong>
        <?php
        $source_map = ['ar_invoice' => 'Piutang', 'ar_payment' => 'Pembayaran Piutang', 'manual_adjustment' => 'Jurnal Manual'];
        echo $source_map[$journal->source_type] ?? $journal->source_type;
        ?><?= $journal->source_id ? ' #' . $journal->source_id : '' ?><br>
        <strong>Keterangan:</strong> <?= $journal->description ?><br>
        <strong>Status:</strong>
        <?= $journal->status == 'posted' ? '<span class="label label-success">Terposting</span>' : '<span class="label label-default">Dibatalkan</span>' ?>
        <?php if ($journal->status == 'void'): ?>
            <br><strong>Alasan Pembatalan:</strong> <?= $journal->void_reason ?>
        <?php endif; ?>
    </p>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Kredit</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lines as $l): ?>
            <tr>
                <td><?= $l->coa_code ?></td>
                <td><?= $l->coa_name ?></td>
                <td class="text-right"><?= $l->debit > 0 ? number_format($l->debit, 0, ',', '.') : '' ?></td>
                <td class="text-right"><?= $l->kredit > 0 ? number_format($l->kredit, 0, ',', '.') : '' ?></td>
                <td><?= $l->notes ?></td>
            </tr>
            <?php endforeach; ?>
        </thead>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">Total</th>
                <th class="text-right"><?= number_format($journal->total_debit, 0, ',', '.') ?></th>
                <th class="text-right"><?= number_format($journal->total_kredit, 0, ',', '.') ?></th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
<div class="modal-footer">
    <?php if ($journal->status == 'posted' && in_array($this->session->userdata('level'), [1])): ?>
    <form method="post" action="<?= site_url('journal/void/' . $journal->journal_id) ?>" onsubmit="return confirm('Yakin batalkan jurnal ini?');" style="display:inline-block;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
        <input type="text" name="void_reason" placeholder="Alasan pembatalan" class="form-control" style="display:inline-block;width:200px;" required>
        <button type="submit" class="btn btn-danger btn-sm">Batalkan</button>
    </form>
    <?php endif; ?>
    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
</div>
