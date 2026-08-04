<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ar_invoice_m extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Journal_m');
        $this->load->model('Coa_m');
    }

    public function ar_no()
    {
        $sql = "SELECT
                    DATE_FORMAT(CURDATE(), '%y%m') AS today,
                    MAX(MID(ar_no, 7, 4)) AS last_no
                FROM ar_invoice
                WHERE MID(ar_no, 3, 4) = DATE_FORMAT(CURDATE(), '%y%m')";
        $row = $this->db->query($sql)->row();

        $today = $row->today;
        if ($row->last_no !== null) {
            $n  = ((int) $row->last_no) + 1;
            $no = sprintf("%'.04d", $n);
        } else {
            $no = '0001';
        }
        return 'AR' . $today . $no;
    }

    public function get($id)
    {
        $this->db->select('ar_invoice.*, customer.nama_customer, customer.phone, kb.kontra_bon_no, t_sale.invoice AS sale_invoice');
        $this->db->from('ar_invoice');
        $this->db->join('customer', 'customer.customer_id = ar_invoice.customer_id');
        $this->db->join('ar_kontra_bon AS kb', 'kb.kontra_bon_id = ar_invoice.kontra_bon_id', 'left');
        $this->db->join('t_sale', 't_sale.sale_id = ar_invoice.sale_id', 'left');
        $this->db->where('ar_invoice.ar_invoice_id', $id);
        return $this->db->get()->row();
    }

    public function get_by_sale($sale_id)
    {
        return $this->db->where('sale_id', $sale_id)->get('ar_invoice')->row();
    }

    public function get_by_customer($customer_id)
    {
        $this->db->where('customer_id', $customer_id);
        $this->db->where('status !=', 'void');
        $this->db->order_by('invoice_date', 'DESC');
        return $this->db->get('ar_invoice')->result();
    }

    /**
     * Hitung ulang due_date SEMUA invoice outstanding/partial berdasarkan
     * payment_term_days customer TERKINI. Dipakai saat termin customer diubah
     * setelah invoice terlanjur dibuat (due_date dihitung sekali saat create
     * dan disimpan statis, tidak otomatis ikut berubah).
     */
    public function recalc_all_due_dates()
    {
        $this->db->select('ar_invoice.ar_invoice_id, ar_invoice.invoice_date, ar_invoice.due_date, customer.payment_term_days');
        $this->db->from('ar_invoice');
        $this->db->join('customer', 'customer.customer_id = ar_invoice.customer_id');
        $this->db->where_in('ar_invoice.status', ['outstanding', 'partial']);
        // Nota yang sudah digabung ke kontra bon ikut jatuh tempo kontra bon, bukan termin customer langsung
        $this->db->where('ar_invoice.kontra_bon_id', null);
        $rows = $this->db->get()->result();

        $updated = 0;
        foreach ($rows as $r) {
            $new_due_date = date('Y-m-d', strtotime($r->invoice_date . ' +' . (int) $r->payment_term_days . ' days'));
            if ($new_due_date !== $r->due_date) {
                $this->db->where('ar_invoice_id', $r->ar_invoice_id)->update('ar_invoice', ['due_date' => $new_due_date]);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Buat AR invoice otomatis dari transaksi Sale kredit.
     * $sale = row t_sale (harus sudah punya customer_id valid).
     */
    public function create_from_sale($sale)
    {
        $customer = $this->db->where('customer_id', $sale->customer_id)->get('customer')->row();
        $invoice_date = $sale->date;
        $due_date = date('Y-m-d', strtotime($invoice_date . ' +' . (int) $customer->payment_term_days . ' days'));

        $this->db->trans_start();

        $ar_no = $this->ar_no();
        $amount = (int) $sale->final_price;

        $this->db->insert('ar_invoice', [
            'ar_no'              => $ar_no,
            'source'             => 'sale',
            'sale_id'            => $sale->sale_id,
            'customer_id'        => $sale->customer_id,
            'invoice_date'       => $invoice_date,
            'due_date'           => $due_date,
            'description'        => 'Piutang dari transaksi penjualan ' . $sale->invoice,
            'amount'             => $amount,
            'paid_amount'        => 0,
            'outstanding_amount' => $amount,
            'status'             => 'outstanding',
            'created_by'         => $sale->user_id,
        ]);
        $ar_invoice_id = $this->db->insert_id();

        $piutang = $this->Coa_m->get_by_subtype('piutang_usaha');
        $pendapatan = $this->Coa_m->get_by_subtype('pendapatan_penjualan');

        $journal_id = $this->Journal_m->post([
            'journal_date' => $invoice_date,
            'source_type'  => 'ar_invoice',
            'source_id'    => $ar_invoice_id,
            'description'  => 'Piutang penjualan kredit ' . $sale->invoice,
            'created_by'   => $sale->user_id,
        ], [
            ['coa_id' => $piutang->coa_id, 'debit' => $amount, 'kredit' => 0, 'notes' => $ar_no],
            ['coa_id' => $pendapatan->coa_id, 'debit' => 0, 'kredit' => $amount, 'notes' => $sale->invoice],
        ]);

        $this->db->query("UPDATE customer SET ar_balance = ar_balance + ? WHERE customer_id = ?", [$amount, $sale->customer_id]);

        $this->db->trans_complete();

        return $ar_invoice_id;
    }

    /**
     * Buat AR invoice manual (bukan dari transaksi sale).
     * $post: customer_id, invoice_date, amount, description, lawan_coa_id, due_date(optional)
     */
    public function create_manual($post, $user_id)
    {
        $customer = $this->db->where('customer_id', $post['customer_id'])->get('customer')->row();
        if (!$customer) throw new Exception('Customer tidak ditemukan');

        $invoice_date = $post['invoice_date'];
        $due_date = !empty($post['due_date'])
            ? $post['due_date']
            : date('Y-m-d', strtotime($invoice_date . ' +' . (int) $customer->payment_term_days . ' days'));
        $amount = (int) $post['amount'];

        $this->db->trans_start();

        $ar_no = $this->ar_no();

        $this->db->insert('ar_invoice', [
            'ar_no'              => $ar_no,
            'source'             => 'manual',
            'sale_id'            => null,
            'customer_id'        => $post['customer_id'],
            'invoice_date'       => $invoice_date,
            'due_date'           => $due_date,
            'description'        => $post['description'],
            'amount'             => $amount,
            'paid_amount'        => 0,
            'outstanding_amount' => $amount,
            'status'             => 'outstanding',
            'created_by'         => $user_id,
        ]);
        $ar_invoice_id = $this->db->insert_id();

        $piutang = $this->Coa_m->get_by_subtype('piutang_usaha');

        $this->Journal_m->post([
            'journal_date' => $invoice_date,
            'source_type'  => 'ar_invoice',
            'source_id'    => $ar_invoice_id,
            'description'  => 'Piutang manual: ' . $post['description'],
            'created_by'   => $user_id,
        ], [
            ['coa_id' => $piutang->coa_id, 'debit' => $amount, 'kredit' => 0, 'notes' => $ar_no],
            ['coa_id' => (int) $post['lawan_coa_id'], 'debit' => 0, 'kredit' => $amount, 'notes' => $ar_no],
        ]);

        $this->db->query("UPDATE customer SET ar_balance = ar_balance + ? WHERE customer_id = ?", [$amount, $post['customer_id']]);

        $this->db->trans_complete();

        return $ar_invoice_id;
    }

    /**
     * Dipanggil setelah pembayaran diterima — recompute paid_amount/outstanding/status,
     * sinkronkan customer.ar_balance dan t_sale.payment_status (kalau sumbernya sale).
     */
    public function update_paid_amount($ar_invoice_id, $paid_delta)
    {
        $ar = $this->db->where('ar_invoice_id', $ar_invoice_id)->get('ar_invoice')->row();
        if (!$ar) return false;

        $new_paid = $ar->paid_amount + $paid_delta;
        $new_outstanding = $ar->amount - $new_paid;
        $status = $new_outstanding <= 0 ? 'paid' : ($new_paid > 0 ? 'partial' : 'outstanding');

        $this->db->where('ar_invoice_id', $ar_invoice_id)->update('ar_invoice', [
            'paid_amount'        => $new_paid,
            'outstanding_amount' => $new_outstanding,
            'status'             => $status,
        ]);

        $this->db->query("UPDATE customer SET ar_balance = ar_balance - ? WHERE customer_id = ?", [$paid_delta, $ar->customer_id]);

        if ($ar->source === 'sale' && $ar->sale_id) {
            $this->db->where('sale_id', $ar->sale_id)->update('t_sale', [
                'payment_status' => $status === 'paid' ? 'lunas' : 'belum lunas',
            ]);
        }

        return true;
    }

    public function void($id, $reason, $user_id)
    {
        $ar = $this->db->where('ar_invoice_id', $id)->get('ar_invoice')->row();
        if (!$ar) return false;
        if ($ar->paid_amount > 0) {
            throw new Exception('Invoice sudah ada pembayaran, tidak bisa di-void.');
        }
        if ($ar->kontra_bon_id) {
            throw new Exception('Nota ini sudah tergabung dalam kontra bon — batalkan kontra bon-nya, bukan nota individual.');
        }

        $this->db->trans_start();

        $this->db->where('ar_invoice_id', $id)->update('ar_invoice', [
            'status'      => 'void',
            'void_reason' => $reason,
            'voided_by'   => $user_id,
            'voided_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->db->query("UPDATE customer SET ar_balance = ar_balance - ? WHERE customer_id = ?", [$ar->outstanding_amount, $ar->customer_id]);

        $journal = $this->Journal_m->get_by_source('ar_invoice', $id);
        if ($journal) {
            $this->Journal_m->void($journal->journal_id, 'AR invoice ' . $ar->ar_no . ' di-void: ' . $reason, $user_id);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_json()
    {
        $draw   = intval($this->input->post('draw'));
        $search = $this->input->post('search')['value'] ?? '';

        $base = function () {
            $this->db->select('ar_invoice.*, customer.nama_customer, t_sale.invoice AS sale_invoice');
            $this->db->from('ar_invoice');
            $this->db->join('customer', 'customer.customer_id = ar_invoice.customer_id');
            $this->db->join('t_sale', 't_sale.sale_id = ar_invoice.sale_id', 'left');
        };

        $base();
        $total = $this->db->count_all_results();

        $base();
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ar_invoice.ar_no', $search);
            $this->db->or_like('customer.nama_customer', $search);
            $this->db->or_like('t_sale.invoice', $search);
            $this->db->group_end();
        }
        $filtered = $this->db->count_all_results('', false);

        $start  = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->order_by('ar_invoice.ar_invoice_id', 'DESC')->get()->result();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ];
    }

    public function get_aging_data($as_of_date)
    {
        $this->db->select("ar_invoice.*, customer.nama_customer,
            DATEDIFF('$as_of_date', ar_invoice.due_date) AS days_overdue", false);
        $this->db->from('ar_invoice');
        $this->db->join('customer', 'customer.customer_id = ar_invoice.customer_id');
        $this->db->where_in('ar_invoice.status', ['outstanding', 'partial']);
        $this->db->order_by('ar_invoice.due_date', 'ASC');
        return $this->db->get()->result();
    }
}
