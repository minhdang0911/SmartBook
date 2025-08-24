<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupOrderSettlement extends Model
{
    protected $fillable = ['group_order_id','member_id','amount_due','amount_paid','status','payment_intent_id'];

    public function group(): BelongsTo { return $this->belongsTo(GroupOrder::class); }
    public function member(): BelongsTo { return $this->belongsTo(GroupOrderMember::class); }
}
