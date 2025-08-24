<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupOrder extends Model
{
    protected $fillable = [
        'owner_user_id','join_token','status','allow_guest','shipping_rule','expires_at','order_id'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_user_id'); }
    public function members(): HasMany { return $this->hasMany(GroupOrderMember::class); }
    public function items(): HasMany { return $this->hasMany(GroupOrderItem::class); }
    public function settlements(): HasMany { return $this->hasMany(GroupOrderSettlement::class); }

    public function scopeOpen($q) { return $q->where('status','open'); }

    // Join URL trỏ FE (configurable, default localhost:3000)
    public function getJoinUrlAttribute(): string
    {
        $base = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');
        return $base.'/Go/'.$this->join_token;
    }
}
