<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Item_pending_m extends CI_Model {

    public function get_pending() {
        $this->db->select('p.*, u.nama AS user_name, nama_supplier, nama_category, nama_unit, photo');
        $this->db->from('p_item_pending p');
        $this->db->join('user u', 'p.created_by = u.user_id');
        $this->db->join('supplier', 'p.supplier_id = supplier.supplier_id', 'left');
		$this->db->join('p_category', 'p.category_id = p_category.category_id', 'left');
		$this->db->join('p_unit', 'p.unit_id = p_unit.unit_id', 'left');
        $this->db->where('status', 'pending');
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get();
    }

    public function get($id) {
        return $this->db->get_where('p_item_pending', ['pending_id' => $id]);
    }

    public function add($post) {
        $params = [
            'nama_item'   => $post['nama_item'],
            'supplier_id' => $post['supplier'],
            'category_id' => $post['category'],
            'unit_id'     => $post['unit'],
            'modal'       => $post['modal'],
            'pk'          => $post['pk'],
            'price'       => $post['price'],
            'stock_date'  => $post['stock_date'],
            'qty'         => $post['qty'] ,
            'photo'       => $post['photo'],
            'created_by'  => $post['created_by']
        ];
        $this->db->insert('p_item_pending', $params);
    }

    public function approve($id) {
        $this->db->where('pending_id', $id);
        $this->db->update('p_item_pending', ['status' => 'approved']);
    }

    public function reject($id) {
        $this->db->where('pending_id', $id);
        $this->db->update('p_item_pending', ['status' => 'rejected']);
    }



    // ===== DATATABLES =====
    var $column_order = array(
        null,
        'p.nama_item',
        's.nama_supplier',
        'c.nama_category',
        'u.nama_unit',
        'p.modal',
        'p.pk',
        'p.price',
        'p.qty',
        'p.stock_date',
        'p.status',
        null
    );

    var $column_search = array(
        'p.nama_item',
        's.nama_supplier'
    );

    var $order = array('p.created_at' => 'desc');

    private function _get_datatables_query()
    {
        $this->db->select('p.*, 
                           s.nama_supplier, 
                           c.nama_category, 
                           u.nama_unit,
                           usr.username AS user_name');

        $this->db->from('p_item_pending p');
        $this->db->join('supplier s','p.supplier_id = s.supplier_id','left');
        $this->db->join('p_category c','p.category_id = c.category_id','left');
        $this->db->join('p_unit u','p.unit_id = u.unit_id','left');
        $this->db->join('user usr','p.created_by = usr.user_id','left');
        $this->db->join('user u_print','p.printed_by = u_print.user_id','left');
        $this->db->join('user u_attach','p.attached_by = u_attach.user_id','left');


        $i = 0;
        $search_value = @$_POST['search']['value'];

        if ($search_value) {
            foreach ($this->column_search as $item) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
                }
                if (count($this->column_search) - 1 == $i)
                    $this->db->group_end();
                $i++;
            }
        }

        if (isset($_POST['order'])) {
            $this->db->order_by(
                $this->column_order[$_POST['order']['0']['column']],
                $_POST['order']['0']['dir']
            );
        } else {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables()
    {
        $this->_get_datatables_query();
        if(@$_POST['length'] != -1)
            $this->db->limit(@$_POST['length'], @$_POST['start']);
        return $this->db->get()->result();
    }

    function count_filtered()
    {
        $this->_get_datatables_query();
        return $this->db->get()->num_rows();
    }

    function count_all()
    {
        $this->db->from('p_item_pending');
        return $this->db->count_all_results();
    }
}


