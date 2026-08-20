<link rel="icon"
      href="<?=base_url()?>assets/dist/img/LOGO JDM BW.jpg" class="img-circle" alt="User Image">

<!-- Content Header -->
<section class="content-header">
    <h1>Dashboard</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
    </ol>
</section>

<!-- Main Content -->
<section class="content">
    <!-- Info Boxes -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-cubes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Items</span>
                    <span class="info-box-number"><?= $this->fungsi->count_item() ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Suppliers</span>
                    <span class="info-box-number"><?= $this->fungsi->count_supplier() ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Customers</span>
                    <span class="info-box-number"><?= $this->fungsi->count_customer() ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="info-box bg-red">
                <span class="info-box-icon"><i class="fa fa-user-secret"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Users</span>
                    <span class="info-box-number"><?= $this->fungsi->count_user() ?></span>
                </div>
            </div>
        </div>
    </div>

</section>