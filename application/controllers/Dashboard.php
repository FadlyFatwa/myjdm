<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	function __construct(){
		parent::__construct();
		check_not_login();
        $this->load->model('sale_m');
		$this->load->model(['customer_m','item_m']);
	}
	public function index()
	{
		$data['sum_today']   = (int) $this->db->query("SELECT COALESCE(SUM(final_price),0) AS v FROM t_sale WHERE DATE(date)=CURDATE() AND final_price > 50")->row()->v;
		$data['sum_month']   = (int) $this->db->query("SELECT COALESCE(SUM(final_price),0) AS v FROM t_sale WHERE YEAR(date)=YEAR(CURDATE()) AND MONTH(date)=MONTH(CURDATE()) AND final_price > 1")->row()->v;
		$data['count_today'] = (int) $this->db->query("SELECT COUNT(*) AS v FROM t_sale WHERE DATE(date)=CURDATE() AND final_price > 50")->row()->v;
		$data['po_aktif']    = (int) $this->db->query("SELECT COUNT(*) AS v FROM po_header WHERE status IN ('sent','partial')")->row()->v;
		$data['cart_items']  = (int) $this->db->query("SELECT COUNT(*) AS v FROM po_cart")->row()->v;
		$this->template->load('template', 'dashboard_main', $data);
	}

	public function get_sales_chart_json()
	{
		$rows = $this->db->query(
			"SELECT DATE(date) AS d, SUM(final_price) AS total, COUNT(*) AS cnt
			 FROM t_sale
			 WHERE date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) AND final_price > 50
			 GROUP BY DATE(date) ORDER BY d ASC"
		)->result();

		$map = [];
		foreach ($rows as $r) $map[$r->d] = ['total' => (int)$r->total, 'cnt' => (int)$r->cnt];

		$labels = $values = $counts = [];
		for ($i = 29; $i >= 0; $i--) {
			$d = date('Y-m-d', strtotime("-{$i} days"));
			$labels[] = date('d/m', strtotime($d));
			$values[] = $map[$d]['total'] ?? 0;
			$counts[] = $map[$d]['cnt']   ?? 0;
		}

		echo json_encode(['labels' => $labels, 'values' => $values, 'counts' => $counts]);
		exit();
	}

	public function get_top_items_json()
	{
		$rows = $this->db->query(
			"SELECT p_item.nama_item,
			        SUM(t_sale_detail.qty) AS total_qty,
			        SUM(t_sale_detail.total) AS total_nilai
			 FROM t_sale_detail
			 JOIN t_sale ON t_sale_detail.sale_id = t_sale.sale_id
			 JOIN p_item ON t_sale_detail.item_id = p_item.item_id
			 WHERE YEAR(t_sale.date)=YEAR(CURDATE()) AND MONTH(t_sale.date)=MONTH(CURDATE())
			       AND t_sale.final_price > 50 AND t_sale_detail.price_sale > 1
			 GROUP BY t_sale_detail.item_id
			 ORDER BY total_qty DESC LIMIT 5"
		)->result();

		echo json_encode($rows);
		exit();
	}

	public function get_recent_sales_json()
	{
		$rows = $this->db->query(
			"SELECT t_sale.sale_id, invoice, customer_name, final_price,
			        date, user.nama AS kasir
			 FROM t_sale
			 JOIN user ON t_sale.user_id = user.user_id
			 WHERE t_sale.final_price > 50
			 ORDER BY t_sale.sale_id DESC LIMIT 7"
		)->result();

		echo json_encode($rows);
		exit();
	}

	public function get_po_active_json()
	{
		$rows = $this->db->query(
			"SELECT po_header.po_id, po_number, nama_supplier, po_date, po_header.status,
			        COALESCE(SUM(po_detail.qty_ordered),0)  AS total_ordered,
			        COALESCE(SUM(po_detail.qty_received),0) AS total_received
			 FROM po_header
			 JOIN supplier ON po_header.supplier_id = supplier.supplier_id
			 LEFT JOIN po_detail ON po_detail.po_id = po_header.po_id
			 WHERE po_header.status IN ('sent','partial')
			 GROUP BY po_header.po_id
			 ORDER BY po_header.po_id DESC LIMIT 5"
		)->result();

		echo json_encode($rows);
		exit();
	}

	public function get_kasir_stats_json()
	{
		$uid = (int) $this->session->userdata('userid');
		$today = $this->db->query(
			"SELECT COALESCE(SUM(final_price),0) AS sum_today, COUNT(*) AS count_today
			 FROM t_sale WHERE DATE(date)=CURDATE() AND user_id=? AND final_price > 50",
			[$uid]
		)->row();
		$month = $this->db->query(
			"SELECT COALESCE(SUM(final_price),0) AS sum_month
			 FROM t_sale WHERE YEAR(date)=YEAR(CURDATE()) AND MONTH(date)=MONTH(CURDATE())
			   AND user_id=? AND final_price > 50",
			[$uid]
		)->row();
		echo json_encode([
			'sum_today'   => (int) $today->sum_today,
			'count_today' => (int) $today->count_today,
			'sum_month'   => (int) $month->sum_month,
		]);
		exit();
	}

	public function get_kasir_recent_json()
	{
		$uid  = (int) $this->session->userdata('userid');
		$rows = $this->db->query(
			"SELECT sale_id, invoice, customer_name, final_price, date
			 FROM t_sale WHERE user_id=? AND final_price > 50
			 ORDER BY sale_id DESC LIMIT 7",
			[$uid]
		)->result();
		echo json_encode($rows);
		exit();
	}

	public function get_kasir_stock_alert_json()
	{
		$rows = $this->db->query(
			"SELECT p_item.item_id, p_item.nama_item, p_item.stock, p_item.price,
			        supplier.nama_supplier
			 FROM p_item
			 JOIN supplier ON p_item.supplier_id = supplier.supplier_id
			 WHERE p_item.stock <= 3 AND p_item.status = 'active'
			 ORDER BY p_item.stock ASC, p_item.nama_item ASC
			 LIMIT 20"
		)->result();
		echo json_encode($rows);
		exit();
	}

	public function stock_empty() {
        
        $this->template->load('template', 'analisis/stock_alert');
    }

	public function get_analisis_penjualan_json() {
		$keyword = $this->input->post('keyword', true);
		if (empty($keyword)) {
			echo json_encode([
				'barang_teranalisis' => [],
				'analisis_bulanan' => []
			]);
			return;
		}
	
		// Data analisis per bulan
		$analisis_bulanan = $this->sale_m->get_analisis_penjualan_json($keyword);
	
		// Data barang yang dianalisis
		$barang_teranalisis = $this->sale_m->get_barang_teranalisis($keyword);
	
		$output = [
			'analisis_bulanan' => [],
			'barang_teranalisis' => []
		];
	
		// Format data analisis per bulan
		foreach ($analisis_bulanan as $row) {
			$output['analisis_bulanan'][] = [
				'year' => $row->year,
				'month' => $row->month,
				'total_qty_sold' => $row->total_qty_sold,
				'total_transactions' => $row->total_transactions,
				'avg_qty_per_transaction' => number_format($row->avg_qty_per_transaction, 2)
			];
		}
	
		// Format data barang yang dianalisis
		foreach ($barang_teranalisis as $item) {
			$output['barang_teranalisis'][] = [
				'item_id' => $item->item_id,
				'nama_item' => $item->nama_item,
				'nama_supplier' => $item->nama_supplier,
				'modal' => indo_currency($item->modal),
				'pk' => $item->pk,
				'total_qty_sold' => $item->total_qty_sold
			];
		}
	
		echo json_encode($output);
	}

	public function get_json_stock_low() {
	
		// Get draw parameter from POST request
		$draw = intval($this->input->post('draw'));
	
		// Query utama dengan join ke `t_sale_detail`
		$this->db->select('
			p_item.item_id,
			p_item.barcode,
			p_item.nama_item,
			supplier.nama_supplier,
			p_category.nama_category,
			p_unit.nama_unit,
			p_item.modal,
			p_item.pk,
			p_item.price,
			p_item.stock,
			IFNULL(SUM(t_sale_detail.qty), 0) AS total_sold
		');
		$this->db->from('p_item');
		$this->db->join('supplier', 'p_item.supplier_id = supplier.supplier_id', 'left');
		$this->db->join('p_category', 'p_item.category_id = p_category.category_id', 'left');
		$this->db->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left');
		$this->db->join('t_sale_detail', 'p_item.item_id = t_sale_detail.item_id', 'left');
		$this->db->where('p_item.stock > 0 AND p_item.stock < 3');
		$this->db->group_by('p_item.item_id'); // Group by untuk menghitung total_sold
	
		// Custom search functionality
		if (!empty($_POST['search']['value'])) {
			$search_value = $_POST['search']['value'];
			$keywords = explode(" ", $search_value);
	
			$this->db->group_start();
			foreach ($keywords as $keyword) {
				$this->db->group_start();
				$this->db->like('supplier.nama_supplier', $keyword);
				$this->db->group_end();
			}
			$this->db->group_end();
		}
	
		// Handle sorting
		if (isset($_POST['order'][0]['column'])) {
			$column_index = intval($_POST['order'][0]['column']); // Column index
			$column_name = $_POST['columns'][$column_index]['data']; // Column name
			$column_sort_order = $_POST['order'][0]['dir']; // Ascending or Descending
		
			// Apply sorting berdasarkan kolom yang dipilih
			$this->db->order_by($column_name, $column_sort_order);
		} else {
			// Default sorting: stock ASC dan total_sold DESC
			$this->db->order_by('p_item.stock', 'ASC'); // Stok ascending
			$this->db->order_by('total_sold', 'DESC'); // Total terjual descending
		}
		
	
		// Count records for filtering
		$totalFiltered = $this->db->count_all_results('', false);
	
		// Apply pagination
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$query = $this->db->get();
		$data = $query->result_array();
	
		// Count total records
		$this->db->from('p_item');
		$totalRecords = $this->db->count_all_results();
	
		// Prepare the response with sequential numbers
		$output = array(
			"draw" => $draw,
			"recordsTotal" => $totalRecords,
			"recordsFiltered" => $totalFiltered,
			"data" => array_map(function($row, $index) {
				return array(
					'no' => $index + 1, // Sequential number
					'barcode' => $row['barcode'],
					'nama_item' => $row['nama_item'],
					'nama_supplier' => $row['nama_supplier'],
					'nama_category' => $row['nama_category'],
					'nama_unit' => $row['nama_unit'],
					'modal' => indo_currency($row['modal']),
					'pk' => $row['pk'],
					'price' => indo_currency($row['price']),
					'stock' => $row['stock'],
					'total_sold' => $row['total_sold'], // Total barang terjual
					'action' => '<a href="' . site_url('item/del/' . $row['item_id']) . '" id="btn-hapus" class="btn btn-danger btn-xs"><i class="fa fa-times"></i> Nonaktif</a>'
				);
			}, $data, array_keys($data)) // Pass the index for sequential numbering
		);
	
		echo json_encode($output);
		exit();
	}
	
	
	public function get_json_stock_0() {
	
		// Get draw parameter from POST request
		$draw = intval($this->input->post('draw'));
	
		// Query utama dengan join ke `t_sale_detail`
		$this->db->select('
			p_item.item_id,
			p_item.barcode,
			p_item.nama_item,
			supplier.nama_supplier,
			p_category.nama_category,
			p_unit.nama_unit,
			p_item.modal,
			p_item.pk,
			p_item.price,
			p_item.stock,
			IFNULL(SUM(t_sale_detail.qty), 0) AS total_sold
		');
		$this->db->from('p_item');
		$this->db->join('supplier', 'p_item.supplier_id = supplier.supplier_id', 'left');
		$this->db->join('p_category', 'p_item.category_id = p_category.category_id', 'left');
		$this->db->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left');
		$this->db->join('t_sale_detail', 'p_item.item_id = t_sale_detail.item_id', 'left');
		$this->db->where('p_item.stock < 1');
		// Perbaikan WHERE (harus dikutip karena ENUM atau VARCHAR)
		$this->db->where('p_item.status', 'active');
		$this->db->group_by('p_item.item_id'); // Group by untuk menghitung total_sold
	
		// Custom search functionality
		if (!empty($_POST['search']['value'])) {
			$search_value = $_POST['search']['value'];
			$keywords = explode(" ", $search_value);
	
			$this->db->group_start();
			foreach ($keywords as $keyword) {
				$this->db->group_start();
				$this->db->like('supplier.nama_supplier', $keyword);
				$this->db->group_end();
			}
			$this->db->group_end();
		}
	
		// Handle sorting
		if (isset($_POST['order'][0]['column'])) {
			$column_index = intval($_POST['order'][0]['column']); // Column index
			$column_name = $_POST['columns'][$column_index]['data']; // Column name
			$column_sort_order = $_POST['order'][0]['dir']; // Ascending or Descending
	
			// Apply sorting
			if ($column_name == 'total_sold') {
				$this->db->order_by('total_sold', $column_sort_order); // Sorting by total sold
			} else {
				$this->db->order_by($column_name, $column_sort_order);
			}
		} else {
			$this->db->order_by('total_sold', 'DESC'); // Default sorting
		}
	
		// Count records for filtering
		$totalFiltered = $this->db->count_all_results('', false);
	
		// Apply pagination
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$query = $this->db->get();
		$data = $query->result_array();
	
		// Count total records
		$this->db->from('p_item');
		$totalRecords = $this->db->count_all_results();
	
		// Prepare the response with sequential numbers
		$output = array(
			"draw" => $draw,
			"recordsTotal" => $totalRecords,
			"recordsFiltered" => $totalFiltered,
			"data" => array_map(function($row, $index) {
				return array(
					'no' => $index + 1, // Sequential number
					'barcode' => $row['barcode'],
					'nama_item' => $row['nama_item'],
					'nama_supplier' => $row['nama_supplier'],
					'nama_category' => $row['nama_category'],
					'nama_unit' => $row['nama_unit'],
					'modal' => indo_currency($row['modal']),
					'pk' => $row['pk'],
					'price' => indo_currency($row['price']),
					'stock' => $row['stock'],
					'total_sold' => $row['total_sold'], // Total barang terjual
					'action' => '<a href="' . site_url('item/del/' . $row['item_id']) . '" id="btn-hapus" class="btn btn-danger btn-xs"><i class="fa fa-times"></i> Nonaktif</a>'
				);
			}, $data, array_keys($data)) // Pass the index for sequential numbering
		);
	
		echo json_encode($output);
		exit();
	}

	public function get_notifications_json() {
		$level = (int) $this->session->userdata('level');
		if (!in_array($level, [1, 2])) { echo json_encode(['count'=>0,'items',[]]); exit(); }
		$this->db->query(
			"DELETE FROM notifications
			 WHERE (is_read = 1 AND created_at < NOW() - INTERVAL 7 DAY)
			    OR created_at < NOW() - INTERVAL 30 DAY"
		);
		$rows = $this->db->query(
			"SELECT id, type, title, message, item_name, created_at, ref_id
			 FROM notifications
			 WHERE is_read = 0 AND for_level = ?
			 ORDER BY id DESC LIMIT 20",
			[$level]
		)->result();
		echo json_encode(['count' => count($rows), 'items' => $rows]);
		exit();
	}

	public function mark_notifications_read() {
		$level = (int) $this->session->userdata('level');
		if (!in_array($level, [1, 2])) { echo json_encode(['success'=>false]); exit(); }
		$this->db->where('is_read', 0)->where('for_level', $level)->update('notifications', ['is_read' => 1]);
		echo json_encode(['success' => true]);
		exit();
	}

	public function mark_one_notification_read() {
		$level = (int) $this->session->userdata('level');
		if (!in_array($level, [1, 2])) { echo json_encode(['success'=>false]); exit(); }
		$id = (int) $this->input->post('id');
		if ($id > 0) {
			$this->db->where('id', $id)->where('for_level', $level)->update('notifications', ['is_read' => 1]);
		}
		echo json_encode(['success' => true]);
		exit();
	}

	public function get_superadmin_extra_json()
	{
		if ((int) $this->fungsi->user_login()->level !== 1) { echo json_encode([]); exit(); }
		$sum_last_month = (int) $this->db->query(
			"SELECT COALESCE(SUM(final_price),0) AS v FROM t_sale
			 WHERE YEAR(date)=YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
			   AND MONTH(date)=MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
			   AND final_price > 1"
		)->row()->v;
		$kasir_active = (int) $this->db->query(
			"SELECT COUNT(DISTINCT user_id) AS v FROM t_sale WHERE DATE(date)=CURDATE() AND final_price > 50"
		)->row()->v;
		echo json_encode(['sum_last_month' => $sum_last_month, 'kasir_active_today' => $kasir_active]);
		exit();
	}

	public function get_admin_stats_json()
	{
		if (!in_array((int) $this->fungsi->user_login()->level, [1, 2])) { echo json_encode([]); exit(); }
		$po_pending    = (int) $this->db->query("SELECT COUNT(*) AS v FROM po_header WHERE status IN ('sent','partial')")->row()->v;
		$stock_0       = (int) $this->db->query("SELECT COUNT(*) AS v FROM p_item WHERE stock < 1 AND status='active'")->row()->v;
		$stock_low     = (int) $this->db->query("SELECT COUNT(*) AS v FROM p_item WHERE stock > 0 AND stock <= 3 AND status='active'")->row()->v;
		$gr_today      = (int) $this->db->query("SELECT COUNT(*) AS v FROM po_receipt WHERE DATE(created_at)=CURDATE()")->row()->v;
		$pending_items = (int) $this->db->query("SELECT COUNT(*) AS v FROM p_item_pending WHERE status='pending'")->row()->v;
		echo json_encode([
			'po_pending'    => $po_pending,
			'stock_0'       => $stock_0,
			'stock_low'     => $stock_low,
			'gr_today'      => $gr_today,
			'pending_items' => $pending_items,
		]);
		exit();
	}

	public function get_admin_gr_recent_json()
	{
		if (!in_array((int) $this->fungsi->user_login()->level, [1, 2])) { echo json_encode([]); exit(); }
		$rows = $this->db->query(
			"SELECT pr.receipt_id, ph.po_number, ph.po_id, s.nama_supplier,
			        pr.receive_date, u.nama AS received_by,
			        COUNT(pd.id) AS total_lines, SUM(pd.qty_received) AS total_qty
			 FROM po_receipt pr
			 JOIN po_header ph ON pr.po_id = ph.po_id
			 JOIN supplier s ON ph.supplier_id = s.supplier_id
			 LEFT JOIN user u ON pr.received_by = u.user_id
			 LEFT JOIN po_detail pd ON pd.receipt_id = pr.receipt_id
			 GROUP BY pr.receipt_id
			 ORDER BY pr.receipt_id DESC LIMIT 5"
		)->result();
		echo json_encode($rows);
		exit();
	}

	public function get_admin_pending_items_json()
	{
		if (!in_array((int) $this->fungsi->user_login()->level, [1, 2])) { echo json_encode([]); exit(); }
		$rows = $this->db->query(
			"SELECT pip.pending_id, pip.nama_item, pip.qty, pip.created_at,
			        s.nama_supplier, u.nama AS submitted_by
			 FROM p_item_pending pip
			 LEFT JOIN supplier s ON pip.supplier_id = s.supplier_id
			 LEFT JOIN user u ON pip.created_by = u.user_id
			 WHERE pip.status = 'pending'
			 ORDER BY pip.created_at DESC LIMIT 5"
		)->result();
		echo json_encode($rows);
		exit();
	}

	public function get_kasir_avg_json()
	{
		$uid = (int) $this->session->userdata('userid');
		$avg = (int) $this->db->query(
			"SELECT COALESCE(AVG(daily_total),0) AS v FROM (
			    SELECT DATE(date) AS d, SUM(final_price) AS daily_total
			    FROM t_sale
			    WHERE user_id=? AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND final_price > 50
			    GROUP BY DATE(date)
			 ) AS t",
			[$uid]
		)->row()->v;
		echo json_encode(['avg' => $avg]);
		exit();
	}

	public function get_kasir_top_items_json()
	{
		$uid = (int) $this->session->userdata('userid');
		$rows = $this->db->query(
			"SELECT p_item.nama_item, SUM(tsd.qty) AS qty
			 FROM t_sale_detail tsd
			 JOIN t_sale ts ON tsd.sale_id = ts.sale_id
			 JOIN p_item ON tsd.item_id = p_item.item_id
			 WHERE ts.user_id=? AND DATE(ts.date)=CURDATE() AND ts.final_price > 50
			 GROUP BY tsd.item_id ORDER BY qty DESC LIMIT 3",
			[$uid]
		)->result();
		echo json_encode($rows);
		exit();
	}

	public function get_gudang_stats_json()
	{
		if ((int) $this->fungsi->user_login()->level !== 4) { echo json_encode([]); exit(); }
		$uid = (int) $this->session->userdata('userid');
		$my_total       = (int) $this->db->query("SELECT COUNT(*) AS v FROM p_item_pending WHERE created_by=?", [$uid])->row()->v;
		$pending_count  = (int) $this->db->query("SELECT COUNT(*) AS v FROM p_item_pending WHERE status='pending'")->row()->v;
		$ready_count    = (int) $this->db->query("SELECT COUNT(*) AS v FROM p_item_pending WHERE status='printed'")->row()->v;
		$attached_today = (int) $this->db->query("SELECT COUNT(*) AS v FROM p_item_pending WHERE status='attached' AND DATE(attached_at)=CURDATE()")->row()->v;
		echo json_encode([
			'my_total'       => $my_total,
			'pending_count'  => $pending_count,
			'ready_count'    => $ready_count,
			'attached_today' => $attached_today,
		]);
		exit();
	}

	public function get_gudang_ready_items_json()
	{
		if ((int) $this->fungsi->user_login()->level !== 4) { echo json_encode([]); exit(); }
		$rows = $this->db->query(
			"SELECT pip.pending_id, pip.nama_item, pip.qty, pip.printed_at,
			        pc.nama_category, pu.nama_unit
			 FROM p_item_pending pip
			 LEFT JOIN p_category pc ON pip.category_id = pc.category_id
			 LEFT JOIN p_unit pu ON pip.unit_id = pu.unit_id
			 WHERE pip.status = 'printed'
			 ORDER BY pip.printed_at ASC LIMIT 10"
		)->result();
		echo json_encode($rows);
		exit();
	}

	public function get_gudang_recent_submissions_json()
	{
		if ((int) $this->fungsi->user_login()->level !== 4) { echo json_encode([]); exit(); }
		$uid  = (int) $this->session->userdata('userid');
		$rows = $this->db->query(
			"SELECT pip.pending_id, pip.nama_item, pip.status, pip.qty, pip.created_at,
			        s.nama_supplier
			 FROM p_item_pending pip
			 LEFT JOIN supplier s ON pip.supplier_id = s.supplier_id
			 WHERE pip.created_by = ?
			 ORDER BY pip.created_at DESC LIMIT 5",
			[$uid]
		)->result();
		echo json_encode($rows);
		exit();
	}

}
