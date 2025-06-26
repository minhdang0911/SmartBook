<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

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
                'user_id' => $user->id,
                'sonha' => $request->input('sonha'),
                'street' => $request->input('street'),
                'district_id' => $request->input('district_id'),
                'ward_id' => $request->input('ward_id'),
                'note' => $request->input('note'),
                'ward_name' => $request->input('ward_name'),
                'district_name' => $request->input('district_name'),
                'payment' => $request->input('payment', 'cod'),
                'status' => 'picking',
                'price' => $total,
                'shipping_fee' => 0,
                'total_price' => $total,
                'address' => $address,
                'created_at' => now(),
            ]);

            // Tạo order items
            foreach ($cartItems as $item) {
                $book = $item->book;

                if (!$book) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Sách không tồn tại.'], 400);
                }

                if ($book->stock < $item->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Sách '{$book->title}' không đủ tồn kho."
                    ], 400);
                }

                $book->stock -= $item->quantity;
                $book->save();

                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $book->id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }

            // Xoá item khỏi giỏ
            $cart->cartItems()->whereIn('id', $cartItemIds)->delete();

            // Cập nhật tổng còn lại
            $remainingAmount = $cart->cartItems()->sum(DB::raw('quantity * price'));
            $cart->update(['total_amount' => $remainingAmount]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công.',
                'order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tạo đơn hàng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 10);
            $status = $request->input('status');
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            $query = Order::with(['orderItems.book.author', 'orderItems.book.category'])
                ->where('user_id', $user->id);

            if ($status) {
                $query->where('status', $status);
            }

            $query->orderBy($sortBy, $sortOrder);
            $orders = $query->paginate($perPage);

            $formattedOrders = collect($orders->items())->map(function ($order) {
                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'payment' => $order->payment,
                    'price' => $order->price,
                    'shipping_fee' => $order->shipping_fee,
                    'total_price' => $order->total_price,
                    'address' => $order->address,
                    'sonha' => $order->sonha,
                    'street' => $order->street,
                    'ward_name' => $order->ward_name,
                    'district_name' => $order->district_name,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'items' => $order->orderItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'book' => [
                                'id' => $item->book->id,
                                'title' => $item->book->title,
                                'image' => $item->book->image,
                                'price' => $item->book->price,
                                'author' => $item->book->author ? $item->book->author->name : null,
                                'category' => $item->book->category ? $item->book->category->name : null,
                            ]
                        ];
                    }),
                    'total_items' => $order->orderItems->sum('quantity')
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách đơn hàng thành công',
                'data' => [
                    'orders' => $formattedOrders,
                    'pagination' => [
                        'current_page' => $orders->currentPage(),
                        'per_page' => $orders->perPage(),
                        'total' => $orders->total(),
                        'last_page' => $orders->lastPage(),
                        'from' => $orders->firstItem(),
                        'to' => $orders->lastItem()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($orderId): JsonResponse
    {
        try {
            $user = Auth::user();

            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }
            $formattedOrder = [
                'id' => $order->id,
                'status' => $order->status,
                'payment' => $order->payment,
                'price' => $order->price,
                'shipping_fee' => $order->shipping_fee,
                'total_price' => $order->total_price,
                'address' => $order->address,
                'sonha' => $order->sonha,
                'street' => $order->street,
                'ward_id' => $order->ward_id,
                'district_id' => $order->district_id,
                'ward_name' => $order->ward_name,
                'district_name' => $order->district_name,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->quantity * $item->price,
                        'book' => [
                            'id' => $item->book->id,
                            'title' => $item->book->title,
                            'image' => $item->book->image,
                            'price' => $item->book->price,
                            'description' => $item->book->description,
                            'author' => $item->book->author ? [
                                'id' => $item->book->author->id,
                                'name' => $item->book->author->name
                            ] : null,
                            'category' => $item->book->category ? [
                                'id' => $item->book->category->id,
                                'name' => $item->book->category->name
                            ] : null,
                        ]
                    ];
                }),
                'total_items' => $order->orderItems->sum('quantity')
            ];

            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết đơn hàng thành công',
                'data' => [
                    'order' => $formattedOrder
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrderStats(): JsonResponse
    {
        try {
            $user = Auth::user();

            $stats = [
                'total_orders' => Order::where('user_id', $user->id)->count(),
                'pending_orders' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
                'processing_orders' => Order::where('user_id', $user->id)->where('status', 'processing')->count(),
                'shipped_orders' => Order::where('user_id', $user->id)->where('status', 'shipped')->count(),
                'delivered_orders' => Order::where('user_id', $user->id)->where('status', 'delivered')->count(),
                'cancelled_orders' => Order::where('user_id', $user->id)->where('status', 'cancelled')->count(),
                'total_spent' => Order::where('user_id', $user->id)
                    ->whereIn('status', ['delivered', 'processing', 'shipped'])
                    ->sum('total_price')
            ];

            return response()->json([
                'success' => true,
                'message' => 'Lấy thống kê đơn hàng thành công',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelOrder($orderId): JsonResponse
    {
        try {
            $user = Auth::user();

            // Kiểm tra user đã đăng nhập
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn cần đăng nhập để thực hiện thao tác này'
                ], 401);
            }


            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }

            // Danh sách trạng thái không được phép hủy
            $nonCancellableStatuses = [
                'picking',
                'money_collect_picking',
                'picked',
                'storing',
                'delivering',
                'delivered',
                'delivery_fail',
                'cancelled',
                'cancel'
            ];

            // Kiểm tra trạng thái đơn hàng - chỉ cho phép hủy khi pending
            if (in_array($order->status, $nonCancellableStatuses)) {
                $statusMessages = [
                    'picking' => 'Không thể hủy đơn hàng đang được lấy hàng',
                    'ready_to_pick' => 'Không thể hủy đơn hàng đã sẵn sàng lấy hàng',
                    'money_collect_picking' => 'Không thể hủy đơn hàng đang thu tiền và lấy hàng',
                    'picked' => 'Không thể hủy đơn hàng đã được lấy',
                    'storing' => 'Không thể hủy đơn hàng đang lưu kho',
                    'delivering' => 'Không thể hủy đơn hàng đang giao',
                    'delivered' => 'Không thể hủy đơn hàng đã giao thành công',
                    'delivery_fail' => 'Không thể hủy đơn hàng giao thất bại',
                    'cancelled' => 'Đơn hàng đã được hủy trước đó',
                    'cancel' => 'Đơn hàng đã được hủy trước đó'
                ];

                $message = $statusMessages[$order->status] ?? 'Chỉ có thể hủy đơn hàng đang chờ xử lý';

                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Hoàn lại số lượng sách vào kho
                foreach ($order->orderItems as $item) {
                    $book = $item->book;
                    if ($book) {
                        $book->increment('stock', $item->quantity);
                    }
                }

                // Cập nhật trạng thái đơn hàng
                $order->update(['status' => 'cancelled']);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Hủy đơn hàng thành công',
                    'data' => [
                        'order_id' => $order->id,
                        'status' => $order->status
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    public function updateOrderStatus(Request $request, $orderId): JsonResponse
    {
        try {
            $user = Auth::user();

            // Nếu cần check quyền admin thì kiểm tra ở đây
            // if (!$user->is_admin) { return response()->json(['message' => 'Unauthorized'], 403); }

            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }

            $newStatus = $request->input('status');
            $validStatuses = ['`ready_to_pick', `picking`, `money_collect_picking`, `picked`, `storing`, `delivering`, `delivered`, `delivery_fail`, `cancel`];

            // if (!in_array($newStatus, $validStatuses)) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Trạng thái không hợp lệ'
            //     ], 400);
            // }

            $order->status = $newStatus;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công',
                'data' => [
                    'order_id' => $order->id,
                    'status' => $order->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getAllOrders(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Chỉ cho phép admin xem tất cả đơn hàng
            // if (!$user || !$user->is_admin) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Bạn không có quyền truy cập'
            //     ], 403);
            // }

            $perPage = $request->input('per_page', 10);
            $status = $request->input('status');
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            $query = Order::with(['user', 'orderItems.book.author', 'orderItems.book.category']);

            if ($status) {
                $query->where('status', $status);
            }

            $query->orderBy($sortBy, $sortOrder);
            $orders = $query->paginate($perPage);

            $formattedOrders = collect($orders->items())->map(function ($order) {
                return [
                    'id' => $order->id,
                    'user' => [
                        'id' => $order->user->id,
                        'name' => $order->user->name,
                        'email' => $order->user->email
                    ],
                    'status' => $order->status,
                    'payment' => $order->payment,
                    'price' => $order->price,
                    'shipping_fee' => $order->shipping_fee,
                    'total_price' => $order->total_price,
                    'address' => $order->address,
                    'sonha' => $order->sonha,
                    'street' => $order->street,
                    'ward_name' => $order->ward_name,
                    'district_name' => $order->district_name,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'items' => $order->orderItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'book' => [
                                'id' => $item->book->id,
                                'title' => $item->book->title,
                                'image' => $item->book->image,
                                'price' => $item->book->price,
                                'author' => $item->book->author ? $item->book->author->name : null,
                                'category' => $item->book->category ? $item->book->category->name : null,
                            ]
                        ];
                    }),
                    'total_items' => $order->orderItems->sum('quantity')
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy tất cả đơn hàng thành công',
                'data' => [
                    'orders' => $formattedOrders,
                    'pagination' => [
                        'current_page' => $orders->currentPage(),
                        'per_page' => $orders->perPage(),
                        'total' => $orders->total(),
                        'last_page' => $orders->lastPage(),
                        'from' => $orders->firstItem(),
                        'to' => $orders->lastItem()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }


}
