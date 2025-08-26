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
use App\Models\GroupOrder;
use Carbon\Carbon;

class OrderController extends Controller
{
    private $ghnApiUrl = 'https://online-gateway.ghn.vn/shiip/public-api/v2';
    private $ghnToken = '49c70366-532e-11f0-b053-3671f28aa7e4';
    private $ghnShopId = '5860204';

    // Định nghĩa các trạng thái hợp lệ
    private $validStatuses = [
        'pending' => 'Chờ xử lý',
        'ready_to_pick' => 'Chờ lấy hàng',
        'picking' => 'Đang lấy hàng',
        'picked' => 'Đã lấy hàng',
        'delivering' => 'Đang giao hàng',
        'delivered' => 'Đã giao hàng',
        'cancelled' => 'Đã hủy'
    ];

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
                'phone' => $request->input('phone'),
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
     * Cập nhật trạng thái đơn hàng - đã được cải tiến để tự quản lý
     */
    public function updateOrderStatus(Request $request, $orderId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Validate status
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:' . implode(',', array_keys($this->validStatuses))
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trạng thái không hợp lệ.',
                    'errors' => $validator->errors(),
                    'valid_statuses' => $this->validStatuses
                ], 422);
            }

            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }

            $newStatus = $request->input('status');
            $oldStatus = $order->status;

            // Kiểm tra logic business cho việc chuyển trạng thái
            $statusChangeResult = $this->validateStatusChange($oldStatus, $newStatus);
            if (!$statusChangeResult['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $statusChangeResult['message']
                ], 400);
            }

            // Cập nhật trạng thái
            $order->status = $newStatus;
            $order->save();

            // Log việc thay đổi trạng thái
            Log::info('Order status updated', [
                'order_id' => $orderId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'updated_by' => $user->id,
                'updated_at' => now()
            ]);

            // Xử lý logic đặc biệt cho một số trạng thái
            $this->handleStatusChangeLogic($order, $oldStatus, $newStatus);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công',
                'data' => [
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'status_text' => $this->validStatuses[$newStatus],
                    'updated_at' => $order->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating order status', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate việc chuyển đổi trạng thái
     */
    private function validateStatusChange($oldStatus, $newStatus): array
    {
        // Các trạng thái cuối không thể chuyển đổi
        $finalStatuses = ['delivered', 'cancelled'];
        
        if (in_array($oldStatus, $finalStatuses)) {
            return [
                'allowed' => false,
                'message' => 'Không thể thay đổi trạng thái của đơn hàng đã hoàn thành hoặc đã hủy'
            ];
        }

        // Logic chuyển đổi trạng thái
        $allowedTransitions = [
            'pending' => ['ready_to_pick', 'cancelled'],
            'ready_to_pick' => ['picking', 'cancelled'],
            'picking' => ['picked', 'cancelled'],
            'picked' => ['delivering', 'cancelled'],
            'delivering' => ['delivered', 'cancelled'],
            'delivered' => [], // Trạng thái cuối
            'cancelled' => [] // Trạng thái cuối
        ];

        if (!isset($allowedTransitions[$oldStatus]) || !in_array($newStatus, $allowedTransitions[$oldStatus])) {
            return [
                'allowed' => false,
                'message' => "Không thể chuyển từ trạng thái '{$this->validStatuses[$oldStatus]}' sang '{$this->validStatuses[$newStatus]}'"
            ];
        }

        return ['allowed' => true, 'message' => ''];
    }

    /**
     * Xử lý logic đặc biệt khi thay đổi trạng thái
     */
    private function handleStatusChangeLogic($order, $oldStatus, $newStatus): void
    {
        try {
            // Khi đơn hàng bị hủy, hoàn lại stock
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                $this->restoreStock($order);
                Log::info('Stock restored for cancelled order', ['order_id' => $order->id]);
            }

            // Khi chuyển sang ready_to_pick, có thể tự động cập nhật shipping_fee nếu cần
            if ($newStatus === 'ready_to_pick' && !$order->shipping_code) {
                // Logic để tính phí ship nếu chưa có
                Log::info('Order ready to pick', ['order_id' => $order->id]);
            }

            // Khi đơn hàng được giao thành công
            if ($newStatus === 'delivered') {
                Log::info('Order delivered successfully', [
                    'order_id' => $order->id,
                    'delivered_at' => now()
                ]);
                // Có thể thêm logic gửi email thông báo giao hàng thành công
            }

        } catch (\Exception $e) {
            Log::error('Error in status change logic', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Hoàn lại stock khi hủy đơn hàng
     */
    private function restoreStock($order): void
    {
        foreach ($order->orderItems as $item) {
            $book = $item->book;
            if ($book) {
                $book->increment('stock', $item->quantity);
                Log::info('Stock restored', [
                    'book_id' => $book->id,
                    'quantity_restored' => $item->quantity,
                    'new_stock' => $book->stock
                ]);
            }
        }
    }

    /**
     * Tạo order code theo format: ddmmyystt
     */
    private function generateOrderCode(): string
    {
        $today = now();
        $datePrefix = $today->format('dmY');
        
        $orderCount = Order::whereDate('created_at', $today->toDateString())->count();
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

            $orderWithItems = Order::with(['orderItems.book', 'user'])->find($orderId);

            if (!$orderWithItems) {
                Log::error('Order not found when loading for email', ['order_id' => $orderId]);
                return;
            }

            if (!$user->email) {
                Log::error('User email is empty', ['user_id' => $user->id]);
                return;
            }

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
                    'status' => 'ready_to_pick', // Tự động chuyển sang ready_to_pick
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

        // --- Query đơn hàng thường ---
        $query = Order::with(['orderItems.book.author', 'orderItems.book.category'])
            ->where('user_id', $user->id);

        if ($status) {
            $query->where('status', $status);
        }

        $query->orderBy($sortBy, $sortOrder);
        $orders = $query->paginate($perPage);

        $formattedOrders = collect($orders->items())->map(function ($order) {
            return [
                'id'          => $order->id,
                'status'      => $order->status,
                'payment'     => $order->payment,
                'price'       => $order->price,
                'shipping_fee'=> $order->shipping_fee,
                'total_price' => $order->total_price,
                'address'     => $order->address,
                'phone'       => $order->phone,
                'created_at'  => $order->created_at,
                'updated_at'  => $order->updated_at,
                'items'       => $order->orderItems->map(function ($item) {
                    return [
                        'id'       => $item->id,
                        'quantity' => $item->quantity,
                        'price'    => $item->price,
                        'book'     => [
                            'id'       => $item->book->id,
                            'title'    => $item->book->title,
                            'image'    => $item->book->image,
                            'price'    => $item->book->price,
                            'author'   => $item->book->author?->name,
                            'category' => $item->book->category?->name,
                        ]
                    ];
                }),
                'total_items' => $order->orderItems->sum('quantity')
            ];
        });

        // --- Query lịch sử group orders ---
        $groupOrders = GroupOrder::with(['members.user:id,name','items.book:id,title,cover_image'])
            ->whereHas('members', fn($q) => $q->where('user_id', $user->id))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($g) {
                return [
                    'id'         => $g->id,
                    'status'     => $g->status,
                    'expires_at' => $g->expires_at,
                    'join_url'   => $g->join_url,
                    'members'    => $g->members->map(fn($m) => [
                        'id'    => $m->id,
                        'name'  => $m->display_name,
                        'role'  => $m->role,
                        'user_id' => $m->user_id,
                    ]),
                    'items' => $g->items->map(fn($i) => [
                        'id'    => $i->id,
                        'title' => $i->book->title,
                        'cover_image' => $i->book->cover_image,
                        'qty'   => $i->quantity,
                        'price' => $i->price_snapshot,
                    ]),
                    'total' => $g->items->sum(fn($i) => $i->quantity * $i->price_snapshot),
                    'created_at' => $g->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách đơn hàng thành công',
            'data' => [
                'orders'       => $formattedOrders,
                'pagination'   => [
                    'current_page' => $orders->currentPage(),
                    'per_page'     => $orders->perPage(),
                    'total'        => $orders->total(),
                    'last_page'    => $orders->lastPage(),
                ],
                'group_orders' => $groupOrders, // 👈 thêm lịch sử group order ở đây
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

            $order = Order::with(['orderItems.book.author', 'orderItems.book.category', 'user'])->find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }

            $formattedOrder = [
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
                'phone' => $order->phone,
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
                'data' => $formattedOrder
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
                'total_orders' => Order::count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'processing_orders' => Order::whereIn('status', ['ready_to_pick', 'picking', 'picked', 'delivering'])->count(),
                'delivered_orders' => Order::where('status', 'delivered')->count(),
                'cancelled_orders' => Order::where('status', 'cancelled')->count()
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

            // Kiểm tra xem có thể hủy đơn hàng không
            $statusChangeResult = $this->validateStatusChange($order->status, 'cancelled');
            if (!$statusChangeResult['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $statusChangeResult['message']
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Hủy đơn ship trên GHN nếu có shipping_code
                if ($order->shipping_code) {
                    $this->cancelGHNOrder($order->shipping_code);
                }

                // Hoàn lại số lượng sách vào kho
                $this->restoreStock($order);

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

    // API để lấy thông tin tracking từ GHN (vẫn giữ để tham khảo nếu cần)
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
}