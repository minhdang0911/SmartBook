<?php

// 1. Migration cho bảng otp_verifications
// Tạo file migration: php artisan make:migration create_otp_verifications_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpVerificationsTable extends Migration
{
    public function up()
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('otp', 6);
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index('email');
            $table->index('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('otp_verifications');
    }
}
