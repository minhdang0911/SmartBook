<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $primaryKey = 'event_id';

    protected $fillable = [
        'event_name', 'start_date', 'end_date', 'status'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function books()
    {
        // pivot chuẩn là book_id, không phải books_id
      return $this->belongsToMany(Book::class, 'event_products', 'event_id', 'books_id')
        ->withPivot('quantity_limit', 'sold_quantity');
    }

    /**
     * Thêm sách vào event (lưu quantity ở pivot)
     */
    public function addBook($bookId, $quantityLimit, $soldQuantity = 0)
    {
        $this->books()->syncWithoutDetaching([
            $bookId => [
                'quantity_limit' => $quantityLimit,
                'sold_quantity'  => $soldQuantity,
            ]
        ]);
    }

    /**
     * Cập nhật số lượng đã bán
     */
    public function updateSoldQuantity($bookId, $quantity)
    {
        $this->books()->updateExistingPivot($bookId, [
            'sold_quantity' => $quantity
        ]);
    }

    /**
     * Kiểm tra event active
     */
    public function isActive()
    {
        return $this->status === 'active' &&
            now()->between($this->start_date, $this->end_date);
    }

    /**
     * Tổng số sản phẩm trong event
     */
    public function getTotalProductsAttribute()
    {
        return $this->books()->count();
    }

    /**
     * Tổng số lượng đã bán
     */
    public function getTotalSoldAttribute()
    {
        return $this->books()->sum('event_products.sold_quantity');
    }
}
