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

    $this->assertOwner($group, $req->user());

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
    public function recalc(Request $req, string $token)
    {
        $group = GroupOrder::where('join_token', $token)
            ->where('status', 'locked')->with(['items', 'members'])->firstOrFail();
        $this->assertOwner($group, $req->user());

        $byMember = $group->items->groupBy('member_id');
        $subtotal = $group->items->sum(fn($i) => $i->quantity * $i->price_snapshot);
        $shipping = (float) ($req->input('shipping_fee', 0));
        $rule = $group->shipping_rule;

        foreach ($group->members as $m) {
            $mSubtotal = ($byMember[$m->id] ?? collect())->sum(fn($i) => $i->quantity * $i->price_snapshot);
            $shipShare = match ($rule) {
                'equal' => count($group->members) ? $shipping / max(1, count($group->members)) : 0,
                'by_value' => $subtotal ? $shipping * ($mSubtotal / $subtotal) : 0,
                default => ($m->role === 'owner' ? $shipping : 0),
            };
            GroupOrderSettlement::updateOrCreate(
                ['group_order_id' => $group->id, 'member_id' => $m->id],
                ['amount_due' => round($mSubtotal + $shipShare, 2)]
            );
        }

        return response()->json([
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
            'settlements' => $group->settlements()->with('member:id,display_name,role')->get()
        ]);
    }

    /**
     * Checkout (owner)
     */
  /**
 * Checkout (owner) - FIXED VERSION
 */
/**
 * Checkout (owner) - Complete Fixed Version
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
    
    $this->assertOwner($group, $req->user());

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
     * Kick hoặc tự rời theo USER ID (BẮT BUỘC JWT)
     * - Param ưu tiên: {userId?}, fallback body 'user_id', cuối cùng là chính actor (self-leave)
     * - Khi rời/kick: set target_user.is_group_cart = false (nếu không còn ở phòng open nào khác)
     */
    public function kickOrLeaveByUser(Request $req, string $token, ?int $targetUserId = null)
    {
        // Chỉ xử lý khi phòng đang mở
        $group = GroupOrder::where('join_token', $token)->firstOrFail();
        if ($group->status !== 'open') {
            return response()->json([
                'message' => "Phòng đang ở trạng thái '{$group->status}', không thể rời/xóa thành viên."
            ], 409);
        }

        // Actor: lấy từ JWT
        $actorUser = $req->user(); // cần middleware auth
        if (!$actorUser) {
            return response()->json(['message' => 'Chưa đăng nhập.'], 401);
        }

        // Actor phải là member của phòng
        $actor = $group->members()->where('user_id', $actorUser->id)->first();
        if (!$actor) {
            return response()->json(['message' => 'Bạn chưa tham gia phòng này.'], 403);
        }

        // Xác định target_user_id
        $targetUserId = $targetUserId
            ?: ($req->filled('user_id') ? (int) $req->input('user_id') : $actorUser->id);

        // Tìm member ứng với target_user_id trong phòng
        $target = $group->members()->where('user_id', $targetUserId)->first();
        if (!$target) {
            return response()->json(['message' => 'Người dùng này không thuộc phòng.'], 404);
        }

        $isSelf = ($targetUserId === $actorUser->id);
        $isOwnerActor = ($actor->role === 'owner');
        $isOwnerTarget = ($target->role === 'owner');

        // Kick người khác => chỉ chủ phòng
        if (!$isSelf && !$isOwnerActor) {
            return response()->json(['message' => 'Chỉ chủ phòng mới có quyền xóa thành viên khác.'], 403);
        }

        // Chủ phòng tự rời: chỉ khi không còn ai khác trong phòng
        if ($isSelf && $isOwnerTarget) {
            $hasOthers = $group->members()->where('user_id', '<>', $actorUser->id)->exists();
            if ($hasOthers) {
                return response()->json([
                    'message' => 'Chủ phòng không thể rời khi vẫn còn thành viên khác. Hãy chuyển quyền chủ hoặc giải tán phòng.'
                ], 422);
            }
            // nếu không còn ai khác, cho phép rời ⇒ sẽ đóng phòng sau khi xóa
        }

        DB::transaction(function () use ($group, $target) {
            // Xóa item/settlement của member target
            GroupOrderItem::where('group_order_id', $group->id)
                ->where('member_id', $target->id)
                ->delete();

            GroupOrderSettlement::where('group_order_id', $group->id)
                ->where('member_id', $target->id)
                ->delete();

            // Xóa member
            $target->delete();
        });

        // ✅ Cập nhật is_group_cart cho target user:
        // Nếu user không còn ở bất kỳ phòng OPEN nào nữa => set false
        $stillInAnyOpen = GroupOrder::where('status', 'open')
            ->whereHas('members', function ($q) use ($targetUserId) {
                $q->where('user_id', $targetUserId);
            })->exists();

        if (!$stillInAnyOpen) {
            User::where('id', $targetUserId)->update(['is_group_cart' => false]);
        }

        // Nếu không còn ai trong phòng ⇒ đóng phòng
        $remain = $group->members()->count();
        if ($remain === 0) {
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
            'message' => $isSelf ? 'Bạn đã rời phòng.' : 'Đã xóa thành viên khỏi phòng.',
            'removed_user_id' => $targetUserId,
            'members_count' => $remain,
            'group_status' => $group->fresh()->status,
        ]);
    }

    /* ================== Helpers ================== */

    private function assertOwner(GroupOrder $group, ?User $user): void
    {
        $owner = $group->members()->where('role', 'owner')->first();
        if (!$user || !$owner || $owner->user_id !== $user->id) {
            abort(403, 'Không phải chủ phòng.');
        }
    }

    private function genOrderCode(): string
    {
        $today = now();
        $datePrefix = $today->format('dmY');
        $count = Order::whereDate('created_at', $today->toDateString())->count();
        return $datePrefix . str_pad($count + 1, 2, '0', STR_PAD_LEFT);
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

}
