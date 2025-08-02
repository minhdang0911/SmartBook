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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\OrderConfirmationMail;

use Carbon\Carbon;


class OrderController extends Controller
{
    private $ghnApiUrl = 'https://online-gateway.ghn.vn/shiip/public-api/v2';
    private $ghnToken = '49c70366-532e-11f0-b053-3671f28aa7e4';
    private $ghnShopId = '5860204';


public function store(Request $request): JsonResponse
{
    // Validate request
    $validator = Validator::make($request->all(), [
        'cart_item_ids' => 'required|array|min:1',
        'cart_item_ids.*' => 'required|integer|exists:cart_items,id',
        'sonha' => 'required|string|max:50',
        'street' => 'required|string|max:100',
        'district_id' => 'required|integer',
        'ward_id' => 'required|integer',
        'ward_name' => 'required|string|max:100',
        'district_name' => 'required|string|max:100',
        'payment' => 'required|in:cod,bank_transfer,credit_card',
        'shipping_fee' => 'nullable|numeric|min:0',
        'total_price' => 'nullable|numeric|min:0',
        'note' => 'nullable|string|max:500',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
            'errors' => $validator->errors()
        ], 422);
    }

    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Người dùng chưa đăng nhập.'
        ], 401);
    }

    DB::beginTransaction();
    try {
        $cartItemIds = $request->input('cart_item_ids', []);

        // Lấy cart
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy giỏ hàng.'
            ], 400);
        }

        // Lấy danh sách cart items được chọn
        $cartItems = $cart->cartItems()->with('book')
            ->whereIn('id', $cartItemIds)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có sản phẩm nào được chọn.'
            ], 400);
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

        $shippingFee = $request->input('shipping_fee', 0);
        $totalPrice = $request->input('total_price', $total + $shippingFee);
        
        // Tạo order_code theo format: ddmmyystt
        $orderCode = $this->generateOrderCode();

        // Tạo đơn hàng với status pending
        $order = Order::create([
            'user_id' => $user->id,
            'order_code' => $orderCode,
            'sonha' => $request->input('sonha'),
            'street' => $request->input('street'),
            'district_id' => $request->input('district_id'),
            'ward_id' => $request->input('ward_id'),
            'note' => $request->input('note'),
            'ward_name' => $request->input('ward_name'),
            'district_name' => $request->input('district_name'),
            'payment' => $request->input('payment', 'cod'),
            'status' => 'pending',
            'price' => $total,
            'shipping_fee' => $shippingFee,
            'total_price' => $totalPrice,
            'address' => $address,
            'created_at' => now(),
        ]);

        Log::info('Order created successfully', [
            'order_id' => $order->id, 
            'order_code' => $order->order_code,
            'user_id' => $user->id
        ]);

        // Tạo order items và kiểm tra stock
        foreach ($cartItems as $item) {
            $book = $item->book;

            if (!$book) {
                DB::rollBack();
                Log::error('Book not found for cart item', ['cart_item_id' => $item->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Sách không tồn tại.'
                ], 400);
            }

            if ($book->stock < $item->quantity) {
                DB::rollBack();
                Log::warning('Insufficient stock', [
                    'book_id' => $book->id,
                    'book_title' => $book->title,
                    'available_stock' => $book->stock,
                    'requested_quantity' => $item->quantity
                ]);
                return response()->json([
                    'success' => false,
                    'message' => "Sách '{$book->title}' không đủ tồn kho. Còn lại: {$book->stock}, yêu cầu: {$item->quantity}"
                ], 400);
            }

            // Trừ stock
            $book->stock -= $item->quantity;
            $book->save();

            // Tạo order item
            OrderItem::create([
                'order_id' => $order->id,
                'book_id' => $book->id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ]);

            Log::info('Order item created', [
                'order_id' => $order->id,
                'book_id' => $book->id,
                'quantity' => $item->quantity,
                'price' => $item->price
            ]);
        }

        // Xoá item khỏi giỏ
        $deletedCount = $cart->cartItems()->whereIn('id', $cartItemIds)->delete();
        Log::info('Cart items deleted', ['count' => $deletedCount]);

        // Cập nhật tổng còn lại
        $remainingAmount = $cart->cartItems()->sum(DB::raw('quantity * price'));
        $cart->update(['total_amount' => $remainingAmount]);

        DB::commit();
        Log::info('Order transaction committed successfully', ['order_id' => $order->id]);

        // Gửi email xác nhận đặt hàng
        $this->sendOrderConfirmationEmail($order->id, $user);

        return response()->json([
            'success' => true,
            'message' => 'Tạo đơn hàng thành công.',
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'status' => $order->status,
                    'total_price' => $order->total_price,
                    'address' => $order->address,
                    'payment' => $order->payment,
                    'created_at' => $order->created_at->format('d/m/Y H:i:s')
                ]
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Order creation failed', [
            'user_id' => $user->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Đã xảy ra lỗi khi tạo đơn hàng.',
            'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
        ], 500);
    }
}

