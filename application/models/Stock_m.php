<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class stock_m extends CI_Model {

    public function get($id = null){
        $this->db->select('t_stock.stock_id, p_item.barcode, p_item.nama_item,qty,t_stock.date,
        detail, supplier.nama_supplier,supplier.supplier_id, p_item.item_id ,p_item.stock');
        $this->db->join('p_item','t_stock.item_id = p_item.item_id');
        $this->db->join('supplier','t_stock.supplier_id = supplier.supplier_id','left');
        $this->db->from('t_stock');
        if($id != null) {
            $this->db->where('stock_id', $id);
        }
        $query = $this->db->get();
        return $query;
    }
    
    public function get_stock_by_item_id($item_id, $stock_id) {
        $this->db->from('t_stock');
        $this->db->where('item_id', $item_id);
        $this->db->where('stock_id', $stock_id);
        $query = $this->db->get();
        return $query;
    }
    
    public function get_stock_with_supplier_unit($stock_ids) {
        $this->db->select('
            t_stock.stock_id,
            p_item.barcode,
            p_item.pk,
            p_item.nama_item,
            p_item.unit_id,
            p_unit.nama_unit,
            t_stock.qty,
            t_stock.date,
            supplier.nama_supplier
        ');
        $this->db->from('t_stock');
        $this->db->join('p_item', 't_stock.item_id = p_item.item_id', 'left'); // Join tabel p_item
        $this->db->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left'); // Join tabel p_unit
        $this->db->join('supplier', 't_stock.supplier_id = supplier.supplier_id', 'left'); // Join tabel supplier
        $this->db->where_in('t_stock.stock_id', $stock_ids); // Filter berdasarkan stock_id
        return $this->db->get()->result();
    }


    public function del($id){
        $this->db->where('stock_id',$id);
        $this->db->delete('t_stock');
    }

    function barcode_qrcode($id) {
        $data['row'] = $this->item_m->get($id)->row();
        $this->template->load('template', 'transaction/stock_in/barcode_qrcode', $data);
    }

    public function get_stock_in(){
        $this->db->select('t_stock.stock_id, p_item.barcode, p_item.nama_item,qty,date,
        detail, supplier.nama_supplier, p_item.item_id, p_item.modal, (p_item.modal * t_stock.qty) as subtotal');
        $this->db->from('t_stock');
        $this->db->join('p_item','t_stock.item_id = p_item.item_id');
        $this->db->join('supplier','t_stock.supplier_id = supplier.supplier_id','left');
        $this->db->where('type','in');
        $this->db->order_by('stock_id','desc');
        $query = $this->db->get();
        return $query;
    }

    public function add_stock_in($post){
        $params = [
            'item_id' => $post['item_id'],
            'type' => 'in',
            'detail' => $post['detail'],
            'supplier_id' => $post['supplier_id'] == '' ? null : $post['supplier_id'],
            'qty' => $post['qty'],
            'date' => $post['date'],
            'created_at' => date('Y-m-d H:i:s')

        ];
        $this->db->insert('t_stock',$params);
    
    }

    public function edit_stock_in($post){
        $old_stock = $this->get($post['stock_id'])->row()->qty; // Dapatkan jumlah stok lama
        $params = [
            'item_id' => $post['item_id'],
            'type' => 'in',
            'detail' => $post['detail'],
            'supplier_id' => $post['supplier_id'] == '' ? null : $post['supplier_id'],
            'qty' => $post['qty'],
            'date' => $post['date'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('stock_id', $post['stock_id']);
        $this->db->update('t_stock', $params);
        return $old_stock;
    }
    
    
    public function get_stock_out()
    {
        $this->db->from('t_stock');
        $this->db->join('p_item', 't_stock.item_id = p_item.item_id');
        $this->db->where('type', 'out');
        $this->db->order_by('stock_id', 'desc');
        $query = $this->db->get();
        return $query;
    }
    public function add_stock_out($post)
    {
        $params = [
            'item_id' => $post['item_id'],
            'type' => 'out',
            'detail' => $post['detail'], 
            'qty' => $post['qty'],
            'date' => $post['date'],
        ];
        $this->db->insert('t_stock', $params);
    }

    public function update_stock_in($post){
        $params = [
            'item_id' => $post['item_id'],
            'type' => 'in',
            'detail' => $post['detail'],
            'supplier_id' => $post['supplier_id'] == '' ? null : $post['supplier_id'],
            'qty' => $post['qty'],
            'date' => $post['date'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('stock_id', $post['stock_id']);
        $this->db->update('t_stock', $params);
    }
        
    public function filter_stock_in($post) {
        $this->db->select('*,t_stock.stock_id, p_item.barcode, p_item.nama_item, qty, date,
        detail, supplier.nama_supplier,supplier.supplier_id, p_item.item_id, p_item.modal, (p_item.modal * t_stock.qty) as subtotal');
        $this->db->from('t_stock');
        $this->db->join('p_item', 't_stock.item_id = p_item.item_id');
        $this->db->join('supplier', 't_stock.supplier_id = supplier.supplier_id', 'left');
        $this->db->where('type', 'in');
    
        if (!empty($post['date1']) && !empty($post['date2'])) {
            $this->db->where('t_stock.date >=', $post['date1']);
            $this->db->where('t_stock.date <=', $post['date2']);
        }
        if (!empty($post['supplier'])) {
            if ($post['supplier'] == 'null') {
                $this->db->where("t_stock.supplier_id IS NULL");
            } else {
                $this->db->where("t_stock.supplier_id", $post['supplier']);
            }
        }
        $query = $this->db->get();
        return $query;
    }

    
}
    