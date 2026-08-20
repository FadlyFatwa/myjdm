<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sale_m extends CI_Model {

    public function invoice_no(){

        $sql = "SELECT 
                    DATE_FORMAT(CURDATE(), '%y%m%d') AS today,
                    MAX(MID(invoice, 9, 4)) AS invoice_no
                FROM t_sale
                WHERE MID(invoice, 3, 6) = DATE_FORMAT(CURDATE(), '%y%m%d')";

        $query = $this->db->query($sql);
        $row = $query->row();

        $today = $row->today;

        if($row->invoice_no != NULL){
            $n = ((int)$row->invoice_no) + 1;
            $no = sprintf("%'.04d", $n);
        } else {
            $no = "0001";
        }

        $invoice = "JM".$today.$no;
        return $invoice;
    }


    public function get_cart($params = null){
        $this->db->select('*, p_item.nama_item, t_cart.price as cart_price');
        $this->db->from('t_cart');
        $this->db->join('p_item','t_cart.item_id = p_item.item_id');
        if($params != null){
            $this->db->where($params);
        }
        $this->db->where('user_id', $this->session->userdata('userid'));
        $query = $this->db->get();
        return $query;
    }

    public function add_cart($post){
        $item_id  = (int) $post['item_id'];
        $query2   = $this->db->query("SELECT nama_item FROM p_item WHERE item_id = ?", [$item_id]);
        $nama_item = $query2->row()->nama_item;

        // cart_id pakai AUTO_INCREMENT, tidak perlu hitung manual
        $params = array(
            'item_id'          => $item_id,
            'nama_barang_jual' => $nama_item,
            'price'            => (float) $post['price'],
            'qty'              => (int) $post['qty'],
            'total'            => ((float)$post['price'] * (int)$post['qty']),
            'user_id'          => (int) $this->session->userdata('userid')
        );

        $this->db->insert('t_cart', $params);
    }

    function update_cart_qty($post){
        $user_id = (int) $this->session->userdata('userid');
        $item_id = (int) $post['item_id'];
        $price   = (float) $post['price'];
        $qty_add = (int) $post['qty'];

        $current = $this->db
            ->where('item_id', $item_id)
            ->where('user_id', $user_id)
            ->get('t_cart')->row();

        if (!$current) return;

        $new_qty   = $current->qty + $qty_add;
        $discount  = (float) ($current->discount_item ?? 0);
        $new_total = ($price * $new_qty) - $discount;

        $this->db
            ->where('item_id', $item_id)
            ->where('user_id', $user_id)
            ->update('t_cart', [
                'price' => $price,
                'qty'   => $new_qty,
                'total' => $new_total,
            ]);
    }

    public function del_cart($params = null){
        if($params != null){
            $this->db->where($params);
        }
        $this->db->delete('t_cart');
    }

    /**
     * Total qty item tertentu yang sudah ada di cart user ini.
     * Dipakai untuk menghitung effective_stock = p_item.stock - cart_qty.
     */
    public function get_cart_qty_for_item($item_id, $user_id)
    {
        $this->db->select('COALESCE(SUM(qty), 0) AS cart_qty');
        $this->db->from('t_cart');
        $this->db->where('item_id', (int) $item_id);
        $this->db->where('user_id', (int) $user_id);
        $result = $this->db->get()->row();
        return (int) $result->cart_qty;
    }
    
    public function edit_cart($post)
    {
        $params = array(
            'nama_barang_jual' => $post['nama_barang_jual'],
            'price' => $post['price'],
            'qty' => $post['qty'],
            'discount_item' => $post['discount'],
            'total' => $post['total'],
        );
        $this->db->where('cart_id', $post['cart_id']);
        $this->db->update('t_cart', $params);
    }

    public function get_sale($id = null)
    {
        $this->db->select('*, customer.nama_customer, user.username as user_name,user.nama,
                         t_sale.create as sale_create , t_sale.payment_method as metode ');
        $this->db->from('t_sale');
        $this->db->join('customer', 't_sale.customer_id = customer.customer_id', 'left');
        $this->db->join('user', 't_sale.user_id = user.user_id');
        if($id != null) {
            $this->db->where('sale_id', $id);
        }
        $this->db->order_by('date', 'desc'); 
        $query = $this->db->get();
        return $query;
    }

    public function get_sale_pagination($limit = null, $start = null)
    {
        $post = $this->session->userdata('search');
        $this->db->select('*, customer.nama_customer, user.username as user_name, 
                        t_sale.create as sale_created');
        $this->db->from('t_sale');
        $this->db->join('customer', 't_sale.customer_id = customer.customer_id', 'left');
        $this->db->join('user', 't_sale.user_id = user.user_id');
        if(!empty($post['date1']) && !empty($post['date2'])) {
            $this->db->where('t_sale.date >=', $post['date1']);
            $this->db->where('t_sale.date <=', $post['date2']);
        }
        if(!empty($post['customer'])) {
            if($post['customer'] == 'null') {
                $this->db->where("t_sale.customer_id IS NULL");
            } else {
                $this->db->where("t_sale.customer_id", $post['customer']);
            }
        }
        if(!empty($post['invoice'])) {
            $this->db->like("invoice", $post['invoice']);
        }
        $this->db->limit($limit, $start);
        $this->db->order_by('create', 'desc');
        $query = $this->db->get();
        return $query;
    }

    public function get_sale_detail($sale_id = null)
    {
        $this->db->from('t_sale_detail');
        $this->db->join('p_item', 't_sale_detail.item_id = p_item.item_id');
        $this->db->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left'); // Join ke p_unit melalui p_item.unit_id
        if ($sale_id != null) {
            $this->db->where('t_sale_detail.sale_id', $sale_id);
        }
        $query = $this->db->get();
        return $query;
    }

    public function get_sale_filtered($post = null)
    {
        // Mengambil data sale dan join ke p_customer
        $this->db->select('t_sale.*, customer.nama_customer as nama_member');
        $this->db->from('t_sale');
        $this->db->join('customer', 'customer.customer_id = t_sale.customer_id', 'left');

        if(!empty($post['date1']) && !empty($post['date2'])){
            $this->db->where('t_sale.date >=', $post['date1']);
            $this->db->where('t_sale.date <=', $post['date2']);
        }

        if(!empty($post['customer'])){
            if($post['customer'] == 'null'){
                // Sesuai screenshot, umum menggunakan ID 0
                $this->db->where('t_sale.customer_id', 0); 
            } else {
                $this->db->where('t_sale.customer_id', $post['customer']);
            }
        }

        if(!empty($post['invoice'])){
            $this->db->like('t_sale.invoice', $post['invoice']);
        }

        $this->db->order_by('t_sale.sale_id', 'DESC');
        return $this->db->get();
    }


    public function add_sale($data)
    {
        $params = array(
            'invoice'        => $this->invoice_no(),
            'customer_id'    => empty($data['customer_id']) ? null : $data['customer_id'],
            'customer_name'  => $data['customer_name'] ?? null,
            // buildSaleData() memakai total_price/final_price; raw POST lama memakai subtotal/grandtotal
            'total_price'    => $data['total_price']  ?? ($data['subtotal']   ?? 0),
            'discount'       => $data['discount']     ?? 0,
            'final_price'    => $data['final_price']  ?? ($data['grandtotal'] ?? 0),
            'cash'           => $data['cash']         ?? 0,
            'change'         => $data['change']       ?? 0,
            'note'           => $data['note']         ?? null,
            'date'           => $data['date'],
            'user_id'        => $data['user_id']      ?? $this->session->userdata('userid'),
            'payment_method' => $data['payment_method'],
            'payment_status' => $data['payment_status'],
        );
        $this->db->insert('t_sale', $params);
        return $this->db->insert_id();
    }
    
    // Di dalam Sale_m.php
    public function update_sale($sale_id, $data, $details) {
        // Mulai transaksi database
        $this->db->trans_start();
        
        // Update data penjualan utama
        $this->db->where('sale_id', $sale_id);
        $this->db->update('t_sale', $data);
        
        // Ambil detail penjualan yang sudah ada
        $existing_details = $this->db->get_where('t_sale_detail', ['sale_id' => $sale_id])->result_array();
        $existing_item_ids = array_column($existing_details, 'item_id');
        
        // Buat mapping untuk quantity lama
       $old_qtys = [];
        foreach ($existing_details as $detail) {
            $old_qtys[$detail['item_id']] = $detail['qty'];
        }
        
        // Proses setiap detail dari form
        foreach ($details as $detail) {
            $detail['sale_id'] = $sale_id;
            $item_id = $detail['item_id'];
            $new_qty = $detail['qty'];
            
            // Jika item sudah ada di database
            if (in_array($item_id, $existing_item_ids)) {
                $old_qty = $old_qtys[$item_id];
                
                // Jika quantity berubah
                if ($old_qty != $new_qty) {
                    // Hitung selisih quantity
                    $qty_diff = $new_qty - $old_qty;
                    
                    // Update stok item
                    $this->db->set('stock', "stock - $qty_diff", false);
                    $this->db->where('item_id', $item_id);
                    $this->db->update('p_item');
                    
                    // Update detail penjualan
                    $this->db->where('sale_id', $sale_id);
                    $this->db->where('item_id', $item_id);
                    $this->db->update('t_sale_detail', $detail);
                } else if ($detail['is_modified'] == 1) {
                    // Jika hanya data lain yang berubah (bukan qty)
                    $this->db->where('sale_id', $sale_id);
                    $this->db->where('item_id', $item_id);
                    $this->db->update('t_sale_detail', $detail);
                }
            } else {
                // // Item baru, insert dan kurangi stok
                $this->db->insert('t_sale_detail', $detail);
                
                // $this->db->set('stock', "stock - $new_qty", false);
                // $this->db->where('item_id', $item_id);
                // $this->db->update('p_item');
            }
        }
        
        // Proses penghapusan item yang dihapus dari form
        $current_item_ids = array_column($details, 'item_id');
        $items_to_delete = array_diff($existing_item_ids, $current_item_ids);
        
        if (!empty($items_to_delete)) {
            foreach ($items_to_delete as $item_id) {
                $old_qty = $old_qtys[$item_id];
                
                // Kembalikan stok untuk item yang dihapus
                // $this->db->set('stock', "stock + $old_qty", false);
                // $this->db->where('item_id', $item_id);
                // $this->db->update('p_item');
            }
            
            // Hapus dari detail penjualan
            $this->db->where('sale_id', $sale_id);
            $this->db->where_in('item_id', $items_to_delete);
            $this->db->delete('t_sale_detail');
        }
        
        // Selesaikan transaksi
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    

    public function add_sale_detail($params) {
        $this->db->insert_batch('t_sale_detail', $params);
    }
    public function del_sale_detail($sale_id) {
        $this->db->where('sale_id', $sale_id);
        $this->db->delete('t_sale_detail'); // Ganti dengan nama tabel detail penjualan Anda
    }
    

    /**
     * Batalkan transaksi: kembalikan stok, lalu tandai t_sale sebagai dibatalkan.
     * Row t_sale, t_sale_detail, dan t_sale_jasa_detail TIDAK dihapus fisik —
     * dibiarkan utuh sebagai histori (barang/jasa apa saja yang tadinya dibeli
     * tetap bisa dilihat di Detail transaksi meski sudah dibatalkan).
     *
     * Stok ditambah manual di sini (bukan lewat trigger `stock_return`, yang cuma
     * nyala kalau row t_sale_detail benar-benar di-DELETE) — karena baris detail
     * sengaja tidak dihapus, trigger itu tidak pernah kepanggil di alur ini,
     * jadi tidak ada risiko stok ditambah dobel.
     */
    public function cancel_sale($id, $reason, $user_id) {
        $this->db->trans_start();

        $details = $this->db->where('sale_id', $id)->get('t_sale_detail')->result();
        foreach ($details as $d) {
            $this->db->set('stock', 'stock + ' . (int) $d->qty, false)
                      ->where('item_id', $d->item_id)
                      ->update('p_item');
        }

        $this->db->where('sale_id', $id)->update('t_sale', [
            'is_cancelled'  => 1,
            'cancel_reason' => $reason,
            'cancelled_by'  => $user_id,
            'cancelled_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Aktifkan kembali transaksi yang sudah dibatalkan: kurangi stok lagi
     * (kebalikan dari cancel_sale) dan hapus tanda pembatalannya.
     */
    public function reactivate_sale($id, $user_id) {
        $sale = $this->db->where('sale_id', $id)->get('t_sale')->row();
        if (!$sale) return false;
        if (!$sale->is_cancelled) {
            throw new Exception('Transaksi ini tidak dalam status dibatalkan.');
        }

        $this->db->trans_start();

        $details = $this->db->where('sale_id', $id)->get('t_sale_detail')->result();
        foreach ($details as $d) {
            $this->db->set('stock', 'stock - ' . (int) $d->qty, false)
                      ->where('item_id', $d->item_id)
                      ->update('p_item');
        }

        $this->db->where('sale_id', $id)->update('t_sale', [
            'is_cancelled'  => 0,
            'cancel_reason' => null,
            'cancelled_by'  => null,
            'cancelled_at'  => null,
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_filtered_sales($post)
    {
        $this->db->select('*, customer.nama_customer as customer_name, user.username as user_name,
                        t_sale.create as sale_created');
        $this->db->from('t_sale');
        $this->db->join('customer', 't_sale.customer_id = customer.customer_id', 'left');
        $this->db->join('user', 't_sale.user_id = user.user_id');
        $this->db->where('t_sale.is_cancelled', 0);
        if(!empty($post['date1']) && !empty($post['date2'])) {
            $this->db->where('t_sale.date >=', $post['date1']);
            $this->db->where('t_sale.date <=', $post['date2']);
        }
        if(!empty($post['customer'])) {
            if($post['customer'] == 'null') {
                $this->db->where("t_sale.customer_id IS NULL");
            } else {
                $this->db->where("t_sale.customer_id", $post['customer']);
            }
        }
        if(!empty($post['invoice'])) {
            $this->db->like("invoice", $post['invoice']);
        }
        $this->db->order_by('date', 'desc');
        $query = $this->db->get();
        return $query;
    }

    function get_detail($id = null){
        $this->db->select('t_sale_detail.*,customer.nama_customer,t_sale.invoice,p_item.nama_item,qty,price_sale,total,t_sale.date' );
        $this->db->from('t_sale_detail');
        $this->db->join('t_sale', 't_sale_detail.sale_id = t_sale.sale_id', 'left');
        $this->db->join('p_item', 't_sale_detail.item_id = p_item.item_id', 'left');
        $this->db->join('customer', 't_sale.customer_id = customer.customer_id', 'left');
        $this->db->where('(t_sale.is_cancelled = 0 OR t_sale.is_cancelled IS NULL)', null, false);
        if($id != null){
            $this->db->where('sale_id', $id);
        }
        $this->db->order_by('detail_id','desc');
        $query = $this->db->get();
        return $query;
    }

    public function get_item($item_id){
        $this->db->from('p_item');
        $this->db->where('item_id', $item_id);
        return $this->db->get()->row();
    }

    public function update_item_price($item_id,$price){
        $this->db->where('item_id',$item_id);
        $this->db->update('p_item',['price' => $price]);
    }

    public function get_analisis_penjualan_json($keyword = null) {
        $this->db->select('
            YEAR(t_sale.date) AS year,
            MONTH(t_sale.date) AS month,
            SUM(t_sale_detail.qty) AS total_qty_sold,
            COUNT(DISTINCT t_sale.sale_id) AS total_transactions,
            AVG(t_sale_detail.qty) AS avg_qty_per_transaction
        ');
        $this->db->from('t_sale');
        $this->db->join('t_sale_detail', 't_sale.sale_id = t_sale_detail.sale_id');
        $this->db->join('p_item', 't_sale_detail.item_id = p_item.item_id');
        $this->db->where('t_sale.is_cancelled', 0);

        // Custom search functionality
        if (!empty($keyword)) {
            $keywords = explode(" ", $keyword);
            $this->db->group_start();
            foreach ($keywords as $key) {
                $this->db->group_start();
                $this->db->like('p_item.nama_item', $key);
                $this->db->group_end();
            }
            $this->db->group_end();
        }
    
        $this->db->group_by('YEAR(t_sale.date), MONTH(t_sale.date)');
        $this->db->order_by('YEAR(t_sale.date)', 'ASC');
        $this->db->order_by('MONTH(t_sale.date)', 'ASC');
    
        $query = $this->db->get();
        return $query->result();
    }
    
    public function get_barang_teranalisis($keyword = null) {
        $this->db->select('p_item.item_id,supplier.nama_supplier, p_item.modal , p_item.pk, p_item.nama_item, SUM(t_sale_detail.qty) AS total_qty_sold');
        $this->db->from('t_sale_detail');
        $this->db->join('t_sale', 't_sale_detail.sale_id = t_sale.sale_id');
        $this->db->join('p_item', 't_sale_detail.item_id = p_item.item_id');
        $this->db->join('supplier', 'supplier.supplier_id = p_item.supplier_id');
        $this->db->where('t_sale.is_cancelled', 0);

        // Custom search functionality
        if (!empty($keyword)) {
            $keywords = explode(" ", $keyword);
            $this->db->group_start();
            foreach ($keywords as $key) {
                $this->db->group_start();
                $this->db->like('p_item.nama_item', $key);
                $this->db->group_end();
            }
            $this->db->group_end();
        }

        $this->db->group_by('p_item.item_id');
        $this->db->order_by('total_qty_sold', 'DESC');
        return $this->db->get()->result();
    }

    // ── Cart Jasa ──────────────────────────────────────────────

    public function get_cart_jasa()
    {
        return $this->db
            ->select('t_cart_jasa.*, p_jasa.nama_jasa')
            ->from('t_cart_jasa')
            ->join('p_jasa', 't_cart_jasa.jasa_id = p_jasa.jasa_id')
            ->where('t_cart_jasa.user_id', (int) $this->session->userdata('userid'))
            ->get()
            ->result();
    }

    public function add_cart_jasa($data)
    {
        $user_id = (int) $this->session->userdata('userid');
        $jasa_id = (int) $data['jasa_id'];

        $existing = $this->db
            ->where('jasa_id', $jasa_id)
            ->where('user_id', $user_id)
            ->get('t_cart_jasa')
            ->row();

        if ($existing) {
            $new_qty   = $existing->qty + (int) $data['qty'];
            $new_total = $existing->tarif * $new_qty;
            $this->db
                ->where('id', $existing->id)
                ->update('t_cart_jasa', ['qty' => $new_qty, 'total' => $new_total]);
        } else {
            $qty   = (int) $data['qty'];
            $tarif = (int) $data['tarif'];
            $this->db->insert('t_cart_jasa', [
                'jasa_id'   => $jasa_id,
                'nama_jasa' => $data['nama_jasa'],
                'tarif'     => $tarif,
                'qty'       => $qty,
                'total'     => $tarif * $qty,
                'user_id'   => $user_id,
            ]);
        }
    }

    public function update_cart_jasa($id, $data)
    {
        $this->db->where('id', $id)->update('t_cart_jasa', $data);
    }

    public function del_cart_jasa($params = null)
    {
        if ($params !== null) {
            $this->db->where($params);
        }
        $this->db->delete('t_cart_jasa');
    }

    public function add_sale_jasa_detail($arr)
    {
        if (!empty($arr)) {
            $this->db->insert_batch('t_sale_jasa_detail', $arr);
        }
    }

    public function get_sale_jasa_detail($sale_id)
    {
        return $this->db
            ->where('sale_id', (int) $sale_id)
            ->get('t_sale_jasa_detail')
            ->result();
    }

}
