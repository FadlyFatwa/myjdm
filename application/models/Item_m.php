<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class item_m extends CI_Model {

     // start datatables
     var $column_order = array(null, 'barcode', 'p_item.nama_item','nama_supplier', 'nama_category', 'nama_unit','modal','pk', 'price','stock',null); //set column field database for datatable orderable
     var $column_search = array('barcode', 'p_item.nama_item','nama_supplier','modal','pk', 'price'); //set column field database for datatable searchable
     var $order = array('barcode' => 'asc'); // default order 
  
     private function _get_datatables_query() {
        $this->db->select('p_item.*, p_category.nama_category , p_unit.nama_unit , supplier.nama_supplier');
        $this->db->from('p_item');
        $this->db->join('supplier', 'p_item.supplier_id = supplier.supplier_id', 'left');
        $this->db->join('p_category', 'p_item.category_id = p_category.category_id');
        $this->db->join('p_unit', 'p_item.unit_id = p_unit.unit_id');
        $i = 0;
        
        $search_value = @$_POST['search']['value'];
        if ($search_value) {
            $pattern = implode('.*', str_split($search_value)); // e.g., 'ob' becomes 'o.*b'
            
            foreach ($this->column_search as $item) { // loop column
                if ($i === 0) { // first loop
                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                    $this->db->where("$item REGEXP", $pattern);
                } else {
                    $this->db->or_where("$item REGEXP", $pattern);
                }
                if (count($this->column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
                $i++;
            }
        }
    
        if (isset($_POST['order'])) { // here order processing
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }
    
     function get_datatables() {
         $this->_get_datatables_query();
         if(@$_POST['length'] != -1)
         $this->db->limit(@$_POST['length'], @$_POST['start']);
         $query = $this->db->get();
         return $query->result();
     }
     function count_filtered() {
         $this->_get_datatables_query();
         $query = $this->db->get();
         return $query->num_rows();
     }
     function count_all() {
         $this->db->from('p_item');
         return $this->db->count_all_results();
     }
     // end datatables

    public function get($id = null){
        $this->db->select('p_item.*, p_category.nama_category , p_unit.nama_unit, supplier.nama_supplier' );
        $this->db->from('p_item');
        $this->db->join('supplier','p_item.supplier_id = supplier.supplier_id','left');
        $this->db->join('p_category','p_item.category_id = p_category.category_id');
        $this->db->join('p_unit','p_item.unit_id = p_unit.unit_id');
        if($id != null){
            $this->db->where('item_id', $id);
        }
        $this->db->order_by('barcode','asc');
        $query = $this->db->get();
        return $query;
    }
    public function get_status($id = null) {
        // Select basic fields
        $this->db->select('
            p_item.*,
            p_category.nama_category,
            p_unit.nama_unit,
            supplier.nama_supplier,
            COALESCE(AVG(t_sale_detail.qty), 0) AS avg_monthly_sales
        ');
    
        // From table p_item
        $this->db->from('p_item');
    
        // Join with related tables
        $this->db->join('supplier', 'p_item.supplier_id = supplier.supplier_id', 'left');
        $this->db->join('p_category', 'p_item.category_id = p_category.category_id', 'left');
        $this->db->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left');
    
        // Join with t_sale and t_sale_detail to calculate average monthly sales
        $this->db->join('t_sale_detail', 'p_item.item_id = t_sale_detail.item_id', 'left');
        $this->db->join('t_sale', 't_sale_detail.sale_id = t_sale.sale_id', 'left');
    
        // Group by item_id to calculate average monthly sales
        $this->db->group_by('p_item.item_id');
    
        // Optional: Filter by ID if provided
        if ($id != null) {
            $this->db->where('p_item.item_id', $id);
        }
    
        // Order by barcode
        $this->db->order_by('p_item.barcode', 'asc');
        
        // Perbaikan WHERE (harus dikutip karena ENUM atau VARCHAR)
        $this->db->where('p_item.status', 'active');
    
        // Execute query
        $query = $this->db->get();
    
        return $query;
    }
    public function get_by_ids($item_ids)
    {
        return $this->db->where_in('item_id', $item_ids)->get('p_item');
    }

    public function get_all_active_for_similarity()
    {
        $this->db->select('p_item.item_id, p_item.barcode, p_item.nama_item, p_item.supplier_id,
            p_item.category_id, p_item.modal, p_item.price, p_item.stock,
            supplier.nama_supplier, p_category.nama_category');
        $this->db->from('p_item');
        $this->db->join('supplier', 'p_item.supplier_id = supplier.supplier_id', 'left');
        $this->db->join('p_category', 'p_item.category_id = p_category.category_id', 'left');
        $this->db->where('p_item.status', 'active');
        $this->db->where("p_item.nama_item IS NOT NULL AND p_item.nama_item != ''", null, false);
        return $this->db->get()->result();
    }

    // Gabungkan beberapa item duplikat (loser) ke satu item target.
    // - Harga & supplier loser dibawa ke supplier_barang milik target (upsert, bukan overwrite buta).
    // - Stok loser dijumlahkan ke target.
    // - Loser di-nonaktifkan (status=inactive), barcode TETAP dipertahankan (bukan dikosongkan
    //   seperti soft_delete biasa) supaya barcode lama tidak bisa dipakai ulang dan riwayatnya jelas.
    public function merge_items($target_id, $loser_ids)
    {
        $target_id = (int) $target_id;
        $loser_ids = array_unique(array_diff(array_map('intval', $loser_ids), [$target_id]));
        if (empty($loser_ids)) {
            return false;
        }

        $this->db->trans_start();

        $target = $this->db->where('item_id', $target_id)->get('p_item')->row();
        if (!$target) {
            $this->db->trans_rollback();
            return false;
        }

        $stock_added = 0;
        $merged      = 0;

        foreach ($loser_ids as $loser_id) {
            $loser = $this->db->where('item_id', $loser_id)->where('status', 'active')->get('p_item')->row();
            if (!$loser) continue;

            // bawa harga/supplier utama loser
            $this->_upsert_supplier_barang($target_id, $loser->supplier_id, $loser->modal, $loser->price, $loser->pk);

            // bawa juga supplier tambahan yang sudah ditrack di supplier_barang milik loser
            $extra = $this->db->where('item_id', $loser_id)->get('supplier_barang')->result();
            foreach ($extra as $sb) {
                $this->_upsert_supplier_barang($target_id, $sb->supplier_id, $sb->harga_beli, $sb->harga_jual, $sb->kode_beli);
            }

            $stock_added += (int) $loser->stock;
            $merged++;

            $this->db->where('item_id', $loser_id)->update('p_item', [
                'status' => 'inactive',
                'stock'  => 0,
            ]);
        }

        if ($stock_added > 0) {
            $this->db->set('stock', 'stock + ' . (int) $stock_added, false);
            $this->db->where('item_id', $target_id);
            $this->db->update('p_item');
        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? $merged : false;
    }

    private function _upsert_supplier_barang($item_id, $supplier_id, $harga_beli, $harga_jual, $kode_beli)
    {
        if (!$supplier_id) return;

        $exists = $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)
                            ->count_all_results('supplier_barang');
        if ($exists) {
            $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)
                      ->update('supplier_barang', [
                          'harga_beli' => (int) $harga_beli,
                          'harga_jual' => (int) $harga_jual,
                          'kode_beli'  => $kode_beli ?: null,
                      ]);
        } else {
            $this->db->insert('supplier_barang', [
                'item_id'     => $item_id,
                'supplier_id' => $supplier_id,
                'harga_beli'  => (int) $harga_beli,
                'harga_jual'  => (int) $harga_jual,
                'kode_beli'   => $kode_beli ?: null,
            ]);
        }
    }

    function get_barcode($barcode = null){
        $this->db->select('p_item.*, p_category.nama_category , p_unit.nama_unit, supplier.nama_supplier' );
        $this->db->from('p_item');
        $this->db->join('supplier','p_item.supplier_id = supplier.supplier_id','left');
        $this->db->join('p_category','p_item.category_id = p_category.category_id');
        $this->db->join('p_unit','p_item.unit_id = p_unit.unit_id');
        if($barcode != null){
            $this->db->where('barcode', $barcode);
        }
        $query = $this->db->get();
        return $query;
    }
    

    public function get_max_barcode() {
        $this->db->select_max('barcode');
        $query = $this->db->get('p_item');
        return $query->row()->barcode;
    }
    



    public function add($post)
    {
        $params = [
            'barcode'     => $post['barcode'],
            'nama_item'   => $post['nama_item'],
            'supplier_id' => $post['supplier'],
            'category_id' => $post['category'],
            'unit_id'     => $post['unit'],
            'modal'       => $post['modal'],
            'pk'          => $post['pk'],
            'price'       => $post['price'],
            'stock'       => 0,
        ];
        $this->db->insert('p_item', $params);

        $item_id     = $this->db->insert_id();
        $supplier_id = (int) $post['supplier'];
        if ($item_id && $supplier_id) {
            $exists = $this->db->where('item_id', $item_id)
                ->where('supplier_id', $supplier_id)
                ->count_all_results('supplier_barang');
            if (!$exists) {
                $this->db->insert('supplier_barang', [
                    'item_id'     => $item_id,
                    'supplier_id' => $supplier_id,
                    'harga_beli'  => (int) $post['modal'],
                ]);
            }
        }
    }

    public function edit($post)
    {
        $params = [
            'barcode'     => $post['barcode'],
            'nama_item'   => $post['nama_item'],
            'supplier_id' => $post['supplier'],
            'category_id' => $post['category'],
            'unit_id'     => $post['unit'],
            'modal'       => $post['modal'],
            'pk'          => $post['pk'],
            'price'       => $post['price'],
        ];
        $this->db->where('item_id', $post['id']);
        $this->db->update('p_item', $params);

        $item_id     = (int) $post['id'];
        $supplier_id = (int) $post['supplier'];
        $harga_beli  = (int) $post['modal'];
        $harga_jual   = (int) $post['price'];
        $pk           = $post['pk'];
        if ($item_id && $supplier_id) {
            $exists = $this->db->where('item_id', $item_id)
                ->where('supplier_id', $supplier_id)
                ->count_all_results('supplier_barang');
            if ($exists) {
                $this->db->where('item_id', $item_id)
                    ->where('supplier_id', $supplier_id)
                    ->update('supplier_barang', [
                        'harga_beli' => $harga_beli,
                        'harga_jual' => $harga_jual,
                        'kode_beli'  => $pk,
                    ]);
            } else {
                $this->db->insert('supplier_barang', [
                    'item_id'     => $item_id,
                    'supplier_id' => $supplier_id,
                    'harga_beli'  => $harga_beli,
                    'harga_jual'  => $harga_jual,
                    'kode_beli'   => $pk,
                ]);
            }
        }
    }
    public function inarchive($post)
    {
        $params = [
            'barcode' => $post['barcode'],
            'nama_item' => $post['nama_item'],
            'supplier_id' => $post['supplier'],
            'category_id' => $post['category'],
            'unit_id' => $post['unit'],
            'modal' => $post['modal'], 
            'pk' => $post['pk'], 
            'price' => $post['price'], 
            'status' => "active",
            'old_barcode' => null   
        ];
        $this->db->where('item_id',$post['id']);
        $this->db->update('p_item',$params);
    }

    function check_barcode($code, $id = null){
        $this->db->from('p_item');
        $this->db->where('barcode',$code);
        if($id != null){
            $this->db->where('item_id !=',$id);
        }
        $query = $this->db->get();
        return $query;
    }

    

    public function del($id)
    {
        $this->db->where('item_id', $id);
        $this->db->delete('p_item');
    }
    public function soft_delete($id)
    {
        $data = [
            'status' => 'inactive',  // Menonaktifkan barang
            'old_barcode' => $this->get_old_barcode($id), // Simpan barcode lama jika ingin melacaknya
            'barcode' => NULL  // Kosongkan barcode agar bisa digunakan kembali
        ];

        $this->db->where('item_id', $id);
        $this->db->update('p_item', $data);
    }
    // Fungsi untuk mendapatkan barcode lama sebelum dihapus
    private function get_old_barcode($id)
    {
        $this->db->select('barcode');
        $this->db->where('item_id', $id);
        $query = $this->db->get('p_item')->row();
        return $query ? $query->barcode : NULL;
    }


    function update_stock_in($data){
        $qty = $data['qty'];
        $id = $data['item_id'];
        $sql = "UPDATE p_item SET stock = stock + '$qty' WHERE item_id = '$id'";
        $this->db->query($sql);
    }

    function update_stock_out($data){
        $qty = $data['qty'];
        $id = $data['item_id'];
        $sql = "UPDATE p_item SET stock = stock - '$qty' WHERE item_id = '$id'";
        $this->db->query($sql);
    }
    function update_item_data($item_id, $new_price, $new_pk){
        $this->db->set('modal',$new_price);
        $this->db->set('pk', $new_pk);
        $this->db->where('item_id', $item_id);
        $this->db->update('p_item');
    }

    public function update($id, $data)
    {
        $this->db->where('item_id', $id);
        $this->db->update('p_item', $data);
    }

    /**
     * Rata-rata qty yang dibeli per transaksi untuk satu item.
     * Digunakan untuk menghitung threshold low-stock adaptif.
     * Jika belum ada histori penjualan, default ke 1.
     */
    public function get_avg_qty_per_transaction($item_id)
    {
        $query = $this->db->query(
            "SELECT COALESCE(AVG(qty), 1) AS avg_qty FROM t_sale_detail WHERE item_id = ?",
            [(int) $item_id]
        );
        return (float) $query->row()->avg_qty;
    }

}