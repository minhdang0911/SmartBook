<?php

namespace App\Providers;

use Cloudinary\Configuration\Configuration;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\JsonResponse;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Configure Cloudinary
        Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME', 'dz7y2yufu'),
                'api_key'    => env('CLOUDINARY_API_KEY', '155772835832488'),
                'api_secret' => env('CLOUDINARY_API_SECRET', 'Ho_6ApwWCE5s1dYtBzHAbPlSSD0'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);

        // ✅ Set JSON encoding options globally (không crash theo version)
        if (method_exists(JsonResponse::class, 'defaultEncodingOptions')) {
            JsonResponse::defaultEncodingOptions(JSON_UNESCAPED_UNICODE);
        }
    }
}
