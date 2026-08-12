<?php
use Dompdf\Dompdf;
use Dompdf\Options;
defined('BASEPATH') OR exit('No direct script access allowed');

class item extends CI_Controller {

	function __construct(){
		parent::__construct();
		check_not_login();
		$this->load->model(['item_m','category_m','unit_m','supplier_m']);
	}

	private function _pk_from_price($price)
	{
		$map   = ['0'=>'Y','1'=>'S','2'=>'I','3'=>'T','4'=>'O','5'=>'M','6'=>'P','7'=>'U','8'=>'L','9'=>'X'];
		$s     = preg_replace('/[^0-9]/', '', (string) $price);
		$out   = '';
		$zeros = 0;
		for ($i = 0; $i < strlen($s); $i++) {
			if ($s[$i] === '0') { $zeros++; }
			else {
				if ($zeros > 1) $out .= 'Y' . $zeros;
				elseif ($zeros === 1) $out .= 'Y';
				$zeros = 0;
				$out .= $map[$s[$i]] ?? $s[$i];
			}
		}
		if ($zeros > 1) $out .= 'Y' . $zeros;
		elseif ($zeros === 1) $out .= 'Y';
		return strtoupper($out);
	}
	
	
	public function index()
	{	
		$data['row'] = $this->item_m->get();
		$this->template->load('template', 'product/item/item_data',$data);
	}

	public function archive()
	{	
		$data['row'] = $this->item_m->get();
		$this->template->load('template', 'product/item/item_archive',$data);
	}

	public function temporary()
	{
		$data['row'] = $this->item_m->get();
		$this->template->load('template', 'product/item/item_temporary',$data);
	}

	// ===================== BARANG MULTI SUPPLIER =====================

	public function multi_supplier()
	{
		check_allowed_levels([1, 2]);
		$this->template->load('template', 'product/item/item_multi_supplier');
	}

