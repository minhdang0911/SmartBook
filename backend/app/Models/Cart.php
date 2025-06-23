<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // Tính tổng tiền giỏ hàng
    public function calculateTotal()
    {
        $total = $this->cartItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });
        
        $this->total_amount = $total;
        $this->save();
        
        return $total;
    }

    // Đếm tổng số sản phẩm trong giỏ
    public function getTotalItemsAttribute()
    {
        return $this->cartItems->sum('quantity');
    }
}