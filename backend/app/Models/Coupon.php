<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'name', 'description', 'discount_type', 'discount_value', 'scope',
        'min_order_value', 'usage_limit', 'is_active', 'start_date', 'end_date'
    ];

    public function books() {
        return $this->belongsToMany(Book::class, 'coupon_books');
    }

    public function categories() {
        return $this->belongsToMany(Category::class, 'coupon_categories');
    }

    public function orders() {
        return $this->belongsToMany(Order::class, 'order_coupon')
                    ->withPivot('discount_amount');
    }
}
