<?php $no = 1;
    if($cart->num_rows() > 0){
        foreach($cart->result() as $c => $data) { ?>
            <tr>
                
                <td><?=$no++ ?></td>
                <td class="barcode">
                    <?= $data->barcode ?>
                    <br>
                    <?php
                        $stok = (int) $data->stock;
                        if ($stok === 0) {
                            echo '<span class="label label-danger">Stok: 0</span>';
                        } elseif ($stok <= 3) {
                            echo '<span class="label label-warning">Stok: ' . $stok . '</span>';
                        } else {
                            echo '<span class="label label-default">Stok: ' . $stok . '</span>';
                        }
                    ?>
                </td>
                <td><?=$data->nama_barang_jual?></td>
                <td class="text-right"><?=indo_currency($data->cart_price)?></td>
                <td class="text-right"><?=$data->qty?></td>
                <td class="text-right"><?=$data->discount_item?></td>  
                <td class="text-right" id="total2"><?=indo_currency($data->total)?></td>
                <td class="hidden" id="total"><?=$data->total?></td> 
                <td class="text-center" width="160px">
                    <button id="update_cart" data-toggle="modal" data-target="#modal-item-edit"
                    data-cartid="<?=$data->cart_id?>"
                    data-barcode="<?=$data->barcode?>"
                    data-pk="<?=$data->pk?>"
                    data-nama_barang_jual="<?=$data->nama_barang_jual?>"
                    data-stock="<?=$data->stock?>"
                    data-price="<?=$data->cart_price?>"
                    data-modal="<?=$data->modal?>"
                    data-qty="<?=$data->qty?>"
                    data-dicount="<?=$data->discount_item?>"
                    data-total="<?=$data->total?>"
                    data-status="<?= $data->status ?>"
                    class="btn btn-xs btn-primary">
                    <i class="fa fa-pencil"></i> Edit
                    </button>
                    <button id="del_cart" data-cartid="<?=$data->cart_id ?>" class="btn btn-xs btn-danger">
                        <i class="fa fa-trash"></i> Hapus
                    </button>
                </td>
            </tr>
        <?php
            }
        }else{
            echo '<tr>
                <td colspan="8" class="text-center">Tidak ada item</td>
            </tr>';
        } ?>

