<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupOrderMember extends Model
{
    protected $fillable = ['group_order_id','user_id','display_name','role'];

    public function group(): BelongsTo { return $this->belongsTo(GroupOrder::class,'group_order_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(GroupOrderItem::class,'member_id'); }
}
