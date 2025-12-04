<?php

namespace App\Http\Controllers\Client;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Course;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartController extends BaseAPIController
{
    public function getCart($id)
    {
        try {
            // Kiểm tra cart có tồn tại không
            $cart = Cart::findOrFail($id);

            // Lấy các cart_item với cart_id tương ứng
            $cartItems = CartItem::where('cart_id', $id)->get();

            // Lấy danh sách courses_id
            $courseIds = $cartItems->pluck('courses_id')->toArray();

            // Nếu không có items trong cart
            if (empty($courseIds)) {
                return $this->ok([
                    'cartId' => $cart->cart_id,
                    'items' => [],
                ]);
            }

            // Query thông tin courses
            $courses = Course::whereIn('courses_id', $courseIds)->get();

            // Map courses thành format response
            $items = $courses->map(function ($course) {
                $originalPrice = (float)$course->price;
                $discountPercent = (float)($course->discount_percent ?? 0);
                $price = $discountPercent > 0
                    ? $originalPrice * (1 - $discountPercent / 100)
                    : $originalPrice;

                return [
                    'title' => $course->title,
                    'description' => $course->description ?? '',
                    'image' => $course->image ?? '',
                    'price' => $originalPrice,
                    'originalPrice' => $price,
                ];
            })->toArray();

            return $this->ok([
                'items' => $items,
            ]);

        } catch (ModelNotFoundException $e) {
            return $this->fail(
                'Cart not found',
                404,
                'NOT_FOUND'
            );
        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while fetching cart',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }
}

