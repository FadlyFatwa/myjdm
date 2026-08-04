<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sale_service
 *
 * Memisahkan business logic penjualan dari controller agar Sale.php
 * hanya bertanggung jawab pada HTTP request/response.
 *
 * Cara pakai di controller:
 *   $this->load->library('sale_service');
 *   $result = $this->sale_service->resolvePayment($post);
 */
class Sale_service {

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /**
     * Tentukan status pembayaran, nominal cash, dan kembalian
     * berdasarkan metode pembayaran yang dipilih kasir.
     *
     * @param  array $post  Data POST yang sudah diambil dari controller
     * @return array        ['status', 'cash', 'change']
     */
    public function resolvePayment(array $post): array
    {
        $method     = $post['payment_method'] ?? 'cash';
        $cash       = (float) ($post['cash']       ?? 0);
        $grandtotal = (float) ($post['grandtotal'] ?? 0);
        $subtotal   = (float) ($post['subtotal']   ?? 0);

        if ($method === 'cash' && $cash >= $grandtotal) {
            return [
                'status' => 'lunas',
                'cash'   => $cash,
                'change' => $cash - $grandtotal,
            ];
        }

        if (in_array($method, ['transfer', 'qris', 'debit'])) {
            return [
                'status' => 'lunas',
                'cash'   => $grandtotal,
                'change' => 0,
            ];
        }

        // Kredit / belum lunas
        return [
            'status' => 'belum lunas',
            'cash'   => $grandtotal,
            'change' => 0,
        ];
    }

    /**
     * Bangun data array untuk insert ke t_sale.
     *
     * @param  array  $post         Data POST tervalidasi
     * @param  array  $payment      Hasil resolvePayment()
     * @param  string $customerName Nama customer yang sudah diresolved
     * @param  int    $userId       ID user dari session
     * @return array
     */
    public function buildSaleData(array $post, array $payment, string $customerName, int $userId): array
    {
        return [
            'customer_id'     => !empty($post['customer_id']) ? (int)$post['customer_id'] : null,
            'customer_name'   => $customerName,
            'total_price'     => (float) $post['subtotal'],
            'discount'        => (float) ($post['discount'] ?? 0),
            'final_price'     => (float) $post['grandtotal'],
            'cash'            => $payment['cash'],
            'change'          => $payment['change'],
            'note'            => $post['note'] ?? null,
            'date'            => $post['date'],
            'user_id'         => $userId,
            'payment_method'  => $post['payment_method'],
            'payment_status'  => $payment['status'],
        ];
    }

    /**
     * Bangun array detail penjualan dari cart untuk insert ke t_sale_detail.
     *
     * @param  array $cart    Hasil get_cart()->result()
     * @param  int   $sale_id ID penjualan yang baru dibuat
     * @return array
     */
    public function buildSaleDetails(array $cart, int $sale_id): array
    {
        $details = [];
        foreach ($cart as $item) {
            $details[] = [
                'sale_id'          => $sale_id,
                'item_id'          => $item->item_id,
                'nama_barang_jual' => $item->nama_barang_jual,
                'price_sale'       => $item->cart_price,
                'qty'              => $item->qty,
                'discount_item'    => $item->discount_item ?? 0,
                'total'            => $item->total,
            ];
        }
        return $details;
    }

    /**
     * Resolve nama customer dari input:
     * - Jika customer_id valid → ambil dari master customer
     * - Jika tidak → gunakan input nama langsung (walk-in)
     *
     * @param  int|null $customerId
     * @param  string   $customerNameInput
     * @return string
     */
    public function resolveCustomerName(?int $customerId, string $customerNameInput): string
    {
        if (!empty($customerId) && $customerId > 0) {
            $cust = $this->CI->db->get_where('customer', ['customer_id' => $customerId])->row();
            return $cust ? $cust->nama_customer : 'Umum';
        }
        return !empty($customerNameInput) ? $customerNameInput : 'Umum';
    }

    /**
     * Bangun array detail jasa dari cart_jasa untuk insert ke t_sale_jasa_detail.
     *
     * @param  array $cart_jasa  Hasil get_cart_jasa()
     * @param  int   $sale_id
     * @return array
     */
    public function buildSaleJasaDetails(array $cart_jasa, int $sale_id): array
    {
        $details = [];
        foreach ($cart_jasa as $item) {
            $details[] = [
                'sale_id'   => $sale_id,
                'jasa_id'   => $item->jasa_id,
                'nama_jasa' => $item->nama_jasa,
                'tarif'     => $item->tarif,
                'qty'       => $item->qty,
                'total'     => $item->total,
            ];
        }
        return $details;
    }
}
