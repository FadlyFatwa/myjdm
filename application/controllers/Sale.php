<?php
use Dompdf\Dompdf;
use Dompdf\Options;
defined('BASEPATH') OR exit('No direct script access allowed');

class Sale extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		check_not_login();
        $this->load->model('sale_m');
		$this->load->model(['customer_m','item_m','return_m']);
		$this->load->library('sale_service');
	}

	public function index()
	{
		$this->template->load('template', 'transaction/sale/sale_form', $this->_pos_data());
	}

	// Mode Full — bisa diakses siapa saja yang berhak buka Sale (termasuk admin via toggle)
	public function full()
	{
		$this->template->load('template', 'transaction/sale/sale_form', $this->_pos_data());
	}

	// Mode Lite — khusus level Admin (LEVEL_ADMIN)
	public function lite()
	{
		check_allowed_levels([LEVEL_ADMIN]);
		$this->template->load('template', 'transaction/sale/sale_form_lite', $this->_pos_data());
	}

	private function _pos_data()
	{
		// Ambil tanggal dari MySQL
		$query = $this->db->query("SELECT CURDATE() AS today");
		$today = $query->row()->today;

		$this->load->model('Service_item_m');

		return array(
			'customer'   => $this->customer_m->get()->result(),
			'item'       => $this->item_m->get_status()->result(),
			'cart'       => $this->sale_m->get_cart(),
			'cart_jasa'  => $this->sale_m->get_cart_jasa(),
			'jasa_list'  => $this->Service_item_m->get_all(),
			'invoice'    => $this->sale_m->invoice_no(),
			'today'      => $today
		);
	}


	function get_item(){
		$barcode = $this->input->post('barcode');
		$item = $this->item_m->get_barcode($barcode)->row();
		if($this->db->affected_rows() > 0) {
			$params = array("success" => true, "item" => $item);
		} else {
			$params = array("success" => false);
		}
		echo json_encode($params);
	}


	public function process(){
		$data = $this->input->post(null, TRUE);
		$mode = $data['mode'] ?? 'full';

		if(isset($_POST['add_cart'])){

			$item_id    = $this->input->post('item_id');
			$check_cart = $this->sale_m->get_cart(['t_cart.item_id' => $item_id])->num_rows();
			if ($check_cart > 0) {
				$this->sale_m->update_cart_qty($data);
			} else {
				$this->sale_m->add_cart($data);
			}

			if ($this->db->affected_rows() > 0) {
				$item_data  = $this->item_m->get($item_id)->row();
				$avg_qty    = $this->item_m->get_avg_qty_per_transaction($item_id);
				$threshold  = max(1, (int) ceil($avg_qty));
				$user_id    = (int) $this->session->userdata('userid');
				$cart_qty   = $this->sale_m->get_cart_qty_for_item($item_id, $user_id);
				$eff_stock  = max(0, (int) $item_data->stock - $cart_qty);

				$low_stock_info = ['show' => false];
				if ($eff_stock === 0) {
					$low_stock_info = [
						'show'    => true,
						'type'    => 'habis',
						'stock'   => 0,
						'message' => "Stok {$item_data->nama_item} sudah HABIS!",
					];
				} elseif ($eff_stock <= $threshold) {
					$low_stock_info = [
						'show'    => true,
						'type'    => 'menipis',
						'stock'   => $eff_stock,
						'message' => "Stok {$item_data->nama_item} menipis, sisa {$eff_stock}",
					];
				}

				$params = ['success' => true, 'low_stock_info' => $low_stock_info];
			} else {
				$params = ['success' => false, 'low_stock_info' => ['show' => false]];
			}
			echo json_encode($params);
		}
		
		if(isset($_POST['edit_cart'])) {

            $edit_price = (float) ($data['price'] ?? 0);

            if ($mode !== 'lite' && $edit_price <= 1) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Harga Rp 1 adalah harga sementara dan tidak bisa dijual. Silahkan masukkan harga jual yang sebenarnya.',
                ]);
                return;
            }

            // Validasi: harga jual tidak boleh dibawah harga modal (dilewati untuk mode lite)
            $cart_item = $this->db
                ->select('t_cart.*, p_item.modal')
                ->from('t_cart')
                ->join('p_item', 't_cart.item_id = p_item.item_id')
                ->where('t_cart.cart_id', $data['cart_id'])
                ->get()->row();

            if ($mode !== 'lite' && $cart_item && (float) $cart_item->modal > 0 && $edit_price < (float) $cart_item->modal) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Harga jual (Rp ' . number_format($edit_price, 0, ',', '.') . ') berada di bawah harga modal (Rp ' . number_format($cart_item->modal, 0, ',', '.') . '). Transaksi tidak dapat diproses.',
                ]);
                return;
            }

            $this->sale_m->edit_cart($data);

            if($this->db->affected_rows() > 0) {
                $params = array("success" => true);
            } else {
                $params = array("success" => false);
            }
            echo json_encode($params);
        }

		if (isset($_POST['process_payment'])) {
			$post = $this->input->post(null, TRUE);

			// Validasi: tidak boleh ada item dengan harga sementara Rp 1 (dilewati untuk mode lite)
			$cart_check = $this->sale_m->get_cart()->result();
			if ($mode !== 'lite') {
				foreach ($cart_check as $c) {
					if ((float) $c->cart_price <= 1) {
						echo json_encode([
							'success' => false,
							'message' => 'Barang "' . $c->nama_barang_jual . '" masih berharga Rp 1 (harga sementara). Silahkan edit harga jual sebelum memproses transaksi.',
						]);
						return;
					}
					if ((float) $c->modal > 0 && (float) $c->cart_price < (float) $c->modal) {
						echo json_encode([
							'success' => false,
							'message' => 'Harga jual barang "' . $c->nama_barang_jual . '" (Rp ' . number_format($c->cart_price, 0, ',', '.') . ') berada di bawah harga modal (Rp ' . number_format($c->modal, 0, ',', '.') . '). Transaksi tidak dapat diproses.',
						]);
						return;
					}
				}
			}

			// Gunakan Sale_service untuk business logic
			$payment       = $this->sale_service->resolvePayment($post);
			$customer_name = $this->sale_service->resolveCustomerName(
				(int) ($post['customer_id'] ?? 0),
				$post['customer_name'] ?? ''
			);
			$sale_data = $this->sale_service->buildSaleData(
				$post,
				$payment,
				$customer_name,
				(int) $this->session->userdata('userid')
			);

			// Simpan data penjualan
			$sale_id = $this->sale_m->add_sale($sale_data);

			// AR (Piutang) otomatis untuk transaksi kredit dengan customer terdaftar
			if ($sale_data['payment_status'] === 'belum lunas' && !empty($sale_data['customer_id'])) {
				$this->load->model('Ar_invoice_m');
				$sale_row = $this->sale_m->get_sale($sale_id)->row();
				if ($sale_row) {
					$this->Ar_invoice_m->create_from_sale($sale_row);
				}
			}

			// Ambil data cart dan update harga jika diperlukan
			$cart    = $cart_check;
			$details = $this->sale_service->buildSaleDetails($cart, $sale_id);

			foreach ($cart as $value) {
				$item = $this->sale_m->get_item($value->item_id);
				if ($item && $item->price == 1) {
					$this->sale_m->update_item_price($value->item_id, $value->cart_price);
				}
			}

			// Simpan detail penjualan
			$this->sale_m->add_sale_detail($details);

			// Auto-add ke PO cart jika stok habis/menipis setelah penjualan
			$this->load->model('po_cart_m');
			$user_id = (int) $this->session->userdata('userid');
			foreach ($cart as $sold) {
				$item_data = $this->item_m->get($sold->item_id)->row();
				if (!$item_data) continue;
				$avg_qty   = $this->item_m->get_avg_qty_per_transaction($sold->item_id);
				$threshold = max(1, (int) ceil($avg_qty));
				if ((int) $item_data->stock <= $threshold) {
					$type = ((int) $item_data->stock <= 0) ? 'habis' : 'menipis';
					$label = ($type === 'habis') ? 'Stok habis' : 'Stok menipis';

					// Ambil semua supplier item dari supplier_barang
					$suppliers = $this->db
						->select('sb.supplier_id, sb.harga_beli')
						->from('supplier_barang sb')
						->where('sb.item_id', $item_data->item_id)
						->get()->result();

					// Fallback jika tidak ada di supplier_barang
					if (empty($suppliers)) {
						$suppliers = [(object)[
							'supplier_id' => $item_data->supplier_id,
							'harga_beli'  => $item_data->modal,
						]];
					}

					foreach ($suppliers as $sp) {
						// Cek per supplier: sudah di keranjang atau PO aktif?
						$in_cart = $this->db->where('item_id', $item_data->item_id)
						                    ->where('supplier_id', $sp->supplier_id)
						                    ->count_all_results('po_cart');

						$in_po = (int) $this->db->query(
							"SELECT COUNT(*) AS cnt FROM po_detail pd
							 JOIN po_header ph ON pd.po_id = ph.po_id
							 WHERE pd.item_id = ? AND ph.supplier_id = ?
							 AND ph.status IN ('sent','partial')
							 AND pd.qty_ordered > pd.qty_received",
							[$item_data->item_id, $sp->supplier_id]
						)->row()->cnt;

						if ($in_cart > 0 || $in_po > 0) continue;

						$this->po_cart_m->add([
							'item_id'     => $item_data->item_id,
							'supplier_id' => $sp->supplier_id,
							'qty'         => max(1, $threshold),
							'ref_price'   => (int) ($sp->harga_beli ?: $item_data->modal),
							'notes'       => 'Auto dari penjualan — stok ' . $type,
							'added_by'    => $user_id,
						]);
						if ($this->db->affected_rows() > 0) {
							foreach ([1, 2] as $target_level) {
								$this->db->insert('notifications', [
									'type'      => 'po_cart_auto',
									'title'     => $label . ' — ' . $item_data->nama_item,
									'message'   => 'Otomatis masuk keranjang PO. Stok saat ini: ' . $item_data->stock,
									'item_id'   => $item_data->item_id,
									'item_name' => $item_data->nama_item,
									'for_level' => $target_level,
								]);
							}
						}
					}
				}
			}

			// Simpan detail jasa
			$cart_jasa       = $this->sale_m->get_cart_jasa();
			$jasa_details    = $this->sale_service->buildSaleJasaDetails($cart_jasa, $sale_id);
			$this->sale_m->add_sale_jasa_detail($jasa_details);

			// Hapus cart setelah proses pembayaran
			$this->sale_m->del_cart(['user_id' => $this->session->userdata('userid')]);
			$this->sale_m->del_cart_jasa(['user_id' => $this->session->userdata('userid')]);

			if ($sale_id) {
				echo json_encode(['success' => true, 'sale_id' => $sale_id]);
			} else {
				echo json_encode(['success' => false]);
			}
		}
 
	}
	
	public function update_status() {
		$sale_id = $this->input->post('sale_id');

		// Transaksi yang sudah dikelola modul AR baru — jangan toggle mentah,
		// arahkan ke alur pembayaran piutang supaya histori cicilan tetap konsisten.
		$this->load->model('Ar_invoice_m');
		$ar = $this->Ar_invoice_m->get_by_sale($sale_id);
		if ($ar) {
			echo json_encode([
				'success'  => false,
				'redirect' => site_url('ar-invoice/detail/' . $ar->ar_invoice_id),
			]);
			return;
		}

		// Fallback: data lama sebelum modul AR aktif — perilaku asli tetap dipertahankan
		$payment_status = $this->input->post('payment_status');
		$this->db->where('sale_id', $sale_id);
		$this->db->update('t_sale', ['payment_status' => $payment_status]);

		if ($this->db->affected_rows() > 0) {
			echo json_encode(['success' => true]);
		} else {
			echo json_encode(['success' => false]);
		}
	}
	

	function cart_data(){
		$cart = $this->sale_m->get_cart();
		$data['cart'] = $cart;
		$this->load->view('transaction/sale/cart_data', $data);
	}

	public function add_jasa_cart()
	{
		$jasa_id  = (int) $this->input->post('jasa_id');
		$qty      = max(1, (int) $this->input->post('qty'));

		$this->load->model('Service_item_m');
		$jasa = $this->Service_item_m->get($jasa_id);
		if (!$jasa) {
			echo json_encode(['success' => false, 'message' => 'Jasa tidak ditemukan']);
			return;
		}

		$this->sale_m->add_cart_jasa([
			'jasa_id'   => $jasa_id,
			'nama_jasa' => $jasa->nama_jasa,
			'tarif'     => $jasa->tarif,
			'qty'       => $qty,
		]);

		echo json_encode(['success' => true]);
	}

	public function del_jasa_cart()
	{
		$id        = (int) $this->input->post('id');
		$clear_all = $this->input->post('clear_all');

		if ($clear_all) {
			$this->sale_m->del_cart_jasa(['user_id' => $this->session->userdata('userid')]);
		} elseif ($id > 0) {
			$this->sale_m->del_cart_jasa(['id' => $id]);
		}

		echo json_encode(['success' => true]);
	}

	public function cart_jasa_data()
	{
		$data['cart_jasa'] = $this->sale_m->get_cart_jasa();
		$this->load->view('transaction/sale/cart_jasa_data', $data);
	}

	public function update_jasa_cart()
	{
		$id    = (int) $this->input->post('id');
		$tarif = (int) $this->input->post('tarif');
		$qty   = max(1, (int) $this->input->post('qty'));

		if ($id < 1 || $tarif < 1) {
			echo json_encode(['success' => false]);
			return;
		}

		$this->sale_m->update_cart_jasa($id, [
			'nama_jasa' => $this->input->post('nama_jasa', TRUE),
			'tarif'     => $tarif,
			'qty'       => $qty,
			'total'     => $tarif * $qty,
		]);

		echo json_encode(['success' => true]);
	}

	public function cart_del(){
		if(isset($_POST['cancel_payment'])){
			$this->sale_m->del_cart(['user_id' => $this->session->userdata('userid')]);
		}else{
        $cart_id = $this->input->post('cart_id');
        $this->sale_m->del_cart(['cart_id'=> $cart_id]);
		}

		if($this->db->affected_rows() > 0){
			$params = array("success" => true);
		}else{
			$params = array("success" => false);

		}
		echo json_encode($params);
    }

	public function search_item()
	{
		$search_value = $this->input->post('keyword');

		$this->db->select('
			p_item.item_id,
			p_item.barcode,
			p_item.nama_item,
			p_item.pk,
			p_item.price,
			p_item.stock,
			p_item.status,
			supplier.nama_supplier,
		');

		$this->db->from('p_item');
		$this->db->join('supplier', 'p_item.supplier_id = supplier.supplier_id', 'left');

		$this->db->where('p_item.status !=', 'inactive');

		if (!empty($search_value)) {

			$keywords = preg_split('/\s+/', trim($search_value));

			$this->db->group_start();

			foreach ($keywords as $keyword) {

				$this->db->group_start();

				$this->db->like('p_item.barcode', $keyword);
				$this->db->or_like('p_item.nama_item', $keyword);
				$this->db->or_like('p_item.pk', $keyword);
				$this->db->or_like('supplier.nama_supplier', $keyword);

				$this->db->group_end();
			}

			$this->db->group_end();
		}

		$this->db->limit(10);

		$query = $this->db->get()->result();

		if($query){
			foreach($query as $row){

				$stok_label = ($row->stock > 0) 
					? '<span class="label label-success">Stok: '.$row->stock.'</span>' 
					: '<span class="label label-danger">Habis</span>';

				echo '
				<a href="#"
				class="list-group-item item-select"
				data-id="'.$row->item_id.'"
				data-name="'.$row->nama_item.'"
				data-price="'.$row->price.'"
   				data-stock="'.$row->stock.'"
				data-pk="'.$row->pk.'"
				data-barcode="'.$row->barcode.'">

					<strong>'.$row->barcode.' - '.$row->nama_item.'</strong><br>
					<small>
						PK: '.($row->pk ?? '-').' |
						Supplier: '.($row->nama_supplier ?? '-').' |
						'.$stok_label.' |
						Harga: '.indo_currency($row->price).'
					</small>
				</a>';
			}
		} else {
			echo '<div class="list-group-item">Tidak ditemukan</div>';
		}
	}


	// Halaman preview print — tampilkan pilihan nota besar / nota kecil
	public function preview($id)
	{
		$sale        = $this->sale_m->get_sale($id)->row();
		$sale_detail = $this->_merge_sale_detail($id);

		$data = [
			'sale'        => $sale,
			'sale_detail' => $sale_detail,
			'from'        => $this->input->get('from') ?? 'sale',
		];

		$this->template->load('template', 'transaction/sale/receipt_preview', $data);
	}

	// Nota Besar — PDF A4 via DOMPDF (receipt_print.php)
	public function cetak($id)
	{
		$sale        = $this->sale_m->get_sale($id)->row();
		$sale_detail = $this->_merge_sale_detail($id);

		$data  = ['sale' => $sale, 'sale_detail' => $sale_detail];
		$html  = $this->load->view('transaction/sale/receipt_print', $data, true);

		$options = new \Dompdf\Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isRemoteEnabled', true);
		$dompdf = new \Dompdf\Dompdf($options);
		$dompdf->loadHtml($html);
		$dompdf->setPaper([0, 0, 595.28, 453.54], 'portrait');
		$dompdf->render();
		$dompdf->stream('nota_besar.pdf', ['Attachment' => 0]);
	}

	// Nota Kecil — browser print thermal (receipt_browser.php)
	public function cetak_kecil($id)
	{
		$sale        = $this->sale_m->get_sale($id)->row();
		$sale_detail = $this->_merge_sale_detail($id);

		$data = ['sale' => $sale, 'sale_detail' => $sale_detail];
		$this->load->view('transaction/sale/receipt_browser', $data);
	}

	// Merge barang + jasa untuk tampilan nota
	private function _merge_sale_detail($sale_id)
	{
		$barang = $this->sale_m->get_sale_detail($sale_id)->result();
		$jasa   = $this->sale_m->get_sale_jasa_detail($sale_id);

		foreach ($jasa as $j) {
			$j->is_jasa          = true;
			$j->nama_barang_jual = $j->nama_jasa;
			$j->price_sale       = $j->tarif;
			$j->discount_item    = 0;
			$j->barcode          = '';
			$j->nama_unit        = '';
		}

		return array_merge($barang, $jasa);
	}

	// Di controller Sale.php
	public function edit($sale_id) {
		$sale = $this->sale_m->get_sale($sale_id)->row();
		$sale_details = $this->sale_m->get_sale_detail($sale_id)->result();
		
		// Ambil data retur
		$returned_items = $this->return_m->get_returned_items($sale_id);
		$returned_data = [];
		foreach ($returned_items as $item) {
			$returned_data[$item->item_id] = $item->returned_qty;
		}
		
		$this->load->model('Service_item_m');

		$data = [
			'sale'              => $sale,
			'sale_details'      => $sale_details,
			'sale_jasa_details' => $this->sale_m->get_sale_jasa_detail($sale_id),
			'jasa_list'         => $this->Service_item_m->get_all(),
			'returned_items'    => $returned_data,
			'customer'          => $this->customer_m->get()->result(),
			'items'             => $this->item_m->get_status()->result(),
			'redirect_after'    => $this->input->get('from') === 'preview' ? 'preview' : 'report',
		];

		$this->template->load('template', 'transaction/sale/edit_form', $data);
	}
	
	public function update() {
		$post = $this->input->post();

		if (empty($post['sale_id'])) {
			$this->session->set_flashdata('error', 'Sale ID tidak ditemukan.');
			redirect('report/sale');
		}

		// Helper: strip titik pemisah ribuan dari string terformat ("125.000" → 125000)
		$strip = function($v) { return (float) str_replace('.', '', $v ?? 0); };

		// Resolve customer_name: jika walk-in pakai input, jika member ambil dari DB
		$customer_id = $post['customer_id'] ?? '';
		if (!empty($customer_id)) {
			$cust          = $this->db->get_where('customer', ['customer_id' => $customer_id])->row();
			$customer_name = $cust ? $cust->nama_customer : '';
		} else {
			$customer_name = $post['customer_name'] ?? '';
		}

		// Data utama penjualan
		$data = [
			'customer_id'    => !empty($customer_id) ? $customer_id : null,
			'customer_name'  => $customer_name,
			'date'           => $post['date'],
			'payment_method' => $post['payment_method'],
			'payment_status' => $post['payment_status'],
			'note'           => $post['note'],
			'discount'       => $strip($post['discount']),
			'total_price'    => $strip($post['subtotal']),
			'final_price'    => $strip($post['grandtotal']),
		];

		// Proses data detail penjualan
		$details = [];
		foreach ($post['item_id'] as $key => $item_id) {
			$details[] = [
				'item_id'          => $item_id,
				'nama_barang_jual' => $post['nama_barang_jual'][$key],
				'price_sale'       => $strip($post['price'][$key]),
				'qty'              => (int) $post['qty'][$key],
				'discount_item'    => $strip($post['discount_item'][$key]),
				// pakai total_raw[] yang menyimpan nilai mentah tanpa format
				'total'            => $strip($post['total_raw'][$key] ?? $post['total'][$key]),
				'is_modified'      => isset($post['is_modified'][$key]) ? $post['is_modified'][$key] : 0,
			];
		}

		// Update data barang
		$success = $this->sale_m->update_sale($post['sale_id'], $data, $details);

		// Update data jasa: hapus semua lama, insert ulang dari form
		$this->db->where('sale_id', $post['sale_id'])->delete('t_sale_jasa_detail');
		if (!empty($post['jasa_nama'])) {
			$jasa_batch = [];
			foreach ($post['jasa_nama'] as $k => $nama_jasa) {
				$tarif = (int) str_replace('.', '', $post['jasa_tarif'][$k] ?? 0);
				$qty   = max(1, (int) ($post['jasa_qty'][$k] ?? 1));
				if (empty($nama_jasa) || $tarif < 1) continue;
				$jasa_batch[] = [
					'sale_id'   => (int) $post['sale_id'],
					'jasa_id'   => (int) ($post['jasa_source_id'][$k] ?? 0),
					'nama_jasa' => $this->security->xss_clean($nama_jasa),
					'tarif'     => $tarif,
					'qty'       => $qty,
					'total'     => $tarif * $qty,
				];
			}
			if (!empty($jasa_batch)) {
				$this->sale_m->add_sale_jasa_detail($jasa_batch);
			}
		}

		if ($success) {
			$this->session->set_flashdata('success', 'Data penjualan berhasil diperbarui.');
		} else {
			$this->session->set_flashdata('error', 'Gagal memperbarui data penjualan.');
		}

		if (($post['redirect_after'] ?? '') === 'preview') {
			redirect('sale/preview/' . $post['sale_id']);
		} else {
			redirect('report/sale');
		}
	}
	

	public function del($id)
    {
        $this->sale_m->del_sale($id);
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Data Penjualan berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus Data Penjualan');
        }
        redirect('report/sale');
    }

	// Sale.php controller
	public function check_stock() {
		$item_id = $this->input->post('item_id');
		$item = $this->item_m->get($item_id)->row();

		if ($item) {
			echo json_encode(['success' => true, 'stock' => $item->stock]);
		} else {
			echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
		}
	}


	public function create_temporary()
	{
		
		$nama_item = $this->input->post('nama_item');
		$price     = (int) $this->input->post('price');

		if($price <= 0){
			echo json_encode(['success' => false]);
			return;
		}

		$data = [
			'nama_item'    => $nama_item,
			'category_id'  => TEMP_CATEGORY_ID,
			'unit_id'      => TEMP_UNIT_ID,
			'supplier_id'  => TEMP_SUPPLIER_ID,
			'price'        => $price,
			'modal'        => 0,
			'stock'        => 0,
			'status'       => 'temporary',
			'is_validated' => 0,
		];

		$this->db->insert('p_item', $data);
		$item_id = $this->db->insert_id();

		if (!$item_id) {
			echo json_encode(['success' => false]);
			return;
		}

		echo json_encode([
			'success' => true,
			'item' => [
				'item_id' => $item_id,
				'price'   => $price,
				'status'  => 'temporary',
			]
		]);
	}

}

