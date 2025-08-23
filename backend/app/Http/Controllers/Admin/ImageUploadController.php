<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CloudinaryService;

class ImageUploadController extends Controller
{
    public function store(Request $request)
    {
        if (!$request->hasFile('upload')) {
            return response()->json(['uploaded' => false, 'error' => ['message' => 'No file uploaded.']], 400);
        }

        $file = $request->file('upload');

        $cloudinary = new CloudinaryService();
        $url = $cloudinary->uploadImage($file, 'posts/content');

        return response()->json([
            'uploaded' => true,
            'url' => $url,
        ]);
    }
}
