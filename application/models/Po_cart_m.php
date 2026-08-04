<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Po_cart_m extends CI_Model {

    public function get($id)
    {
        return $this->db->where('id', $id)->get('po_cart')->row();
    }

    public function get_cart($supplier_id = null)
    {
        $this->db->select('po_cart.*, COALESCE(p_item.nama_item, po_cart.item_name_temp) AS display_name, p_item.barcode, p_item.stock, supplier.nama_supplier, p_unit.nama_unit');
        $this->db->from('po_cart');
        $this->db->join('supplier', 'po_cart.supplier_id = supplier.supplier_id');
        $this->db->join('p_item', 'po_cart.item_id = p_item.item_id', 'left');
        $this->db->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left');
        if ($supplier_id !== null) {
            $this->db->where('po_cart.supplier_id', (int) $supplier_id);
        }
        $this->db->order_by('supplier.nama_supplier', 'ASC');
        $this->db->order_by('po_cart.created_at', 'ASC');
        return $this->db->get()->result();
    }

    public function get_cart_grouped_by_supplier()
    {
        $rows = $this->get_cart();
        $grouped = [];
        foreach ($rows as $row) {
            $sid = $row->supplier_id;
            if (!isset($grouped[$sid])) {
                $grouped[$sid] = [
                    'supplier_id'   => $row->supplier_id,
                    'nama_supplier' => $row->nama_supplier,
                    'items'         => [],
                ];
            }
            $grouped[$sid]['items'][] = $row;
        }
        return $grouped;
    }

    public function add($data)
    {
        $item_id        = isset($data['item_id']) ? (int) $data['item_id'] : 'NULL';
        $item_name_temp = isset($data['item_name_temp']) ? $this->db->escape($data['item_name_temp']) : 'NULL';
        $pending_id     = isset($data['pending_item_id']) ? (int) $data['pending_item_id'] : 'NULL';
        $supplier_id    = (int) $data['supplier_id'];
        $qty            = (int) $data['qty'];
        $ref_price      = (int) ($data['ref_price'] ?? 0);
        $notes          = isset($data['notes']) ? $this->db->escape($data['notes']) : 'NULL';
        $ref_sales_qty  = isset($data['ref_sales_qty']) ? (float) $data['ref_sales_qty'] : 'NULL';
        $ref_period     = isset($data['ref_period']) ? $this->db->escape($data['ref_period']) : 'NULL';
        $added_by       = (int) $data['added_by'];

        $item_id_val        = $item_id === 'NULL' ? 'NULL' : $item_id;
        $item_name_temp_val = $item_name_temp === 'NULL' ? 'NULL' : $item_name_temp;
        $pending_id_val     = $pending_id === 'NULL' ? 'NULL' : $pending_id;
        $notes_val          = $notes === 'NULL' ? 'NULL' : $notes;
        $ref_sales_qty_val  = $ref_sales_qty === 'NULL' ? 'NULL' : $ref_sales_qty;
        $ref_period_val     = $ref_period === 'NULL' ? 'NULL' : $ref_period;

        $sql = "INSERT INTO po_cart
                    (item_id, item_name_temp, supplier_id, qty, ref_price, notes,
                     ref_sales_qty, ref_period, added_by, pending_item_id)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    qty            = qty + VALUES(qty),
                    ref_price      = VALUES(ref_price),
                    last_edited_by = VALUES(added_by),
                    updated_at     = CURRENT_TIMESTAMP";

        $bindings = [
            $item_id === 'NULL' ? null : $item_id,
            $item_name_temp === 'NULL' ? null : trim($data['item_name_temp'] ?? ''),
            $supplier_id,
            $qty,
            $ref_price,
            $notes === 'NULL' ? null : ($data['notes'] ?? null),
            $ref_sales_qty === 'NULL' ? null : $ref_sales_qty,
            $ref_period === 'NULL' ? null : ($data['ref_period'] ?? null),
            $added_by,
            $pending_id === 'NULL' ? null : $pending_id,
        ];

        $this->db->query($sql, $bindings);
        return $this->db->affected_rows();
    }

    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('id', (int) $id)->update('po_cart', $data);
    }

    public function remove($id)
    {
        return $this->db->where('id', (int) $id)->delete('po_cart');
    }

    /**
     * Hapus baris cart item yang sama di supplier lain (dipakai saat PO
     * dibuat untuk salah satu supplier — item dianggap sudah diproses,
     * jadi tidak perlu nyangkut lagi di keranjang supplier lain).
     */
    public function remove_siblings($item_id, $except_supplier_id)
    {
        if (!$item_id) return 0;
        return $this->db->where('item_id', (int) $item_id)
            ->where('supplier_id !=', (int) $except_supplier_id)
            ->delete('po_cart');
    }

    /**
     * Peta item_id => daftar semua supplier (dari supplier_barang) yang
     * menyuplai item tersebut. Dipakai untuk menandai item multi-supplier
     * di tampilan Keranjang PO.
     */
    public function get_multi_supplier_map($item_ids)
    {
        $item_ids = array_values(array_unique(array_filter(array_map('intval', $item_ids))));
        if (empty($item_ids)) return [];

        $rows = $this->db->select('sb.item_id, sb.supplier_id, supplier.nama_supplier')
            ->from('supplier_barang sb')
            ->join('supplier', 'supplier.supplier_id = sb.supplier_id')
            ->where_in('sb.item_id', $item_ids)
            ->order_by('supplier.nama_supplier', 'ASC')
            ->get()->result();

        $map = [];
        foreach ($rows as $r) {
            $map[$r->item_id][] = ['supplier_id' => (int) $r->supplier_id, 'nama_supplier' => $r->nama_supplier];
        }
        return $map;
    }

    public function clear($supplier_id = null)
    {
        if ($supplier_id !== null) {
            $this->db->where('supplier_id', (int) $supplier_id);
        }
        return $this->db->delete('po_cart');
    }

    public function count_cart()
    {
        return $this->db->count_all_results('po_cart');
    }

    /**
     * Cek apakah item sudah ada di keranjang PO atau sudah punya PO aktif
     * yang belum selesai diterima (sent/partial).
     * Dipakai untuk mencegah double-order dari auto-add saat penjualan.
     */
    public function item_already_ordered($item_id)
    {
        $item_id = (int) $item_id;

        // 1. Sudah ada di po_cart
        $in_cart = $this->db->where('item_id', $item_id)->count_all_results('po_cart');
        if ($in_cart > 0) return true;

        // 2. Sudah punya PO aktif (sent/partial) yang belum terpenuhi
        $in_po = $this->db->query(
            "SELECT COUNT(*) AS cnt
             FROM po_detail pd
             JOIN po_header ph ON pd.po_id = ph.po_id
             WHERE pd.item_id = ?
               AND ph.status IN ('sent','partial')
               AND pd.qty_ordered > pd.qty_received",
            [$item_id]
        )->row()->cnt;

        return (int) $in_po > 0;
    }

    public function get_ref_price($item_id, $supplier_id)
    {
        $sql = "SELECT COALESCE(sb.harga_beli, p.modal, 0) AS ref_price
                FROM p_item p
                LEFT JOIN supplier_barang sb
                    ON sb.item_id = p.item_id AND sb.supplier_id = ?
                WHERE p.item_id = ?
                LIMIT 1";
        $row = $this->db->query($sql, [(int) $supplier_id, (int) $item_id])->row();
        return $row ? (int) $row->ref_price : 0;
    }
}
