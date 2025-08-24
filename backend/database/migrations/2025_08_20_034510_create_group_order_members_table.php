<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('group_order_members', function (Blueprint $t) {
            $t->id();
            $t->foreignId('group_order_id')->constrained('group_orders')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // guest = null
            $t->string('display_name')->nullable();
            $t->enum('role', ['owner','member'])->default('member');
            $t->timestamps();
            $t->unique(['group_order_id','user_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('group_order_members'); }
};
