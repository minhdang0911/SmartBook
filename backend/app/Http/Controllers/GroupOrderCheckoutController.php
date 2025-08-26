<?php 

namespace App\Http\Controllers;

use App\Models\GroupOrder;
use App\Models\GroupOrderItem;
use App\Models\GroupOrderMember;
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
use App\Mail\GroupOrderConfirmationMail;
use Carbon\Carbon;

class GroupOrderCheckoutController extends Controller
{
    private $ghnApiUrl = 'https://online-gateway.ghn.vn/shiip/public-api/v2';
    private $ghnToken = '49c70366-532e-11f0-b053-3671f28aa7e4';
    private $ghnShopId = '5860204';

    // Định nghĩa các trạng thái hợp lệ cho group order
    private $validStatuses = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'ready_to_pick' => 'Chờ lấy hàng',
        'picking' => 'Đang lấy hàng',
        'picked' => 'Đã lấy hàng',
        'delivering' => 'Đang giao hàng',
        'delivered' => 'Đã giao hàng',
        'cancelled' => 'Đã hủy'
    ];

    /**
     * Checkout group order - Tạo đơn hàng từ group order
     */
    public function checkoutGroupOrder(Request $request, $groupOrderToken): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'sonha' => 'required|string|max:50',
            'street' => 'required|string|max:100',
            'district_id' => 'required|integer',
            'ward_id' => 'required|integer',
            'ward_name' => 'required|string|max:100',
            'district_name' => 'required|string|max:100',
            'payment' => 'required|in:cod,bank_transfer,credit_card',
            'shipping_fee' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
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
            // Lấy group order (không lock ở đây), chỉ kiểm tra tình trạng
            $groupOrder = GroupOrder::with(['members'])
                ->where('token', $groupOrderToken)
                ->where('status', 'open')
                ->first();

            if (!$groupOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy giỏ hàng nhóm hoặc giỏ hàng đã đóng.'
                ], 400);
            }

            // Kiểm tra user có phải owner không
            $member = $groupOrder->members()->where('user_id', $user->id)->first();
            if (!$member || $member->role !== 'owner') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ chủ nhóm mới có thể thực hiện checkout.'
                ], 403);
            }

            // KHÓA các items của group để tránh race-condition khi checkout
            $lockedItems = GroupOrderItem::with('book')
                ->where('group_order_id', $groupOrder->id)
                ->lockForUpdate()
                ->get();

            if ($lockedItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giỏ hàng nhóm không có sản phẩm nào.'
                ], 400);
            }

            // Tính tổng tiền từ snapshot
            $total = $lockedItems->sum(function ($item) {
                return $item->quantity * $item->price_snapshot;
            });

            // Ghép địa chỉ
            $address = 'Số ' . $request->input('sonha') . ', '
                . $request->input('street') . ', '
                . $request->input('ward_name') . ', '
                . $request->input('district_name');

            $shippingFee = $request->input('shipping_fee', 0);
            $totalPrice = $total + $shippingFee;
            
            // Tạo order_code
            $orderCode = $this->generateOrderCode();

            // Tạo đơn hàng chính từ group order
            $order = Order::create([
                'user_id' => $user->id,
                'group_order_id' => $groupOrder->id, // Liên kết với group order
                'order_code' => $orderCode,
                'sonha' => $request->input('sonha'),
                'phone' => $request->input('customer_phone', $user->phone),
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

            Log::info('Group order checkout - Order created', [
                'order_id' => $order->id,
                'group_order_id' => $groupOrder->id,
                'order_code' => $order->order_code
            ]);

            // Tạo order items và kiểm tra stock
            foreach ($lockedItems as $groupItem) {
                $book = $groupItem->book;

                if (!$book) {
                    DB::rollBack();
                    Log::error('Book not found for group order item', [
                        'group_order_item_id' => $groupItem->id
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Sách không tồn tại.'
                    ], 400);
                }

                // Kiểm tra stock
                if ($book->stock < $groupItem->quantity) {
                    DB::rollBack();
                    Log::warning('Insufficient stock for group order', [
                        'book_id' => $book->id,
                        'book_title' => $book->title,
                        'available_stock' => $book->stock,
                        'requested_quantity' => $groupItem->quantity
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => "Sách '{$book->title}' không đủ tồn kho. Còn lại: {$book->stock}, yêu cầu: {$groupItem->quantity}"
                    ], 400);
                }

                // Trừ stock
                $book->stock -= $groupItem->quantity;
                $book->save();

                // Tạo order item, tạm thời còn giữ liên kết group_order_item_id
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $book->id,
                    'quantity' => $groupItem->quantity,
                    'price' => $groupItem->price_snapshot,
                    'group_order_item_id' => $groupItem->id, // Liên kết với group order item
                ]);

                Log::info('Group order - Order item created', [
                    'order_id' => $order->id,
                    'book_id' => $book->id,
                    'quantity' => $groupItem->quantity,
                    'price' => $groupItem->price_snapshot
                ]);
            }

            // Đóng group order và cập nhật trạng thái
            $groupOrder->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'order_id' => $order->id
            ]);

            // Đánh dấu tất cả items trong group order là đã checkout
            GroupOrderItem::where('group_order_id', $groupOrder->id)->update(['status' => 'confirmed']);

            /**
             * =========================
             *  DỌN SẠCH ITEM SAU CHECKOUT
             * =========================
             * YÊU CẦU: order_items.group_order_item_id phải nullable
             * hoặc FK ON DELETE SET NULL để không vướng khóa ngoại.
             */

            // Gỡ liên kết từ order_items -> group_order_items (tránh FK khi xóa)
            OrderItem::where('order_id', $order->id)
                ->whereNotNull('group_order_item_id')
                ->update(['group_order_item_id' => null]);

            // Xóa sạch items của group
            GroupOrderItem::where('group_order_id', $groupOrder->id)->delete();

            DB::commit();
            Log::info('Group order checkout completed successfully', [
                'order_id' => $order->id,
                'group_order_id' => $groupOrder->id
            ]);

            // Gửi email thông báo cho tất cả members
            $this->sendGroupOrderConfirmationEmails($order, $groupOrder);

            return response()->json([
                'success' => true,
                'message' => 'Tạo đơn hàng từ giỏ hàng nhóm thành công.',
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'group_order_id' => $groupOrder->id,
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'status' => $order->status,
                        'total_price' => $order->total_price,
                        'address' => $order->address,
                        'payment' => $order->payment,
                        'created_at' => $order->created_at->format('d/m/Y H:i:s')
                    ],
                    'group_order' => [
                        'id' => $groupOrder->id,
                        'token' => $groupOrder->token,
                        'status' => $groupOrder->status,
                        'members_count' => $groupOrder->members->count(),
                        'total_items' => 0 // sau khi dọn sạch
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Group order checkout failed', [
                'group_order_token' => $groupOrderToken,
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tạo đơn hàng từ giỏ hàng nhóm.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Cập nhật trạng thái đơn hàng group order
     */
    public function updateGroupOrderStatus(Request $request, $orderId): JsonResponse
    {
        try {
            $user = Auth::user();
            
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

            $order = Order::with('groupOrder')->find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }

            // Kiểm tra đây có phải đơn hàng từ group order không
            if (!$order->group_order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đây không phải đơn hàng từ giỏ hàng nhóm'
                ], 400);
            }

            $newStatus = $request->input('status');
            $oldStatus = $order->status;

            $statusChangeResult = $this->validateStatusChange($oldStatus, $newStatus);
            if (!$statusChangeResult['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $statusChangeResult['message']
                ], 400);
            }

            DB::beginTransaction();
            try {
                // Cập nhật trạng thái order
                $order->status = $newStatus;
                $order->save();

                // Cập nhật trạng thái group order tương ứng
                if ($order->groupOrder) {
                    $groupOrderStatus = $this->mapOrderStatusToGroupOrder($newStatus);
                    $order->groupOrder->update(['status' => $groupOrderStatus]);
                }

                DB::commit();

                Log::info('Group order status updated', [
                    'order_id' => $orderId,
                    'group_order_id' => $order->group_order_id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'updated_by' => $user->id
                ]);

                $this->handleGroupOrderStatusChangeLogic($order, $oldStatus, $newStatus);

                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật trạng thái đơn hàng nhóm thành công',
                    'data' => [
                        'order_id' => $order->id,
                        'group_order_id' => $order->group_order_id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'status_text' => $this->validStatuses[$newStatus],
                        'updated_at' => $order->updated_at
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error updating group order status', [
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
     * Tạo đơn ship cho group order
     */
    public function createGroupOrderShipping(Request $request, $orderId): JsonResponse
    {
        try {
            $user = Auth::user();
            Log::info('Bắt đầu tạo đơn ship cho group order ID: ' . $orderId);

            $order = Order::with(['orderItems.book', 'user', 'groupOrder.members'])->find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }

            if (!$order->group_order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đây không phải đơn hàng từ giỏ hàng nhóm'
                ], 400);
            }

            if ($order->shipping_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng đã có mã vận đơn: ' . $order->shipping_code
                ], 400);
            }

            DB::beginTransaction();
            try {
                $shippingResult = $this->createGHNShippingOrder($order, $request);

                if (!$shippingResult['success']) {
                    DB::rollBack();
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

                // Cập nhật group order status
                if ($order->groupOrder) {
                    $order->groupOrder->update(['status' => 'ready_to_pick']);
                }

                DB::commit();

                Log::info("Tạo đơn ship cho group order thành công", [
                    'order_id' => $order->id,
                    'group_order_id' => $order->group_order_id,
                    'shipping_code' => $order->shipping_code
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Tạo đơn ship cho đơn hàng nhóm thành công',
                    'data' => [
                        'order_id' => $order->id,
                        'group_order_id' => $order->group_order_id,
                        'shipping_code' => $order->shipping_code,
                        'status' => $order->status,
                        'shipping_fee' => $order->shipping_fee,
                        'total_price' => $order->total_price,
                        'ghn_data' => $shippingResult['data']
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error("Lỗi tạo đơn ship cho group order: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hủy group order
     */
    public function cancelGroupOrder($orderId): JsonResponse
    {
        try {
            $user = Auth::user();

            $order = Order::with('groupOrder')->find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }

            if (!$order->group_order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đây không phải đơn hàng từ giỏ hàng nhóm'
                ], 400);
            }

            $statusChangeResult = $this->validateStatusChange($order->status, 'cancelled');
            if (!$statusChangeResult['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $statusChangeResult['message']
                ], 400);
            }

            DB::beginTransaction();
            try {
                // Hủy đơn ship nếu có
                if ($order->shipping_code) {
                    $this->cancelGHNOrder($order->shipping_code);
                }

                // Hoàn lại stock
                $this->restoreStock($order);

                // Cập nhật trạng thái
                $order->update(['status' => 'cancelled']);
                
                // Cập nhật group order
                if ($order->groupOrder) {
                    $order->groupOrder->update(['status' => 'cancelled']);
                    // Các item group lúc checkout đã xóa, chỉ cập nhật status nếu còn sót
                    GroupOrderItem::where('group_order_id', $order->group_order_id)->update(['status' => 'cancelled']);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Hủy đơn hàng nhóm thành công',
                    'data' => [
                        'order_id' => $order->id,
                        'group_order_id' => $order->group_order_id,
                        'status' => $order->status
                    ]
                ]);

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

    /**
     * Lấy danh sách đơn hàng từ group orders
     */
    public function getGroupOrders(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 10);
            $status = $request->input('status');
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            $query = Order::with(['orderItems.book.author', 'orderItems.book.category', 'groupOrder.members'])
                ->whereNotNull('group_order_id');

            // Lọc theo user role - owner có thể xem tất cả, member chỉ xem của mình
            $query->whereHas('groupOrder.members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

            if ($status) {
                $query->where('status', $status);
            }

            $query->orderBy($sortBy, $sortOrder);
            $orders = $query->paginate($perPage);

            $formattedOrders = collect($orders->items())->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'status' => $order->status,
                    'payment' => $order->payment,
                    'price' => $order->price,
                    'shipping_fee' => $order->shipping_fee,
                    'total_price' => $order->total_price,
                    'address' => $order->address,
                    'shipping_code' => $order->shipping_code,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'group_order' => [
                        'id' => $order->groupOrder->id,
                        'token' => $order->groupOrder->token,
                        'status' => $order->groupOrder->status,
                        'members_count' => $order->groupOrder->members->count(),
                        'expires_at' => $order->groupOrder->expires_at,
                    ],
                    'items' => $order->orderItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'book' => [
                                'id' => $item->book->id,
                                'title' => $item->book->title,
                                'image' => $item->book->image,
                                'author' => $item->book->author ? $item->book->author->name : null,
                            ]
                        ];
                    }),
                    'total_items' => $order->orderItems->sum('quantity')
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách đơn hàng nhóm thành công',
                'data' => [
                    'orders' => $formattedOrders,
                    'pagination' => [
                        'current_page' => $orders->currentPage(),
                        'per_page' => $orders->perPage(),
                        'total' => $orders->total(),
                        'last_page' => $orders->lastPage(),
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

    // Helper methods (giữ nguyên logic từ OrderController)
    private function validateStatusChange($oldStatus, $newStatus): array
    {
        $finalStatuses = ['delivered', 'cancelled'];
        
        if (in_array($oldStatus, $finalStatuses)) {
            return [
                'allowed' => false,
                'message' => 'Không thể thay đổi trạng thái của đơn hàng đã hoàn thành hoặc đã hủy'
            ];
        }

        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['ready_to_pick', 'cancelled'],
            'ready_to_pick' => ['picking', 'cancelled'],
            'picking' => ['picked', 'cancelled'],
            'picked' => ['delivering', 'cancelled'],
            'delivering' => ['delivered', 'cancelled'],
            'delivered' => [],
            'cancelled' => []
        ];

        if (!isset($allowedTransitions[$oldStatus]) || !in_array($newStatus, $allowedTransitions[$oldStatus])) {
            return [
                'allowed' => false,
                'message' => "Không thể chuyển từ trạng thái '{$this->validStatuses[$oldStatus]}' sang '{$this->validStatuses[$newStatus]}'"
            ];
        }

        return ['allowed' => true, 'message' => ''];
    }

    private function mapOrderStatusToGroupOrder($orderStatus): string
    {
        $mapping = [
            'pending' => 'pending',
            'confirmed' => 'confirmed',
            'ready_to_pick' => 'processing',
            'picking' => 'processing',
            'picked' => 'processing',
            'delivering' => 'processing',
            'delivered' => 'completed',
            'cancelled' => 'cancelled'
        ];

        return $mapping[$orderStatus] ?? $orderStatus;
    }

    private function handleGroupOrderStatusChangeLogic($order, $oldStatus, $newStatus): void
    {
        try {
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                $this->restoreStock($order);
                Log::info('Stock restored for cancelled group order', [
                    'order_id' => $order->id,
                    'group_order_id' => $order->group_order_id
                ]);
            }

            if ($newStatus === 'delivered') {
                Log::info('Group order delivered successfully', [
                    'order_id' => $order->id,
                    'group_order_id' => $order->group_order_id,
                    'delivered_at' => now()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in group order status change logic', [
                'order_id' => $order->id,
                'group_order_id' => $order->group_order_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function restoreStock($order): void
    {
        foreach ($order->orderItems as $item) {
            $book = $item->book;
            if ($book) {
                $book->increment('stock', $item->quantity);
                Log::info('Stock restored for group order', [
                    'book_id' => $book->id,
                    'quantity_restored' => $item->quantity,
                    'new_stock' => $book->stock
                ]);
            }
        }
    }

    private function generateOrderCode(): string
    {
        $today = now();
        $datePrefix = $today->format('dmY');
        $orderCount = Order::whereDate('created_at', $today->toDateString())->count();
        $sequenceNumber = str_pad($orderCount + 1, 2, '0', STR_PAD_LEFT);
        return 'GRP' . $datePrefix . $sequenceNumber; // Thêm prefix GRP để phân biệt group order
    }

    private function sendGroupOrderConfirmationEmails($order, $groupOrder)
    {
        try {
            // Gửi email cho tất cả members trong group
            foreach ($groupOrder->members as $member) {
                if ($member->user && $member->user->email) {
                    try {
                        Mail::to($member->user->email)->send(new GroupOrderConfirmationMail($order, $groupOrder, $member));
                        Log::info('Group order confirmation email sent', [
                            'order_id' => $order->id,
                            'member_id' => $member->id,
                            'user_email' => $member->user->email
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send group order email to member', [
                            'member_id' => $member->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error sending group order confirmation emails', [
                'order_id' => $order->id,
                'group_order_id' => $groupOrder->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function createGHNShippingOrder($order, $request = null)
    {
        // Giữ nguyên logic GHN từ OrderController
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
                'note' => $order->note ?? 'Đơn hàng nhóm - Group Order',
                'required_note' => 'KHONGCHOXEMHANG',
                'to_name' => $customerName,
                'to_phone' => $customerPhone,
                'to_address' => $order->address,
                'to_ward_code' => (string) $order->ward_id,
                'to_district_id' => $order->district_id,
                'cod_amount' => $order->payment === 'cod' ? (int) $order->price : 0,
                'content' => 'Sách - Đơn hàng nhóm',
                'weight' => array_sum(array_column($items, 'weight')),
                'length' => 25,
                'width' => 20,
                'height' => 10,
                'service_type_id' => 2,
                'items' => $items
            ];

            Log::info("Dữ liệu gửi GHN cho group order:", [
                'order_id' => $order->id,
                'group_order_id' => $order->group_order_id,
                'data' => $orderData
            ]);

            $response = Http::withHeaders([
                'Token' => $this->ghnToken,
                'ShopId' => $this->ghnShopId,
                'Content-Type' => 'application/json'
            ])->post($this->ghnApiUrl . '/shipping-order/create', $orderData);

            Log::info("Phản hồi từ GHN cho group order", [
                'order_id' => $order->id,
                'group_order_id' => $order->group_order_id,
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
            Log::error("Lỗi exception GHN cho group order: " . $e->getMessage(), [
                'order_id' => $order->id ?? null,
                'group_order_id' => $order->group_order_id ?? null
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
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
            Log::error('Error cancelling GHN order', [
                'shipping_code' => $shippingCode,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Lấy thông tin chi tiết đơn hàng nhóm
     */
    public function showGroupOrder($orderId): JsonResponse
    {
        try {
            $user = Auth::user();

            $order = Order::with([
                'orderItems.book.author', 
                'orderItems.book.category', 
                'user',
                'groupOrder.members.user'
            ])->find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }

            if (!$order->group_order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đây không phải đơn hàng từ giỏ hàng nhóm'
                ], 400);
            }

            // Kiểm tra quyền truy cập
            $isMember = $order->groupOrder->members()->where('user_id', $user->id)->exists();
            if (!$isMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền xem đơn hàng này'
                ], 403);
            }

            $formattedOrder = [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'user' => [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email
                ],
                'status' => $order->status,
                'status_text' => $this->validStatuses[$order->status],
                'payment' => $order->payment,
                'price' => $order->price,
                'shipping_fee' => $order->shipping_fee,
                'total_price' => $order->total_price,
                'address' => $order->address,
                'phone' => $order->phone,
                'shipping_code' => $order->shipping_code,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'group_order' => [
                    'id' => $order->groupOrder->id,
                    'token' => $order->groupOrder->token,
                    'status' => $order->groupOrder->status,
                    'expires_at' => $order->groupOrder->expires_at,
                    'confirmed_at' => $order->groupOrder->confirmed_at,
                    'members' => $order->groupOrder->members->map(function ($member) {
                        return [
                            'id' => $member->id,
                            'role' => $member->role,
                            'user' => [
                                'id' => $member->user->id,
                                'name' => $member->user->name,
                                'email' => $member->user->email
                            ]
                        ];
                    })
                ],
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
                'message' => 'Lấy chi tiết đơn hàng nhóm thành công',
                'data' => $formattedOrder
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thống kê đơn hàng nhóm
     */
    public function getGroupOrderStats(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Thống kê cho user hiện tại
            $userStats = [
                'total_group_orders' => Order::whereNotNull('group_order_id')
                    ->whereHas('groupOrder.members', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })->count(),
                'pending_group_orders' => Order::whereNotNull('group_order_id')
                    ->where('status', 'pending')
                    ->whereHas('groupOrder.members', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })->count(),
                'processing_group_orders' => Order::whereNotNull('group_order_id')
                    ->whereIn('status', ['confirmed', 'ready_to_pick', 'picking', 'picked', 'delivering'])
                    ->whereHas('groupOrder.members', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })->count(),
                'delivered_group_orders' => Order::whereNotNull('group_order_id')
                    ->where('status', 'delivered')
                    ->whereHas('groupOrder.members', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })->count(),
                'cancelled_group_orders' => Order::whereNotNull('group_order_id')
                    ->where('status', 'cancelled')
                    ->whereHas('groupOrder.members', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })->count()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Lấy thống kê đơn hàng nhóm thành công',
                'data' => $userStats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thông tin shipping từ GHN cho group order
     */
    public function getGroupOrderShippingInfo($orderId): JsonResponse
    {
        try {
            $order = Order::with('groupOrder')->find($orderId);

            if (!$order || !$order->group_order_id || !$order->shipping_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy mã vận đơn cho đơn hàng nhóm'
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
                    'message' => 'Lấy thông tin vận chuyển đơn hàng nhóm thành công',
                    'data' => [
                        'order_id' => $order->id,
                        'group_order_id' => $order->group_order_id,
                        'shipping_info' => $responseData['data']
                    ]
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
