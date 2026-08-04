<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_tax_m extends CI_Model {

    /**
     * =========================
     * PREVIEW DATA BERSIH
     * =========================
     */
    public function get_preview($period)
    {
        $sql = "
            SELECT
                s.sale_id,
                s.invoice,
                DATE(s.date) AS sale_date,
                COUNT(d.item_id) AS total_item,
                SUM(d.qty) AS total_qty,
                SUM(d.total) AS grand_total,
                t.tax_id
            FROM t_sale s
            JOIN t_sale_detail d ON d.sale_id = s.sale_id
            LEFT JOIN t_sale_tax t ON t.sale_id = s.sale_id
            WHERE
                d.price_sale > 1000
                AND d.qty > 0
                AND d.total > 0
                AND DATE_FORMAT(s.date,'%Y-%m') = ?
            GROUP BY s.sale_id
            HAVING SUM(d.total) >= 1000
            ORDER BY s.date ASC
        ";

        return $this->db->query($sql, [$period])->result();
    }

    /**
     * =========================
     * GENERATE SELECTED
     * =========================
     */
    public function generate_selected($sale_ids = [])
{
    if (empty($sale_ids)) return false;

    $this->db->trans_start();

    foreach ($sale_ids as $sale_id) {

        // =========================
        // SKIP JIKA SUDAH ADA
        // =========================
        $exists = $this->db
            ->where('sale_id', $sale_id)
            ->get('t_sale_tax')
            ->row();

        if ($exists) continue;

        // =========================
        // AMBIL HEADER ASLI
        // =========================
        $sale = $this->db
            ->where('sale_id', $sale_id)
            ->get('t_sale')
            ->row();

        if (!$sale) continue;

        // =========================
        // 🔥 DETAIL BERSIH (FIXED)
        // =========================
        $details = $this->db
            ->where('sale_id', $sale_id)
            ->where('price_sale >', 1000)   // ✅ samakan dengan preview
            ->where('qty >', 0)
            ->where('total >', 0)           // ✅ tambahan penting
            ->get('t_sale_detail')
            ->result();

        if (empty($details)) continue;

        // =========================
        // HITUNG ULANG HEADER
        // =========================
        $total_item = count($details);
        $total_qty  = array_sum(array_column($details, 'qty'));
        $subtotal   = array_sum(array_column($details, 'total'));

        // 🔥 GUARD TAMBAHAN (ANTI RECEH)
        if ($subtotal < 1000) continue;

        // =========================
        // RUMUS PAJAK
        // =========================
        $dpp_header = round($subtotal * 11 / 12);
        $ppn_header = round($dpp_header * 0.12);

        // =========================
        // INSERT HEADER TAX
        // =========================
        $header = [
            'sale_id'      => $sale->sale_id,
            'invoice'      => $sale->invoice,
            'customer_id'  => $sale->customer_id ?? null,
            'user_id'      => $sale->user_id ?? null,
            'sale_date'    => date('Y-m-d', strtotime($sale->date)),

            'total_item'   => $total_item,
            'total_qty'    => $total_qty,
            'grand_total'  => $subtotal,

            'dpp'          => $dpp_header,
            'ppn'          => $ppn_header,
            'tax_period'   => date('Y-m', strtotime($sale->date)),
            'generated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('t_sale_tax', $header);
        $tax_id = $this->db->insert_id();

        // =========================
        // INSERT DETAIL TAX
        // =========================
        foreach ($details as $d) {

            $dpp_item = round($d->total * 11 / 12);
            $ppn_item = round($dpp_item * 0.12);

            $detail = [
                'tax_id'           => $tax_id,
                'sale_id'          => $sale_id,
                'item_id'          => $d->item_id,
                'nama_barang_jual' => $d->nama_barang_jual,
                'price_sale'       => $d->price_sale,
                'qty'              => $d->qty,
                'discount_item'    => $d->discount_item ?? 0,
                'total'            => $d->total,
                'dpp'              => $dpp_item,
                'ppn'              => $ppn_item
            ];

            $this->db->insert('t_sale_tax_detail', $detail);
        }
    }

    $this->db->trans_complete();
    return $this->db->trans_status();
}

    public function get_tax_data($period)
    {
        return $this->db
            ->where('tax_period', $period)
            ->order_by('sale_date', 'ASC')
            ->get('t_sale_tax')
            ->result();
    }

    // ===============================
    // HEADER
    // ===============================
    public function get_tax_header($tax_id)
    {
        return $this->db
            ->where('tax_id', $tax_id)
            ->get('t_sale_tax')
            ->row();
    }

    // ===============================
    // DETAIL
    // ===============================
    public function get_tax_detail($tax_id)
    {
        return $this->db
            ->where('tax_id', $tax_id)
            ->get('t_sale_tax_detail')
            ->result();
    }

    // ===============================
    // DELETE (SAFE)
    // ===============================
    public function delete_tax($tax_id)
    {
        $this->db->trans_start();

        $this->db->where('tax_id', $tax_id)
                 ->delete('t_sale_tax_detail');

        $this->db->where('tax_id', $tax_id)
                 ->delete('t_sale_tax');

        $this->db->trans_complete();
    }


    public function delete_all_by_period($period)
    {
        $tax_ids = $this->db
            ->select('tax_id')
            ->where('tax_period', $period)
            ->get('t_sale_tax')
            ->result_array();

        if (empty($tax_ids)) return 0;

        $ids = array_column($tax_ids, 'tax_id');

        $this->db->trans_start();
        $this->db->where_in('tax_id', $ids)->delete('t_sale_tax_detail');
        $this->db->where('tax_period', $period)->delete('t_sale_tax');
        $this->db->trans_complete();

        return count($ids);
    }

    public function get_tax_by_period($period)
    {
        return $this->db
            ->where('tax_period', $period)
            ->order_by('sale_date', 'ASC')
            ->get('t_sale_tax')
            ->result();
    }

    public function get_tax_summary($period)
    {
        return $this->db
            ->select('
                SUM(grand_total) as subtotal,
                SUM(dpp) as total_dpp,
                SUM(ppn) as total_ppn
            ')
            ->where('tax_period', $period)
            ->get('t_sale_tax')
            ->row();
    }
}