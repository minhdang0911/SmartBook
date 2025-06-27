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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class OrderController extends Controller
{
    private $ghnApiUrl = 'https://online-gateway.ghn.vn/shiip/public-api/v2';
    private $ghnToken = '49c70366-532e-11f0-b053-3671f28aa7e4';
    private $ghnShopId = '5860204';


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

            // Tạo đơn hàng với status pending
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
                'status' => 'pending',
                'price' => $total, // giữ nguyên: tiền sách
                'shipping_fee' => $request->input('shipping_fee', 0), // ✅ dùng phí ship từ FE
                'total_price' => $request->input('total_price', $total), // ✅ dùng tổng từ FE
                'address' => $address,
                'created_at' => now(),
                // 'phOne'=> $request->input('phone'),
            ]);


            // Tạo order items và kiểm tra stock
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
                'message' => 'Tạo đơn hàng thành công.',
                'order_id' => $order->id,
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'status' => $order->status,
                        'total_price' => $order->total_price,
                        'address' => $order->address
                    ]
                ]
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

    // API riêng để tạo đơn ship
    public function createShipping(Request $request, $orderId): JsonResponse
    {
        try {
            $user = Auth::user();
            Log::info('Bắt đầu tạo đơn ship cho order ID: ' . $orderId);

            $order = Order::with(['orderItems.book', 'user'])->find($orderId);

            if (!$order) {
                Log::warning("Không tìm thấy đơn hàng ID: {$orderId}");
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }

            if ($order->shipping_code) {
                Log::info("Đơn hàng {$orderId} đã có mã vận đơn: " . $order->shipping_code);
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng đã có mã vận đơn: ' . $order->shipping_code
                ], 400);
            }

            DB::beginTransaction();
            try {
                Log::info("Gọi GHN API tạo đơn hàng cho order ID: {$orderId}");
                $shippingResult = $this->createGHNShippingOrder($order, $request);

                if (!$shippingResult['success']) {
                    DB::rollBack();
                    Log::error("Lỗi từ GHN: " . $shippingResult['message']);
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi tạo đơn ship: ' . $shippingResult['message']
                    ], 400);
                }

                $shippingFee = $shippingResult['data']['total_fee'] ?? 0;
                $order->update([
                    'shipping_code' => $shippingResult['data']['order_code'],
                    'status' => 'ready_to_pick',
                    'shipping_fee' => $shippingFee,
                    'total_price' => $order->price + $shippingFee
                ]);

                DB::commit();

                Log::info("Tạo đơn ship thành công cho order ID: {$order->id}, mã vận đơn: " . $order->shipping_code);

                $order->refresh();

                return response()->json([
                    'success' => true,
                    'message' => 'Tạo đơn ship thành công',
                    'data' => [
                        'order_id' => $order->id,
                        'shipping_code' => $order->shipping_code,
                        'status' => $order->status,
                        'shipping_fee' => $order->shipping_fee,
                        'total_price' => $order->total_price,
                        'ghn_data' => $shippingResult['data']
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Lỗi khi tạo đơn ship nội bộ (DB): " . $e->getMessage());
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error("Lỗi tổng khi tạo đơn ship: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    protected function createGHNShippingOrder($order, $request = null)
    {
        try {
            $items = $order->orderItems->map(function ($item) {
                return [
                    'name' => $item->book->title,
                    'quantity' => $item->quantity,
                    'weight' => 200,
                    'length' => 20,
                    'width' => 15,
                    'height' => 2
                ];
            })->toArray();

            $customerName = $request ? $request->input('customer_name', $order->user->name) : $order->user->name;
            $customerPhone = $request ? $request->input('customer_phone', $order->user->phone ?? '') : ($order->user->phone ?? '');

            $orderData = [
                'payment_type_id' => $order->payment === 'cod' ? 2 : 1,
                'note' => $order->note ?? '',
                'required_note' => 'KHONGCHOXEMHANG',
                'to_name' => $customerName,
                'to_phone' => $customerPhone,
                'to_address' => $order->address,
                'to_ward_code' => (string) $order->ward_id,
                'to_district_id' => $order->district_id,
                'cod_amount' => $order->payment === 'cod' ? (int) $order->price : 0,

                'content' => 'Sách',
                'weight' => array_sum(array_column($items, 'weight')),
                'length' => 25,
                'width' => 20,
                'height' => 10,
                'service_type_id' => 2,
                'items' => $items
            ];

            Log::info("Dữ liệu gửi GHN:", $orderData);

            $response = Http::withHeaders([
                'Token' => $this->ghnToken,
                'ShopId' => $this->ghnShopId,
                'Content-Type' => 'application/json'
            ])->post($this->ghnApiUrl . '/shipping-order/create', $orderData);

            Log::info("Phản hồi từ GHN", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                if ($responseData['code'] == 200) {
                    return [
                        'success' => true,
                        'data' => $responseData['data']
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $responseData['message'] ?? 'Lỗi từ GHN'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Không thể kết nối đến GHN: ' . $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error("Lỗi exception GHN: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
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
                     'phone' => $order->phone,
                    'street' => $order->street,
                    'ward_name' => $order->ward_name,
                    'district_name' => $order->district_name,
                    'shipping_code' => $order->shipping_code,
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
                'shipping_code' => $order->shipping_code,
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
                // Hủy đơn ship trên GHN nếu có shipping_code
                if ($order->shipping_code) {
                    $this->cancelGHNOrder($order->shipping_code);
                }

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

    private function cancelGHNOrder($shippingCode)
    {
        try {
            $response = Http::withHeaders([
                'Token' => $this->ghnToken,
                'ShopId' => $this->ghnShopId,
                'Content-Type' => 'application/json'
            ])->post($this->ghnApiUrl . '/shipping-order/cancel', [
                        'order_codes' => [$shippingCode]
                    ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateOrderStatus(Request $request, $orderId): JsonResponse
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

            $newStatus = $request->input('status');
            $validStatuses = ['ready_to_pick', 'picking', 'money_collect_picking', 'picked', 'storing', 'delivering', 'delivered', 'delivery_fail', 'cancel'];

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

    public function updateShippingStatus(Request $request): JsonResponse
    {
        try {
            $shippingCode = $request->input('OrderCode');
            $status = $request->input('Status');

            // Map status GHN sang status của bạn
            $statusMap = [
                'ready_to_pick' => 'ready_to_pick',
                'picking' => 'picking',
                'money_collect_picking' => 'money_collect_picking',
                'picked' => 'picked',
                'storing' => 'storing',
                'transporting' => 'transporting',
                'sorting' => 'sorting',
                'delivering' => 'delivering',
                'delivered' => 'delivered',
                'delivery_fail' => 'delivery_fail',
                'waiting_to_return' => 'waiting_to_return',
                'return' => 'return',
                'return_transporting' => 'return_transporting',
                'return_sorting' => 'return_sorting',
                'returning' => 'returning',
                'return_fail' => 'return_fail',
                'returned' => 'returned',
                'exception' => 'exception',
                'damage' => 'damage',
                'lost' => 'lost',
                'cancel' => 'cancel'
            ];

            $order = Order::where('shipping_code', $shippingCode)->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }

            $newStatus = $statusMap[$status] ?? $status;
            $order->update(['status' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    // API để lấy thông tin tracking từ GHN
    public function getShippingInfo($orderId): JsonResponse
    {
        try {
            $order = Order::find($orderId);

            if (!$order || !$order->shipping_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy mã vận đơn'
                ], 404);
            }

            $response = Http::withHeaders([
                'Token' => $this->ghnToken,
                'Content-Type' => 'application/json'
            ])->post($this->ghnApiUrl . '/shipping-order/detail', [
                        'order_code' => $order->shipping_code
                    ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return response()->json([
                    'success' => true,
                    'data' => $responseData['data']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy thông tin vận chuyển'
            ], 400);
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
                     'phone' => $order->phone,
                    'ward_name' => $order->ward_name,
                    'district_name' => $order->district_name,
                    'shipping_code' => $order->shipping_code,
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
    public function syncOrderStatusFromGHN($orderId): JsonResponse
    {
        try {
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }

            if (!$order->shipping_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng chưa có mã vận đơn'
                ], 400);
            }

            // Gọi API GHN để lấy chi tiết đơn hàng
            $response = Http::withHeaders([
                'Token' => $this->ghnToken,
                'Content-Type' => 'application/json'
            ])->post($this->ghnApiUrl . '/shipping-order/detail', [
                        'order_code' => $order->shipping_code
                    ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể kết nối đến GHN API'
                ], 400);
            }

            $responseData = $response->json();

            if ($responseData['code'] !== 200) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi từ GHN: ' . ($responseData['message'] ?? 'Unknown error')
                ], 400);
            }

            $ghnData = $responseData['data'];
            $ghnStatus = $ghnData['status'] ?? null;

            if (!$ghnStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không lấy được status từ GHN'
                ], 400);
            }

            // Map status từ GHN sang status của hệ thống
            $newStatus = $this->mapGHNStatusToSystemStatus($ghnStatus);
            $oldStatus = $order->status;

            // Cập nhật status nếu có thay đổi
            if ($newStatus !== $oldStatus) {
                $order->update(['status' => $newStatus]);

                Log::info("Đã cập nhật status đơn hàng {$orderId} từ {$oldStatus} thành {$newStatus}");
            }

            return response()->json([
                'success' => true,
                'message' => 'Đồng bộ status thành công',
                'data' => [
                    'order_id' => $order->id,
                    'shipping_code' => $order->shipping_code,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'ghn_status' => $ghnStatus,
                    'updated' => $newStatus !== $oldStatus,
                    'ghn_data' => $ghnData
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Lỗi khi đồng bộ status từ GHN cho đơn hàng {$orderId}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    private function mapGHNStatusToSystemStatus($ghnStatus): string
    {
        $statusMap = [
            'ready_to_pick' => 'ready_to_pick',
            'picking' => 'picking',
            'money_collect_picking' => 'money_collect_picking',
            'picked' => 'picked',
            'storing' => 'storing',
            'transporting' => 'transporting',
            'sorting' => 'sorting',
            'delivering' => 'delivering',
            'delivered' => 'delivered',
            'delivery_fail' => 'delivery_fail',
            'waiting_to_return' => 'waiting_to_return',
            'return' => 'return',
            'return_transporting' => 'return_transporting',
            'return_sorting' => 'return_sorting',
            'returning' => 'returning',
            'return_fail' => 'return_fail',
            'returned' => 'returned',
            'exception' => 'exception',
            'damage' => 'damage',
            'lost' => 'lost',
            'cancel' => 'cancelled', // Map 'cancel' của GHN thành 'cancelled' của hệ thống
        ];

        return $statusMap[$ghnStatus] ?? $ghnStatus;
    }
}