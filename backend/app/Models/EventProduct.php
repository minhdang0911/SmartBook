<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventProduct extends Model
{
    public $timestamps = false;

    protected $table = 'event_products';

    protected $fillable = [
        'event_id',
        'books_id',
        'quantity_limit',
        'sold_quantity'
        // Bỏ discount_percent vì giá giảm sẽ lưu trong bảng books
    ];

    protected $casts = [
        'quantity_limit' => 'integer',
        'sold_quantity' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'books_id', 'id');
    }

    /**
     * Kiểm tra còn số lượng không
     */
    public function hasStock()
    {
        return $this->sold_quantity < $this->quantity_limit;
    }

    /**
     * Lấy số lượng còn lại
     */
    public function getRemainingQuantityAttribute()
    {
        return max(0, $this->quantity_limit - $this->sold_quantity);
    }
}