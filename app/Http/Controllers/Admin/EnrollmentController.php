<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Course;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class EnrollmentController extends Controller
{
    /**
     * Người dùng nhấn Đăng ký ngay tại trang chi tiết → hiển thị đơn hàng
     */
    public function previewSingleCourse($courseId)
    {
        $course = Course::find($courseId);

        if (!$course) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy khoá học'], 404);
        }

        return response()->json([
            'status' => true,
            'order_preview' => [
                'course' => $course,
                'total' => floatval($course->price)
            ]
        ]);
    }


    /**
     * Thanh toán 1 khóa học → lưu vào DB
     */
    public function paySingleCourse(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'courses_id' => 'required'
        ]);

        $course = Course::find($request->courses_id);

        if (!$course) {
            return response()->json(['status' => false, 'message' => 'Khoá học không tồn tại'], 404);
        }

        // Payment time
        $paymentTime = now();

        // Tạo order
        $order = Order::create([
            'user_id' => $request->user_id,
            'total_price' => $course->price,
            'payment_status' => 'paid',
            'payment_time' => $paymentTime,
            'created_at' => now()
        ]);

        // expired = payment_time + duration (months)
        $expiredAt = date(
            'Y-m-d H:i:s',
            strtotime($paymentTime . " +" . $course->duration . " months")
        );

        // Tạo order_item
        OrderItem::create([
            'orders_id' => $order->orders_id,
            'courses_id' => $course->courses_id,
            'unit_price' => $course->price,
            'expired_at' => $expiredAt
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Thanh toán thành công',
            'order' => $order
        ]);
    }



    /**
     * Thêm khóa học vào giỏ hàng
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'courses_id' => 'required'
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $request->user_id]);

        $exists = CartItem::where('cart_id', $cart->cart_id)
            ->where('courses_id', $request->courses_id)
            ->exists();

        if ($exists) {
            return response()->json(['status' => false, 'message' => 'Khoá học đã có trong giỏ hàng']);
        }

        CartItem::create([
            'cart_id' => $cart->cart_id,
            'courses_id' => $request->courses_id,
            'created_at' => now()
        ]);

        return response()->json(['status' => true, 'message' => 'Đã thêm vào giỏ hàng']);
    }



    /**
     * Trang giỏ hàng → hiển thị các khóa học
     */
    public function getCart($userId)
    {
        $cart = Cart::where('user_id', $userId)
            ->with('items.course')
            ->first();

        if (!$cart) {
            return response()->json(['status' => true, 'items' => []]);
        }

        return response()->json([
            'status' => true,
            'cart' => $cart
        ]);
    }



    /**
     * Preview đơn hàng từ giỏ hàng (nhiều khóa)
     */
    public function previewCartOrder(Request $request)
    {
        $request->validate([
            'courses_ids' => 'required|array'
        ]);

        $courses = Course::whereIn('courses_id', $request->courses_ids)->get();
        $total = $courses->sum('price');

        return response()->json([
            'status' => true,
            'order_preview' => [
                'courses' => $courses,
                'total' => $total
            ]
        ]);
    }



    /**
     * Thanh toán khóa học từ giỏ hàng
     */
    public function payFromCart(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'courses_ids' => 'required|array'
        ]);

        $courses = Course::whereIn('courses_id', $request->courses_ids)->get();
        $total = $courses->sum('price');

        // Payment time
        $paymentTime = now();

        // Tạo order
        $order = Order::create([
            'user_id' => $request->user_id,
            'total_price' => $total,
            'payment_status' => 'paid',
            'payment_time' => $paymentTime,
            'created_at' => now()
        ]);

        // Tạo order_items
        // foreach ($courses as $course) {

        //     $expiredAt = date(
        //         'Y-m-d H:i:s',
        //         strtotime($paymentTime . " +" . $course->duration . " months")
        //     );

        //     OrderItem::create([
        //         'orders_id' => $order->orders_id,
        //         'courses_id' => $course->courses_id,
        //         'unit_price' => $course->price,
        //         'expired_at' => $expiredAt
        //     ]);
        // }

        foreach ($courses as $course) {
            $expiredAt = date('Y-m-d H:i:s', strtotime($paymentTime . " +" . $course->duration . " months"));

            // Log trước khi tạo OrderItem
            Log::info('Attempting to create OrderItem', [
                'orders_id' => $order->orders_id,
                'courses_id' => $course->courses_id,
                'unit_price' => $course->price,
                'expired_at' => $expiredAt
            ]);

            try {
                OrderItem::create([
                    'orders_id' => $order->orders_id,
                    'courses_id' => $course->courses_id,
                    'unit_price' => $course->price,
                    'expired_at' => $expiredAt
                ]);

                Log::info("OrderItem created successfully for course_id: {$course->courses_id}");
            } catch (\Exception $e) {
                Log::error("Failed to create OrderItem", [
                    'error' => $e->getMessage(),
                    'orders_id' => $order->orders_id,
                    'courses_id' => $course->courses_id
                ]);
            }
        }

        // Xóa khóa học khỏi cart
        $cart = Cart::where('user_id', $request->user_id)->first();
        if ($cart) {
            CartItem::where('cart_id', $cart->cart_id)
                ->whereIn('courses_id', $request->courses_ids)
                ->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Thanh toán thành công',
            'order' => $order
        ]);
    }

    /**
     * Trang đơn hàng (thông tin các đơn hàng theo User)
     */
    public function getOrders($userId)
    {
        // Lấy danh sách đơn hàng theo user, kèm order_items + course
        $orders = Order::where('user_id', $userId)
            ->with('items.course')     // Quan hệ: order → order_item → course
            ->orderBy('orders_id', 'desc')  // Mới nhất trước
            ->get();

        // Nếu không có đơn hàng
        if ($orders->isEmpty()) {
            return response()->json([
                'status' => true,
                'orders' => []
            ]);
        }

        return response()->json([
            'status' => true,
            'orders' => $orders
        ]);
    }
}
