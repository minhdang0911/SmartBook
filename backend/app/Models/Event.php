<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $primaryKey = 'event_id';

    protected $fillable = [
        'event_name', 'start_date', 'end_date', 'status'
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class, 'event_products', 'event_id', 'books_id')
                    ->withPivot('discount_percent', 'quantity_limit', 'sold_quantity');
    }

    /**
     * Thêm hoặc cập nhật sản phẩm trong sự kiện.
     *
     * @param int $bookId
     * @param float $discountPercent
     * @param int $quantityLimit
     * @param int $soldQuantity
     * @return void
     */
    public function addOrUpdateBook($bookId, $discountPercent, $quantityLimit, $soldQuantity = 0)
    {
        $this->books()->syncWithoutDetaching([
            $bookId => [
                'discount_percent' => $discountPercent,
                'quantity_limit' => $quantityLimit,
                'sold_quantity' => $soldQuantity
            ]
        ]);
    }
}
