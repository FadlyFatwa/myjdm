<section class="content-header">
    <h1>Return Items
        <small>Return items for Invoice: <?= $invoice ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i></a></li>
        <li><a href="#">Sales</a></li>
        <li class="active">Return</li>
    </ol>
</section>

<section class="content">
    <form action="<?= site_url('retur/process') ?>" method="post" id="returnForm">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-undo"></i> Return Details</h3>
                <div class="pull-right">
                <a href="<?=site_url('report/sale') ?>" class="btn btn-warning btn-flat">
                    <i class="fa  fa-undo"></i> Kembali
                </a>
            </div>
            </div>
            <div class="box-body">
                <!-- Informasi Utama -->
                <div class="row">
                    <div class="col-lg-4">
                        <div class="box box-widget">
                            <div class="box-body">
                                <table width="100%">
                                    <tr>
                                        <td style="vertical-align: top; width: 30%;">
                                            <label for="invoice"><i class="fa fa-file-text-o"></i> Invoice</label>
                                        </td>
                                        <td>
                                            <input type="hidden" name="invoice" value="<?= $invoice ?>">
                                            <h4><b>: <span id="invoice"><?= $invoice ?></span></b></h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: top;">
                                            <label for="date"><i class="fa fa-calendar"></i> Tanggal</label>
                                        </td>
                                        <td>
                                            <div>
                                                <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="form-control">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: top;">
                                            <label for="user"><i class="fa fa-user"></i> Kasir</label>
                                        </td>
                                        <td>
                                            <input type="text" id="user" value="<?= $this->fungsi->user_login()->nama ?>" class="form-control" readonly>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: top;">
                                            <label for="customer"><i class="fa fa-user-circle"></i> Pembeli</label>
                                        </td>
                                        <td>
                                            <input type="text" name="customer_id" value="<?= $nama_customer ?>" class="form-control" readonly>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Barang -->
                    <div class="col-lg-8">
                        <input type="hidden" name="sale_id" value="<?= $sale_id ?>">
                        <input type="hidden" name="invoice" value="<?= $invoice ?>">

                        <table class="table table-bordered table-striped">
                            <thead class="bg-primary">
                                <tr>
                                    <th><i class="fa fa-barcode"></i> Barcode</th>
                                    <th><i class="fa fa-shopping-cart"></i> Item</th>
                                    <th><i class="fa fa-money"></i> Price</th>
                                    <th><i class="fa fa-sort-numeric-asc"></i> Qty</th>
                                    <th><i class="fa fa-undo"></i> Return Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sale_detail as $detail): ?>
                                <tr>
                                    <td><?= $detail->barcode ?></td>
                                    <td><?= $detail->nama_item ?></td>
                                    <td>Rp <?= number_format($detail->price_sale - $detail->discount_item, 0, ',', '.') ?></td>
                                    <td><?= $detail->qty ?></td>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" name="items[<?= $detail->item_id ?>][qty]" value="0" max="<?= $detail->qty ?>" class="form-control return-qty"
                                                data-price="<?= $detail->price_sale - $detail->discount_item ?>" data-max="<?= $detail->qty ?>">
                                            <input type="hidden" name="items[<?= $detail->item_id ?>][price]" value="<?= $detail->price_sale ?>">
                                            <input type="hidden" name="items[<?= $detail->item_id ?>][item_id]" value="<?= $detail->item_id ?>">
                                        </div>
                                    </td>   
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Catatan dan Total -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="note"><i class="fa fa-sticky-note"></i> Note:</label>
                            <textarea name="note" class="form-control" rows="3" placeholder="Tambahkan catatan disini..."></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="total_return"><i class="fa fa-money"></i> Total Return:</label>
                            <input type="number" name="total_return" id="total_return" value="0" readonly class="form-control bg-success text-white" style="font-size: 18px; font-weight: bold;">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="box-footer clearfix">
                    <div class="pull-right">
                        <button type="submit" class="btn btn-success btn-flat"><i class="fa fa-check"></i> Proses Return</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const returnForm = document.getElementById('returnForm');
        const totalReturnInput = document.getElementById('total_return');
        // Auto-focus dan highlight barang yang dipilih
            <?php if(isset($selected_item_id)): ?>
                const itemId = <?= $selected_item_id ?>;
                const inputQty = document.querySelector(`input[name="items[${itemId}][qty]"]`);
                
                if(inputQty) {
                    inputQty.focus();
                    inputQty.closest('tr').style.backgroundColor = '#fffde7';
                }
            <?php endif; ?>

        returnForm.addEventListener('input', function () {
            let totalReturn = 0;
            const returnQtyInputs = document.querySelectorAll('.return-qty');

            returnQtyInputs.forEach(function (input) {
                const qty = parseInt(input.value);
                const price = parseFloat(input.getAttribute('data-price'));
                const maxQty = parseInt(input.getAttribute('data-max'));

                // Check if return quantity exceeds purchase quantity
                if (qty > maxQty) {
                    alert('Return quantity cannot exceed purchase quantity!');
                    input.value = maxQty; // Set the quantity back to the maximum allowed
                    return;
                }

                if (!isNaN(qty) && qty > 0) {
                    totalReturn += qty * price;
                }
            });

            totalReturnInput.value = totalReturn.toFixed(2);
        });
    });
</script>