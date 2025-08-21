<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{ 
    /**
     * Lấy giỏ hàng của user hiện tại
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            $cart = Cart::with(['cartItems.book.author', 'cartItems.book.category'])
                ->where('user_id', $user->id)
                ->first();

            if (!$cart) {
                return response()->json([
                    'success' => true,
                    'message' => 'Giỏ hàng trống',
                    'data' => [
                        'cart' => null,
                        'items' => [],
                        'total_amount' => 0,
                        'total_items' => 0
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy giỏ hàng thành công',
                'data' => [
                    'cart' => $cart,
                    'items' => $cart->cartItems,
                    'total_amount' => $cart->total_amount,
                    'total_items' => $cart->cartItems()->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Thêm sách vào giỏ hàng
     */
    public function addToCart(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'book_id' => 'required|exists:books,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $user = Auth::user();
            $book = Book::find($request->book_id);

            // Kiểm tra stock nếu là sách vật lý
            if ($book->is_physical && $book->stock < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không đủ hàng trong kho. Còn lại: ' . $book->stock
                ], 400);
            }

            // Tìm hoặc tạo giỏ hàng
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            // Kiểm tra sách đã có trong giỏ chưa
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('book_id', $book->id)
                ->first();

            if ($cartItem) {
                // Nếu đã có, cập nhật số lượng
                $newQuantity = $cartItem->quantity + $request->quantity;

                // Kiểm tra stock lại
                if ($book->is_physical && $book->stock < $newQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không đủ hàng trong kho. Còn lại: ' . $book->stock
                    ], 400);
                }

                $cartItem->quantity = $newQuantity;
                $cartItem->save();

                $message = 'Cập nhật số lượng sách trong giỏ hàng thành công';
            } else {
                // Nếu chưa có, tạo mới
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'book_id' => $book->id,
                    'quantity' => $request->quantity,
                    'price' => $book->price
                ]);

                $message = 'Thêm sách vào giỏ hàng thành công';
            }

            // Cập nhật tổng tiền giỏ hàng
            $cart->calculateTotal();

            // Load lại cart với relationships
            $cart->load(['cartItems.book.author', 'cartItems.book.category']);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'cart' => $cart,
                    'cart_item' => $cartItem->load('book'),
                    'total_amount' => $cart->total_amount,
                    'total_items' => $cart->cartItems()->count()
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật số lượng sách trong giỏ hàng
     */
    public function updateCartItem(Request $request, $cartItemId): JsonResponse
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            $user = Auth::user();
            $cartItem = CartItem::whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->with('book')
                ->find($cartItemId);

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm trong giỏ hàng'
                ], 404);
            }

            // Kiểm tra stock nếu là sách vật lý
            if ($cartItem->book->is_physical && $cartItem->book->stock < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không đủ hàng trong kho. Còn lại: ' . $cartItem->book->stock
                ], 400);
            }

            $cartItem->quantity = $request->quantity;
            $cartItem->save();

            // Cập nhật tổng tiền giỏ hàng
            $cartItem->cart->calculateTotal();

            // Load lại cart với relationships
            $cart = $cartItem->cart->load(['cartItems.book.author', 'cartItems.book.category']);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật giỏ hàng thành công',
                'data' => [
                    'cart' => $cart,
                    'cart_item' => $cartItem->fresh()->load('book'),
                    'total_amount' => $cart->total_amount,
                    'total_items' => $cart->cartItems()->count()
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa sách khỏi giỏ hàng
     */
    public function removeFromCart(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'cart_item_ids' => 'required|array|min:1',
                'cart_item_ids.*' => 'integer|exists:cart_items,id'
            ]);

            $user = Auth::user();
            $cartItemIds = $request->cart_item_ids;

            $cartItems = CartItem::whereHas('cart', function($query) use ($user) {
                                    $query->where('user_id', $user->id);
                                })
                                ->whereIn('id', $cartItemIds)
                                ->with('cart')
                                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm nào trong giỏ hàng'
                ], 404);
            }

            $foundIds = $cartItems->pluck('id')->toArray();
            $notFoundIds = array_diff($cartItemIds, $foundIds);
            
            if (!empty($notFoundIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Một số sản phẩm không tìm thấy trong giỏ hàng: ' . implode(', ', $notFoundIds)
                ], 404);
            }

            $cart = $cartItems->first()->cart;

            $deletedCount = $cartItems->count();
            CartItem::whereIn('id', $cartItemIds)->delete();

            $cart->calculateTotal();

            if ($cart->cartItems()->count() == 0) {
                $cart->delete();
                
                return response()->json([
                    'success' => true,
                    'message' => "Đã xóa {$deletedCount} sản phẩm khỏi giỏ hàng",
                    'data' => [
                        'cart' => null,
                        'total_amount' => 0,
                        'total_items' => 0,
                        'deleted_count' => $deletedCount
                    ]
                ]);
            }

            $cart->load(['cartItems.book.author', 'cartItems.book.category']);

            return response()->json([
                'success' => true,
                'message' => "Đã xóa {$deletedCount} sản phẩm khỏi giỏ hàng",
                'data' => [
                    'cart' => $cart,
                    'total_amount' => $cart->total_amount,
                    'total_items' => $cart->cartItems()->count(),
                    'deleted_count' => $deletedCount
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeFromCartSingle($cartItemId): JsonResponse
    {
        try {
            $user = Auth::user();
            $cartItem = CartItem::whereHas('cart', function($query) use ($user) {
                                    $query->where('user_id', $user->id);
                                })
                                ->find($cartItemId);

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm trong giỏ hàng'
                ], 404);
            }

            $cart = $cartItem->cart;
            $cartItem->delete();

            $cart->calculateTotal();

            if ($cart->cartItems()->count() == 0) {
                $cart->delete();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa sản phẩm khỏi giỏ hàng thành công',
                    'data' => [
                        'cart' => null,
                        'total_amount' => 0,
                        'total_items' => 0
                    ]
                ]);
            }

            $cart->load(['cartItems.book.author', 'cartItems.book.category']);

            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm khỏi giỏ hàng thành công',
                'data' => [
                    'cart' => $cart,
                    'total_amount' => $cart->total_amount,
                    'total_items' => $cart->cartItems()->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clearCart(): JsonResponse
    {
        try {
            $user = Auth::user();
            $cart = Cart::where('user_id', $user->id)->first();

            if (!$cart) {
                return response()->json([
                    'success' => true,
                    'message' => 'Giỏ hàng đã trống'
                ]);
            }

            $cart->cartItems()->delete();
            $cart->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa toàn bộ giỏ hàng',
                'data' => [
                    'cart' => null,
                    'total_amount' => 0,
                    'total_items' => 0
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đếm số lượng items trong giỏ hàng
     */
    public function getCartCount(): JsonResponse
    {
        try {
            $user = Auth::user();
            $cart = Cart::where('user_id', $user->id)->first();

            $count = $cart ? $cart->cartItems()->count() : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }
}
