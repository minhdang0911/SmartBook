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
        'discount_percent',
        'quantity_limit',
        'sold_quantity'
    ];
}
