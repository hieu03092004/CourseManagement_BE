<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZaloPayService
{
    private $appId;
    private $key1;
    private $key2;
    private $endpoint;
    private $queryEndpoint;
    private $callbackUrl;
    private $returnUrl;

    public function __construct()
    {
        $this->appId = config('zalopay.app_id');
        $this->key1 = config('zalopay.key1');
        $this->key2 = config('zalopay.key2');
        $this->endpoint = config('zalopay.endpoint');
        $this->queryEndpoint = config('zalopay.query_endpoint');
        $this->callbackUrl = config('zalopay.callback_url');
        $this->returnUrl = route('zalopay.return');
    }

    /**
     * Tạo order và lấy payment URL từ ZaloPay
     */
    public function createOrder($amount, $description = '', $embedData = [], $item = [], $bankCode = '')
    {
        // Chấp nhận cả array hoặc JSON string
        $embedDataArray = is_array($embedData) ? $embedData : (json_decode($embedData, true) ?? []);
        $itemArray = is_array($item) ? $item : (json_decode($item, true) ?? []);

        // Ensure items array không empty và có đúng format
        if (empty($itemArray)) {
            $itemArray = [[
                'itemid' => 'default',
                'itemname' => $description ?: 'Thanh toán đơn hàng',
                'itemprice' => $amount,
                'itemquantity' => 1,
            ]];
        }

        // Đơn giản hóa embed_data - chỉ giữ thông tin cần thiết
        $simpleEmbedData = [
            'user_id' => $embedDataArray['user_id'] ?? '',
            'user_email' => $embedDataArray['user_email'] ?? '',
            'course_ids' => $embedDataArray['course_ids'] ?? ($embedDataArray['courses'] ? array_column($embedDataArray['courses'], 'id') : []),
            'cart_id' => $embedDataArray['cart_id'] ?? null,
            'preferred_payment_method' => ["domestic_card",  "account"],
            'redirecturl' => $this->returnUrl,

        ];

        $order = [
            'app_id' => (int)$this->appId,
            'app_trans_id' => date('ymd') . '_' . uniqid(),
            'expire_duration_seconds' => 300,
            'app_user' => $embedDataArray['user_email'] ?? 'user_' . uniqid(),
            'app_time' => round(microtime(true) * 1000),
            'amount' => $amount,
            'item' => json_encode($itemArray, JSON_UNESCAPED_UNICODE),
            'embed_data' => json_encode($simpleEmbedData, JSON_UNESCAPED_UNICODE),
            'description' => $description ?: 'Thanh toán đơn hàng',
            'bank_code' => $bankCode ?: '',
            'callback_url' => $this->callbackUrl,
        ];

        // Tạo MAC
        $data = $order['app_id'] . '|'
            . $order['app_trans_id'] . '|'
            . $order['app_user'] . '|'
            . $order['amount'] . '|'
            . $order['app_time'] . '|'
            . $order['embed_data'] . '|'
            . $order['item'];

        $order['mac'] = hash_hmac('sha256', $data, $this->key1);

        // Log FULL payload gửi lên ZaloPay để debug
        Log::info('🚀 ZaloPay Create Order - FULL PAYLOAD', [
            'app_id' => $order['app_id'],
            'app_trans_id' => $order['app_trans_id'],
            'app_user' => $order['app_user'],
            'app_time' => $order['app_time'],
            'amount' => $order['amount'],
            'item' => $order['item'],
            'item_decoded' => json_decode($order['item'], true),
            'embed_data' => $order['embed_data'],
            'embed_data_decoded' => json_decode($order['embed_data'], true),
            'description' => $order['description'],
            'bank_code' => $order['bank_code'],
            'callback_url' => $order['callback_url'],
            'mac' => $order['mac'],
            'mac_data_string' => $data,
        ]);

        // Gọi API ZaloPay (endpoint base + /create)
        $endpoint = rtrim($this->endpoint, '/');
        if (substr($endpoint, -7) !== '/create') {
            $endpoint .= '/create';
        }

        $response = Http::withOptions(['verify' => false])->post($endpoint, $order);
        $result = $response->json();

        Log::info('ZaloPay Create Order Response', [
            'app_trans_id' => $order['app_trans_id'],
            'return_code' => $result['return_code'] ?? null,
            'return_message' => $result['return_message'] ?? null,
            'order_url' => $result['order_url'] ?? null,
        ]);

        // Thêm app_trans_id vào result để dễ tracking
        if (isset($result['return_code']) && $result['return_code'] == 1) {
            $result['app_trans_id'] = $order['app_trans_id'];
        }

        return $result;
    }
    /**
     * Query order status từ ZaloPay
     */
    public function queryOrder($appTransId)
    {
        $data = $this->appId . '|' . $appTransId . '|' . $this->key1;
        $mac = hash_hmac('sha256', $data, $this->key1);

        $params = [
            'app_id' => $this->appId,
            'app_trans_id' => $appTransId,
            'mac' => $mac
        ];

        Log::info('ZaloPay Query Order Request', $params);

        try {
            $response = Http::withOptions(['verify' => false])
            ->asForm()
            ->post($this->queryEndpoint, $params);
            $result = $response->json();

            Log::info('ZaloPay Query Order Response', $result);

            return $result;
        } catch (\Exception $e) {
            Log::error('ZaloPay Query Order Error: ' . $e->getMessage());
            return [
                'return_code' => -1,
                'return_message' => 'Failed to query order: ' . $e->getMessage()
            ];
        }
    }
}
