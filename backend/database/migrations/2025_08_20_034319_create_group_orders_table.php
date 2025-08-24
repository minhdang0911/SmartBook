<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('group_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_user_id')->constrained('users');
            $t->string('join_token', 64)->unique();
            $t->enum('status', ['open','locked','checked_out','expired','cancelled'])->default('open');
            $t->boolean('allow_guest')->default(false);
            $t->enum('shipping_rule', ['equal','by_value','owner_only'])->default('equal');
            $t->timestamp('expires_at')->nullable();
            $t->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete(); // order thật sau checkout
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('group_orders'); }
};
