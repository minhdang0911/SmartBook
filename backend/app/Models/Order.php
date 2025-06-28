<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sonha',
        'street',
        'district_id',
        'ward_id',
        'district_name',
        'ward_name',
        'card_id',
        'payment',
        'status',
        'price',
        'shipping_fee',
        'total_price',
        'address',
        'created_at',
        'note',
        'shipping_code',
        'phone'
    ];

    public $timestamps = false; // Vì bạn đang dùng $table->timestamp('created_at')->useCurrent();

    // Quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Quan hệ với OrderItems
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

}
