<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupOrderPayment extends Model
{
    protected $fillable = [
        'group_order_id','member_id','gateway','provider_txn_id','pay_url',
        'amount','status','email_sent_at','paid_at','meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'email_sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function group()  { return $this->belongsTo(GroupOrder::class, 'group_order_id'); }
    public function member() { return $this->belongsTo(GroupOrderMember::class, 'member_id'); }
}
