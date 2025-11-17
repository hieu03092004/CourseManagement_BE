<?php 

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Course;
use Illuminate\Http\Request;

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
     * Thanh toán 1 khóa học → lưu vào DB (KHÔNG TRANSACTION)
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
            'payment_status' => 'completed',
            'payment_time' => $paymentTime
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
            'courses_id' => $request->courses_id
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
     * Thanh toán khóa học từ giỏ hàng (KHÔNG TRANSACTION)
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
            'payment_status' => 'completed',
            'payment_time' => $paymentTime
        ]);

        // Tạo order_items
        foreach ($courses as $course) {

            $expiredAt = date(
                'Y-m-d H:i:s',
                strtotime($paymentTime . " +" . $course->duration . " months")
            );

            OrderItem::create([
                'orders_id' => $order->orders_id,
                'courses_id' => $course->courses_id,
                'unit_price' => $course->price,
                'expired_at' => $expiredAt
            ]);
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
}