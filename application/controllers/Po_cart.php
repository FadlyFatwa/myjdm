<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Po_cart extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        check_not_login();
        $this->load->model(['po_cart_m', 'po_m']);
    }

    public function index()
    {
        $grouped_cart = $this->po_cart_m->get_cart_grouped_by_supplier();
        $suppliers    = $this->db->order_by('nama_supplier')->get('supplier')->result();

        $item_ids = [];
        foreach ($grouped_cart as $g) {
            foreach ($g['items'] as $item) {
                if ($item->item_id) $item_ids[] = $item->item_id;
            }
        }
        $multi_supplier_map = $this->po_cart_m->get_multi_supplier_map($item_ids);

        $this->template->load('template', 'purchasing/po_cart', [
            'grouped_cart'        => $grouped_cart,
            'suppliers'           => $suppliers,
            'multi_supplier_map'  => $multi_supplier_map,
        ]);
    }

    public function add()
    {
        $user_id        = (int) $this->session->userdata('userid');
        $supplier_id    = (int) $this->input->post('supplier_id');
        $item_id        = $this->input->post('item_id') ? (int) $this->input->post('item_id') : null;
        $item_name_temp = $this->input->post('item_name_temp') ?: null;
        $qty            = max(1, (int) $this->input->post('qty'));
        $ref_price      = (int) $this->input->post('ref_price');
        $notes          = $this->input->post('notes') ?: null;

        if (!$supplier_id || (!$item_id && !$item_name_temp)) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
            exit();
        }

        $this->po_cart_m->add([
            'item_id'        => $item_id,
            'item_name_temp' => $item_name_temp,
            'supplier_id'    => $supplier_id,
            'qty'            => $qty,
            'ref_price'      => $ref_price,
            'notes'          => $notes,
            'added_by'       => $user_id,
        ]);

        echo json_encode(['status' => 'success']);
        exit();
    }

    public function update()
    {
        $id        = (int) $this->input->post('id');
        $qty       = max(1, (int) $this->input->post('qty'));
        $ref_price = (int) $this->input->post('ref_price');
        $notes     = $this->input->post('notes');

        $this->po_cart_m->update($id, [
            'qty'       => $qty,
            'ref_price' => $ref_price,
            'notes'     => $notes ?: null,
        ]);

        echo json_encode(['status' => 'success']);
        exit();
    }

    public function remove($id)
    {
        $this->po_cart_m->remove((int) $id);
        redirect('po-cart');
    }

    public function clear()
    {
        $supplier_id = $this->input->post('supplier_id');
        if ($supplier_id) {
            $this->po_cart_m->clear((int) $supplier_id);
        } else {
            $this->po_cart_m->clear();
        }
        redirect('po-cart');
    }

    public function create_po()
    {
        if ((int) $this->fungsi->user_login()->level !== 1) {
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
            exit();
        }

        $supplier_id   = (int) $this->input->post('supplier_id');
        $po_date       = $this->input->post('po_date');
        $expected_date = $this->input->post('expected_date') ?: null;
        $notes         = $this->input->post('notes') ?: null;
        $selected_ids  = (array) $this->input->post('selected_ids');
        $user_id       = (int) $this->session->userdata('userid');

        if (!$supplier_id || !$po_date || empty($selected_ids)) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
            exit();
        }

        $po_number = $this->po_m->po_number();

        // Transaksi: kalau po_header gagal dibuat, jangan sampai po_detail
        // ke-insert dengan po_id ngawur dan cart terhapus padahal PO-nya tidak ada.
        $this->db->trans_start();

        $this->db->insert('po_header', [
            'po_number'     => $po_number,
            'supplier_id'   => $supplier_id,
            'po_date'       => $po_date,
            'expected_date' => $expected_date,
            'notes'         => $notes,
            'status'        => 'draft',
            'created_by'    => $user_id,
        ]);
        $po_id = $this->db->insert_id();

        if (!$po_id) {
            $this->db->trans_complete();
            echo json_encode(['status' => 'error', 'message' => 'PO gagal dibuat.']);
            exit();
        }

        foreach ($selected_ids as $cart_id) {
            $item = $this->po_cart_m->get((int) $cart_id);
            if (!$item || (int) $item->supplier_id !== $supplier_id) continue;

            $this->db->insert('po_detail', [
                'po_id'          => $po_id,
                'item_id'        => $item->item_id,
                'item_name_temp' => $item->item_name_temp,
                'qty_ordered'    => (int) $item->qty,
                'qty_received'   => 0,
                'unit_price'     => (int) $item->ref_price,
                'notes'          => $item->notes,
            ]);

            $this->po_cart_m->remove($item->id);

            if ($item->item_id) {
                $this->po_cart_m->remove_siblings($item->item_id, $supplier_id);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['status' => 'error', 'message' => 'PO gagal disimpan, silakan coba lagi.']);
            exit();
        }

        echo json_encode(['status' => 'success', 'po_id' => $po_id]);
        exit();
    }
}
