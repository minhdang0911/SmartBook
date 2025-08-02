<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};
