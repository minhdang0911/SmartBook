<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Event extends Model
{
    protected $primaryKey = 'event_id';

    protected $fillable = [
        'event_name', 'start_date', 'end_date', 'status'
    ];

    public function books() {
        return $this->belongsToMany(Book::class, 'event_products', 'event_id', 'books_id')
                    ->withPivot('discount_percent', 'quantity_limit', 'sold_quantity');
    }
}
