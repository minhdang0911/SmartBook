<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
   public function store(Request $request)
{
    $user = auth()->user();

    DB::beginTransaction();
    try {
        $cartItemIds = $request->input('cart_item_ids', []);

        // Lấy cart
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy giỏ hàng.'], 400);
        }

        // Lấy danh sách cart items được chọn
        $cartItems = $cart->cartItems()->with('book')
            ->whereIn('id', $cartItemIds)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Không có sản phẩm nào được chọn.'], 400);
        }

        // Tính tổng tiền các item được chọn
        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        // Ghép địa chỉ từ các trường
        $address = 'Số ' . $request->input('sonha') . ', '
            . $request->input('street') . ', '
            . $request->input('ward_name') . ', '
            . $request->input('district_name');

        // Tạo đơn hàng
        $order = Order::create([
            'user_id'       => $user->id,
            'sonha'         => $request->input('sonha'),
            'street'        => $request->input('street'),
            'district_id'   => $request->input('district_id'),
            'ward_id'       => $request->input('ward_id'),
             'ward_name' => $request->input('ward_name'),
            'district_name' => $request->input('district_name'),
            'payment'       => $request->input('payment', 'cod'),
            'status'        => 'pending',
            'price'         => $total,
            'shipping_fee'  => 0,
            'total_price'   => $total,
            'address'       => $address,
            'created_at'    => now(),
        ]);

        // Tạo các order item và xử lý tồn kho
        foreach ($cartItems as $item) {
            $book = $item->book;

            if ($book->stock < $item->quantity) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Sách '{$book->title}' không đủ tồn kho."
                ], 400);
            }

            // Trừ tồn kho
            $book->stock -= $item->quantity;
            $book->save();

            OrderItem::create([
                'order_id' => $order->id,
                'book_id'  => $book->id,
                'quantity' => $item->quantity,
                'price'    => $item->price,
            ]);
        }

        // Xóa các item đã mua
        $cart->cartItems()->whereIn('id', $cartItemIds)->delete();

        // Cập nhật lại total_amount còn lại
        $remainingAmount = $cart->cartItems()->sum(DB::raw('quantity * price'));
        $cart->update(['total_amount' => $remainingAmount]);

        DB::commit();

        return response()->json([
            'success'  => true,
            'message'  => 'Đặt hàng thành công.',
            'order_id' => $order->id
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Đã xảy ra lỗi khi tạo đơn hàng.',
            'error'   => $e->getMessage()
        ], 500);
    }
}


}
