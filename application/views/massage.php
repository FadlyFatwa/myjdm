<?php if($this->session->flashdata('success')){ ?>
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-check"></i> <?= $this->session->flashdata('success') ?></h4>
</div>
<?php }?>

<?php if($this->session->flashdata('error')){ ?>
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-ban"></i> <?= $this->session->flashdata('error') ?></h4>
</div>
<?php }?>

