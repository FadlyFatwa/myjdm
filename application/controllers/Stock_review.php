<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_review extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        check_not_login();
        if (!in_array($this->fungsi->user_login()->level, [1, 2])) {
            redirect('dashboard');
        }
        $this->load->model(['item_m', 'supplier_m', 'po_cart_m']);
    }

    public function index()
    {
        $suppliers = $this->supplier_m->get()->result();
        $data = ['suppliers' => $suppliers];
        $this->template->load('template', 'purchasing/stock_review', $data);
    }

    public function get_json()
    {
        $draw         = (int) $this->input->post('draw');
        $start        = (int) $this->input->post('start');
        $length       = (int) $this->input->post('length');
        $search_value = $_POST['search']['value'] ?? '';

        $avg_subquery = "(SELECT COALESCE(ROUND(SUM(sd.qty)/6, 1), 0)
                          FROM t_sale_detail sd
                          INNER JOIN t_sale ts ON sd.sale_id = ts.sale_id
                          WHERE sd.item_id = p_item.item_id
                            AND ts.date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH))";

        $in_cart_subquery = "(SELECT 1 FROM po_cart WHERE po_cart.item_id = p_item.item_id LIMIT 1)";

        $this->db->select("p_item.item_id, p_item.barcode, p_item.nama_item, p_item.stock, p_item.modal,
                           supplier.nama_supplier, supplier.supplier_id,
                           COALESCE(supplier_barang.harga_beli, p_item.modal, 0) AS ref_price,
                           {$avg_subquery} AS avg_monthly_sales,
                           {$in_cart_subquery} AS in_cart", false);
        $this->db->from('p_item');
        $this->db->join('supplier', 'p_item.supplier_id = supplier.supplier_id', 'left');
        $this->db->join('supplier_barang', 'supplier_barang.item_id = p_item.item_id AND supplier_barang.supplier_id = p_item.supplier_id', 'left');
        $this->db->where('p_item.status', 'active');

        if (!empty($search_value)) {
            $keywords = explode(' ', trim($search_value));
            $this->db->group_start();
            foreach ($keywords as $kw) {
                $this->db->group_start();
                $this->db->like('p_item.barcode', $kw);
                $this->db->or_like('p_item.nama_item', $kw);
                $this->db->or_like('supplier.nama_supplier', $kw);
                $this->db->group_end();
            }
            $this->db->group_end();
        }

        if (isset($_POST['order'])) {
            $col_index = (int) $_POST['order'][0]['column'];
            $col_name  = $_POST['columns'][$col_index]['data'] ?? 'item_id';
            $col_dir   = $_POST['order'][0]['dir'] ?? 'DESC';
            $map = [
                'barcode'       => 'p_item.barcode',
                'nama_item'     => 'p_item.nama_item',
                'nama_supplier' => 'supplier.nama_supplier',
                'stock'         => 'p_item.stock',
                'ref_price'     => 'ref_price',
            ];
            $order_col = $map[$col_name] ?? 'p_item.item_id';
            $this->db->order_by($order_col, $col_dir);
        } else {
            $this->db->order_by('p_item.nama_item', 'ASC');
        }

        $temp_db       = clone $this->db;
        $totalFiltered = $this->db->count_all_results('', false);

        $this->db->limit($length, $start);
        $data = $this->db->get()->result();

        $totalRecords = $this->db->where('status', 'active')->count_all_results('p_item');

        $result = [];
        foreach ($data as $i => $row) {
            $stok_class = $row->stock <= 0 ? 'danger' : ($row->stock < $row->avg_monthly_sales ? 'warning' : 'success');
            $stok_label = $row->stock <= 0 ? 'HABIS' : ($row->stock < $row->avg_monthly_sales ? 'MENIPIS' : 'AMAN');

            $result[] = [
                'no'            => $start + $i + 1,
                'barcode'       => $row->barcode,
                'nama_item'     => $row->nama_item,
                'nama_supplier' => $row->nama_supplier ?? '-',
                'stock'         => '<span class="label label-' . $stok_class . '">' . $row->stock . '</span> ' . $stok_label,
                'avg_sales'     => $row->avg_monthly_sales . ' /bln',
                'ref_price'     => indo_currency($row->ref_price),
                'action'        => $row->in_cart
                    ? '<span class="label label-info" style="padding:6px 10px;font-size:11px;display:inline-block">
                           <i class="fa fa-shopping-basket"></i> Di Keranjang
                       </span>'
                    : '<button class="btn btn-success btn-xs btn-add-cart"
                           data-item_id="' . $row->item_id . '"
                           data-nama="' . htmlspecialchars($row->nama_item, ENT_QUOTES) . '"
                           data-supplier_id="' . $row->supplier_id . '"
                           data-supplier="' . htmlspecialchars($row->nama_supplier ?? '', ENT_QUOTES) . '"
                           data-ref_price="' . $row->ref_price . '"
                           data-avg="' . $row->avg_monthly_sales . '">
                           <i class="fa fa-cart-plus"></i> Tambah ke Keranjang
                       </button>',
            ];
        }

        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data'            => $result,
        ]);
        exit();
    }

    public function get_ref_price()
    {
        $item_id     = (int) $this->input->post('item_id');
        $supplier_id = (int) $this->input->post('supplier_id');
        $ref_price   = $this->po_cart_m->get_ref_price($item_id, $supplier_id);
        echo json_encode(['ref_price' => $ref_price]);
        exit();
    }

    public function check_item_ordered()
    {
        $item_id = (int) $this->input->post('item_id');
        $this->load->model('po_cart_m');
        $already = $this->po_cart_m->item_already_ordered($item_id);

        // Kalau sudah dipesan, cari konteksnya untuk ditampilkan ke user
        $context = '';
        if ($already) {
            $in_cart = $this->db->where('item_id', $item_id)->count_all_results('po_cart');
            if ($in_cart > 0) {
                $context = 'sudah ada di keranjang PO';
            } else {
                $po = $this->db->query(
                    "SELECT ph.po_number, ph.status FROM po_detail pd
                     JOIN po_header ph ON pd.po_id = ph.po_id
                     WHERE pd.item_id = ? AND ph.status IN ('sent','partial')
                     AND pd.qty_ordered > pd.qty_received
                     LIMIT 1",
                    [$item_id]
                )->row();
                if ($po) {
                    $status = $po->status === 'sent' ? 'sudah terkirim ke supplier' : 'sebagian sudah diterima';
                    $context = 'sudah ada di PO <b>' . htmlspecialchars($po->po_number) . '</b> (' . $status . ')';
                }
            }
        }

        echo json_encode(['already_ordered' => $already, 'context' => $context]);
        exit();
    }

    // Ambil semua supplier yang terdaftar di supplier_barang untuk satu item
    public function get_item_suppliers()
    {
        $item_id = (int) $this->input->post('item_id');
        $rows = $this->db
            ->select('sb.supplier_id, s.nama_supplier, COALESCE(sb.harga_beli, 0) AS harga_beli', false)
            ->from('supplier_barang sb')
            ->join('supplier s', 'sb.supplier_id = s.supplier_id')
            ->where('sb.item_id', $item_id)
            ->order_by('s.nama_supplier', 'ASC')
            ->get()->result();
        echo json_encode($rows);
        exit();
    }
}
