<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Course;
use App\Models\User;
use App\Services\ZaloPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class ZaloPayController extends Controller
{
    private ZaloPayService $zaloPayService;

    public function __construct(ZaloPayService $zaloPayService)
    {
        $this->zaloPayService = $zaloPayService;
    }

    /**
     * Tạo order trên ZaloPay - Flow đơn giản
     * POST /api/payments/zalopay/create
     * Payload: { user_id, total_amount, course_ids[], bank_code? }
     */
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string',
            'total_amount' => 'required|integer|min:1000',
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'required|string',
            'bank_code' => 'nullable|string',
            'cart_id' => 'nullable|string',
        ]);
        $bankCode = $request->input('bank_code', ''); // nếu không gửi thì để rỗng
        Log::info('Validated data', $validated);
        Log::info('BankCode passed to service', ['bank_code' => $bankCode]);


        try {
            // 1. Lấy thông tin user từ database
            $user = User::find($validated['user_id']);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // 2. Lấy thông tin courses từ database
            $courses = Course::whereIn('courses_id', $validated['course_ids'])->get();
            if ($courses->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Courses not found',
                ], 404);
            }

            // 3. Tạo items array cho ZaloPay với price từ database
            $items = $courses->map(function ($course) {
                return [
                    'itemid' => (string)$course->courses_id,
                    'itemname' => $course->title,
                    'itemprice' => (int)$course->price,
                    'itemquantity' => 1,
                ];
            })->values()->toArray();

            // 4. Tạo embed_data
            $embedData = [
                'user_id' => (string)$user->user_id,
                'user_email' => $user->email,
                'course_ids' => $validated['course_ids'],
                'cart_id' => $validated['cart_id'] ?? null,
                'total_amount' => $validated['total_amount'],
            ];

            // 5. Tạo description
            $description = count($courses) === 1
                ? "Thanh toán khóa học: {$courses->first()->title}"
                : "Thanh toán " . count($courses) . " khóa học";

            // 6. Log request
            Log::info('Creating Order (Simplified)', [
                'user_id' => $user->id,
                'total_amount' => $validated['total_amount'],
                'course_ids' => $validated['course_ids'],
            ]);

            // 7. Gọi ZaloPay Service (truyền array thay vì JSON string)
            $result = $this->zaloPayService->createOrder(
                $validated['total_amount'],
                $description,
                $embedData, // Array
                $items,
                $bankCode // Array
            );

            if ($result['return_code'] == 1) {
                $appTransId = $result['app_trans_id'];
                
                // Lưu embed_data và items vào cache để dùng trong returnUrl
                // items chứa price từ database, sẽ dùng để tạo order_item
                Cache::put("zalopay_embed_data_{$appTransId}", $embedData, 3600);
                
                Log::info('✅ Order Created on ZaloPay', [
                    'app_trans_id' => $appTransId,
                    'order_url' => $result['order_url'],
                ]);

                return response()->json([
                    'success' => true,
                    'order_url' => $result['order_url'],
                    'app_trans_id' => $appTransId,
                    'zp_trans_token' => $result['zp_trans_token'] ?? null,
                ]);
            }

            Log::error('ZaloPay Failed', $result);

            return response()->json([
                'success' => false,
                'message' => $result['return_message'] ?? 'Tạo đơn hàng thất bại',
                'return_code' => $result['return_code'],
            ], 400);
        } catch (\Exception $e) {
            Log::error('Create Order Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Query order status từ ZaloPay
     * GET /api/payments/zalopay/query?app_trans_id=xxx
     */
    public function queryOrder(Request $request): JsonResponse
    {
        $appTransId = $request->input('app_trans_id');

        if (!$appTransId) {
            return response()->json([
                'return_code' => -1,
                'return_message' => 'app_trans_id is required',
            ], 400);
        }

        try {
            // 1. Gọi ZaloPay
            $zpResult = $this->zaloPayService->queryOrder($appTransId);
            $returnCode       = (int) ($zpResult['return_code']       ?? -1);
            $subReturnCode    =        $zpResult['sub_return_code']   ?? null;
            $subReturnMessage =        $zpResult['sub_return_message'] ?? null;

            // 3. Map sang status cho UI
            $uiStatusCode    = -1;
            $uiStatusMessage = 'Không xác định trạng thái thanh toán.';

            $isTimeInvalid = ($returnCode === -54) || ($subReturnCode == -54);

            if ($returnCode === 1) {
                // Thành công
                $uiStatusCode    = 1;
                $uiStatusMessage = 'Giao dịch thành công.';
            } elseif ($isTimeInvalid) {
                // Hết hạn thanh toán trên cổng (TIME_INVALID)
                $uiStatusCode    = 4; // quy ước: 4 = expired
                $uiStatusMessage = 'Giao dịch đã hết thời gian thanh toán. Vui lòng tạo đơn mới.';
            } elseif ($returnCode === 3) {
                // Đang xử lý, chưa timeout
                $uiStatusCode    = 3;
                $uiStatusMessage = 'Giao dịch đang được xử lý, vui lòng chờ thêm.';
            } elseif ($returnCode === 2) {
                // Thất bại → chi tiết theo sub_return_code (ví dụ từ Status Codes docs)
                $uiStatusCode = 2;

                if ($subReturnCode == -101) {
                    $uiStatusMessage = 'Không tìm thấy đơn hàng trên ZaloPay (mã -101). Có thể đơn đã hết hạn hoặc sai mã đơn.';
                } elseif ($subReturnCode == -401) {
                    $uiStatusMessage = 'Tham số yêu cầu không hợp lệ (mã -401). Vui lòng kiểm tra lại thông tin đơn hàng.';
                } elseif ($subReturnCode == -402) {
                    $uiStatusMessage = 'Sai thông tin xác thực với ZaloPay (mã -402). Vui lòng kiểm tra lại app_id / key.';
                } elseif ($subReturnCode == -503) {
                    $uiStatusMessage = 'Hệ thống ZaloPay đang bảo trì (mã -503). Vui lòng thử lại sau.';
                } else {
                    $uiStatusMessage = $zpResult['return_message'] ?? 'Thanh toán thất bại.';
                }
            }

            Log::info('📊 Query Order Result', [
                'app_trans_id'   => $appTransId,
                'return_code'    => $returnCode,
                'sub_return_code' => $subReturnCode,
                'ui_status_code' => $uiStatusCode,
            ]);

            // 4. Trả về cho FE:
            //  - Giữ nguyên các field của ZaloPay (return_code, sub_return_code, ...)
            //  - Bổ sung thêm field cho UI
            $response = array_merge($zpResult, [
                'app_trans_id'             => $appTransId,
                'ui_status_code'           => $uiStatusCode,
                'ui_status_message'        => $uiStatusMessage,
            ]);

            return response()->json($response);
        } catch (\Throwable $e) {
            Log::error('Query Order Exception', [
                'app_trans_id' => $appTransId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'return_code' => -1,
                'return_message' => 'Query failed: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Return URL - Redirect từ ZaloPay về Frontend
     * GET /api/payments/zalopay/return
     */
    public function returnUrl(Request $request)
    {
        Log::info('ZaloPay Return URL', [
            'full_url' => $request->fullUrl(),
            'query'    => $request->query(),
        ]);

        $appTransId = $request->query('apptransid', '');
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

        if (empty($appTransId)) {
            return redirect($frontendUrl . '/member/order/success');
        }

        try {
            // Query order từ ZaloPay để lấy full data
            $zpResult = $this->zaloPayService->queryOrder($appTransId);
            $returnCode = (int)($zpResult['return_code'] ?? -1);

            Log::info('Return URL - Query Order Result', [
                'app_trans_id' => $appTransId,
                'return_code' => $returnCode,
            ]);

            // Chỉ xử lý order nếu thanh toán thành công
            if ($returnCode === 1) {
                // ZaloPay Query API không trả về embed_data và items, nên lấy từ cache
                $embedData = Cache::get("zalopay_embed_data_{$appTransId}", []);

                if (empty($embedData)) {
                    Log::warning('Return URL - Embed data not found in cache', [
                        'app_trans_id' => $appTransId,
                        'has_embed_data' => !empty($embedData),
                        'has_items' => !empty($items),
                    ]);
                } else {
                    $amount = (float)($zpResult['amount'] ?? 0);

                    Log::info('Return URL - Found embed data in cache', [
                        'app_trans_id' => $appTransId,
                        'user_id' => $embedData['user_id'] ?? null,
                        'course_ids' => $embedData['course_ids'] ?? [],
                    ]);

                    // Xử lý tạo order từ embed_data
                    $this->processOrderFromEmbedData($embedData, $amount);

                    // Xóa cache sau khi xử lý xong
                    Cache::forget("zalopay_embed_data_{$appTransId}");
                }
            }
        } catch (\Throwable $e) {
            Log::error('Return URL - Error processing order', [
                'app_trans_id' => $appTransId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Redirect về FE với apptransid
        $redirectUrl = $frontendUrl
            . '/member/order/success?apptransid='
            . urlencode($appTransId);

        return redirect($redirectUrl);
    }

    /**
     * Get order status từ ZaloPay gateway (alias cho queryOrder)
     * GET /api/payments/zalopay/order-status/{app_trans_id}
     */
    public function getOrderStatus($appTransId): JsonResponse
    {
        return $this->queryOrder(new Request(['app_trans_id' => $appTransId]));
    }

    /**
     * Xử lý tạo order từ embed_data
     * Tách logic để tái sử dụng trong callback và returnUrl
     */
    private function processOrderFromEmbedData(array $embedData, float $totalAmount): void
    {
        try {
            DB::beginTransaction();

            $userId = (int)($embedData['user_id'] ?? 0);
            $courseIds = $embedData['course_ids'] ?? [];
            $cartId = $embedData['cart_id'] ?? null;

            if (empty($courseIds) || $userId <= 0) {
                Log::error('Process Order - Missing required data', [
                    'user_id' => $userId,
                    'course_ids' => $courseIds,
                    'embed_data' => $embedData,
                ]);
                DB::rollBack();
                return;
            }

            // Xóa các cart_item với cart_id và courses_id tương ứng
            if ($cartId) {
                $deletedCount = CartItem::where('cart_id', $cartId)
                    ->whereIn('courses_id', $courseIds)
                    ->delete();
                Log::info('Deleted cart items', [
                    'cart_id' => $cartId,
                    'course_ids' => $courseIds,
                    'deleted_count' => $deletedCount,
                ]);
            }

            // Tạo bản ghi mới trong orders
            $order = Order::create([
                'user_id' => $userId,
                'total_price' => $totalAmount,
                'payment_status' => 'paid',
                'payment_time' => now(),
                'created_at' => now(),
            ]);

            Log::info('Order created', [
                'orders_id' => $order->orders_id,
                'user_id' => $userId,
                'total_price' => $totalAmount,
            ]);

            // Lấy thông tin courses để lấy duration và price
            $courses = Course::whereIn('courses_id', $courseIds)->get();

            // Tạo các bản ghi trong order_item
            foreach ($courses as $course) {
                // Tính unit_price với discount_percent
                $originalPrice = (float)$course->price;
                $discountPercent = (float)($course->discount_percent ?? 0);
                $unitPrice = $discountPercent > 0
                    ? $originalPrice * (1 - $discountPercent / 100)
                    : $originalPrice;
                
                $expiredAt = now();
                if ($course->duration) {
                    // duration được tính bằng ngày trong database
                    $expiredAt = $expiredAt->addDays($course->duration);
                } else {
                    $expiredAt = $expiredAt->addYear();
                }

                OrderItem::create([
                    'courses_id' => $course->courses_id,
                    'orders_id' => $order->orders_id,
                    'unit_price' => $unitPrice,
                    'expired_at' => $expiredAt,
                ]);

                Log::info('Order item created', [
                    'courses_id' => $course->courses_id,
                    'orders_id' => $order->orders_id,
                    'unit_price' => $unitPrice,
                    'expired_at' => $expiredAt->toDateTimeString(),
                    'duration' => $course->duration,
                ]);
            }

            DB::commit();

            Log::info('✅ Order processing completed successfully', [
                'orders_id' => $order->orders_id,
                'order_items_count' => count($courses),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
