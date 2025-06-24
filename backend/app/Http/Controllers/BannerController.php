<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Admin\AdminApi;


class BannerController extends Controller
{
    // Lấy danh sách banner
    public function index(): JsonResponse
    {
        $banners = Banner::all();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách banner thành công',
            'data' => $banners
        ]);
    }

    public function GetBanner(): JsonResponse
{
   $banner = Banner::orderBy('id', 'desc')->limit(4)->get();


    return response()->json([
        'success' => true,
        'message' => 'Lấy danh sách banner thành công',
        'data' => $banner
    ]);
}


    // Tạo banner
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link' => 'nullable|url',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'book_id' => 'nullable|integer',
        ]);

        $imageUrl = null;
        $link = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File upload không hợp lệ'
                ], 400);
            }

            try {
                // Upload to Cloudinary - Cách đúng
                $uploadApi = new UploadApi();
                $uploadResult = $uploadApi->upload(
                    $file->getRealPath(),
                    [
                        'folder' => 'banners',
                        'use_filename' => true,
                        'unique_filename' => true,
                    ]
                );

                $imageUrl = $uploadResult['secure_url'];

                \Log::info('Cloudinary upload success: ', [
                    'url' => $imageUrl,
                    'public_id' => $uploadResult['public_id']
                ]);

            } catch (\Exception $e) {
                \Log::error('Cloudinary upload failed: ', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Fallback to local storage
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('banners', $fileName, 'public');
                $imageUrl = asset('storage/' . $path);

                \Log::info('Fallback to local storage: ' . $imageUrl);
            }

        } elseif ($request->filled('link')) {
            $link = $request->link;
            $imageUrl = null;
        }

        try {
            $banner = Banner::create([
                'image' => $imageUrl,
                'link' => $link,
                'title' => $request->title,
                'description' => $request->description,
                'book_id' => $request->book_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tạo banner thành công',
                'data' => $banner
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Database save failed: ', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu banner: ' . $e->getMessage()
            ], 500);
        }
    }
    // Lấy chi tiết banner
    public function show($id): JsonResponse
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy banner'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin banner thành công',
            'data' => $banner
        ]);
    }

public function update(Request $request, $id): JsonResponse
{
   $banner = Banner::find($id);
   if (!$banner) {
       return response()->json([
           'success' => false,
           'message' => 'Không tìm thấy banner'
       ], 404);
   }

   $request->validate([
       'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
       'link' => 'nullable|url',
       'title' => 'nullable|string|max:255',
       'description' => 'nullable|string',
       'book_id' => 'nullable|integer',
   ]);

   if ($request->hasFile('image')) {
       $file = $request->file('image');

       if (!$file->isValid()) {
           return response()->json([
               'success' => false,
               'message' => 'File upload không hợp lệ'
           ], 400);
       }

       // Xóa ảnh cũ trên Cloudinary nếu có
       if ($banner->image) {
           $this->deleteOldImage($banner->image);
       }

       try {
           // Upload to Cloudinary - Cách đúng (giống hàm store)
           $uploadApi = new UploadApi();
           $uploadResult = $uploadApi->upload(
               $file->getRealPath(),
               [
                   'folder' => 'banners',
                   'use_filename' => true,
                   'unique_filename' => true,
               ]
           );

           $banner->image = $uploadResult['secure_url'];
           $banner->link = null;

           \Log::info('Cloudinary upload success: ', [
               'url' => $banner->image,
               'public_id' => $uploadResult['public_id']
           ]);

       } catch (\Exception $e) {
           \Log::error('Cloudinary upload failed: ', [
               'error' => $e->getMessage(),
               'trace' => $e->getTraceAsString()
           ]);

           // Fallback to local storage
           $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
           $path = $file->storeAs('banners', $fileName, 'public');
           $banner->image = asset('storage/' . $path);
           $banner->link = null;

           \Log::info('Fallback to local storage: ' . $banner->image);
       }

   } elseif ($request->filled('link')) {
       // Xóa ảnh cũ trên Cloudinary nếu có khi chuyển sang dùng link
       if ($banner->image) {
           $this->deleteOldImage($banner->image);
       }

       $banner->link = $request->link;
       $banner->image = null;
   }

   // Cập nhật các trường khác
   $banner->title = $request->title ?? $banner->title;
   $banner->description = $request->description ?? $banner->description;
   $banner->book_id = $request->book_id ?? $banner->book_id;

   try {
       $banner->save();

       return response()->json([
           'success' => true,
           'message' => 'Cập nhật banner thành công',
           'data' => $banner
       ]);

   } catch (\Exception $e) {
       \Log::error('Database update failed: ', [
           'error' => $e->getMessage()
       ]);

       return response()->json([
           'success' => false,
           'message' => 'Lỗi khi cập nhật banner: ' . $e->getMessage()
       ], 500);
   }
}


    private function deleteOldImage(string $imageUrl): void
    {
        try {
            // Lấy public_id từ URL
            $publicId = $this->getPublicIdFromUrl($imageUrl);
            if ($publicId) {
                Cloudinary::destroy($publicId);
            }
        } catch (\Exception $e) {
            \Log::warning('Không thể xóa ảnh cũ trên Cloudinary: ' . $e->getMessage());
        }
    }

    private function getPublicIdFromUrl(string $url): ?string
    {
        $pattern = '/\/v\d+\/(.+)\./';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
    // Xoá banner
    public function destroy($id): JsonResponse
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy banner'
            ], 404);
        }

        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa banner thành công'
        ]);
    }
}
