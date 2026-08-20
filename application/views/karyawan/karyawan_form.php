<section class = "content-header">
    <h1>Karyawan
        <small>Tambah/Edit Karyawan</small>
</h1>
    <ol class="breadcrumb">
        <li><a href = "#"><i class="fa fa-dashboard"></i></a></li>
        <li class="active">Karyawan</li>
    </ol>
</section>

<section class="content">
    <div class="box">
        <div class="box-header">
            <h3 class="box-title"><?=ucfirst($page)?> Karyawan</h3>
            <div class="pull-right">
                <a href="<?=site_url('karyawan') ?>" class="btn btn-warning btn-flat">
                    <i class="fa  fa-undo"></i> Kembali
                </a>
            </div>
        </div>

        <div class="box-body">
            <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <form action="<?=site_url('karyawan/process')?>" method="post">
                    <input type="hidden" name="id" value="<?=$row->karyawan_id?>">
                    <div class="form-group">
                        <label>Nama Karyawan *</label>
                        <input type="text" name="nama" value="<?=$row->nama?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Akun Login Terhubung (opsional)</label>
                        <select name="user_id" class="form-control select2" style="width:100%">
                            <option value="">- Tidak Ada -</option>
                            <?php foreach ($user_list as $u): ?>
                                <option value="<?= $u->user_id ?>" <?= $row->user_id == $u->user_id ? 'selected' : '' ?>><?= $u->nama ?> (<?= $u->username ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="<?=$page?>" class="btn btn-success btn-flat">
                            <i class="fa fa-paper-plane"></i> Save
                        </button>
                        <button type="Reset" class="btn btn-flat">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('.select2').select2();
});
</script>
