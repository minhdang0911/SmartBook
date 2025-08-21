<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sonha',
        'street',
        'district_id',
        'ward_id',
        'note',
        'ward_name',
        'district_name',
        'payment',
        'status',
        'price',
        'shipping_fee',
        'total_price',
        'address',
        'order_code',
        'phone',
        'shipping_code'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING      = 'pending';
    const STATUS_CONFIRMED    = 'confirmed';
    const STATUS_PROCESSING   = 'processing';
    const STATUS_READY_TO_PICK= 'ready_to_pick';
    const STATUS_SHIPPING     = 'shipping';
    const STATUS_DELIVERED    = 'delivered';
    const STATUS_CANCELLED    = 'cancelled';

    // Payment constants
    const PAYMENT_COD          = 'cod';
    const PAYMENT_BANK_TRANSFER= 'bank_transfer';
    const PAYMENT_CREDIT_CARD  = 'credit_card';

    /** Scope: chỉ đơn đã giao thành công */
    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_PENDING => 'Chờ xác nhận',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_READY_TO_PICK => 'Sẵn sàng lấy hàng',
            self::STATUS_SHIPPING => 'Đang giao hàng',
            self::STATUS_DELIVERED => 'Đã giao hàng',
            self::STATUS_CANCELLED => 'Đã hủy',
        ];

        return $labels[$this->status] ?? 'Không xác định';
    }

    public function getPaymentLabelAttribute(): string
    {
        $labels = [
            self::PAYMENT_COD => 'Thanh toán khi nhận hàng',
            self::PAYMENT_BANK_TRANSFER => 'Chuyển khoản ngân hàng',
            self::PAYMENT_CREDIT_CARD => 'Thẻ tín dụng',
        ];

        return $labels[$this->payment] ?? 'Không xác định';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function getTotalQuantityAttribute(): int
    {
        return $this->orderItems->sum('quantity');
    }

    public function hasShippingCode(): bool
    {
        return !empty($this->shipping_code);
    }
}
