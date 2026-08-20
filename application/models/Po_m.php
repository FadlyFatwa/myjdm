<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Po_m extends CI_Model {

    public function po_number()
    {
        $sql = "SELECT
                    DATE_FORMAT(CURDATE(), '%y%m') AS today,
                    MAX(MID(po_number, 10, 3)) AS last_no
                FROM po_header
                WHERE MID(po_number, 6, 4) = DATE_FORMAT(CURDATE(), '%y%m')";
        $row = $this->db->query($sql)->row();

        $today = $row->today;
        if ($row->last_no !== null) {
            $n  = ((int) $row->last_no) + 1;
            $no = sprintf("%'.03d", $n);
        } else {
            $no = '001';
        }
        return 'POJDM' . $today . $no;
    }

    public function get_po($po_id = null)
    {
        $this->db->select('po_header.*, supplier.nama_supplier, supplier.phone, supplier.alamat, user.nama AS created_by_name');
        $this->db->from('po_header');
        $this->db->join('supplier', 'po_header.supplier_id = supplier.supplier_id');
        $this->db->join('user', 'po_header.created_by = user.user_id');
        if ($po_id !== null) {
            $this->db->where('po_header.po_id', (int) $po_id);
            return $this->db->get()->row();
        }
        $this->db->order_by('po_header.po_id', 'DESC');
        return $this->db->get()->result();
    }

    public function count_overdue()
    {
        $sql = "SELECT COUNT(*) AS cnt FROM po_header
                WHERE status IN ('sent','partial')
                AND (
                    CASE
                        WHEN (SELECT MAX(receive_date) FROM po_receipt WHERE po_id = po_header.po_id) IS NOT NULL
                            THEN DATEDIFF(CURDATE(), (SELECT MAX(receive_date) FROM po_receipt WHERE po_id = po_header.po_id)) > 7
                        ELSE DATEDIFF(CURDATE(), po_date) > 7
                    END
                )";
        return (int) $this->db->query($sql)->row()->cnt;
    }

    public function _get_datatables_query($filter = 'all')
    {
        $search_value = $this->input->post('search')['value'] ?? '';

        $this->db->select('po_header.po_id, po_header.po_number, po_header.po_date, po_header.expected_date,
            po_header.status, po_header.is_direct, supplier.nama_supplier,
            (SELECT MAX(receive_date) FROM po_receipt WHERE po_id = po_header.po_id) AS last_receipt_date', false);
        $this->db->from('po_header');
        $this->db->join('supplier', 'po_header.supplier_id = supplier.supplier_id');

        // Status filter
        if ($filter === 'overdue') {
            $this->db->where_in('po_header.status', ['sent', 'partial']);
            $this->db->where("(
                CASE
                    WHEN (SELECT MAX(receive_date) FROM po_receipt WHERE po_id = po_header.po_id) IS NOT NULL
                        THEN DATEDIFF(CURDATE(), (SELECT MAX(receive_date) FROM po_receipt WHERE po_id = po_header.po_id)) > 7
                    ELSE DATEDIFF(CURDATE(), po_header.po_date) > 7
                END
            ) = 1", null, false);
        } elseif (!empty($filter) && $filter !== 'all') {
            $this->db->where('po_header.status', $filter);
        }

        if (!empty($search_value)) {
            $keywords = explode(' ', trim($search_value));
            $this->db->group_start();
            foreach ($keywords as $kw) {
                $this->db->group_start();
                $this->db->like('po_header.po_number', $kw);
                $this->db->or_like('supplier.nama_supplier', $kw);
                $this->db->or_like('po_header.status', $kw);
                $this->db->group_end();
            }
            $this->db->group_end();
        }
    }

    public function get_po_datatables($filter = 'all')
    {
        $this->_get_datatables_query($filter);

        $start  = (int) $this->input->post('start');
        $length = (int) $this->input->post('length');

        if (isset($_POST['order'])) {
            $col_index = (int) $_POST['order'][0]['column'];
            $col_name  = $_POST['columns'][$col_index]['data'] ?? 'po_id';
            $col_dir   = $_POST['order'][0]['dir'] ?? 'DESC';
            $map = [
                'po_number'    => 'po_header.po_number',
                'nama_supplier'=> 'supplier.nama_supplier',
                'po_date'      => 'po_header.po_date',
                'status'       => 'po_header.status',
            ];
            $order_col = $map[$col_name] ?? 'po_header.po_id';
            $this->db->order_by($order_col, $col_dir);
        } else {
            $this->db->order_by('po_header.po_id', 'DESC');
        }

        if ($length > 0) $this->db->limit($length, $start);
        return $this->db->get()->result();
    }

    public function count_filtered($filter = 'all')
    {
        $this->_get_datatables_query($filter);
        return (int) $this->db->count_all_results();
    }

    public function count_all()
    {
        return $this->db->count_all('po_header');
    }

    public function get_po_detail($po_id)
    {
        $this->db->select('po_detail.*, COALESCE(p_item.nama_item, po_detail.item_name_temp) AS display_name, p_item.barcode, p_item.stock, p_unit.nama_unit');
        $this->db->from('po_detail');
        $this->db->join('p_item', 'po_detail.item_id = p_item.item_id', 'left');
        $this->db->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left');
        $this->db->where('po_detail.po_id', (int) $po_id);
        return $this->db->get()->result();
    }

    public function create_po($data)
    {
        $data['po_number'] = $this->po_number();
        $this->db->insert('po_header', $data);
        return $this->db->insert_id();
    }

    public function add_po_detail($rows)
    {
        // Jangan include price_variance — GENERATED column
        $clean_rows = [];
        foreach ($rows as $r) {
            $clean_rows[] = [
                'po_id'          => $r['po_id'],
                'item_id'        => $r['item_id'] ?? null,
                'item_name_temp' => $r['item_name_temp'] ?? null,
                'qty_ordered'    => (int) $r['qty_ordered'],
                'qty_received'   => 0,
                'unit_price'     => (float) $r['unit_price'],
                'notes'          => $r['notes'] ?? null,
            ];
        }
        return $this->db->insert_batch('po_detail', $clean_rows);
    }

    public function update_status($po_id, $status)
    {
        return $this->db->where('po_id', (int) $po_id)->update('po_header', ['status' => $status]);
    }

    public function recalc_status($po_id)
    {
        $sql = "SELECT
                    COUNT(*) AS total_lines,
                    SUM(CASE WHEN qty_received >= qty_ordered THEN 1 ELSE 0 END) AS fully_received
                FROM po_detail WHERE po_id = ?";
        $stat = $this->db->query($sql, [(int) $po_id])->row();
        $new_status = ($stat->fully_received == $stat->total_lines && $stat->total_lines > 0)
            ? 'received' : 'partial';
        $this->db->where('po_id', (int) $po_id)->update('po_header', ['status' => $new_status]);
        return $new_status;
    }

    /**
     * Daftarkan item baru (belum ada di p_item) — dipakai baik untuk temp item
     * di draft PO (register_temp_item) maupun barang ekstra saat penerimaan
     * (receive_add_item). Tidak mengkredit stok — itu tetap tanggung jawab
     * receive_detail() supaya satu jalur kredit stok.
     */
    public function create_item_from_temp($nama_item, $barcode, $pk, $category_id, $unit_id, $modal, $supplier_id)
    {
        $this->db->insert('p_item', [
            'nama_item'   => $nama_item,
            'barcode'     => $barcode,
            'pk'          => $pk,
            'category_id' => $category_id,
            'unit_id'     => $unit_id,
            'modal'       => $modal,
            'price'       => 1,
            'stock'       => 0,
            'supplier_id' => $supplier_id,
            'status'      => 'active',
        ]);
        $item_id = $this->db->insert_id();
        if (!$item_id) return null;

        $existing = $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)->get('supplier_barang')->row();
        if (!$existing) {
            $this->db->insert('supplier_barang', [
                'item_id'     => $item_id,
                'supplier_id' => $supplier_id,
                'harga_beli'  => $modal,
            ]);
        }

        return $item_id;
    }

    public function receive_detail($po_id, $detail_id, $qty_received, $actual_price, $supplier_id, $po_number, $invoice_date = null, $receipt_id = null)
    {
        $this->db->trans_start();

        // 1. Update po_detail — qty kumulatif agar partial receive ke-2+ terakumulasi
        $this->db->query(
            "UPDATE po_detail SET qty_received = qty_received + ?, actual_price = ? WHERE id = ? AND po_id = ?",
            [(int) $qty_received, (float) $actual_price, (int) $detail_id, (int) $po_id]
        );

        // 2. Hitung status PO
        $sql = "SELECT
                    COUNT(*) AS total_lines,
                    SUM(CASE WHEN qty_received >= qty_ordered THEN 1 ELSE 0 END) AS fully_received
                FROM po_detail WHERE po_id = ?";
        $stat = $this->db->query($sql, [(int) $po_id])->row();
        $new_status = ($stat->fully_received == $stat->total_lines && $stat->total_lines > 0)
            ? 'received' : 'partial';
        $this->db->where('po_id', (int) $po_id)->update('po_header', ['status' => $new_status]);

        // 3. Get item_id dari detail
        $detail = $this->db->where('id', (int) $detail_id)->get('po_detail')->row();
        $item_id = $detail ? $detail->item_id : null;

        if ($item_id) {
            // 4. Insert t_stock — gunakan invoice_date sebagai tanggal stok masuk
            $this->db->insert('t_stock', [
                'item_id'      => (int) $item_id,
                'type'         => 'in',
                'supplier_id'  => (int) $supplier_id,
                'po_detail_id' => (int) $detail_id,
                'qty'          => (int) $qty_received,
                'date'         => $invoice_date ?: date('Y-m-d'),
                'detail'       => 'Goods Receipt ' . $po_number,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

            // 5. Update p_item.stock
            $this->db->query(
                "UPDATE p_item SET stock = stock + ? WHERE item_id = ?",
                [(int) $qty_received, (int) $item_id]
            );
        }

        // 6. Link receipt_id ke po_detail — dilakukan buat SEMUA baris (bukan cuma yang
        // sudah punya item_id), supaya item belum terdaftar tetap muncul di halaman
        // detail penerimaan (yang query-nya berdasarkan receipt_id) sampai didaftarkan.
        if ($receipt_id) {
            $this->db->where('id', (int) $detail_id)->update('po_detail', ['receipt_id' => (int) $receipt_id]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function log_price_change($data)
    {
        // Jangan include selisih — GENERATED column
        $insert = [
            'item_id'     => (int) $data['item_id'],
            'supplier_id' => isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            'harga_lama'  => (int) $data['harga_lama'],
            'harga_baru'  => (int) $data['harga_baru'],
            'sumber'      => $data['sumber'] ?? 'manual',
            'sumber_id'   => isset($data['sumber_id']) ? (int) $data['sumber_id'] : null,
            'catatan'     => $data['catatan'] ?? null,
            'changed_by'  => (int) $data['changed_by'],
        ];
        return $this->db->insert('harga_log', $insert);
    }

    public function get_price_history($item_id)
    {
        $this->db->select('harga_log.*, user.nama AS changed_by_name, supplier.nama_supplier');
        $this->db->from('harga_log');
        $this->db->join('user', 'harga_log.changed_by = user.user_id');
        $this->db->join('supplier', 'harga_log.supplier_id = supplier.supplier_id', 'left');
        $this->db->where('harga_log.item_id', (int) $item_id);
        $this->db->order_by('harga_log.changed_at', 'DESC');
        return $this->db->get()->result();
    }
}
