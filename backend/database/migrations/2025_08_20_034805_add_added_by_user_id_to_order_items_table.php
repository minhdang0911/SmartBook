<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('group_order_settlements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('group_order_id')->constrained('group_orders')->cascadeOnDelete();
            $t->foreignId('member_id')->constrained('group_order_members')->cascadeOnDelete();
            $t->decimal('amount_due', 12, 2);
            $t->decimal('amount_paid', 12, 2)->default(0);
            $t->enum('status', ['pending','paid','refunded','cancelled'])->default('pending');
            $t->string('payment_intent_id')->nullable();
            $t->timestamps();
            $t->unique(['group_order_id','member_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('group_order_settlements'); }
};
