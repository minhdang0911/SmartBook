<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('group_order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('group_order_id')->constrained('group_orders')->cascadeOnDelete();
            $t->foreignId('member_id')->constrained('group_order_members')->cascadeOnDelete();
            $t->foreignId('book_id')->constrained('books');
            $t->unsignedInteger('quantity');
            $t->decimal('price_snapshot', 12, 2); // chốt giá lúc add
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('group_order_items'); }
};
