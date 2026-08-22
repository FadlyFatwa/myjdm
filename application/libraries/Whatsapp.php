<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp
{
    protected $CI;
    protected $token;
    protected $group_id;
    protected $stock_group_id;
    protected $temp_item_group_id;
    protected $enabled;
    protected $api_url = 'https://api.fonnte.com/send';

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('whatsapp', TRUE);

        $this->token              = $this->CI->config->item('wa_token', 'whatsapp');
        $this->group_id           = $this->CI->config->item('wa_group_id', 'whatsapp');
        $this->stock_group_id     = $this->CI->config->item('wa_stock_group_id', 'whatsapp');
        $this->temp_item_group_id = $this->CI->config->item('wa_temp_item_group_id', 'whatsapp');
        $this->enabled            = $this->CI->config->item('wa_enabled', 'whatsapp');
    }

    /**
     * Kirim pesan ke grup
     *
     * @param string $message
     * @param string|null $target Override group_id kalau perlu kirim ke nomor lain
     * @return array ['success' => bool, 'response' => array]
     */
    public function send_to_group(string $message, string $target = null): array
    {
        if (!$this->enabled) {
            return ['success' => false, 'response' => ['reason' => 'WA disabled']];
        }

        $payload = [
            'target'      => $target ?? $this->group_id,
            'message'     => $message,
            'countryCode' => '62',
        ];

        return $this->_post($payload);
    }

    /**
     * Kirim pesan ke grup notifikasi stok (grup terpisah dari grup penerimaan barang)
     *
     * @param string $message
     * @return array ['success' => bool, 'response' => array]
     */
    public function send_to_stock_group(string $message): array
    {
        if (!$this->enabled) {
            return ['success' => false, 'response' => ['reason' => 'WA disabled']];
        }

        if (empty($this->stock_group_id)) {
            return ['success' => false, 'response' => ['reason' => 'Stock WA group belum dikonfigurasi']];
        }

        return $this->send_to_group($message, $this->stock_group_id);
    }

    /**
     * Kirim pesan ke grup reminder barang sementara (perlu dicek & didaftarkan jadi item resmi)
     *
     * @param string $message
     * @return array ['success' => bool, 'response' => array]
     */
    public function send_to_temp_item_group(string $message): array
    {
        if (!$this->enabled) {
            return ['success' => false, 'response' => ['reason' => 'WA disabled']];
        }

        if (empty($this->temp_item_group_id)) {
            return ['success' => false, 'response' => ['reason' => 'Grup WA barang sementara belum dikonfigurasi']];
        }

        return $this->send_to_group($message, $this->temp_item_group_id);
    }

    /**
     * cURL POST ke Fonnte API
     */
    private function _post(array $payload): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->api_url,
            CURLOPT_POST           => TRUE,
            CURLOPT_POSTFIELDS     => http_build_query($payload),
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . $this->token,
            ],
        ]);

        $result   = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            log_message('error', '[Whatsapp] cURL Error: ' . $err);
            return ['success' => false, 'response' => ['reason' => $err]];
        }

        $decoded = json_decode($result, TRUE);
        $success = isset($decoded['status']) && $decoded['status'] === true;

        if (!$success) {
            log_message('error', '[Whatsapp] Send failed: ' . $result);
        }

        return ['success' => $success, 'response' => $decoded];
    }
}