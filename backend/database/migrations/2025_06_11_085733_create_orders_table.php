<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users');

        // Địa chỉ chi tiết
        $table->string('sonha')->nullable();
        $table->string('street')->nullable();
        $table->unsignedBigInteger('district_id')->nullable();
        $table->unsignedBigInteger('ward_id')->nullable();
        $table->string('district_name')->nullable();
        $table->string('ward_name')->nullable();

        // Thông tin thanh toán
        $table->unsignedBigInteger('cart_id')->nullable(); // ✅ cart_id là khóa ngoại
        $table->foreign('cart_id')->references('id')->on('carts')->onDelete('set null');

        $table->string('payment')->default('cod');
        $table->string('status')->default('deposit');

        // Tổng tiền
        $table->decimal('price', 10, 2)->default(0);
        $table->decimal('shipping_fee', 10, 2)->default(0);
        $table->decimal('total_price', 10, 2)->default(0);

        $table->text('address')->nullable();
        $table->timestamp('created_at')->useCurrent();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['cart_id']);
            $table->dropColumn([
                'sonha',
                'street',
                'district_id',
                'ward_id',
                'district_name',
                'ward_name',
                'cart_id',
                'payment',
                'price',
            ]);

            // Optional: rollback default status nếu cần
            // $table->string('status')->default('pending')->change();
        });
    }
};
