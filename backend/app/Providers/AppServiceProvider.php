<?php

namespace App\Providers;

use Cloudinary\Configuration\Configuration;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\JsonResponse; // ✅ thêm dòng này

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure Cloudinary
        Configuration::instance([
            'cloud' => [
                'cloud_name' => 'dz7y2yufu',
                'api_key'    => '155772835832488',
                'api_secret' => 'Ho_6ApwWCE5s1dYtBzHAbPlSSD0',
            ],
            'url' => [
                'secure' => true
            ]
        ]);

        // ✅ FIX Unicode JSON (QUAN TRỌNG)
        JsonResponse::setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}
