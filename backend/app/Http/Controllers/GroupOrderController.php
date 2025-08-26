<?php

namespace App\Http\Controllers;

use App\Models\{
    GroupOrder,
    GroupOrderMember,
    GroupOrderItem,
    GroupOrderSettlement,
    Book,
    Order,
    OrderItem,
    User
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\GroupOrderPayment;
use App\Mail\GroupPaymentLinkMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;


class GroupOrderController extends Controller
{
    /**
     * Tạo phòng (BẮT BUỘC JWT)
     * - Set user.is_group_cart = true
     */
    public function store(Request $req)
    {
        $user = $req->user();

        $req->validate([
            'allow_guest' => 'boolean',
            'expires_hours' => 'nullable|integer|min:1|max:72',
            'shipping_rule' => 'nullable|in:equal,by_value,owner_only'
        ]);

        $group = GroupOrder::create([
            'owner_user_id' => $user->id,
            'join_token' => Str::ulid(),
            'allow_guest' => (bool) $req->boolean('allow_guest'), // route đã bắt buộc JWT nên guest cũng khỏi vào
            'shipping_rule' => $req->input('shipping_rule', 'equal'),
            'expires_at' => now()->addHours($req->input('expires_hours', 6)),
        ]);

        $group->members()->create([
            'user_id' => $user->id,
            'display_name' => $user->name,
            'role' => 'owner',
        ]);

        // ✅ Đánh dấu user đang ở group cart
        $user->forceFill(['is_group_cart' => true])->save();

        return response()->json([
            'join_url' => $group->join_url, // -> http://localhost:3000/go/{token}
            'group' => $group,
        ], 201);
    }

    /**
     * Join bằng link (BẮT BUỘC JWT)
     * - Set user.is_group_cart = true
     */
    public function join(Request $req, string $token)
    {
        $user = $req->user(); // đảm bảo có nhờ middleware

        $group = GroupOrder::open()
            ->where('join_token', $token)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->firstOrFail();

        // tạo hoặc lấy member ứng với user hiện tại
        $member = $group->members()->firstOrCreate(
            ['user_id' => $user->id],
            ['display_name' => $user->name, 'role' => 'member']
        );

        // ✅ Đánh dấu user đang ở group cart
        $user->forceFill(['is_group_cart' => true])->save();

        return response()->json([
            'group_id' => $group->id,
            'member_id' => $member->id,
            'status' => $group->status
        ]);
    }

    /**
     * Thêm món (snapshot giá) — KHÔNG nhận member_id, tự map theo JWT đã join
     */
    public function addItem(Request $req, string $token)
    {
        $data = $req->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $group = GroupOrder::open()->where('join_token', $token)->firstOrFail();

        // chỉ cho user đã join group này add món
        $member = $group->members()->where('user_id', $req->user()->id)->first();
        if (!$member) {
            return response()->json(['message' => 'Bạn chưa join group này'], 403);
        }

        $book = Book::select('id', 'price', 'discount_price', 'stock', 'is_physical')->findOrFail($data['book_id']);
        $price = $book->discount_price ?? $book->price;

        // Kiểm tra xem item đã tồn tại chưa
        $existingItem = $group->items()
            ->where('member_id', $member->id)
            ->where('book_id', $book->id)
            ->first();

        $totalQuantity = $data['quantity'];
        
        if ($existingItem) {
            $totalQuantity = $existingItem->quantity + $data['quantity'];
        }

        // Kiểm tra tồn kho với tổng số lượng
        if ($book->is_physical && $book->stock < $totalQuantity) {
            return response()->json(['message' => 'Hết hàng hoặc không đủ tồn'], 400);
        }

        if ($existingItem) {
            // Cập nhật số lượng và giá nếu item đã tồn tại
            $existingItem->update([
                'quantity' => $totalQuantity,
                'price_snapshot' => $price, // cập nhật giá mới nhất
            ]);
            $item = $existingItem;
        } else {
            // Tạo item mới nếu chưa tồn tại
            $item = $group->items()->create([
                'member_id' => $member->id,
                'book_id' => $book->id,
                'quantity' => $data['quantity'],
                'price_snapshot' => $price,
            ]);
        }

        // load thêm cover_image luôn
        return response()->json(
            $item->load('book:id,title,cover_image'),
            201
        );
    }

    /**
     * Xoá món — owner hoặc chính chủ item (đều phải JWT)
     */
    public function removeItem(Request $req, string $token, $id)
    {
        \Log::info('removeItem called', [
            'token' => $token,
            'id' => $id,
            'id_type' => gettype($id),
            'user_id' => $req->user()->id ?? 'no user'
        ]);

        try {
            $group = GroupOrder::open()->where('join_token', $token)->firstOrFail();
            \Log::info('Group found', ['group_id' => $group->id]);

            $item = $group->items()->with('member')->findOrFail($id);
            \Log::info('Item found', ['item_id' => $item->id, 'member_id' => $item->member_id]);

            $isOwner = $group->members()->where('user_id', $req->user()->id)->where('role', 'owner')->exists();
            $isSelf = $item->member->user_id && $item->member->user_id === $req->user()->id;

            \Log::info('Permissions', ['is_owner' => $isOwner, 'is_self' => $isSelf]);

            if (!$isOwner && !$isSelf) {
                abort(403, 'Không có quyền xoá item này');
            }

            $item->delete();
            return response()->noContent();
        } catch (\Exception $e) {
            \Log::error('removeItem error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Khoá phòng (owner)
     */
    public function lock(Request $req, string $token)
    {
        $group = GroupOrder::open()
            ->where('join_token', $token)
            ->firstOrFail();

         

        DB::transaction(function () use ($group) {
            $group->update(['status' => 'locked']);
        });

        return response()->json([
            'success'   => true,
            'message'   => 'Phòng đã được khoá thành công',
            'group_id'  => $group->id,
            'status'    => 'locked',
            'locked_at' => now()->format('d/m/Y H:i:s'),
            // rỗng hết
            'members'   => [],
            'items'     => [],
            'by_member' => [],
            'total'     => 0,
        ]);
    }

    /**
     * Tính chia tiền (owner)
     */
    

    /**
     * Checkout (owner) - Complete Fixed Version with Null Handling
     */
    public function checkout(Request $req, string $token)
    {
        $group = GroupOrder::whereIn('status', ['open', 'locked','checked_out'])
            ->where('join_token', $token)
            ->with(['items.book', 'owner', 'members'])
            ->firstOrFail();
        
        

        $req->validate([
            'payment' => 'nullable|in:cod,bank_transfer,credit_card',
            'shipping_fee' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'sonha' => 'nullable|string|max:50',
            'street' => 'nullable|string|max:100',
            'district_id' => 'nullable|integer',
            'ward_id' => 'nullable|integer',
            'ward_name' => 'nullable|string|max:100',
            'district_name' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:500',
        ]);

        $items = $group->items;
        if ($items->isEmpty()) {
            return response()->json(['message' => 'Phòng trống, checkout gì?'], 400);
        }

        // Kiểm tra owner tồn tại
        $owner = $group->owner;
        if (!$owner) {
            return response()->json(['message' => 'Không tìm thấy chủ phòng'], 400);
        }

        $subtotal = $items->sum(fn($i) => $i->quantity * $i->price_snapshot);
        $shipping = (float) ($req->input('shipping_fee', 0));
        $total = $subtotal + $shipping;
        
        // Xử lý địa chỉ với null safety
        $sonha = $req->input('sonha', $owner->sonha ?? '');
        $street = $req->input('street', $owner->street ?? '');
        $districtId = $req->input('district_id', $owner->district_id ?? null);
        $wardId = $req->input('ward_id', $owner->ward_id ?? null);
        $wardName = $req->input('ward_name', $owner->ward_name ?? '');
        $districtName = $req->input('district_name', $owner->district_name ?? '');
        
        // Ghép địa chỉ đầy đủ
        $fullAddress = $req->input('address');
        if (!$fullAddress && $sonha && $street && $wardName && $districtName) {
            $fullAddress = "Số {$sonha}, {$street}, {$wardName}, {$districtName}";
        } elseif (!$fullAddress) {
            $fullAddress = $owner->address ?? '';
        }
        
        $phone = $req->input('phone', $owner->phone ?? '');
        $payment = $req->input('payment', Order::PAYMENT_COD);

        $createdOrder = null;

        try {
            DB::transaction(function () use (
                $group, $items, $subtotal, $shipping, $total, $owner, 
                $sonha, $street, $districtId, $wardId, $wardName, $districtName,
                $fullAddress, $phone, $payment, $req, &$createdOrder
            ) {
                // Tạo order
                $createdOrder = Order::create([
                    'user_id' => $owner->id,
                    'group_order_id' => $group->id,
                    'order_code' => $this->genOrderCode(),
                    'sonha' => $sonha,
                    'phone' => $phone,
                    'street' => $street,
                    'district_id' => $districtId,
                    'ward_id' => $wardId,
                    'ward_name' => $wardName,
                    'district_name' => $districtName,
                    'payment' => $payment,
                    'status' => Order::STATUS_PENDING,
                    'price' => $subtotal,
                    'shipping_fee' => $shipping,
                    'total_price' => $total,
                    'address' => $fullAddress,
                    'note' => $req->input('note'),
                ]);

                // Kiểm tra và tạo order items
                foreach ($items as $i) {
                    $book = $i->book;
                    if (!$book) {
                        throw new \RuntimeException("Không tìm thấy sách với ID: {$i->book_id}");
                    }

                    // Kiểm tra stock cho sách vật lý
                    if (($book->is_physical ?? false) && $book->stock < $i->quantity) {
                        throw new \RuntimeException("Sách '{$book->title}' không đủ tồn kho. Còn lại: {$book->stock}, yêu cầu: {$i->quantity}");
                    }
                    
                    // Trừ stock cho sách vật lý
                    if (($book->is_physical ?? false)) {
                        $book->decrement('stock', $i->quantity);
                    }

                    // Tạo order item
                    OrderItem::create([
                        'order_id' => $createdOrder->id,
                        'book_id' => $i->book_id,
                        'quantity' => $i->quantity,
                        'price' => $i->price_snapshot,
                    ]);
                }

                // Cập nhật group order
                $group->update([
                    'status' => 'checked_out', 
                    'order_id' => $createdOrder->id,
                    'confirmed_at' => now()
                ]);
            });

            // Kiểm tra order đã được tạo thành công
            if (!$createdOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi tạo đơn hàng - Order null'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Checkout group thành công',
                'data' => [
                    'order_id' => $createdOrder->id,
                    'order_code' => $createdOrder->order_code,
                    'group_order_id' => $group->id,
                    'total_price' => $createdOrder->total_price,
                    'status' => $createdOrder->status,
                    'payment' => $createdOrder->payment,
                    'address' => $createdOrder->address,
                    'phone' => $createdOrder->phone,
                    'shipping_fee' => $createdOrder->shipping_fee,
                    'created_at' => $createdOrder->created_at->format('d/m/Y H:i:s'),
                    'items_count' => $items->count(),
                    'total_quantity' => $items->sum('quantity')
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Group order checkout failed', [
                'group_id' => $group->id,
                'token' => $token,
                'user_id' => $req->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Checkout thất bại: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xem phòng (public)
     */
    public function show(Request $req, string $token)
    {
        $group = GroupOrder::where('join_token', $token)
            ->with([
                'members.user:id,name',
                'items.book' => function ($q) {
                    $q->select('id', 'title', 'price', 'cover_image'); // <-- thêm cover_image
                },
            ])
            ->firstOrFail();

        $byMember = $group->items->groupBy('member_id')->map(function ($list) {
            return [
                'subtotal' => $list->sum(fn($i) => $i->quantity * $i->price_snapshot),
                'items' => $list->map(fn($i) => [
                    'id'          => $i->id,
                    'book_id'     => $i->book_id,
                    'title'       => $i->book->title,
                    'cover_image' => $i->book->cover_image,   // <-- map ra JSON
                    'qty'         => $i->quantity,
                    'price'       => $i->price_snapshot,
                ])->values(),
            ];
        });

        $total = $group->items->sum(fn($i) => $i->quantity * $i->price_snapshot);

        return response()->json([
            'status'     => $group->status,
            'expires_at' => $group->expires_at,
            'join_url'   => $group->join_url,
            'members'    => $group->members->map(fn($m) => [
                'id'      => $m->id,
                'name'    => $m->display_name,
                'role'    => $m->role,
                'user_id' => $m->user_id,
            ])->values(),
            'by_member' => $byMember,
            'total'     => $total,
        ]);
    }

    /**
     * Kick thành viên khỏi phòng (chỉ owner có thể kick, BẮT BUỘC JWT)
     */

    // App\Http\Controllers\GroupOrderController.php

private function findGroupByToken(string $rawToken, ?string $requireStatus = null): GroupOrder
{
    $token = trim(urldecode($rawToken));       // gọt rác + decode nếu FE encode
    // Nếu dùng ULID thì thường uppercase; chuẩn hoá để tránh collation ngáo
    $tokenUp = strtoupper($token);

    // Query case-insensitive (đè mọi collation lạ đời)
    $q = GroupOrder::query()
        ->where(function ($qq) use ($token, $tokenUp) {
            $qq->where('join_token', $token)
               ->orWhere('join_token', $tokenUp)
               ->orWhereRaw('LOWER(join_token) = LOWER(?)', [$token]);
        });

    if ($requireStatus) {
        $q->where('status', $requireStatus);
    }

    $group = $q->first();

    if (!$group) {
        \Log::warning('Group not found by token', [
            'raw'     => $rawToken,
            'trim'    => $token,
            'upper'   => $tokenUp,
            'env'     => config('app.env'),
            'db'      => config('database.connections.'.config('database.default').'.database'),
            // bật lên nếu cần: 'routes' => \Route::currentRouteName(),
        ]);
        abort(404, 'Group not found by token');
    }

    return $group;
}

public function kick(Request $req, string $token, $userId)
{
    // --- chuẩn hoá token ---
    $rawToken = $token;
    $norm = trim(urldecode($rawToken));
    $upper = strtoupper($norm);
    $lower = strtolower($norm);

    // --- THỬ TÌM GROUP THEO 3 CÁCH + ĐẾM ---
    $exact = GroupOrder::where('join_token', $norm)->first();
    $exactUpper = GroupOrder::where('join_token', $upper)->first();
    $loose = GroupOrder::whereRaw('LOWER(join_token) = ?', [$lower])->first();

    $dbName = config('database.connections.'.config('database.default').'.database');
    $env    = config('app.env');

    $group = $exact ?: $exactUpper ?: $loose;

    if (!$group) {
        return response()->json([
            'code'    => 'group_not_found',
            'message' => 'Không tìm thấy phòng theo token.',
            'debug'   => [
                'env'        => $env,
                'db'         => $dbName,
                'token_raw'  => $rawToken,
                'token_norm' => $norm,
                'match'      => [
                    'exact'       => (bool) $exact,
                    'exactUpper'  => (bool) $exactUpper,
                    'looseLower'  => (bool) $loose,
                ],
            ],
        ], 404);
    }

    // --- chỉ cho kick khi phòng đang OPEN ---
    if ($group->status !== 'open') {
        return response()->json([
            'code'    => 'group_not_open',
            'message' => 'Phòng không ở trạng thái open.',
            'status'  => $group->status,
            'debug'   => ['group_id' => $group->id],
        ], 409);
    }

    // --- phải đăng nhập ---
    $actorUser = $req->user();
    if (!$actorUser) {
        return response()->json(['code' => 'unauth', 'message' => 'Chưa đăng nhập.'], 401);
    }

    // --- actor phải là owner ---
    $actor = $group->members()->where('user_id', $actorUser->id)->first();
    if (!$actor || $actor->role !== 'owner') {
        return response()->json([
            'code'    => 'not_owner',
            'message' => 'Chỉ chủ phòng mới có quyền kick thành viên.',
            'debug'   => ['actor_user_id' => $actorUser->id, 'actor_member' => optional($actor)->only(['id','role'])],
        ], 403);
    }

    // --- cấm tự-kick ---
    if ((string)$userId === (string)$actorUser->id) {
        return response()->json([
            'code'    => 'self_kick_forbidden',
            'message' => 'Không thể kick chính mình. Dùng API leave để tự rời phòng.',
        ], 422);
    }

    // --- tìm target theo user_id, fallback member.id ---
    $target = $group->members()
        ->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('id', (int)$userId);
        })
        ->first();

    if (!$target) {
        return response()->json([
            'code'    => 'target_not_in_group',
            'message' => 'Người dùng này không thuộc phòng.',
            'input'   => (string)$userId,
        ], 404);
    }

    DB::transaction(function () use ($group, $target) {
        GroupOrderItem::where('group_order_id', $group->id)
            ->where('member_id', $target->id)
            ->delete();

        GroupOrderSettlement::where('group_order_id', $group->id)
            ->where('member_id', $target->id)
            ->delete();

        $target->delete();
    });

    $this->updateUserGroupCartStatus((int) $target->user_id);

    return response()->json([
        'success'        => true,
        'message'        => 'Đã kick thành viên khỏi phòng.',
        'kicked_user_id' => (int) $target->user_id,
        'members_count'  => $group->members()->count(),
        'group_status'   => $group->fresh()->status,
    ]);
}




    /**
     * Tự rời phòng (BẮT BUỘC JWT)
     */
    public function leave(Request $req, string $token)
    {
        // Chỉ xử lý khi phòng đang mở
        $group = GroupOrder::where('join_token', $token)->firstOrFail();
        if ($group->status !== 'open') {
            return response()->json([
                'message' => "Phòng đang ở trạng thái '{$group->status}', không thể rời phòng."
            ], 409);
        }

        $actorUser = $req->user(); // cần middleware auth
        if (!$actorUser) {
            return response()->json(['message' => 'Chưa đăng nhập.'], 401);
        }

        // Actor phải là member của phòng
        $actor = $group->members()->where('user_id', $actorUser->id)->first();
        if (!$actor) {
            return response()->json(['message' => 'Bạn chưa tham gia phòng này.'], 403);
        }

        // Nếu là owner và vẫn còn thành viên khác thì không được rời
        if ($actor->role === 'owner') {
            $hasOthers = $group->members()->where('user_id', '<>', $actorUser->id)->exists();
            if ($hasOthers) {
                return response()->json([
                    'message' => 'Chủ phòng không thể rời khi vẫn còn thành viên khác. Hãy chuyển quyền chủ hoặc giải tán phòng.'
                ], 422);
            }
        }

        DB::transaction(function () use ($group, $actor) {
            // Xóa item/settlement của member
            GroupOrderItem::where('group_order_id', $group->id)
                ->where('member_id', $actor->id)
                ->delete();

            GroupOrderSettlement::where('group_order_id', $group->id)
                ->where('member_id', $actor->id)
                ->delete();

            // Xóa member
            $actor->delete();
        });

        // ✅ Cập nhật is_group_cart cho user hiện tại
        $this->updateUserGroupCartStatus($actorUser->id);

        // Nếu không còn ai trong phòng ⇒ đóng phòng
        $remainingCount = $group->members()->count();
        if ($remainingCount === 0) {
            $group->update(['status' => 'closed']);
            return response()->json([
                'success' => true,
                'message' => 'Bạn đã rời phòng. Phòng không còn thành viên nên đã được đóng.',
                'group_status' => 'closed',
                'members_count' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bạn đã rời phòng.',
            'members_count' => $remainingCount,
            'group_status' => $group->fresh()->status,
        ]);
    }

    /**
     * Xoá nhiều items cùng lúc (owner hoặc chính chủ)
     */
    public function removeItems(Request $req, string $token)
    {
        $data = $req->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:group_order_items,id',
        ]);

        $group = GroupOrder::open()->where('join_token', $token)->firstOrFail();

        $items = $group->items()->with('member')->whereIn('id', $data['ids'])->get();

        $userId = $req->user()->id;
        $isOwner = $group->members()->where('user_id', $userId)->where('role', 'owner')->exists();

        foreach ($items as $item) {
            $isSelf = $item->member && $item->member->user_id === $userId;
            if (!$isOwner && !$isSelf) {
                return response()->json(['message' => 'Không có quyền xoá 1 số item'], 403);
            }
        }

        GroupOrderItem::whereIn('id', $data['ids'])->delete();

        return response()->json(['success' => true, 'deleted' => $data['ids']]);
    }

    /**
     * Update số lượng item (tăng/giảm)
     */
    public function updateItemQuantity(Request $req, string $token, int $id)
    {
        $data = $req->validate([
            'quantity' => 'required|integer', // cho phép âm hoặc dương
        ]);

        $group = GroupOrder::open()->where('join_token', $token)->firstOrFail();

        $item = $group->items()->with('member', 'book')->findOrFail($id);

        $userId = $req->user()->id;
        $isOwner = $group->members()->where('user_id', $userId)->where('role', 'owner')->exists();
        $isSelf = $item->member && $item->member->user_id === $userId;

        if (!$isOwner && !$isSelf) {
            return response()->json(['message' => 'Không có quyền chỉnh số lượng item này'], 403);
        }

        // tính số lượng mới
        $newQty = $item->quantity + $data['quantity'];

        if ($newQty < 1) {
            return response()->json(['message' => 'Số lượng phải >= 1'], 400);
        }

        // Check tồn kho nếu là physical
        if ($item->book->is_physical && $item->book->stock < $newQty) {
            return response()->json(['message' => 'Không đủ tồn kho'], 400);
        }

        $item->update(['quantity' => $newQty]);

        return response()->json([
            'success' => true,
            'item' => $item->fresh(['book:id,title,price'])
        ]);
    }

    /* ================== Helpers ================== */

    // private function assertOwner(GroupOrder $group, ?User $user): void
    // {
    //     $owner = $group->members()->where('role', 'owner')->first();
    //     if (!$user || !$owner || $owner->user_id !== $user->id) {
    //         abort(403, 'Không phải chủ phòng.');
    //     }
    // }

    private function genOrderCode(): string
    {
        $today = now();
        $datePrefix = $today->format('dmY');
        $count = Order::whereDate('created_at', $today->toDateString())->count();
        return $datePrefix . str_pad($count + 1, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Cập nhật trạng thái is_group_cart cho user
     */
    private function updateUserGroupCartStatus(int $userId): void
    {
        // Nếu user không còn ở bất kỳ phòng OPEN nào nữa => set false
        $stillInAnyOpen = GroupOrder::where('status', 'open')
            ->whereHas('members', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->exists();

        if (!$stillInAnyOpen) {
            User::where('id', $userId)->update(['is_group_cart' => false]);
        }
    }

    public function recalc(Request $req, string $token)
{
    $group = GroupOrder::where('join_token', $token)
        ->where('status', 'open')->with(['items', 'members'])->firstOrFail();

    $byMember = $group->items->groupBy('member_id');
    $subtotal = $group->items->sum(fn($i) => $i->quantity * $i->price_snapshot);

    $shipping = (int) round((float) $req->input('shipping_fee', 0));
    $count = max(1, $group->members->count());

    // chia đều, phần lẻ dồn lên đầu để tổng khớp
    $base = intdiv($shipping, $count);
    $rem  = $shipping % $count;

    foreach ($group->members as $idx => $m) {
        $mSubtotal = ($byMember[$m->id] ?? collect())
            ->sum(fn($i) => $i->quantity * $i->price_snapshot);
        $share = $base + ($idx < $rem ? 1 : 0);
        $amount = (int) ($mSubtotal + $share);

        GroupOrderSettlement::updateOrCreate(
            ['group_order_id' => $group->id, 'member_id' => $m->id],
            ['amount_due' => $amount]
        );
    }

    return response()->json([
        'subtotal'    => $subtotal,
        'shipping'    => $shipping,
        'total'       => $subtotal + $shipping,
        'settlements' => $group->settlements()->with('member:id,display_name,role,user_id')->get(),
    ]);
}

public function createPayLinks(Request $req, string $token)
{
    $req->validate([
        'gateway' => 'required|in:momo,vnpay',
        'subject' => 'nullable|string|max:120',
        'message' => 'nullable|string|max:1000',
    ]);

    $group = GroupOrder::where('join_token', $token)
        ->where('status', 'open')
        ->with(['members.user', 'settlements'])
        ->firstOrFail();

    $gateway = $req->input('gateway');
    $subject = $req->input('subject', 'Thanh toán nhóm');
    $extraMsg = $req->input('message');

    $links = [];

    DB::transaction(function () use ($group, $gateway, $subject, $extraMsg, &$links) {
        foreach ($group->members as $m) {
            $settle = $group->settlements->firstWhere('member_id', $m->id);
            $amount = (int) ($settle->amount_due ?? 0);
            if ($amount <= 0) continue;

            $payment = GroupOrderPayment::updateOrCreate(
                ['group_order_id' => $group->id, 'member_id' => $m->id],
                ['gateway' => $gateway, 'amount' => $amount, 'status' => 'pending']
            );

            if ($gateway === 'momo') {
                $payload = $this->momoCreatePayment(
                    orderId: 'GO-'.$group->id.'-M'.$m->id.'-'.time(),
                    amount: $amount,
                    orderInfo: "Thanh toán nhóm #{$group->id} - {$m->display_name}"
                );
                $payment->update([
                    'provider_txn_id' => $payload['orderId'] ?? null,
                    'pay_url'         => $payload['payUrl'] ?? null,
                    'meta'            => $payload,
                ]);
            } else {
                $payload = $this->vnpayCreatePayment(
                    txnRef: 'GO'.$group->id.'M'.$m->id.time(),
                    amount: $amount,
                    orderInfo: "Thanh toán nhóm #{$group->id} - {$m->display_name}"
                );
                $payment->update([
                    'provider_txn_id' => $payload['vnp_TxnRef'] ?? null,
                    'pay_url'         => $payload['payUrl'] ?? null,
                    'meta'            => $payload,
                ]);
            }

            $links[] = [
                'member_id'   => $m->id,
                'member_name' => $m->display_name,
                'email'       => optional($m->user)->email,
                'amount'      => $amount,
                'gateway'     => $gateway,
                'pay_url'     => $payment->pay_url,
            ];

            // gửi mail (nếu có email)
            if ($m->user && $m->user->email && $payment->pay_url) {
                try {
                    Mail::to($m->user->email)->send(new GroupPaymentLinkMail(
                        subject: $subject,
                        memberName: $m->display_name,
                        amount: $amount,
                        payUrl: $payment->pay_url,
                        extraMsg: $extraMsg
                    ));
                    $payment->update(['email_sent_at' => now()]);
                } catch (\Throwable $e) {
                    \Log::warning('Send mail failed', ['member_id' => $m->id, 'err' => $e->getMessage()]);
                }
            }
        }
    });

    return response()->json(['success' => true, 'gateway' => $gateway, 'links' => $links]);
}

private function momoCreatePayment(string $orderId, int $amount, string $orderInfo): array
{
    // 🔥 gán cứng sandbox MoMo
    $hostname     = 'test-payment.momo.vn';
    $endpointPath = '/v2/gateway/api/create';
    $partnerCode  = 'MOMO';
    $accessKey    = 'F8BBA842ECF85';
    $secretKey    = 'K951B6PE1waDMi640xX08PD3vg6EkVlz';
    $redirectUrl  = 'http://localhost:8000/api/group-orders/payments/momo/return';
    $ipnUrl       = 'http://localhost:8000/api/group-orders/payments/momo/ipn';
    $requestType  = 'payWithMethod'; // mặc định hỗ trợ cả VISA/Master

    $endpoint = 'https://' . $hostname . $endpointPath;

    $data = [
        'partnerCode' => $partnerCode,
        'partnerName' => 'SmartBook',
        'storeId'     => 'SmartBookStore',
        'requestId'   => $orderId,
        'amount'      => (string) $amount,
        'orderId'     => $orderId,
        'orderInfo'   => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl'      => $ipnUrl,
        'lang'        => 'vi',
        'requestType' => $requestType,
        'extraData'   => base64_encode(json_encode(['type' => 'group'])),
    ];

    // 🔑 ký chữ ký
    $raw = "accessKey={$accessKey}&amount={$data['amount']}&extraData={$data['extraData']}&ipnUrl={$data['ipnUrl']}&orderId={$data['orderId']}&orderInfo={$data['orderInfo']}&partnerCode={$partnerCode}&redirectUrl={$data['redirectUrl']}&requestId={$data['requestId']}&requestType={$data['requestType']}";
    $data['signature'] = hash_hmac('sha256', $raw, $secretKey);

    try {
        $res = Http::acceptJson()->post($endpoint, $data);
        $json = $res->json() ?? [];

        $payUrl = $json['payUrl'] ?? $json['deeplink'] ?? null;

        if (!$payUrl) {
            Log::warning('MoMo no payUrl', [
                'status' => $res->status(),
                'body'   => $json,
            ]);
        }

        return [
            'orderId'     => $orderId,
            'payUrl'      => $payUrl,
            'raw'         => $json,
            'http_status' => $res->status(),
        ];
    } catch (\Throwable $e) {
        Log::error('MoMo request failed', ['err' => $e->getMessage()]);
        return [
            'orderId'     => $orderId,
            'payUrl'      => null,
            'raw'         => ['error' => $e->getMessage()],
            'http_status' => 0,
        ];
    }
}



private function vnpayCreatePayment(string $txnRef, int $amount, string $orderInfo): array
{
    $cfg = config('gateways.vnpay');

    $input = [
        "vnp_Version"    => "2.1.0",
        "vnp_TmnCode"    => $cfg['tmn_code'],
        "vnp_Amount"     => $amount * 100,
        "vnp_Command"    => "pay",
        "vnp_CreateDate" => now()->format('YmdHis'),
        "vnp_CurrCode"   => "VND",
        "vnp_IpAddr"     => request()->ip(),
        "vnp_Locale"     => "vn",
        "vnp_OrderInfo"  => $orderInfo,
        "vnp_OrderType"  => "other",
        "vnp_ReturnUrl"  => $cfg['return_url'], // nên trỏ BE
        "vnp_TxnRef"     => $txnRef,
    ];

    ksort($input);
    $query = [];
    $hashdata = [];
    foreach ($input as $k => $v) {
        $query[]    = urlencode($k) . "=" . urlencode($v);
        $hashdata[] = urlencode($k) . "=" . urlencode($v);
    }
    $hashdata = implode('&', $hashdata);
    $secure   = hash_hmac('sha512', $hashdata, $cfg['hash_secret']);
    $payUrl   = $cfg['url'] . "?" . implode('&', $query) . "&vnp_SecureHash=" . $secure;

    return ['vnp_TxnRef' => $txnRef, 'payUrl' => $payUrl];
}
public function momoIpn(Request $req)
{
    $orderId    = $req->input('orderId');
    $resultCode = (int) $req->input('resultCode', -1);

    $pay = GroupOrderPayment::where('provider_txn_id', $orderId)->first();
    if (!$pay) return response('not found', 404);

    if ($resultCode === 0) {
        $pay->update(['status' => 'paid', 'paid_at' => now()]);
    } else {
        $pay->update(['status' => 'failed']);
    }

    $this->tryFinalizeGroupOrder($pay->group_order_id);
    return response('ok');
}

public function vnpayReturn(Request $req)
{
    $txnRef = $req->input('vnp_TxnRef');
    $code   = $req->input('vnp_ResponseCode'); // '00' là ok

    $pay = GroupOrderPayment::where('provider_txn_id', $txnRef)->first();
    if (!$pay) return response()->json(['message' => 'not found'], 404);

    if ($code === '00') {
        $pay->update(['status' => 'paid', 'paid_at' => now()]);
    } else {
        $pay->update(['status' => 'failed'] );
    }

    $this->tryFinalizeGroupOrder($pay->group_order_id);
    return response()->json(['success' => $code === '00']);
}

/** đủ tiền -> tạo Order 1 lần */
private function tryFinalizeGroupOrder(int $groupId): void
{
    $group = GroupOrder::with(['items.book','owner','members','settlements'])->find($groupId);
    if (!$group || $group->status === 'checked_out') return;

    $payments = GroupOrderPayment::where('group_order_id', $groupId)->get();

    // tất cả member có settlement > 0 phải paid
    foreach ($group->settlements as $s) {
        if (($s->amount_due ?? 0) <= 0) continue;
        $p = $payments->firstWhere('member_id', $s->member_id);
        if (!$p || $p->status !== 'paid') return; // còn thiếu thằng nào chưa trả
    }

    DB::transaction(function () use ($group) {
        $items = $group->items;
        if ($items->isEmpty()) throw new \RuntimeException('Empty items');

        $owner = $group->owner;
        if (!$owner) throw new \RuntimeException('Owner not found');

        $subtotal = $items->sum(fn($i) => $i->quantity * $i->price_snapshot);
        $totalSettled = $group->settlements->sum('amount_due');
        $shipping = max(0, $totalSettled - $subtotal);
        $total = $subtotal + $shipping;

        $order = Order::create([
            'user_id'       => $owner->id,
            'group_order_id'=> $group->id,
            'order_code'    => $this->genOrderCode(),
            'payment'       => Order::PAYMENT_COD, // hoặc 'prepaid' tuỳ enum của m
            'status'        => Order::STATUS_PENDING,
            'price'         => $subtotal,
            'shipping_fee'  => $shipping,
            'total_price'   => $total,
            'address'       => $owner->address ?? '',
            'phone'         => $owner->phone ?? '',
        ]);

        foreach ($items as $i) {
            $book = $i->book;
            if (($book->is_physical ?? false) && $book->stock < $i->quantity) {
                throw new \RuntimeException("Kho không đủ cho {$book->title}");
            }
            if (($book->is_physical ?? false)) $book->decrement('stock', $i->quantity);

            OrderItem::create([
                'order_id' => $order->id,
                'book_id'  => $i->book_id,
                'quantity' => $i->quantity,
                'price'    => $i->price_snapshot,
            ]);
        }

        $group->update([
            'status'       => 'checked_out',
            'order_id'     => $order->id,
            'confirmed_at' => now(),
        ]);
    });
}



}