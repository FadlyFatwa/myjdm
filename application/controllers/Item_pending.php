<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Item_pending extends CI_Controller {

    public function __construct() {
        parent::__construct();
        check_not_login();
        $this->load->model([
            'item_pending_m',
            'supplier_m',
            'category_m',
            'unit_m',
            'item_m'
        ]);
    }

    public function get_json()
{
    $list = $this->item_pending_m->get_datatables();
    $data = [];
    $no = $_POST['start'];

    foreach ($list as $row) {

        $no++;

        $row->stock_date  = $row->stock_date ? indo_date($row->stock_date) : null;

        // Badge Status
        switch($row->status){
        case 'pending':
            $status = '<span class="badge bg-yellow">Pending</span>';
            break;
        case 'approved':
            $status = '<span class="badge bg-blue">Approved</span>';
            break;
        case 'printed':
            $status = '<span class="badge bg-purple">Sudah Print</span>';
            break;
        case 'attached':
            $status = '<span class="badge bg-green">Sudah Ditempel</span>';
            break;
        default:
            $status = '<span class="badge bg-red">Rejected</span>';
    }
            


        
        // Action
        $action = '';

        if(in_array($this->fungsi->user_login()->level, [1,2,4]))  {

            $action .= '
                <button class="btn btn-info btn-xs btn-detail"
                        data-id="'.$row->pending_id.'">
                    <i class="fa fa-eye"></i>
                </button>';

            if($row->status == 'pending') {
                $action .= '
                    <button class="btn btn-primary btn-xs btn-edit"
                            data-id="'.$row->pending_id.'">
                        <i class="fa fa-pencil"></i>
                    </button>';
            }
        }

        // APPROVE (Admin Level 1)
        if ($row->status == 'pending' && 
            in_array($this->fungsi->user_login()->level, [1,2])) {

            $action .= '
                <button class="btn btn-success btn-xs btn-approve"
                        data-id="'.$row->pending_id.'">
                    <i class="fa fa-check"></i>
                </button>
            ';
        }

        // PRINT (Admin Level 1)
        if ($row->status == 'approved' && 
            in_array($this->fungsi->user_login()->level, [1,2])) {

            $action .= '
                <button class="btn btn-primary btn-xs btn-print"
                        data-id="'.$row->pending_id.'">
                    <i class="fa fa-print"></i>
                </button>
            ';
        }

        // ATTACH (Gudang Level 2)
        if ($row->status == 'printed' && 
            $this->fungsi->user_login()->level == 4) {

            $action .= '
                <button class="btn btn-warning btn-xs btn-attach"
                        data-id="'.$row->pending_id.'">
                    <i class="fa fa-tag"></i>
                </button>
            ';
        }





        $data[] = array(
            "no" => $no,
            "nama_item" => $row->nama_item,
            "nama_supplier" => $row->nama_supplier,
            "nama_category" => $row->nama_category,
            "nama_unit" => $row->nama_unit,
            "modal" => indo_currency($row->modal),
            "pk" => $row->pk,
            "price" => indo_currency($row->price),
            "qty" => $row->qty,
            "stock_date" => $row->stock_date,
            "user_name" => $row->user_name,
            "status" => $status,
            "action" => $action
        );
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->item_pending_m->count_all(),
        "recordsFiltered" => $this->item_pending_m->count_filtered(),
        "data" => $data,
    );

    echo json_encode($output);
}

public function get_detail($id)
{
    $this->db->select('p.*, 
                       s.nama_supplier,
                       c.nama_category,
                       u.nama_unit,
                       u_create.username AS created_name,
                       u_print.username AS printed_name,
                       u_attach.username AS attached_name');

    $this->db->from('p_item_pending p');
    $this->db->join('supplier s','p.supplier_id = s.supplier_id','left');
    $this->db->join('p_category c','p.category_id = c.category_id','left');
    $this->db->join('p_unit u','p.unit_id = u.unit_id','left');
    $this->db->join('user u_create','p.created_by = u_create.user_id','left');
    $this->db->join('user u_print','p.printed_by = u_print.user_id','left');
    $this->db->join('user u_attach','p.attached_by = u_attach.user_id','left');
    $this->db->where('p.pending_id',$id);

    $row = $this->db->get()->row();

    if($row){
        $row->modal = indo_currency($row->modal);
        $row->price = indo_currency($row->price);
        $row->stock_date = indo_date($row->stock_date);
        $row->created_at = indo_datetime($row->created_at,true);
        $row->printed_at = $row->printed_at ? indo_datetime($row->printed_at,true) : null;
        $row->attached_at = $row->attached_at ? indo_datetime($row->attached_at,true) : null;
    }

    echo json_encode($row);
}

public function get_edit($id)
{
    $this->db->from('p_item_pending');
    $this->db->where('pending_id',$id);
    $row = $this->db->get()->row();

    echo json_encode($row);
}



public function update()
{
    $post = $this->input->post(null, TRUE);

    $data = [
        'nama_item'   => $post['nama_item'],
        'supplier_id' => $post['supplier'],
        'category_id' => $post['category'],
        'unit_id'     => $post['unit'],
        'modal'       => $post['modal'],
        'pk'          => $post['pk'],
        'qty'         => $post['qty'],
        'stock_date'  => $post['stock_date']
    ];

    $this->db->where('pending_id', $post['pending_id']);
    $this->db->update('p_item_pending', $data);

    echo json_encode([
        'status' => 'success',
        'message' => 'Data berhasil diperbarui'
    ]);

}




public function edit($id)
{
    $data['row'] = $this->item_pending_m->get($id)->row();
    $this->template->load('template',
                          'product/item_pending/item_pending_form_edit',
                          $data);
}


    // LIST PENDING
   public function index()
    {
        $query_supplier = $this->supplier_m->get();
        $supplier[null] = '- Pilih -';
        foreach ($query_supplier->result() as $spy) {
            $supplier[$spy->supplier_id] = $spy->nama_supplier;
        }

        $query_category = $this->category_m->get();
        $category[null] = '- Pilih -';
        foreach ($query_category->result() as $ctg) {
            $category[$ctg->category_id] = $ctg->nama_category;
        }

        $query_unit = $this->unit_m->get();
        $unit[null] = '- Pilih -';
        foreach ($query_unit->result() as $unt) {
            $unit[$unt->unit_id] = $unt->nama_unit;
        }

        $data = [
            'supplier' => $supplier,
            'category' => $category,
            'unit'     => $unit
        ];

        $this->template->load('template',
                            'product/item_pending/item_pending_index',
                            $data);
    }


    // FORM AJUKAN BARANG
    public function add() {
        $supplier[null] = '- Pilih -';
        foreach ($this->supplier_m->get()->result() as $s) {
            $supplier[$s->supplier_id] = $s->nama_supplier;
        }

        $category[null] = '- Pilih -';
        foreach ($this->category_m->get()->result() as $c) {
            $category[$c->category_id] = $c->nama_category;
        }

        $unit[null] = '- Pilih -';
        foreach ($this->unit_m->get()->result() as $u) {
            $unit[$u->unit_id] = $u->nama_unit;
        }

        $data = [
            'supplier' => $supplier,
            'category' => $category,
            'unit' => $unit
        ];

        $this->template->load('template', 'product/item_pending/item_pending_form', $data);
    }

    // SIMPAN PENDING
    public function process() {
        $config['upload_path']   = './uploads/item_pending/';
        $config['allowed_types'] = 'jpg|jpeg|png|wepg';
        $config['max_size']      = 10240;
        $config['encrypt_name']  = TRUE;
        $config['file_ext_tolower'] = TRUE;
        $config['remove_spaces'] = TRUE;
        $config['file_name']     = 'item_' . time();

        $this->load->library('upload', $config);

        $photo = null;
        if (!empty($_FILES['photo']['name'])) {
            if ($this->upload->do_upload('photo')) {
                $photo = $this->upload->data('file_name');
            }
        }

        $post = $this->input->post(null, TRUE);
        $post['photo'] = $photo;
        $post['created_by'] = $this->session->userdata('userid');

        $this->item_pending_m->add($post);
        $this->session->set_flashdata('success','Data berhasil disimpan');
        redirect('item_pending');
    }

    public function approve($id)
    {
        check_allowed_levels([1, 2]);

        $pending = $this->item_pending_m->get($id)->row();
        if (!$pending) show_404();

        // 1️⃣ Generate barcode
        $barcode = str_pad((int)$this->item_m->get_max_barcode() + 1, 5, '0', STR_PAD_LEFT);

        // Satu transaksi untuk 4 langkah di bawah -> kalau salah satu gagal,
        // semuanya di-rollback (tidak ada p_item/t_stock yatim atau status
        // pending yang ke-approve padahal barangnya gagal dibuat).
        $this->db->trans_start();

        // 2️⃣ Insert ke p_item
        $item_data = [
            'barcode'     => $barcode,
            'nama_item'   => $pending->nama_item,
            'supplier_id' => $pending->supplier_id,
            'category_id' => $pending->category_id,
            'unit_id'     => $pending->unit_id,
            'modal'       => $pending->modal,
            'pk'          => $pending->pk,
            'price'       => $pending->price,
            'stock'       => 0,
            'status'      => 'active'
        ];

        $this->db->insert('p_item', $item_data);
        $item_id = $this->db->insert_id();

        // 3️⃣ Insert ke t_stock_in
        $stock_data = [
            'item_id'     => $item_id,
            'type'        => 'in',
            'supplier_id' => $pending->supplier_id,
            'qty'         => $pending->qty,
            'date'        => $pending->stock_date,
            'detail'      => 'Stock awal dari pending approval'
        ];

        $this->db->insert('t_stock', $stock_data);

        // 4️⃣ Update stock item
        $this->item_m->update_stock_in([
            'item_id' => $item_id,
            'qty'     => $pending->qty
        ]);

        // 5️⃣ Update status pending
        $this->item_pending_m->approve($id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Approve gagal disimpan, silakan coba lagi.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Barang berhasil di-approve'
        ]);
    }


    // REJECT
    public function reject($id) {
        check_allowed_levels([1, 2]);
        $this->item_pending_m->reject($id);
        redirect('item_pending');
    }


    public function mark_printed($id)
{
    $level = $this->fungsi->user_login()->level;

    if ($level != 2) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized'
        ]);
        return;
    }

    $this->db->where('pending_id', $id);
    $this->db->where('status', 'approved');
    $this->db->update('p_item_pending', [
        'status'     => 'printed',
        'printed_at' => date('Y-m-d H:i:s'),
        'printed_by' => $this->session->userdata('userid')
    ]);

    if ($this->db->affected_rows() > 0) {

        echo json_encode([
            'status' => 'success',
            'message' => 'Status menjadi Sudah Print'
        ]);

    } else {

        echo json_encode([
            'status' => 'error',
            'message' => 'Status tidak valid atau sudah diproses'
        ]);

    }
}


    public function mark_attached($id)
    {
        $level = $this->fungsi->user_login()->level;

        if($level != 4) show_error('Unauthorized');

        $this->db->where('pending_id', $id);
        $this->db->where('status', 'printed'); // tidak bisa loncat
        $this->db->update('p_item_pending', [
            'status'       => 'attached',
            'attached_at'  => date('Y-m-d H:i:s'),
            'attached_by'  => $this->session->userdata('userid')
        ]);
            echo json_encode([
            'status' => 'success',
            'message' => 'Status menjadi Sudah Ditempel'
        ]);
    }


}