	function get_json_multi_supplier() {
		$draw = intval($this->input->post('draw'));

		$this->db->select("p_item.item_id, p_item.supplier_id, p_item.barcode, p_item.nama_item,
			sup.nama_supplier, p_category.nama_category, p_unit.nama_unit,
			p_item.modal, p_item.pk, p_item.price, p_item.stock,
			(SELECT COUNT(DISTINCT supplier_id) FROM supplier_barang WHERE item_id = p_item.item_id) AS supplier_count", false);
		$this->db->from('p_item');
		$this->db->join('supplier sup', 'p_item.supplier_id = sup.supplier_id', 'left');
		$this->db->join('p_category', 'p_item.category_id = p_category.category_id', 'left');
		$this->db->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left');
		$this->db->where('p_item.status', 'active');
		$this->db->where("(SELECT COUNT(DISTINCT supplier_id) FROM supplier_barang WHERE item_id = p_item.item_id) > 1", null, false);

		$search_value = $_POST['search']['value'] ?? null;
		$order_column_index = $_POST['order'][0]['column'] ?? null;

		// Search
		if (!empty($search_value)) {
			$keywords = explode(" ", $search_value);

			$this->db->group_start();
			foreach ($keywords as $keyword) {
				$this->db->group_start();
				$this->db->like('barcode', $keyword);
				$this->db->or_like('nama_item', $keyword);
				$this->db->or_like('nama_supplier', $keyword);
				$this->db->or_like('nama_category', $keyword);
				$this->db->or_like('nama_unit', $keyword);
				$this->db->or_like('pk', $keyword);
				$this->db->group_end();
			}
			$this->db->group_end();
		}

		// Order
		if (isset($order_column_index)) {
			$column_name = $_POST['columns'][$order_column_index]['data'];
			$column_sort_order = $_POST['order'][0]['dir'];

			$allowed_columns = [
				'barcode'        => 'p_item.barcode',
				'nama_item'      => 'p_item.nama_item',
				'nama_supplier'  => 'sup.nama_supplier',
				'nama_category'  => 'p_category.nama_category',
				'nama_unit'      => 'p_unit.nama_unit',
				'modal'          => 'p_item.modal',
				'pk'             => 'p_item.pk',
				'price'          => 'p_item.price',
				'stock'          => 'p_item.stock',
				'supplier_count' => 'supplier_count',
			];
			if (isset($allowed_columns[$column_name])) {
				$this->db->order_by($allowed_columns[$column_name], $column_sort_order);
			}
		} else {
			$this->db->order_by('supplier_count', 'DESC');
		}

		$totalFiltered = $this->db->count_all_results('', false);
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$data = $this->db->get()->result_array();

		$totalRecords = $this->db->query(
			"SELECT COUNT(*) AS cnt FROM p_item
			 WHERE status = 'active'
			   AND (SELECT COUNT(DISTINCT supplier_id) FROM supplier_barang WHERE item_id = p_item.item_id) > 1"
		)->row()->cnt;

		// Batch fetch semua supplier per item di halaman ini (1 query untuk semua item)
		$sup_map = [];
		if (!empty($data)) {
			$item_ids = array_column($data, 'item_id');
			$this->db->select('sb.item_id, sb.supplier_id, sb.harga_beli, sb.kode_beli, s.nama_supplier');
			$this->db->from('supplier_barang sb');
			$this->db->join('supplier s', 'sb.supplier_id = s.supplier_id');
			$this->db->where_in('sb.item_id', $item_ids);
			$this->db->order_by('sb.harga_beli', 'DESC');
			foreach ($this->db->get()->result_array() as $sr) {
				$sup_map[$sr['item_id']][] = $sr;
			}
		}

		$output = array(
			"draw" => $draw,
			"recordsTotal" => $totalRecords,
			"recordsFiltered" => $totalFiltered,
			"data" => array_map(function($row) use ($sup_map) {
				$active_sup_id = (int) $row['supplier_id'];
				$sups = $sup_map[$row['item_id']] ?? [];

				$supplier_list = implode('', array_map(function($s) use ($active_sup_id) {
					$is_act = ((int) $s['supplier_id'] === $active_sup_id);
					$name   = htmlspecialchars($s['nama_supplier']);
					if ($is_act) {
						$name = '<b>' . $name . '</b> <span class="label label-primary" style="font-size:10px">aktif</span>';
					}
					return '<div>' . $name . ' &mdash; ' . indo_currency($s['harga_beli'])
						. (!empty($s['kode_beli']) ? ' <small class="text-muted">(' . htmlspecialchars($s['kode_beli']) . ')</small>' : '')
						. '</div>';
				}, $sups));

				$action = '<a href="' . site_url('item/edit/' . $row['item_id']) . '" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i> Edit</a>';

				return array(
					'barcode'        => $row['barcode'],
					'nama_item'      => htmlspecialchars($row['nama_item']),
					'nama_category'  => $row['nama_category'],
					'nama_unit'      => $row['nama_unit'],
					'supplier_count' => '<span class="label label-info">' . (int) $row['supplier_count'] . ' supplier</span>',
					'supplier_list'  => $supplier_list,
					'price'          => indo_currency($row['price']),
					'stock'          => $row['stock'],
					'action'         => $action,
				);
			}, $data)
		);

		echo json_encode($output);
		exit();
	}

	function get_json() {
		$draw = intval($this->input->post('draw'));

		$this->db->select('p_item.item_id, p_item.supplier_id, p_item.barcode, p_item.nama_item,
			sup.nama_supplier, p_category.nama_category, p_unit.nama_unit,
			p_item.modal, p_item.pk, p_item.price, p_item.stock, p_item.status, p_item.is_validated,
			(SELECT COUNT(*) FROM supplier_barang
			 WHERE item_id = p_item.item_id AND supplier_id != p_item.supplier_id) AS extra_suppliers,
			(SELECT harga_lama FROM harga_log WHERE item_id = p_item.item_id AND changed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY changed_at DESC LIMIT 1) AS price_lama,
			(SELECT harga_baru FROM harga_log WHERE item_id = p_item.item_id AND changed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY changed_at DESC LIMIT 1) AS price_baru', false);
		$this->db->from('p_item');
		$this->db->join('supplier sup', 'p_item.supplier_id = sup.supplier_id', 'left');
		$this->db->join('p_category', 'p_item.category_id = p_category.category_id', 'left');
		$this->db->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left');
		$this->db->where('p_item.status', 'active');

		$search_value = $_POST['search']['value'] ?? null;
		$order_column_index = $_POST['order'][0]['column'] ?? null;

		// Search
		if (!empty($search_value)) {
			$keywords = explode(" ", $search_value);

			$this->db->group_start();
			foreach ($keywords as $keyword) {
				$this->db->group_start();
				$this->db->like('barcode', $keyword);
				$this->db->or_like('nama_item', $keyword);
				$this->db->or_like('nama_supplier', $keyword);
				$this->db->or_like('nama_category', $keyword);
				$this->db->or_like('nama_unit', $keyword);
				$this->db->or_like('pk', $keyword);
				$this->db->group_end();
			}
			$this->db->group_end();
		}

		// Order
		if (isset($order_column_index)) {
			$column_name = $_POST['columns'][$order_column_index]['data'];
			$column_sort_order = $_POST['order'][0]['dir'];

			// Peta nama kolom (data DataTables) ke kolom SQL, sekaligus mencegah SQL injection
			$allowed_columns = [
				'barcode'       => 'p_item.barcode',
				'nama_item'     => 'p_item.nama_item',
				'nama_supplier' => 'sup.nama_supplier',
				'nama_category' => 'p_category.nama_category',
				'nama_unit'     => 'p_unit.nama_unit',
				'modal'         => 'p_item.modal',
				'pk'            => 'p_item.pk',
				'price'         => 'p_item.price',
				'stock'         => 'p_item.stock',
			];
			if (isset($allowed_columns[$column_name])) {
				$this->db->order_by($allowed_columns[$column_name], $column_sort_order);
			}
		} elseif (!empty($search_value)) {
			// Jika hanya search (tanpa order), urutkan berdasarkan stok descending
			$this->db->order_by('stock', 'DESC');
		} else {
			// Default order (misal nama_item ASC)
			$this->db->order_by('barcode', 'ASC');
		}


		$totalFiltered = $this->db->count_all_results('', false);
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$query = $this->db->get();
		$data = $query->result_array();

		$totalRecords = $this->db->count_all('p_item');

		// Batch fetch semua supplier per item di halaman ini (1 query untuk semua item)
		$sup_map = [];
		if (!empty($data)) {
			$item_ids = array_column($data, 'item_id');
			$this->db->select('sb.item_id, sb.supplier_id, sb.harga_beli, sb.kode_beli, s.nama_supplier');
			$this->db->from('supplier_barang sb');
			$this->db->join('supplier s', 'sb.supplier_id = s.supplier_id');
			$this->db->where_in('sb.item_id', $item_ids);
			$this->db->where('sb.harga_beli >', 0);
			$this->db->order_by('sb.harga_beli', 'DESC');
			foreach ($this->db->get()->result_array() as $sr) {
				$sup_map[$sr['item_id']][] = $sr;
			}
		}

		$output = array(
			"draw" => $draw,
			"recordsTotal" => $totalRecords,
			"recordsFiltered" => $totalFiltered,
			"data" => array_map(function($row, $index) use ($sup_map) {
				$avg_monthly_sales = $this->calculate_average_monthly_sales($row['item_id']);
				$last_update = $this->get_last_update_date_and_qty($row['item_id']);

				// Stock status
				$status = '';
				if ($this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2) {
					if ($row['stock'] == 0) {
						$status = '<span class="label label-danger">HABIS (' . $avg_monthly_sales . ' terjual/bulan)</span>';
					} elseif ($row['stock'] < $avg_monthly_sales) {
						$status = '<span class="label label-warning">MENIPIS (' . $avg_monthly_sales . ' terjual/bulan)</span>';
					} else {
						$status = '<span class="label label-success">TERSEDIA</span>';
					}
					$update = '<small class="text-muted">(in: ' 
							. ($last_update['qty'] ?? '-') 
							. ', ' 
							. ($last_update['date'] ? indo_date($last_update['date']) : '-') 
							. ')</small>';
				} else {
					if ($row['stock'] == 0) {
						$status = '<span class="label label-danger">HABIS</span>';
					} elseif ($row['stock'] < $avg_monthly_sales) {
						$status = '<span class="label label-warning">MENIPIS</span>';
					} else {
						$status = '<span class="label label-success">TERSEDIA</span>';
					}
				}

				// Nama item + centang validasi

				$nama_item = $row['nama_item'];
				if ($this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2) {
					$nama_item .= '<br>' . $status;
				}else{
					$nama_item .= '<br>' . $status;
				}


				// Action buttons
				$action = '<a href="' . site_url('item/edit/' . $row['item_id']) . '" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i> Edit</a> '
						. '<a href="' . site_url('item/del/' . $row['item_id']) . '" id="btn-hapus" class="btn btn-danger btn-xs"><i class="fa fa-times"></i> Nonaktif</a>';

				// if ($this->fungsi->user_login()->level == 1) {
				// 	if ($row['is_validated'] == 0) {
				// 		$action .= ' <a href="' . site_url('item/toggle_validate/' . $row['item_id']) . '" class="btn btn-success btn-xs btn-validate">
				// 						<i class="fa fa-check"></i> Validate
				// 					</a>';
				// 	} else {
				// 		$action .= ' <a href="' . site_url('item/toggle_validate/' . $row['item_id']) . '" class="btn btn-warning btn-xs btn-not-validate">
				// 						<i class="fa fa-times"></i> Not Valid
				// 					</a>';
				// 	}
				// }

				if ($this->fungsi->user_login()->level == 1 || $this->fungsi->user_login()->level == 2) {
					$row['barcode'] .= '<br>' . $update ;
					// . '<br>
					// 	<a href="' . site_url('barcode/barcode_qrcode/' . $row['item_id'] . '?back_from=item') . '" class="btn btn-default btn-xs">
					// 		Print Barcode <i class="fa fa-barcode"></i>
					// 	</a>';
					$row['stock'] .= '<br>
						<a href="' . site_url('stock/stock_in_add_after/' . $row['item_id']) . '" class="btn btn-info btn-xs">
							<i class="fa fa-plus"></i> Stock
						</a>';
				}

				$extra    = (int) ($row['extra_suppliers'] ?? 0);
				$is_admin = in_array($this->fungsi->user_login()->level, [1, 2]);
				$supplier_display = $row['nama_supplier']
					? htmlspecialchars($row['nama_supplier'])
					  . ($is_admin && $extra > 0 ? ' <span class="label label-info" style="font-size:10px;margin-left:3px">+' . $extra . ' ref</span>' : '')
					: '<span class="text-muted">—</span>';

				// Trend dari harga_log (30 hari terakhir)
				$trend_diff = 0;
				$trend_lama = 0;
				if (!empty($row['price_lama']) && !empty($row['price_baru'])) {
					$trend_lama = (int) $row['price_lama'];
					$trend_baru = (int) $row['price_baru'];
					$trend_diff = $trend_baru - $trend_lama;
				}

				$active_sup_id = (int) $row['supplier_id'];
				$sups          = $sup_map[$row['item_id']] ?? [];
				$has_multi     = count($sups) > 1;

				// Badge trend selalu tampil (tanpa hover)
				$trend_inner    = ''; // span isi: admin=arah+nominal, kasir=arah saja
				$trend_badge    = ''; // admin single supplier: dibawah harga
				$pk_trend_badge = ''; // icon arah saja, inline di pk (admin)
				$pk_trend_below = ''; // icon arah saja, baris baru di pk (kasir)
				if ($trend_diff !== 0) {
					$tc   = $trend_diff > 0 ? '#dd4b39' : '#00a65a';
					$ti   = $trend_diff > 0 ? 'fa-long-arrow-up' : 'fa-long-arrow-down';
					$tpfx = $trend_diff > 0 ? '+' : '';
					$pk_trend_badge = ' <small style="color:' . $tc . ';font-size:10px"><i class="fa ' . $ti . '"></i></small>';
					$pk_trend_below = '<br><small style="color:' . $tc . ';font-size:10px"><i class="fa ' . $ti . '"></i></small>';
					if ($is_admin) {
						$trend_inner = '<span style="color:' . $tc . '"><i class="fa ' . $ti . '"></i> '
							. $tpfx . number_format($trend_diff, 0, ',', '.') . '</span>';
						$trend_badge = '<br><small style="font-size:10px">' . $trend_inner . '</small>';
					} else {
						$trend_inner = '<span style="color:' . $tc . '"><i class="fa ' . $ti . '"></i></span>';
					}
				}

				if ($has_multi) {
					usort($sups, function($a, $b) use ($active_sup_id) {
						$a_first = ((int)$a['supplier_id'] === $active_sup_id) ? 0 : 1;
						$b_first = ((int)$b['supplier_id'] === $active_sup_id) ? 0 : 1;
						return $a_first - $b_first;
					});

					$max_price  = (int) max(array_column($sups, 'harga_beli'));
					$modal_rows = [];
					$pk_rows    = [];
					foreach ($sups as $sup) {
						$is_act  = ((int)$sup['supplier_id'] === $active_sup_id);
						$is_max  = ((int)$sup['harga_beli'] === $max_price);
						$pfmt    = indo_currency($sup['harga_beli']);
						$pk_code = $is_act
							? htmlspecialchars($row['pk'])
							: (!empty($sup['kode_beli']) ? htmlspecialchars($sup['kode_beli']) : htmlspecialchars($this->_pk_from_price($sup['harga_beli'])));
						$sname   = htmlspecialchars($sup['nama_supplier'])
							. ($is_act ? ' <span style="color:#aaa;font-weight:normal">(aktif)</span>' : '');

						if ($is_max) {
							$modal_rows[] = '<b>' . $sname . ': ' . $pfmt . '</b>';
							$pk_row       = '<b>' . $sname . ': ' . $pk_code . '</b>';
						} else {
							$modal_rows[] = $sname . ': ' . $pfmt;
							$pk_row       = $sname . ': ' . $pk_code;
						}
						$pk_rows[] = $pk_row;
					}

					// Modal hint: trend nominal di kiri (admin), supplier count
					$modal_hint_line = ($trend_inner ? $trend_inner . ' &nbsp;' : '')
						. '<i class="fa fa-users"></i> ' . count($sups) . ' supplier';
					$modal_hint = '<span class="item-hint"><br><small style="color:#aaa;font-size:10px">'
						. $modal_hint_line . '</small></span>';
					$detail_modal = '<span class="item-sub"><br><small style="color:#888;font-size:10px;line-height:1.8">'
						. implode('<br>', $modal_rows) . '</small></span>';

					// PK hint: supplier count di kiri, was OLDPK di kanan (selalu tampil)
					$pk_hint_line = '<i class="fa fa-users"></i> ' . count($sups) . ' supplier';
					if ($trend_diff !== 0) {
						$tc = $trend_diff > 0 ? '#dd4b39' : '#00a65a';
						$ti = $trend_diff > 0 ? 'fa-long-arrow-up' : 'fa-long-arrow-down';
						$pk_hint_line .= ' &nbsp;<span style="color:' . $tc . '">'
							. '<i class="fa ' . $ti . '"></i> was: '
							. htmlspecialchars($this->_pk_from_price($trend_lama)) . '</span>';
					}
					$pk_hint = '<span class="item-hint"><br><small style="color:#aaa;font-size:10px">'
						. $pk_hint_line . '</small></span>';

					// Hover PK: hanya daftar supplier lain (was sudah di hint, tidak dobel)
					$detail_pk = '<span class="item-sub"><br><small style="color:#888;font-size:10px;line-height:1.8">'
						. implode('<br>', $pk_rows) . '</small></span>';

					$modal_display = indo_currency($row['modal']) . $modal_hint . $detail_modal;
					$pk_display    = $row['pk'] . $pk_hint . $detail_pk;

				} else {
					// Single supplier — was: OLDPK selalu tampil di bawah pk (tanpa hover)
					$pk_was = '';
					if ($trend_diff !== 0) {
						$tc = $trend_diff > 0 ? '#dd4b39' : '#00a65a';
						$ti = $trend_diff > 0 ? 'fa-long-arrow-up' : 'fa-long-arrow-down';
						$pk_was = '<br><small style="color:' . $tc . ';font-size:10px">'
							. '<i class="fa ' . $ti . '"></i> was: ' . $this->_pk_from_price($trend_lama)
							. '</small>';
					}
					$modal_display = indo_currency($row['modal']) . $trend_badge;
					$pk_display    = $row['pk'] . $pk_was;
				}

				return array(
					'barcode'       => $row['barcode'],
					'nama_item'     => $nama_item,
					'nama_supplier' => $supplier_display,
					'nama_category' => $row['nama_category'],
					'nama_unit'     => $row['nama_unit'],
					'modal'         => $is_admin ? $modal_display : indo_currency($row['modal']),
					'pk'            => $pk_display,
					'price'         => indo_currency($row['price']),
					'stock'         => $row['stock'],
					'action'        => $action
				);

			}, $data, array_keys($data))
		);

		echo json_encode($output);
		exit();
	}

	// ===================== CROSS-TABLE SEARCH =====================

	public function search_cross()
	{
		check_not_login();
		$this->load->model('category_m');
		$data['categories'] = $this->category_m->get()->result();
		$this->template->load('template', 'product/item/item_search', $data);
	}

	public function get_json_cross()
	{
		check_not_login();
		$draw          = intval($this->input->post('draw'));
		$search_value  = $this->input->post('search')['value'] ?? '';
		$order_index   = $this->input->post('order')[0]['column'] ?? null;
		$start         = intval($this->input->post('start'));
		$length        = intval($this->input->post('length'));

		// Filter tambahan
		$filter_category = $this->input->post('filter_category', TRUE);
		$filter_mapped   = $this->input->post('filter_mapped', TRUE);

		// Base query dengan GROUP_CONCAT untuk master & vehicle
		$this->db->select("
			p_item.item_id, p_item.barcode, p_item.nama_item, p_item.stock,
			p_item.price, p_item.modal, p_item.pk,
			supplier.nama_supplier,
			p_category.nama_category,
			GROUP_CONCAT(DISTINCT mb.nama ORDER BY mb.nama SEPARATOR ', ') as master_names,
			GROUP_CONCAT(DISTINCT CONCAT(
				IFNULL(vg.nickname, vg.code),
				IF(vg.start_year IS NOT NULL,
					CONCAT(' (', RIGHT(vg.start_year,2), '-',
						IF(vg.end_year IS NOT NULL, RIGHT(vg.end_year,2), 'skrg'), ')'
					), ''
				)
			) ORDER BY v.manufacturer, v.name SEPARATOR ' / ') as vehicle_names
		", false);
		$this->db->from('p_item');
		$this->db->join('supplier',    'p_item.supplier_id = supplier.supplier_id',         'left');
		$this->db->join('p_category',  'p_item.category_id = p_category.category_id',       'left');
		$this->db->join('p_item_master pim', 'pim.item_id = p_item.item_id',                'left');
		$this->db->join('master_barang mb',  'pim.master_barang_id = mb.id',                'left');
		$this->db->join('p_item_vehicle piv','piv.item_id = p_item.item_id',                'left');
		$this->db->join('vehicle_generations vg', 'piv.vehicle_generation_id = vg.id',     'left');
		$this->db->join('vehicles v',         'vg.vehicle_id = v.id',                       'left');
		$this->db->where('p_item.status', 'active');
		$this->db->group_by('p_item.item_id');

		// Filter kategori
		if (!empty($filter_category)) {
			$this->db->where('p_item.category_id', $filter_category);
		}

		// Filter mapped
		if ($filter_mapped === 'mapped') {
			$this->db->where('pim.item_id IS NOT NULL', null, false);
		} elseif ($filter_mapped === 'unmapped') {
			$this->db->where('pim.item_id IS NULL', null, false);
		}

		// Search multi-keyword lintas tabel
		if (!empty($search_value)) {
			$keywords = explode(' ', trim($search_value));
			$this->db->group_start();
			foreach ($keywords as $keyword) {
				$safe = $this->db->escape_like_str($keyword);
				$this->db->group_start();
				$this->db->like('p_item.barcode',      $keyword);
				$this->db->or_like('p_item.nama_item', $keyword);
				$this->db->or_like('supplier.nama_supplier', $keyword);
				$this->db->or_like('p_category.nama_category', $keyword);
				// Master barang
				$this->db->or_where("EXISTS (
					SELECT 1 FROM p_item_master pim2
					JOIN master_barang mb2 ON pim2.master_barang_id = mb2.id
					WHERE pim2.item_id = p_item.item_id
					AND mb2.nama LIKE '%{$safe}%'
				)", null, false);
				// Kendaraan
				$this->db->or_where("EXISTS (
					SELECT 1 FROM p_item_vehicle piv2
					JOIN vehicle_generations vg2 ON piv2.vehicle_generation_id = vg2.id
					JOIN vehicles v2 ON vg2.vehicle_id = v2.id
					WHERE piv2.item_id = p_item.item_id
					AND (v2.name LIKE '%{$safe}%'
					  OR v2.manufacturer LIKE '%{$safe}%'
					  OR vg2.code LIKE '%{$safe}%'
					  OR vg2.nickname LIKE '%{$safe}%')
				)", null, false);
				$this->db->group_end();
			}
			$this->db->group_end();
		}

		// Hitung total filtered (pakai subquery agar GROUP BY tidak mengganggu COUNT)
		$count_query    = $this->db->get_compiled_select('', false);
		$totalFiltered  = $this->db->query("SELECT COUNT(*) as cnt FROM ({$count_query}) as sub")->row()->cnt;

		// Sorting
		$allowed_cols = ['barcode', 'nama_item', 'nama_supplier', 'nama_category', 'price', 'stock'];
		if ($order_index !== null) {
			$col = $this->input->post('columns')[$order_index]['data'] ?? 'barcode';
			$dir = $this->input->post('order')[0]['dir'] ?? 'asc';
			if (in_array($col, $allowed_cols)) {
				$this->db->order_by($col, $dir);
			}
		} else {
			$this->db->order_by('p_item.barcode', 'asc');
		}

		$this->db->limit($length, $start);
		$rows = $this->db->get()->result_array();

		$totalRecords = $this->db->where('status', 'active')->count_all_results('p_item');

		$data = array_map(function($row) {

			// Vehicle pills — separator sesuai GROUP_CONCAT (' / ')
			$vehicle_html = '';
			if (!empty($row['vehicle_names'])) {
				$vehicles = explode(' / ', $row['vehicle_names']);
				foreach ($vehicles as $v) {
					$v = trim($v);
					if ($v === '') continue;
					$vehicle_html .= '<span style="display:inline-block;margin:2px 3px 2px 0;padding:2px 7px;'
						. 'background:#f0f0f0;border:1px solid #ccc;border-radius:3px;'
						. 'font-size:12px;color:#444;white-space:nowrap;">'
						. '<i class="fa fa-car" style="color:#888;margin-right:3px;"></i>'
						. htmlspecialchars($v)
						. '</span>';
				}
			}

			// Kolom Nama Barang:
			// Baris 1: nama komponen/master (abu-abu kecil)
			// Baris 2: brand + variant nama_item (bold, ukuran normal)
			// Baris 3: vehicle pills
			$nama_col = '';
			if (!empty($row['master_names'])) {
				$nama_col .= '<div style="color:#888;font-size:11px;line-height:1.4;">'
					. htmlspecialchars($row['master_names']) . '</div>';
			}
			$nama_col .= '<div style="font-weight:600;font-size:13px;line-height:1.5;">'
				. htmlspecialchars($row['nama_item']) . '</div>';
			if ($vehicle_html) {
				$nama_col .= '<div style="margin-top:4px;line-height:1.8;">' . $vehicle_html . '</div>';
			}

			// Supplier pill
			$supplier_html = $row['nama_supplier']
				? '<span style="display:inline-block;padding:2px 8px;background:#e8f0fe;border:1px solid #b3c6f7;'
					. 'border-radius:3px;font-size:12px;color:#1a56bb;white-space:nowrap;">'
					. htmlspecialchars($row['nama_supplier']) . '</span>'
				: '<span class="text-muted">—</span>';

			$action = '<a href="' . site_url('item/edit/' . $row['item_id']) . '" class="btn btn-warning btn-xs" title="Edit"><i class="fa fa-pencil"></i></a> '
					. '<a href="' . site_url('item/del/' . $row['item_id']) . '" id="btn-hapus" class="btn btn-danger btn-xs" title="Nonaktif"><i class="fa fa-trash"></i></a>';

			return [
				'item_id'       => $row['item_id'],
				'barcode'       => $row['barcode'],
				'nama_barang'   => $nama_col,
				'nama_supplier' => $supplier_html,
				'nama_category' => $row['nama_category'] ?: '—',
				'cost_code'     => $row['pk'] ?: '—',
				'harga_beli'    => indo_currencyex($row['modal']),
				'harga_jual'    => indo_currencyex($row['price']),
				'stock'         => $row['stock'],
				'action'        => $action,
			];
		}, $rows);

		echo json_encode([
			'draw'            => $draw,
			'recordsTotal'    => $totalRecords,
			'recordsFiltered' => $totalFiltered,
			'data'            => $data,
		]);
		exit();
	}


	// ===================== DETEKSI ITEM MIRIP =====================

	public function duplicate()
	{
		$threshold = (int) ($this->input->get('threshold') ?: 80);
		$threshold = max(50, min(100, $threshold));
		$include_fuzzy = $this->input->get('fuzzy') == '1';

		$items = $this->item_m->get_all_active_for_similarity();
		$groups = $this->detect_similar_items($items, $threshold, $include_fuzzy);

		$data = [
			'groups'        => $groups,
			'threshold'     => $threshold,
			'include_fuzzy' => $include_fuzzy,
			'total_items'   => count($items),
		];
		$this->template->load('template', 'product/item/item_duplicate', $data);
	}

	// Normalisasi nama: lowercase, buang karakter selain huruf/angka, rapatkan spasi
	private function normalize_name($name)
	{
		$name = strtolower($name);
		$name = preg_replace('/[^a-z0-9\s]/', ' ', $name);
		$name = preg_replace('/\s+/', ' ', $name);
		return trim($name);
	}

	// Signature kata yang diurutkan -> mendeteksi nama dengan susunan kata berbeda
	// contoh: "Filter Oli Yamaha" dan "Oli Filter Yamaha" akan punya signature sama
	private function sorted_signature($name)
	{
		$words = explode(' ', $this->normalize_name($name));
		sort($words);
		return implode(' ', $words);
	}

	// Ekstrak tag grade dari nama ASLI (sebelum tanda kutipnya dibuang oleh normalize_name).
	// Konvensi toko: 'G' = Original, 'B' = KW, 'L' = Lelangan (atau kombinasinya seperti 'GL').
	// Tag lain dalam kutip, atau tidak ada tag sama sekali (cuma nama merk aftermarket biasa),
	// dianggap satu kategori bersama: AFTERMARKET.
	private function extract_grade($raw_name)
	{
		if (preg_match("/'([A-Za-z]{1,3})'/", $raw_name, $m)) {
			$tag = strtoupper($m[1]);
			if (preg_match('/^[BGL]+$/', $tag)) {
				return $tag;
			}
		}
		return 'AFTERMARKET';
	}

	// Mengelompokkan item dengan nama mirip menggunakan union-find.
	// Tier 1 (selalu jalan): cocok kata sama tapi susunan berbeda -> O(n), hashmap, TANPA bandingkan pasangan.
	// Tier 2 (opsional/fuzzy): typo/singkatan -> pakai inverted index kata paling jarang sebagai blocking,
	// jadi tidak perlu membandingkan tiap item ke semua item lain (yang O(n^2) dan lambat untuk dataset besar
	// karena sebagian besar item numpuk di satu kategori).
	// Grade (Original/KW/Lelangan/Aftermarket) jadi gerbang wajib: item dengan grade berbeda TIDAK PERNAH
	// digabung walau nama sisanya identik/mirip, karena itu memang produk berbeda (beda harga & keaslian).
	private function detect_similar_items($items, $threshold, $include_fuzzy = false)
	{
		$n = count($items);
		$parent = range(0, $n - 1);

		$find = function ($x) use (&$parent, &$find) {
			while ($parent[$x] !== $x) {
				$x = $parent[$x];
			}
			return $x;
		};

		$grade = [];
		foreach ($items as $i => $item) {
			$grade[$i] = $this->extract_grade($item->nama_item);
			$item->grade = $grade[$i];
		}

		$union = function ($a, $b) use (&$parent, $find, $grade) {
			if ($grade[$a] !== $grade[$b]) return; // beda grade -> jangan digabung
			$ra = $find($a);
			$rb = $find($b);
			if ($ra !== $rb) {
				$parent[$ra] = $rb;
			}
		};

		$norm  = [];
		$sig   = [];
		$words = [];
		foreach ($items as $i => $item) {
			$norm[$i]  = $this->normalize_name($item->nama_item);
			$sig[$i]   = $this->sorted_signature($item->nama_item);
			$words[$i] = array_values(array_filter(explode(' ', $norm[$i]), function ($w) {
				return $w !== '';
			}));
		}

		// ===== TIER 1: signature kata sama (urutan beda) — O(n), tanpa pairwise =====
		$sig_buckets = [];
		foreach ($sig as $i => $s) {
			$sig_buckets[$s][] = $i;
		}
		foreach ($sig_buckets as $indices) {
			if (count($indices) < 2) continue;
			$first = $indices[0];
			for ($k = 1, $cnt = count($indices); $k < $cnt; $k++) {
				$union($first, $indices[$k]);
			}
		}

		// ===== TIER 2: fuzzy typo/singkatan — dibatasi inverted index kata langka =====
		if ($include_fuzzy) {
			$by_category = [];
			foreach ($items as $i => $item) {
				$by_category[$item->category_id ?: 0][] = $i;
			}

			foreach ($by_category as $indices) {
				// document frequency kata dalam kategori ini
				$df = [];
				foreach ($indices as $i) {
					foreach (array_unique($words[$i]) as $w) {
						$df[$w] = ($df[$w] ?? 0) + 1;
					}
				}

				// setiap item hanya diindeks via kata paling jarang miliknya (blocking key)
				$inverted = [];
				foreach ($indices as $i) {
					if (empty($words[$i])) continue;
					$rarest = $words[$i][0];
					$rarest_df = $df[$rarest] ?? PHP_INT_MAX;
					foreach ($words[$i] as $w) {
						if (($df[$w] ?? 0) < $rarest_df) {
							$rarest = $w;
							$rarest_df = $df[$w];
						}
					}
					$inverted[$rarest][] = $i;
				}

				foreach ($inverted as $bucket) {
					$count = count($bucket);
					// kata yang masih dipakai >150 item dianggap terlalu umum untuk dijadikan blocking key, skip
					if ($count < 2 || $count > 150) continue;

					for ($a = 0; $a < $count; $a++) {
						for ($b = $a + 1; $b < $count; $b++) {
							$i = $bucket[$a];
							$j = $bucket[$b];
							if ($find($i) === $find($j)) continue;

							$len_i = strlen($norm[$i]);
							$len_j = strlen($norm[$j]);
							$max_len = max($len_i, $len_j);
							if ($max_len === 0) continue;
							// upper bound similar_text: skip cepat kalau panjang dua nama terlalu jauh beda
							if ((min($len_i, $len_j) / $max_len) * 100 < $threshold) continue;

							similar_text($norm[$i], $norm[$j], $percent);
							if ($percent >= $threshold) {
								$union($i, $j);
							}
						}
					}
				}
			}
		}

		$clusters = [];
		foreach ($items as $i => $item) {
			$clusters[$find($i)][] = $item;
		}

		$groups = [];
		foreach ($clusters as $cluster) {
			if (count($cluster) > 1) {
				$groups[] = $cluster;
			}
		}

		// Prioritaskan grup yang melibatkan lebih dari satu supplier
		// (inilah kasus yang merusak data stok/pengadaan)
		usort($groups, function ($a, $b) {
			$sup_a = count(array_unique(array_column($a, 'supplier_id')));
			$sup_b = count(array_unique(array_column($b, 'supplier_id')));
			return $sup_b - $sup_a;
		});

		return $groups;
	}

	// Gabungkan beberapa item duplikat ke satu item target (dari halaman Deteksi Item Mirip).
	// Item selain target di-nonaktifkan, stoknya digabung ke target, dan harga/suppliernya
	// dipindahkan ke supplier_barang milik target supaya tidak hilang.
	public function merge()
	{
		check_allowed_levels([1, 2]);
		$this->output->set_content_type('application/json');

		$target_id = (int) $this->input->post('target_id');
		$item_ids  = $this->input->post('item_ids');

		if (!$target_id || empty($item_ids)) {
			echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
			return;
		}
		if (!is_array($item_ids)) {
			$item_ids = explode(',', $item_ids);
		}

		$loser_ids = array_diff(array_map('intval', $item_ids), [$target_id]);
		if (empty($loser_ids)) {
			echo json_encode(['status' => 'error', 'message' => 'Pilih minimal satu item lain untuk di-merge ke target.']);
			return;
		}

		$merged = $this->item_m->merge_items($target_id, $loser_ids);
		if ($merged) {
			echo json_encode(['status' => 'success', 'message' => $merged . ' item berhasil di-merge ke item target.']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Gagal melakukan merge.']);
		}
	}

	function get_json_archive() {
		// Get draw parameter from POST request
		$draw = intval($this->input->post('draw'));
	
		// Your query setup
		$this->db->select('p_item.item_id, old_barcode, nama_item, nama_supplier, nama_category, nama_unit, modal, pk, price, stock ,status');
		$this->db->from('p_item');
		$this->db->join('supplier', 'p_item.supplier_id = supplier.supplier_id', 'left');
		$this->db->join('p_category', 'p_item.category_id = p_category.category_id', 'left');
		$this->db->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left');
		
		// Perbaikan WHERE (harus dikutip karena ENUM atau VARCHAR)
		$this->db->where('p_item.status', 'inactive');
	
		// Custom search functionality
		if (!empty($_POST['search']['value'])) {
			$search_value = $_POST['search']['value'];
			$keywords = explode(" ", $search_value);
	
			$this->db->group_start();
			foreach ($keywords as $keyword) {
				$this->db->group_start();
				$this->db->like('old_barcode', $keyword);
				$this->db->or_like('nama_item', $keyword);
				$this->db->or_like('nama_supplier', $keyword);
				$this->db->or_like('nama_category', $keyword);
				$this->db->or_like('nama_unit', $keyword);
				$this->db->or_like('pk', $keyword);
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
			$this->db->order_by($column_name, $column_sort_order);
		}
	
		// Count records for filtering
		$totalFiltered = $this->db->count_all_results('', false);
	
		// Apply pagination
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$query = $this->db->get();
		$data = $query->result_array();
	
		// Count total records
		$totalRecords = $this->db->count_all('p_item');
	
		// Prepare the response with sequential numbers
		$output = array(
			"draw" => $draw,
			"recordsTotal" => $totalRecords,
			"recordsFiltered" => $totalFiltered,
			"data" => array_map(function($row, $index) {
				// Calculate average monthly sales for the item
				$avg_monthly_sales = $this->calculate_average_monthly_sales($row['item_id']);
	
				// Get last update date from stock table
				$last_update_data = $this->get_last_update_date_and_qty($row['item_id']);
	
				// Determine stock status based on stock and average monthly sales
				$status = '';
				if ($row['stock'] == 0) {
					$status = '<span class="badge bg-danger">Habis (' . $avg_monthly_sales . ' terjual/bulan)</span>';
				} elseif ($row['stock'] <= $avg_monthly_sales) {
					$status = '<span class="badge bg-warning">Menipis (' . $avg_monthly_sales . ' terjual/bulan)</span>';
				} else {
					$status = '<span class="badge bg-success">Tersedia (' . $avg_monthly_sales . ' terjual/bulan)</span>';
				}
	
				// Add last update information next to the status
				$status_with_update = $status . ' <small class="text-muted">(last in: ' 
				. ($last_update_data['date'] ? indo_date($last_update_data['date']) : '-') 
				. ', qty: ' 
				. ($last_update_data['qty'] ?? '-') 
				. ')</small>';
	
				$return_array = array(
					'barcode' => $row['old_barcode'],
					'nama_item' => $row['nama_item'] .'<br>'. '<br>' . $status_with_update,
					'nama_supplier' => $row['nama_supplier'],
					'nama_category' => $row['nama_category'],
					'nama_unit' => $row['nama_unit'],
					'modal' => indo_currency($row['modal']),
					'pk' => $row['pk'],
					'price' => indo_currency($row['price']),
					'stock' => $row['stock'],
					'action' => '<a href="' . site_url('item/active/' . $row['item_id']) . '" class="btn btn-success btn-xs"><i class="fa fa-check"></i> Aktifkan</a> '
				);
				return $return_array;
	
			}, $data, array_keys($data)) // Pass the index for sequential numbering
		);
	
		echo json_encode($output);
		exit();
	}

	public function check_barcode_range()
	{
		check_allowed_levels([1, 2]);
		$from = (int) $this->input->post('from');
		$to   = (int) $this->input->post('to');

		if ($from < 1 || $to < $from || ($to - $from) > 10000) {
			echo json_encode(['status' => 'error', 'message' => 'Range tidak valid (max 10.000 barcode sekaligus).']);
			return;
		}

		// Ambil barcode yang sudah terdaftar dalam range ini
		$rows = $this->db->select('barcode')->from('p_item')
			->where('barcode >=', str_pad($from, 5, '0', STR_PAD_LEFT))
			->where('barcode <=', str_pad($to,   5, '0', STR_PAD_LEFT))
			->get()->result_array();

		$existing = array_column($rows, 'barcode');

		// Generate semua barcode dalam range, cari yang hilang
		$missing = [];
		for ($i = $from; $i <= $to; $i++) {
			$barcode = str_pad($i, 5, '0', STR_PAD_LEFT);
			if (!in_array($barcode, $existing)) {
				$missing[] = $barcode;
			}
		}

		echo json_encode([
			'status'   => 'success',
			'total'    => $to - $from + 1,
			'used'     => count($existing),
			'missing'  => count($missing),
			'barcodes' => $missing,
		]);
	}

	function get_json_unused() {
		$draw   = intval($this->input->post('draw'));
		$search = $_POST['search']['value'] ?? '';

		$this->db->select('p_item.item_id, p_item.barcode, p_item.nama_item,
			sup.nama_supplier, p_unit.nama_unit, p_item.modal, p_item.pk, p_item.price', false);
		$this->db->from('p_item');
		$this->db->join('supplier sup', 'p_item.supplier_id = sup.supplier_id', 'left');
		$this->db->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left');
		$this->db->where('p_item.status', 'active');
		$this->db->where("p_item.item_id NOT IN (SELECT item_id FROM t_stock WHERE item_id IS NOT NULL)", null, false);
		$this->db->where("p_item.item_id NOT IN (SELECT item_id FROM t_sale_detail WHERE item_id IS NOT NULL)", null, false);

		if (!empty($search)) {
			$keywords = explode(' ', trim($search));
			$this->db->group_start();
			foreach ($keywords as $kw) {
				$this->db->group_start();
				$this->db->like('p_item.barcode', $kw);
				$this->db->or_like('p_item.nama_item', $kw);
				$this->db->or_like('sup.nama_supplier', $kw);
				$this->db->group_end();
			}
			$this->db->group_end();
		}

		$totalFiltered = $this->db->count_all_results('', false);
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$this->db->order_by('p_item.barcode', 'ASC');
		$data = $this->db->get()->result_array();

		$totalRecords = $this->db->query(
			"SELECT COUNT(*) AS cnt FROM p_item
			 WHERE status = 'active'
			   AND item_id NOT IN (SELECT item_id FROM t_stock WHERE item_id IS NOT NULL)
			   AND item_id NOT IN (SELECT item_id FROM t_sale_detail WHERE item_id IS NOT NULL)"
		)->row()->cnt;

		$is_admin = in_array($this->fungsi->user_login()->level, [1, 2]);

		$output = [
			'draw'            => $draw,
			'recordsTotal'    => $totalRecords,
			'recordsFiltered' => $totalFiltered,
			'data'            => array_map(function($row) use ($is_admin) {
				return [
					'barcode'       => $row['barcode'],
					'nama_item'     => htmlspecialchars($row['nama_item']),
					'nama_supplier' => $row['nama_supplier'] ?: '—',
					'nama_unit'     => $row['nama_unit'] ?: '—',
					'modal'         => $is_admin ? indo_currency($row['modal']) : null,
					'pk'            => $row['pk'],
					'price'         => indo_currency($row['price']),
					'item_id'       => $row['item_id'],
				];
			}, $data),
		];

		echo json_encode($output);
		exit();
	}

	function get_json_temporary() {
		// Get draw parameter from POST request
		$draw = intval($this->input->post('draw'));
	
		// Your query setup
		$this->db->select('p_item.item_id, old_barcode, nama_item, nama_supplier, nama_category, nama_unit, modal, pk, price, stock ,status');
		$this->db->from('p_item');
		$this->db->join('supplier', 'p_item.supplier_id = supplier.supplier_id', 'left');
		$this->db->join('p_category', 'p_item.category_id = p_category.category_id', 'left');
		$this->db->join('p_unit', 'p_item.unit_id = p_unit.unit_id', 'left');
		
		// Perbaikan WHERE (harus dikutip karena ENUM atau VARCHAR)
		$this->db->where('p_item.status', 'temporary');
	
		// Custom search functionality
		if (!empty($_POST['search']['value'])) {
			$search_value = $_POST['search']['value'];
			$keywords = explode(" ", $search_value);
	
			$this->db->group_start();
			foreach ($keywords as $keyword) {
				$this->db->group_start();
				$this->db->like('old_barcode', $keyword);
				$this->db->or_like('nama_item', $keyword);
				$this->db->or_like('nama_supplier', $keyword);
				$this->db->or_like('nama_category', $keyword);
				$this->db->or_like('nama_unit', $keyword);
				$this->db->or_like('pk', $keyword);
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
			$this->db->order_by($column_name, $column_sort_order);
		}
	
		// Count records for filtering
		$totalFiltered = $this->db->count_all_results('', false);
	
		// Apply pagination
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$query = $this->db->get();
		$data = $query->result_array();
	
		// Count total records
		$totalRecords = $this->db->count_all('p_item');
	
		// Prepare the response with sequential numbers
		$output = array(
			"draw" => $draw,
			"recordsTotal" => $totalRecords,
			"recordsFiltered" => $totalFiltered,
			"data" => array_map(function($row, $index) {
				// Calculate average monthly sales for the item
				$avg_monthly_sales = $this->calculate_total_sales($row['item_id']);
	
				// Get last update date from stock table
				$last_update_data = $this->get_last_update_date_and_qty($row['item_id']);
	
				// Determine stock status based on stock and average monthly sales
				$status = '';
				if ($row['stock'] == 0) {
					$status = '<span class="badge bg-danger">Habis (' . $avg_monthly_sales . ' terjual/bulan)</span>';
				} elseif ($row['stock'] <= $avg_monthly_sales) {
					$status = '<span class="badge bg-warning">Menipis (' . $avg_monthly_sales . ' terjual/bulan)</span>';
				} else {
					$status = '<span class="badge bg-success">Tersedia (' . $avg_monthly_sales . ' terjual/bulan)</span>';
				}
	
				// Add last update information next to the status
				$status_with_update = $status . ' <small class="text-muted">(last in: ' 
				. ($last_update_data['date'] ? indo_date($last_update_data['date']) : '-') 
				. ', qty: ' 
				. ($last_update_data['qty'] ?? '-') 
				. ')</small>';
	
				$return_array = array(
					'barcode' => $row['old_barcode'],
					'nama_item' => $row['nama_item'] .'<br>'. '<br>' . $status_with_update,
					'nama_supplier' => $row['nama_supplier'],
					'nama_category' => $row['nama_category'],
					'nama_unit' => $row['nama_unit'],
					'modal' => indo_currency($row['modal']),
					'pk' => $row['pk'],
					'price' => indo_currency($row['price']),
					'stock' => $row['stock'],
					'action' => '<a href="' . site_url('item/active/' . $row['item_id']) . '" class="btn btn-success btn-xs"><i class="fa fa-check"></i> Aktifkan</a> '
				);
				return $return_array;
	
			}, $data, array_keys($data)) // Pass the index for sequential numbering
		);
	
		echo json_encode($output);
		exit();
	}


	public function toggle_validate($id)
	{
		$item = $this->item_m->get($id)->row();
		if ($item) {
			$new_status = $item->is_validated ? 0 : 1;

			$this->item_m->update($id, ['is_validated' => $new_status]);

			$status_text = $new_status ? 'divalidasi' : 'dibatalkan';
			$this->session->set_flashdata('success', 'Barang berhasil ' . $status_text . '.');
		} else {
			$this->session->set_flashdata('error', 'Data barang tidak ditemukan.');
		}

		redirect('item');
	}

	private function calculate_total_sales($item_id) {

		$this->db->select('SUM(t_sale_detail.qty) AS total_sold');
		$this->db->from('t_sale');
		$this->db->join('t_sale_detail', 't_sale.sale_id = t_sale_detail.sale_id');
		$this->db->where('t_sale_detail.item_id', $item_id);

		$query = $this->db->get();
		$result = $query->row();

		return ($result->total_sold != null) ? (int)$result->total_sold : 0;
	}


	// Function to calculate average monthly sales for an item
	private function calculate_average_monthly_sales($item_id) {
		$this->db->select('YEAR(t_sale.date) AS year,
			MONTH(t_sale.date) AS month,
			SUM(t_sale_detail.qty) AS total_sold');
		$this->db->from('t_sale');
		$this->db->join('t_sale_detail', 't_sale.sale_id = t_sale_detail.sale_id');
		$this->db->where('item_id', $item_id);
		$this->db->group_by('YEAR(t_sale.date), MONTH(t_sale.date)');
		$query = $this->db->get();
	
		$results = $query->result_array();
		$total_months = count($results);
		$total_sales = 0;
	
		foreach ($results as $result) {
			$total_sales += $result['total_sold'];
		}
	
		// Avoid division by zero
		return ($total_months > 0) ? ceil($total_sales / $total_months) : 0;
	}
	
	// Function to get the last update date for an item from the stock table
	private function get_last_update_date_and_qty($item_id) {
		// Ambil semua kolom yang diperlukan
		$this->db->select('date, qty'); 
		$this->db->from('t_stock');
		$this->db->where('item_id', $item_id);
		$this->db->where('type', "in");
		
		// Urutkan berdasarkan tanggal secara descending dan ambil 1 baris teratas
		$this->db->order_by('date', 'DESC');
		$this->db->limit(1);
		
		$query = $this->db->get();
		$result = $query->row_array();
		
		// Kembalikan hasil sebagai array asosiatif atau null jika tidak ada data
		return $result ? $result : ['date' => null, 'qty' => null];
	}
	
	public function add(){
		check_allowed_levels([1, 2]);
		$item = new stdClass();
		$item->item_id = null;
		$item->nama_item = null;
		$item->barcode = null;
		$item->modal = null;
		$item->pk = null;
		$item->price = null;
	
		$max_barcode = $this->item_m->get_max_barcode();
		$new_barcode = str_pad((int)$max_barcode + 1, 5, '0', STR_PAD_LEFT); // Increment the max barcode or start from 1 if no barcode exists
	
		$query_supplier = $this->supplier_m->get();
		$supplier[null] = '-Pilih-';
		foreach($query_supplier->result() as $spy){
			$supplier[$spy->supplier_id] = $spy->nama_supplier;
		}
	
		$query_category = $this->category_m->get();
		$category[null] = '- Pilih -';
		foreach($query_category->result()as $ctg){
			$category[$ctg->category_id] = $ctg->nama_category;
		}
	
		$query_unit = $this->unit_m->get();
		$unit[null] = '- Pilih -';
		foreach($query_unit->result()as $unt){
			$unit[$unt->unit_id] = $unt->nama_unit;
		}
	
		$data = array(
			'page' => 'add',
			'row' => $item,
			'category' => $category, 'selectedcategory' => 'null',
			'supplier' => $supplier, 'selectedsupplier' => 'null',
			'unit' => $unit, 'selectedunit' => 'null',
			'new_barcode' => $new_barcode
		);
		$this->template->load('template', 'product/item/item_form',$data);
	}
	public function add_multiple()
	{
		check_allowed_levels([1, 2]);
		// Query untuk dropdown supplier
		$query_supplier = $this->supplier_m->get();
		$supplier[null] = '-Pilih-';
		foreach ($query_supplier->result() as $spy) {
			$supplier[$spy->supplier_id] = $spy->nama_supplier;
		}

		// Query untuk dropdown kategori
		$query_category = $this->category_m->get();
		$category[null] = '- Pilih -';
		foreach ($query_category->result() as $ctg) {
			$category[$ctg->category_id] = $ctg->nama_category;
		}

		// Query untuk dropdown unit
		$query_unit = $this->unit_m->get();
		$unit[null] = '- Pilih -';
		foreach ($query_unit->result() as $unt) {
			$unit[$unt->unit_id] = $unt->nama_unit;
		}

		// Ambil barcode maksimal untuk menghasilkan barcode baru
		$max_barcode = $this->item_m->get_max_barcode();
		$new_barcode = str_pad((int)$max_barcode + 1, 5, '0', STR_PAD_LEFT);  // Increment barcode

		// Data yang dikirim ke view
		$data = array(
			'page' => 'add',
			'category' => $category,
			'supplier' => $supplier,
			'unit' => $unit,
			'new_barcode' => $new_barcode
		);

		// Load view dengan template
		$this->template->load('template', 'product/item/item_form_multiple', $data);
	}

	public function edit($id)
	{
		check_allowed_levels([1, 2]);
		$query = $this->item_m->get($id);
		if($query->num_rows() >  0){
			$item = $query->row();

			$query_supplier = $this->supplier_m->get();
            $supplier[null] = '- Pilih -';
            foreach($query_supplier->result()as $spy){
                $supplier[$spy->supplier_id] = $spy->nama_supplier;
            }

			$query_category = $this->category_m->get();
            $category[null] = '- Pilih -';
            foreach($query_category->result()as $ctg){
                $category[$ctg->category_id] = $ctg->nama_category;
            }

            $query_unit = $this->unit_m->get();
            $unit[null] = '- Pilih -';
            foreach($query_unit->result()as $unt){
                $unit[$unt->unit_id] = $unt->nama_unit;
            }

            // Supplier yang sudah terdaftar di supplier_barang untuk item ini
            $supplier_barang = $this->db
                ->select('sb.supplier_id, sb.harga_beli, sb.kode_beli, s.nama_supplier', false)
                ->from('supplier_barang sb')
                ->join('supplier s', 'sb.supplier_id = s.supplier_id')
                ->where('sb.item_id', $id)
                ->order_by('s.nama_supplier', 'ASC')
                ->get()->result();

		    $data = array(
			    'page'            => 'edit',
			    'row'             => $item,
				'supplier'        => $supplier, 'selectedsupplier' => $item->supplier_id,
                'category'        => $category, 'selectedcategory' => $item->category_id,
                'unit'            => $unit,     'selectedunit'     => $item->unit_id,
                'supplier_barang' => $supplier_barang,
                'all_suppliers'   => $query_supplier->result(),
		    );
		    $this->template->load('template', 'product/item/item_form',$data);
	    }else{
			echo "<script>alert('Data Tidak Ditemukan');";
			redirect('item');
		}
	}
    public function add_supplier()
    {
        check_allowed_levels([1, 2]);
        $item_id     = (int) $this->input->post('item_id');
        $supplier_id = (int) $this->input->post('supplier_id');
        $harga_beli  = (int) str_replace('.', '', $this->input->post('harga_beli'));
        $kode_beli   = trim($this->input->post('kode_beli') ?? '');

        if (!$item_id || !$supplier_id) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
            return;
        }

        $exists = $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)
                           ->count_all_results('supplier_barang');
        if ($exists) {
            $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)
                     ->update('supplier_barang', ['harga_beli' => $harga_beli, 'kode_beli' => $kode_beli ?: null]);
        } else {
            $this->db->insert('supplier_barang', [
                'item_id'     => $item_id,
                'supplier_id' => $supplier_id,
                'harga_beli'  => $harga_beli,
                'kode_beli'   => $kode_beli ?: null,
            ]);
        }

        $supplier = $this->db->where('supplier_id', $supplier_id)->get('supplier')->row();
        echo json_encode([
            'status'       => 'success',
            'supplier_id'  => $supplier_id,
            'nama_supplier'=> $supplier ? $supplier->nama_supplier : '',
            'harga_beli'   => $harga_beli,
            'kode_beli'    => $kode_beli,
        ]);
    }

    public function update_supplier_kode()
    {
        check_allowed_levels([1, 2]);
        $item_id     = (int) $this->input->post('item_id');
        $supplier_id = (int) $this->input->post('supplier_id');
        $kode_beli   = trim($this->input->post('kode_beli') ?? '');

        if (!$item_id || !$supplier_id) {
            echo json_encode(['status' => 'error']); return;
        }
        $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)
                 ->update('supplier_barang', ['kode_beli' => $kode_beli ?: null]);
        echo json_encode(['status' => 'success']);
    }

    public function remove_supplier()
    {
        check_allowed_levels([1, 2]);
        $item_id     = (int) $this->input->post('item_id');
        $supplier_id = (int) $this->input->post('supplier_id');
        $main_supplier = $this->db->select('supplier_id')->where('item_id', $item_id)->get('p_item')->row();

        if ($main_supplier && (int)$main_supplier->supplier_id === $supplier_id) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak bisa hapus supplier utama. Ganti supplier utama dulu di form.']);
            return;
        }

        $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)->delete('supplier_barang');
        echo json_encode(['status' => 'success']);
    }

	public function active($id)
	{
		$query = $this->item_m->get($id);
		if($query->num_rows() >  0){
			$item = $query->row();

			$query_supplier = $this->supplier_m->get();
            $supplier[null] = '- Pilih -';
            foreach($query_supplier->result()as $spy){
                $supplier[$spy->supplier_id] = $spy->nama_supplier;
            }

			$query_category = $this->category_m->get();
            $category[null] = '- Pilih -';
            foreach($query_category->result()as $ctg){
                $category[$ctg->category_id] = $ctg->nama_category;
            }

            $query_unit = $this->unit_m->get();
            $unit[null] = '- Pilih -';
            foreach($query_unit->result()as $unt){
                $unit[$unt->unit_id] = $unt->nama_unit;
            }

		    $data = array(
			    'page' => 'active',
			    'row' => $item,
				'supplier' => $supplier, 'selectedsupplier' => $item->supplier_id,
                'category' => $category, 'selectedcategory' => $item->category_id,
                'unit' => $unit, 'selectedunit' => $item->unit_id,
		    );
		    $this->template->load('template', 'product/item/item_form',$data);
	    }else{
			echo "<script>alert('Data Tidak Ditemukan');";
			redirect('item');
		}
	}

	public function process()
	{
		check_allowed_levels([1, 2]);
		$post = $this->input->post(null, TRUE);
		if(isset($_POST['add'])){
			if($this->item_m->check_barcode($post['barcode'])->num_rows() > 0){
				$this->session->set_flashdata('error',"Barcode $post[barcode] sudah dipakai barang lain");
				redirect('item/add');
			}else {
				$this->item_m->add($post);
					$item_id = $this->db->insert_id();  // Get the newly added item's ID
	
					if ($this->db->affected_rows() > 0) {
						$this->session->set_flashdata('success', 'Data Barang berhasil disimpan');
					}
	
					// Set flashdata to indicate that SweetAlert should be displayed
					$this->session->set_flashdata('show_sweetalert', true);
					$this->session->set_flashdata('item_id', $item_id);
	
					// Redirect to the view where SweetAlert will be shown
					redirect('item');
			}
	
		}elseif (isset($_POST['add_multiple'])) {
				// Loop melalui setiap barang yang ditambahkan
				$barcodes       = $post['barcode'];
				$nama_items     = $post['nama_item'];
				$suppliers      = $post['supplier'];
				$supplier_refs  = $this->input->post('supplier_refs') ?: [];
				$categories     = $post['category'];
				$units          = $post['unit'];
				$modals         = $post['modal'];
				$pks            = $post['pk'];
				$prices         = $post['price'];

				$item_ids = []; // Array untuk menyimpan semua item_id
				foreach ($barcodes as $index => $barcode) {
					// Validasi apakah barcode sudah ada di database
					if ($this->item_m->check_barcode($barcode)->num_rows() > 0) {
						$this->session->set_flashdata('error', "Barcode $barcode sudah dipakai barang lain");
						redirect('item/add_multiple');
						return;
					}

					$params = [
						'barcode'     => $barcode,
						'nama_item'   => $nama_items[$index],
						'supplier_id' => $suppliers[$index],
						'category_id' => $categories[$index],
						'unit_id'     => $units[$index],
						'modal'       => $modals[$index],
						'pk'          => $pks[$index],
						'price'       => $prices[$index],
						'stock'       => 0,
					];

					$this->db->insert('p_item', $params);
					$new_item_id = $this->db->insert_id();
					$item_ids[]  = $new_item_id;

					// Supplier utama ke supplier_barang
					$sid = (int) $suppliers[$index];
					if ($new_item_id && $sid) {
						$this->db->insert('supplier_barang', [
							'item_id'     => $new_item_id,
							'supplier_id' => $sid,
							'harga_beli'  => (int) $modals[$index],
							'harga_jual'  => (int) $prices[$index],
							'kode_beli'   => $pks[$index],
						]);
					}

					// Supplier refs tambahan (bisa 0, 1, atau lebih)
					$refs = $supplier_refs[$index] ?? [];
					foreach ($refs as $ref_sid) {
						$ref_sid = (int) $ref_sid;
						if ($ref_sid && $ref_sid !== $sid) {
							$exists = $this->db->where('item_id', $new_item_id)
							                   ->where('supplier_id', $ref_sid)
							                   ->count_all_results('supplier_barang');
							if (!$exists) {
								$this->db->insert('supplier_barang', [
									'item_id'     => $new_item_id,
									'supplier_id' => $ref_sid,
									'harga_beli'  => (int) $modals[$index],
									'harga_jual'  => (int) $prices[$index],
									'kode_beli'   => $pks[$index],
								]);
							}
						}
					}
				} // end foreach

				// Set flashdata untuk notifikasi sukses
				if ($this->db->affected_rows() > 0) {
					$this->session->set_flashdata('success', 'Data Barang berhasil disimpan');
				}
				// item_ids dikirim lewat query string (bukan hanya flashdata) supaya tidak
				// hilang kalau session tidak konsisten antar-request di server hosting.
				redirect('stock/in/add_multiple?ids=' . implode(',', $item_ids));
			}else if(isset($_POST['edit'])){
			if($this->item_m->check_barcode($post['barcode'], $post['id'])->num_rows() > 0){
				$this->session->set_flashdata('error',"Barcode $post[barcode] sudah dipakai barang lain");
				redirect('item/edit/'.$post['id']);
			}else{
				$this->item_m->edit($post);
			}
			
		}else if(isset($_POST['active'])){
			if($this->item_m->check_barcode($post['barcode'], $post['id'])->num_rows() > 0){
				$this->session->set_flashdata('error',"Barcode $post[barcode] sudah dipakai barang lain");
				redirect('item/edit/'.$post['id']);
			}else{
				$this->item_m->inarchive($post);
			}
			
		}
		if($this->db->affected_rows() > 0){
            $this->session->set_flashdata('success','Data Barang berhasil disimpan');
        }
        redirect('item');
		
	}

	public function hard_del($id)
	{
		$this->item_m->del($id);
		if($this->db->affected_rows() > 0){
			$this->session->set_flashdata('success','Data Barang berhasil dihapus');
		}
		redirect('item');
	}
	public function del($id)
{
    check_allowed_levels([1, 2]);
    $this->item_m->soft_delete($id);
    if($this->db->affected_rows() > 0){
        $this->session->set_flashdata('success', 'Data Barang berhasil dinonaktifkan dan barcode dikosongkan');
    }
    redirect('item');
}


	

    

}