/**
 * Tạo order code theo format: ddmmyystt
 * Ví dụ: 01082501, 01082502
 */
private function generateOrderCode(): string
{
    $today = now();
    $datePrefix = $today->format('dmY'); // ddmmyyyy
    
    // Đếm số order đã tạo trong ngày hôm nay
    $orderCount = Order::whereDate('created_at', $today->toDateString())->count();
    
    // Tạo số thứ tự (STT) với 2 chữ số, bắt đầu từ 01
    $sequenceNumber = str_pad($orderCount + 1, 2, '0', STR_PAD_LEFT);
    
    return $datePrefix . $sequenceNumber;
}

private function sendOrderConfirmationEmail($orderId, $user)
{
    try {
        Log::info('Starting to send email for order', [
            'order_id' => $orderId,
            'user_email' => $user->email
        ]);

        // Load lại order với orderItems và books để gửi email
        $orderWithItems = Order::with(['orderItems.book', 'user'])->find($orderId);

        if (!$orderWithItems) {
            Log::error('Order not found when loading for email', ['order_id' => $orderId]);
            return;
        }

        if (!$user->email) {
            Log::error('User email is empty', ['user_id' => $user->id]);
            return;
        }

        // Gửi email
        Mail::to($user->email)->send(new OrderConfirmationMail($orderWithItems));

        Log::info('Order confirmation email sent successfully', [
            'order_id' => $orderId,
            'order_code' => $orderWithItems->order_code,
            'user_email' => $user->email
        ]);

    } catch (\Exception $mailException) {
        Log::error('Failed to send order confirmation email', [
            'order_id' => $orderId,
            'user_email' => $user->email ?? 'N/A',
            'error' => $mailException->getMessage(),
            'trace' => $mailException->getTraceAsString()
        ]);

        // Có thể gửi notification cho admin hoặc retry queue
        // $this->notifyAdminEmailFailed($orderId, $mailException);
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

    private $zaloPayConfig;

    public function __construct()
    {
        $this->zaloPayConfig = [
            'app_id' => 554,
            'key1' => '8NdU5pG5R2spGHGhyO99HN1OhD8IQJBn', // key1 sandbox
            'key2' => 'uUfsWgfLkRLzq6W2uNXTCxrfxs51auny', // key2 cho callback
            'endpoint' => 'https://sb-openapi.zalopay.vn/v2/create'
        ];
    }

    /**
     * Tạo đơn hàng ZaloPay
     */
  public function createZaloPayOrder(Request $request)
{
    // Validate request
    $request->validate([
        'amount' => 'required|integer|min:1000',
        'description' => 'nullable|string|max:255',
        'items' => 'nullable|array',
        'items.*.itemid' => 'required_with:items|string',
        'items.*.itemname' => 'required_with:items|string',
        'items.*.itemprice' => 'required_with:items|integer|min:1',
        'items.*.itemquantity' => 'required_with:items|integer|min:1'
    ]);

    try {
        $amount = $request->input('amount');
        $description = $request->input('description', 'Thanh toán đơn hàng');
        $items = $request->input('items', []);

        // Kiểm tra cấu hình ZaloPay trước khi sử dụng
        if (!isset($this->zaloPayConfig['app_id']) || !isset($this->zaloPayConfig['key1']) || !isset($this->zaloPayConfig['endpoint'])) {
            throw new \Exception('ZaloPay configuration is missing or incomplete');
        }

        // Tạo app_trans_id theo format YYMMDD_XXXXXX
        $today = Carbon::now();
        $yymmdd = $today->format('ymd');
        $app_trans_id = $yymmdd . '_' . rand(100000, 999999);
        
        // Đảm bảo app_trans_id là duy nhất (tùy chọn)
        // Có thể thêm check database nếu cần

        $app_time = time() * 1000; // milliseconds

        $embed_data = [
            'redirecturl' => 'https://sandbox.zalopay.vn/thankyou'
        ];

        // Mặc định items nếu không có
        $orderItems = !empty($items) ? $items : [
            [
                'itemid' => 'default',
                'itemname' => 'Sản phẩm mặc định',
                'itemprice' => $amount,
                'itemquantity' => 1,
            ]
        ];

        // Validate tổng giá trị items với amount
        if (!empty($items)) {
            $totalItemsAmount = 0;
            foreach ($items as $item) {
                $totalItemsAmount += $item['itemprice'] * $item['itemquantity'];
            }
            
            if ($totalItemsAmount !== $amount) {
                Log::warning('Amount mismatch:', [
                    'request_amount' => $amount,
                    'calculated_amount' => $totalItemsAmount
                ]);
                // Có thể throw exception hoặc warning tùy business logic
            }
        }

        // Tạo MAC theo đúng format của ZaloPay
        // Format: app_id|app_trans_id|app_user|amount|app_time|embed_data|item
        $embed_data_json = json_encode($embed_data);
        $item_json = json_encode($orderItems);
        
        // Kiểm tra JSON encoding
        if ($embed_data_json === false || $item_json === false) {
            throw new \Exception('Failed to encode JSON data');
        }

        $data = $this->zaloPayConfig['app_id'] . '|' . $app_trans_id . '|demo|' . $amount . '|' . $app_time . '|' . $embed_data_json . '|' . $item_json;
        $mac = hash_hmac('sha256', $data, $this->zaloPayConfig['key1']);

        $order = [
            'app_id' => $this->zaloPayConfig['app_id'],
            'app_trans_id' => $app_trans_id,
            'app_user' => 'demo',
            'app_time' => $app_time,
            'amount' => $amount,
            'item' => $item_json,
            'embed_data' => $embed_data_json,
            'description' => $description ?: "Demo - Thanh toan don hang #$app_trans_id",
            'callback_url' => url('/api/orders/zalopay/callback'),
            'mac' => $mac,
        ];

        Log::info('Data để tạo MAC:', ['data' => $data]);
        Log::info('MAC generated:', ['mac' => $mac]);
        Log::info('Order data:', $order);

        // Thêm timeout và retry logic với headers cụ thể
        $response = Http::timeout(30)
            ->retry(3, 1000)
            ->asForm()
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
                'User-Agent' => 'SmartBook/1.0'
            ])
            ->post($this->zaloPayConfig['endpoint'], $order);

        // Log full response để debug
        Log::info('ZaloPay Full Response:', [
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->body(),
            'content_type' => $response->header('Content-Type')
        ]);

        if ($response->successful()) {
            $responseBody = $response->body();
            
            // Kiểm tra xem response có phải là HTML không
            if (str_contains($responseBody, '<html>') || str_contains($responseBody, '<!DOCTYPE')) {
                Log::error('ZaloPay returned HTML instead of JSON:', [
                    'body' => $responseBody,
                    'endpoint' => $this->zaloPayConfig['endpoint']
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'ZaloPay API returned HTML instead of JSON',
                    'details' => 'Invalid endpoint or server error'
                ], 500);
            }
            
            // Thử parse JSON
            try {
                $responseData = $response->json();
            } catch (\Exception $e) {
                Log::error('Failed to parse ZaloPay response as JSON:', [
                    'body' => $responseBody,
                    'error' => $e->getMessage()
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid JSON response from ZaloPay',
                    'details' => $responseBody
                ], 500);
            }
            
            // Kiểm tra response từ ZaloPay
            if (isset($responseData['return_code']) && $responseData['return_code'] == 1) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData,
                    'app_trans_id' => $app_trans_id
                ]);
            } else {
                // ZaloPay trả về lỗi
                $errorMessage = isset($responseData['return_message']) ? $responseData['return_message'] : 'Unknown ZaloPay error';
                Log::error('ZaloPay Error Response:', $responseData);
                
                return response()->json([
                    'success' => false,
                    'error' => 'ZaloPay Error: ' . $errorMessage,
                    'details' => $responseData
                ], 400);
            }
        } else {
            $errorBody = $response->body();
            Log::error('ZaloPay HTTP Error:', [
                'status' => $response->status(),
                'body' => $errorBody,
                'headers' => $response->headers()
            ]);
            
            throw new \Exception('ZaloPay API HTTP Error: ' . $response->status() . ' - ' . $errorBody);
        }

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation Error:', $e->errors());
        return response()->json([
            'success' => false,
            'error' => 'Validation failed',
            'details' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        Log::error('Lỗi khi tạo đơn hàng ZaloPay:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Không tạo được đơn hàng ZaloPay',
            'details' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Callback endpoint để nhận thông báo từ ZaloPay
     */
    public function zaloPayCallback(Request $request)
    {
        Log::info('ZaloPay Callback received:', $request->all());
        
        try {
            $callbackData = $request->input('data');
            $receivedMac = $request->input('mac');
            
            // Verify MAC từ callback
            $computedMac = hash_hmac('sha256', $callbackData, $this->zaloPayConfig['key2']);
            
            if ($computedMac === $receivedMac) {
                Log::info('Callback MAC hợp lệ');
                
                // Parse callback data để lấy thông tin
                $parsedData = json_decode($callbackData, true);
                Log::info('Parsed callback data:', $parsedData);
                
                // TODO: Xử lý logic cập nhật trạng thái đơn hàng trong database
                // Ví dụ: $this->updateOrderStatus($parsedData['app_trans_id'], 'paid');
                
                return response()->json([
                    'return_code' => 1,
                    'return_message' => 'success'
                ]);
            } else {
                Log::warning('Callback MAC không hợp lệ');
                return response()->json([
                    'return_code' => -1,
                    'return_message' => 'mac not equal'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Lỗi xử lý callback:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'return_code' => 0,
                'return_message' => 'exception'
            ]);
        }
    }

    /**
     * Check trạng thái đơn hàng ZaloPay
     */
    public function checkZaloPayStatus(Request $request)
    {
        try {
            $app_trans_id = $request->input('app_trans_id');
            
            if (!$app_trans_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'app_trans_id là bắt buộc'
                ], 400);
            }
            
            $data = $this->zaloPayConfig['app_id'] . '|' . $app_trans_id . '|' . $this->zaloPayConfig['key1'];
            $mac = hash_hmac('sha256', $data, $this->zaloPayConfig['key1']);
            
            $checkData = [
                'app_id' => $this->zaloPayConfig['app_id'],
                'app_trans_id' => $app_trans_id,
                'mac' => $mac
            ];
            
            $response = Http::asForm()->post('https://sb-openapi.zalopay.vn/v2/query', $checkData);
            
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json()
                ]);
            } else {
                throw new \Exception('ZaloPay Query API Error: ' . $response->body());
            }
            
        } catch (\Exception $e) {
            Log::error('Lỗi check status:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Không check được status',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật trạng thái đơn hàng (có thể customize theo nhu cầu)
     */
  
}
