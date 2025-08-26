<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('group_order_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('group_order_id')->constrained('group_orders')->cascadeOnDelete();
            $t->foreignId('member_id')->constrained('group_order_members')->cascadeOnDelete();
            $t->enum('gateway', ['momo','vnpay']);
            $t->string('provider_txn_id')->nullable(); // orderId (MoMo) / vnp_TxnRef (VNPay)
            $t->string('pay_url')->nullable();
            $t->unsignedBigInteger('amount'); // VND
            $t->enum('status', ['pending','paid','failed','canceled'])->default('pending');
            $t->timestamp('email_sent_at')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->unique(['group_order_id','member_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('group_order_payments');
    }
};
