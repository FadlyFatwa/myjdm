<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1><i class="fa fa-check-square-o text-primary"></i> Absensi & Uang Makan</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> SDM</a></li>
        <li class="active">Absensi & Uang Makan</li>
    </ol>
</section>

<section class="content">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?= $this->session->flashdata('success') ?>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?= $this->session->flashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-calendar"></i> Kehadiran Tanggal</h3>
            <div class="box-tools pull-right">
                <a href="<?= site_url('absensi/history') ?>" class="btn btn-default btn-sm">
                    <i class="fa fa-history"></i> Riwayat Uang Makan
                </a>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Pilih Tanggal</label>
                        <input type="date" id="input-tanggal" class="form-control" value="<?= $tanggal ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tarif Uang Makan / Karyawan</label>
                        <?php if ($level == 1): ?>
                        <form method="post" action="<?= site_url('absensi/update-tarif') ?>" class="form-inline">
                            <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
                            <div class="input-group">
                                <span class="input-group-addon">Rp</span>
                                <input type="text" name="tarif" class="form-control input-rupiah" value="<?= number_format($tarif, 0, ',', '.') ?>">
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-default"><i class="fa fa-save"></i></button>
                                </span>
                            </div>
                        </form>
                        <?php else: ?>
                            <p class="form-control-static">Rp <?= number_format($tarif, 0, ',', '.') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <label>Status</label>
                    <p>
                        <?php if ($is_processed): ?>
                            <span class="label label-success">Uang makan sudah diproses</span>
                        <?php else: ?>
                            <span class="label label-default">Belum diproses</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <?php if ($is_processed): ?>
                <div class="alert alert-info">
                    Uang makan tanggal <?= $tanggal ?> sudah diproses dan tercatat sebagai Beban Operasional.
                    Kehadiran tidak bisa diubah lagi. Batalkan dulu dari halaman <a href="<?= site_url('absensi/history') ?>">Riwayat</a> kalau perlu koreksi.
                </div>
            <?php endif; ?>

            <form method="post" action="<?= site_url('absensi/save') ?>" id="form-absensi">
                <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Karyawan</th>
                            <th width="15%" class="text-center">Hadir</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($karyawan_list)): ?>
                        <tr><td colspan="3" class="text-center">Belum ada data karyawan. Tambah dulu di menu <a href="<?= site_url('karyawan') ?>">Karyawan</a>.</td></tr>
                    <?php else: $no = 1; foreach ($karyawan_list as $k): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $k->nama ?></td>
                            <td class="text-center">
                                <input type="checkbox" class="chk-hadir" name="karyawan_id[]" value="<?= $k->karyawan_id ?>"
                                    <?= in_array($k->karyawan_id, $hadir_ids) ? 'checked' : '' ?>
                                    <?= $is_processed ? 'disabled' : '' ?>>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>

                <?php if (!$is_processed && !empty($karyawan_list)): ?>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-flat">
                        <i class="fa fa-save"></i> Simpan Kehadiran
                    </button>
                    <button type="submit" formaction="<?= site_url('absensi/process') ?>" class="btn btn-success btn-flat" id="btn-proses">
                        <i class="fa fa-money"></i> Proses Uang Makan Hari Ini (<span id="preview-total">Rp 0</span>)
                    </button>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    var tarif = <?= (int) $tarif ?>;

    function updatePreview() {
        var jumlah = $('.chk-hadir:checked').length;
        $('#preview-total').text('Rp ' + (jumlah * tarif).toLocaleString('id-ID'));
    }
    updatePreview();
    $(document).on('change', '.chk-hadir', updatePreview);

    $('#input-tanggal').on('change', function() {
        window.location = '<?= site_url('absensi') ?>/' + $(this).val();
    });

    $(document).on('input', '.input-rupiah', function() {
        var val = $(this).val().replace(/\D/g, '');
        $(this).val(parseInt(val || 0).toLocaleString('id-ID').replace(/,/g, '.'));
    });

    $('#btn-proses').on('click', function(e) {
        e.preventDefault();

        var jumlah = $('.chk-hadir:checked').length;
        if (jumlah === 0) {
            Swal.fire('Belum Ada Kehadiran', 'Centang minimal 1 karyawan yang hadir sebelum memproses uang makan.', 'warning');
            return;
        }

        var btn = this;
        Swal.fire({
            title: 'Proses Uang Makan?',
            text: jumlah + ' karyawan hadir x Rp ' + tarif.toLocaleString('id-ID') + ' = Rp ' + (jumlah * tarif).toLocaleString('id-ID'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Proses'
        }).then(function(result) {
            if (result.isConfirmed) {
                $('#form-absensi').attr('action', btn.getAttribute('formaction')).trigger('submit');
            }
        });
    });
});
</script>
