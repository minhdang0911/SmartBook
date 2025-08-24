<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupOrderItem extends Model
{
    protected $fillable = ['group_order_id','member_id','book_id','quantity','price_snapshot'];

    public function group(): BelongsTo { return $this->belongsTo(GroupOrder::class); }
    public function member(): BelongsTo { return $this->belongsTo(GroupOrderMember::class); }
    public function book(): BelongsTo { return $this->belongsTo(Book::class); }
}
