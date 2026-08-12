<?php
use Dompdf\Dompdf;
use Dompdf\Options;
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends CI_Controller {

	function __construct(){
		parent::__construct();
		check_not_login();
        $this->load->model(['item_m','supplier_m','stock_m','unit_m']);
	}
    public function get_json() {
        $draw = intval($this->input->post('draw'));
        $start = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        $search_value = $_POST['search']['value'];

        // 1. Base Query
        $this->db->select('t_stock.stock_id, p_item.barcode, p_item.nama_item, qty, date, detail, supplier.nama_supplier, supplier.keterangan, p_item.item_id');
        $this->db->from('t_stock');
        $this->db->join('supplier', 't_stock.supplier_id = supplier.supplier_id', 'left');
        $this->db->join('p_item', 't_stock.item_id = p_item.item_id', 'left');
        $this->db->where('type', 'in');

        // 🔥 Default: tampilkan hari ini jika tidak search
        if (empty($search_value)) {

            $start_date = date('Y-m-d 00:00:00', strtotime('-7 days'));
            $end_date   = date('Y-m-d 23:59:59');

            $this->db->where('t_stock.created_at >=', $start_date);
            $this->db->where('t_stock.created_at <=', $end_date);
        }

        // 2. Optimized Search (Hanya jalan jika ada input)
        if (!empty($search_value)) {
            $keywords = explode(" ", $search_value);
            $this->db->group_start();
            foreach ($keywords as $keyword) {
                $this->db->group_start();
                $this->db->like('p_item.barcode', $keyword);
                $this->db->or_like('p_item.nama_item', $keyword);
                $this->db->or_like('supplier.nama_supplier', $keyword);
                $this->db->or_like('supplier.keterangan', $keyword);
                $this->db->or_like('detail', $keyword);
                $this->db->group_end();
            }
            $this->db->group_end();
        }

        // 3. Sorting
        if (isset($_POST['order'])) {
            $col_index = intval($_POST['order'][0]['column']);
            $col_name = $_POST['columns'][$col_index]['data'];
            $col_dir = $_POST['order'][0]['dir'];
            
            // Mapping nama kolom database jika berbeda dengan key JSON
            $map = [
                'barcode' => 'p_item.barcode',
                'nama_item' => 'p_item.nama_item',
                'nama_supplier' => 'supplier.nama_supplier',
                'date' => 't_stock.date'
            ];
            $order_col = isset($map[$col_name]) ? $map[$col_name] : 't_stock.stock_id';
            $this->db->order_by($order_col, $col_dir);
        } else {
            $this->db->order_by('t_stock.stock_id', 'DESC');
        }

        // 4. Get Filtered Count & Data
        $temp_db = clone $this->db; 
        $totalFiltered = $this->db->count_all_results('', false);

        $this->db->limit($length, $start);
        $query = $this->db->get();
        $data = $query->result();

        // 5. Total Records (Sangat cepat karena index)
        $totalRecords = $this->db->where('type', 'in')->count_all_results('t_stock');

        // 6. Formating Output
        $result = array();
        foreach ($data as $index => $row) {
            $result[] = [
                'checkbox' => '<input type="checkbox" class="check-item" value="'.$row->stock_id.'">',
                'no' => $start + $index + 1,
                'barcode' => $row->barcode . '<br><a href="'.site_url('barcode/barcode_qrcode_stock/'.$row->item_id.'/'.$row->stock_id).'" class="btn btn-default btn-xs">Print <i class="fa fa-barcode"></i></a>',
                'nama_item' => $row->nama_item,
                'nama_supplier' => $row->nama_supplier . " (".$row->keterangan.")",
                'qty' => $row->qty,
                'detail' => $row->detail,
                'date' => indo_date($row->date),
                'action' => '
                    <a href="'.site_url('stock/in/edit/'.$row->stock_id.'/'.$row->item_id).'" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i></a>
                    <a href="'.site_url('stock/in/del/'.$row->stock_id.'/'.$row->item_id).'" id="btn-hapus" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a>
                '
            ];
        }

        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFiltered,
            "data" => $result
        ]);
        exit();
    }
    public function stock_in_data(){
        $this->template->load('template', 'transaction/stock_in/stock_in_data');
    }
    
    public function stock_in_add(){
        $item = $this->item_m->get_status()->result();
        $supplier = $this->supplier_m->get()->result();
        $data = ['item' => $item, 'supplier' => $supplier];
        $this->template->load('template', 'transaction/stock_in/stock_in_form', $data);
    }
    
    public function stock_in_add_after($item_id = null) {
        $item = $this->item_m->get($item_id)->row(); // Mendapatkan data barang berdasarkan item_id
        $supplier = $this->supplier_m->get()->result();
        $unit = $this->unit_m->get()->result();
        $data = [
            'item' => $item,
            'supplier' => $supplier,
            'unit' => $unit,
            'row' => $item,
            
        ];
        $this->template->load('template', 'transaction/stock_in/stock_in_form_after', $data);
    }

    public function stock_in_add_multiple()
    {
        // item_ids diambil dari query string (utama) dengan fallback ke session
        // flashdata (kompatibilitas lama) kalau query string tidak ada.
        $item_ids = $this->input->get('ids');
        if (!$item_ids) {
            $item_ids = $this->session->flashdata('item_ids');
        }

        // Pisahkan item_ids menjadi array
        $item_ids_array = [];
        if ($item_ids) {
            $item_ids_array = array_filter(explode(',', $item_ids));
        }

        // Ambil data supplier dan unit untuk dropdown
        $supplier = $this->supplier_m->get()->result();
        $unit = $this->unit_m->get()->result();

        // Ambil data barang berdasarkan item_ids
        $items = [];
        if (!empty($item_ids_array)) {
            $items = $this->item_m->get_by_ids($item_ids_array)->result();
        }

        // Data yang dikirim ke view
        $data = [
            'supplier' => $supplier,
            'unit' => $unit,
            'items' => $items, // Kirim data barang ke view
        ];

        // Load template dengan view untuk update stock multiple
        $this->template->load('template', 'transaction/stock_in/stock_in_form_multiple', $data);
    }

    
    public function stock_in_edit($id){
        $item = $this->item_m->get()->result();
        $supplier = $this->supplier_m->get()->result();
        $query = $this->stock_m->get($id);
        if($query->num_rows() > 0) {
            $stock    = $query->row();
            $item_row = $this->item_m->get($stock->item_id)->row();
            $data = [
                'item'     => $item,
                'supplier' => $supplier,
                'row'      => $stock,
                'item_row' => $item_row,
            ];
            $this->template->load('template', 'transaction/stock_in/stock_in_edit', $data);
        } else {
            $this->session->set_flashdata('error', 'Data tidak ditemukan');
            redirect('stock/in');
        }
    }
    


    public function stock_in_del(){
        $stock_id = $this->uri->segment(4);
        $item_id = $this->uri->segment(5);
        $qty = $this->stock_m->get($stock_id)->row()->qty;
        $data = ['qty' => $qty, 'item_id' => $item_id];
        $this->item_m->update_stock_out($data);
        $this->stock_m->del($stock_id);

        if($this->db->affected_rows() > 0){
            $this->session->set_flashdata('success','Update Stock berhasil dihapus');
        }
        redirect('stock/in');


    }
    
    public function process()
    {
    if(isset($_POST['in_add'])) {
        $post        = $this->input->post(null, TRUE);
        $user_id     = (int) $this->session->userdata('userid');
        $item_id     = (int) $post['item_id'];
        $supplier_id = !empty($post['supplier_id']) ? (int) $post['supplier_id'] : null;
        $new_price   = (int) str_replace('.', '', $post['modal'] ?? 0);
        $new_pk      = trim($post['pk'] ?? '');

        $this->stock_m->add_stock_in(array_merge($post, ['supplier_id' => $supplier_id]));
        $this->item_m->update_stock_in($post);

        $item = $this->item_m->get($item_id)->row();
        if ($item && $new_price > 0 && $new_price !== (int) $item->modal) {
            $this->load->model('po_m');
            $pk_final = $new_pk ?: $item->pk;
            $this->db->where('item_id', $item_id)->update('p_item', [
                'modal' => $new_price, 'pk' => $pk_final,
                'supplier_id' => $supplier_id ?: $item->supplier_id,
            ]);
            if ($supplier_id) {
                $sb = $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)->get('supplier_barang')->row();
                if ($sb) {
                    $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)->update('supplier_barang', ['harga_beli' => $new_price]);
                } else {
                    $this->db->insert('supplier_barang', ['item_id' => $item_id, 'supplier_id' => $supplier_id, 'harga_beli' => $new_price]);
                }
            }
            $this->po_m->log_price_change([
                'item_id' => $item_id, 'supplier_id' => $supplier_id,
                'harga_lama' => (int) $item->modal, 'harga_baru' => $new_price,
                'sumber' => 'stock_in', 'sumber_id' => null,
                'catatan' => 'Stok masuk (after)', 'changed_by' => $user_id,
            ]);
        }

        if($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Data Update Stock berhasil disimpan');
        }
        redirect('stock/in');
    }elseif (isset($_POST['in_add_multiple'])) {
        // Ambil data POST dari form
        $post = $this->input->post();
    
        $user_id = (int) $this->session->userdata('userid');
        $this->load->model('po_m');

        foreach ($post['item_id'] as $key => $item_id) {
            $supplier_id = !empty($post['supplier_id'][$key]) ? (int) $post['supplier_id'][$key] : null;
            $new_price   = (int) str_replace('.', '', $post['modal'][$key]);
            $new_pk      = $post['pk'][$key];

            $data = [
                'item_id'     => $item_id,
                'date'        => $post['date'][$key],
                'supplier_id' => $supplier_id,
                'detail'      => $post['detail'][$key],
                'qty'         => $post['qty'][$key],
            ];

            $this->stock_m->add_stock_in($data);
            $this->item_m->update_stock_in($data);

            // Cek perubahan harga vs harga item saat ini
            $item = $this->item_m->get($item_id)->row();
            if (!$item) continue;

            $harga_lama = (int) $item->modal;
            $harga_berubah = ($new_price > 0 && $new_price !== $harga_lama);

            if ($harga_berubah) {
                // Update p_item.modal, pk, supplier_id
                $this->db->where('item_id', $item_id)->update('p_item', [
                    'modal'       => $new_price,
                    'pk'          => $new_pk,
                    'supplier_id' => $supplier_id ?: $item->supplier_id,
                ]);

                // Update supplier_barang untuk supplier ini
                if ($supplier_id) {
                    $sb = $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)
                                   ->get('supplier_barang')->row();
                    if ($sb) {
                        $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)
                                 ->update('supplier_barang', ['harga_beli' => $new_price]);
                    } else {
                        $this->db->insert('supplier_barang', [
                            'item_id'     => $item_id,
                            'supplier_id' => $supplier_id,
                            'harga_beli'  => $new_price,
                        ]);
                    }
                }

                // Log perubahan harga
                $this->po_m->log_price_change([
                    'item_id'     => $item_id,
                    'supplier_id' => $supplier_id,
                    'harga_lama'  => $harga_lama,
                    'harga_baru'  => $new_price,
                    'sumber'      => 'stock_in',
                    'sumber_id'   => null,
                    'catatan'     => 'Update stok masuk',
                    'changed_by'  => $user_id,
                ]);
            }
        }
    
        // Set pesan sukses
        $this->session->set_flashdata('success', 'Stok berhasil diperbarui untuk semua barang!');
        
        // Redirect ke halaman daftar stok atau halaman lain
        redirect('stock/in');
    } else if(isset($_POST['in_edit'])) {
        $post        = $this->input->post(null, TRUE);
        $user_id     = (int) $this->session->userdata('userid');
        $item_id     = (int) $post['item_id'];
        $supplier_id = !empty($post['supplier_id']) ? (int) $post['supplier_id'] : null;
        $new_price   = (int) str_replace('.', '', $post['modal'] ?? 0);
        $new_pk      = trim($post['pk'] ?? '');

        $old_stock = $this->stock_m->edit_stock_in($post);
        $this->item_m->update_stock_out(['item_id' => $item_id, 'qty' => $old_stock]);
        $this->item_m->update_stock_in($post);

        // Update supplier_id di t_stock jika berubah
        if ($supplier_id) {
            $this->db->where('stock_id', $post['stock_id'])->update('t_stock', ['supplier_id' => $supplier_id]);
        }

        $item = $this->item_m->get($item_id)->row();
        if ($item && $new_price > 0 && $new_price !== (int) $item->modal) {
            $this->load->model('po_m');
            $pk_final = $new_pk ?: $item->pk;
            $this->db->where('item_id', $item_id)->update('p_item', [
                'modal' => $new_price, 'pk' => $pk_final,
                'supplier_id' => $supplier_id ?: $item->supplier_id,
            ]);
            if ($supplier_id) {
                $sb = $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)->get('supplier_barang')->row();
                if ($sb) {
                    $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)->update('supplier_barang', ['harga_beli' => $new_price]);
                } else {
                    $this->db->insert('supplier_barang', ['item_id' => $item_id, 'supplier_id' => $supplier_id, 'harga_beli' => $new_price]);
                }
            }
            $this->po_m->log_price_change([
                'item_id' => $item_id, 'supplier_id' => $supplier_id,
                'harga_lama' => (int) $item->modal, 'harga_baru' => $new_price,
                'sumber' => 'stock_in', 'sumber_id' => null,
                'catatan' => 'Edit stok masuk', 'changed_by' => $user_id,
            ]);
        }

        if($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Data Update Stock berhasil diperbarui');
        }
        redirect('stock/in');
    } else if(isset($_POST['out_add'])) {
        $post = $this->input->post(null, TRUE);
        $row_item = $this->item_m->get($this->input->post('item_id'))->row();
        if($row_item->stock < $this->input->post('qty')) {
            $this->session->set_flashdata('error', 'Qty melebihi stock barang');
            redirect('stock/out/add');
        } else {
            $this->stock_m->add_stock_out($post);
            $this->item_m->update_stock_out($post);
            if($this->db->affected_rows() > 0) {
                $this->session->set_flashdata('success', 'Data Barang Keluar berhasil disimpan');
            }
            redirect('stock/out');
        }
    }
}




    public function stock_out_data()
    {
        $this->template->load('template', 'transaction/stock_out/stock_out_data');
    }

    public function get_json_out()
    {
        $draw   = intval($this->input->post('draw'));
        $start  = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        $search = $_POST['search']['value'] ?? '';

        $this->db->select('t_stock.stock_id, p_item.barcode, p_item.nama_item, t_stock.qty, t_stock.date, t_stock.detail, p_item.item_id');
        $this->db->from('t_stock');
        $this->db->join('p_item', 't_stock.item_id = p_item.item_id', 'left');
        $this->db->where('t_stock.type', 'out');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('p_item.barcode',   $search);
            $this->db->or_like('p_item.nama_item', $search);
            $this->db->or_like('t_stock.detail',   $search);
            $this->db->group_end();
        }

        $this->db->order_by('t_stock.stock_id', 'DESC');
        $totalFiltered = $this->db->count_all_results('', false);

        $this->db->limit($length, $start);
        $rows = $this->db->get()->result();

        $totalRecords = $this->db->where('type', 'out')->count_all_results('t_stock');

        $data = [];
        foreach ($rows as $i => $row) {
            $data[] = [
                'no'        => $start + $i + 1,
                'barcode'   => $row->barcode,
                'nama_item' => $row->nama_item,
                'qty'       => $row->qty,
                'detail'    => $row->detail ?? '-',
                'date'      => indo_date($row->date),
                'action'    => '<a href="' . site_url('stock/out/del/' . $row->stock_id . '/' . $row->item_id) . '"
                                   id="btn-hapus" class="btn btn-danger btn-xs">
                                   <i class="fa fa-trash"></i> Hapus
                               </a>',
            ];
        }

        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data'            => $data,
        ]);
    }

    public function stock_out_add()
    {
        $item = $this->item_m->get_status()->result();
        $data = ['item' => $item];
        $this->template->load('template', 'transaction/stock_out/stock_out_form', $data);
    }
    public function stock_out_del()
    {
        $stock_id = $this->uri->segment(4);
        $item_id = $this->uri->segment(5);
        $qty = $this->stock_m->get($stock_id)->row()->qty;
        $data = ['qty' => $qty, 'item_id' => $item_id];
        $this->item_m->update_stock_in($data);
        $this->stock_m->del($stock_id);
        if($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Data Barang Keluar berhasil dihapus');
        }
        redirect('stock/out');
    }


   

}