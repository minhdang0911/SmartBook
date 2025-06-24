<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'book_id', 'quantity', 'price'];

    public $timestamps = false;

    // 👉 Quan hệ với Book
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
