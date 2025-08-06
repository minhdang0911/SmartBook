<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CloudinaryService
{
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => ['secure' => true],
        ]);
    }

    public function uploadImage($file, $folder = 'uploads')
    {
        $uploaded = $this->cloudinary->uploadApi()->upload(
            $file->getRealPath(),
            ['folder' => $folder]
        );

        return $uploaded['secure_url'] ?? null;
    }

    /**
     * Upload PDF file to Cloudinary
     */
    public function uploadPdf(UploadedFile $file, $folder = 'book_chapters', $publicId = null)
    {
        try {
            // Validate file
            if (!$file->isValid()) {
                throw new \Exception('File PDF không hợp lệ.');
            }

            if ($file->getMimeType() !== 'application/pdf') {
                throw new \Exception('File không phải định dạng PDF.');
            }

            if ($file->getSize() > 10 * 1024 * 1024) { // 10MB limit
                throw new \Exception('File PDF không được vượt quá 10MB.');
            }

            // Tạo public_id với .pdf extension cố định
            if ($publicId) {
                $cleanPublicId = str_replace('.pdf', '', $publicId);
                $finalPublicId = $cleanPublicId . '.pdf';
            } else {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $cleanName = Str::slug($originalName);
                $finalPublicId = $cleanName . '_' . time() . '.pdf';
            }

            // Upload options với format cụ thể
            $options = [
                'resource_type' => 'raw',
                'public_id' => $finalPublicId,
                'folder' => $folder,
                'use_filename' => false,
                'unique_filename' => false,
                'overwrite' => false,
                'invalidate' => true,
                'format' => 'pdf', // Cố định format
            ];

            \Log::info('Starting PDF upload via CloudinaryService', [
                'filename' => $file->getClientOriginalName(),
                'final_public_id' => $finalPublicId,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'options' => $options
            ]);

            // Upload file
            $result = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                $options
            );

            \Log::info('PDF upload successful', [
                'secure_url' => $result['secure_url'] ?? 'missing',
                'public_id' => $result['public_id'] ?? 'missing',
                'format' => $result['format'] ?? 'unknown',
                'resource_type' => $result['resource_type'] ?? 'unknown',
                'full_result' => $result
            ]);

            if (!isset($result['secure_url'])) {
                throw new \Exception('Không lấy được URL từ kết quả upload.');
            }

            // URL gốc KHÔNG có fl_attachment - sẽ hiển thị inline trong browser
            return [
                'url' => $result['secure_url'], // URL gốc - browser sẽ hiển thị PDF
                'view_url' => $result['secure_url'], // Giống URL gốc
                'public_id' => $result['public_id'],
                'format' => 'pdf',
                'bytes' => $result['bytes'] ?? $file->getSize(),
                'created_at' => $result['created_at'] ?? now()
            ];

        } catch (\Exception $e) {
            \Log::error('PDF upload failed via CloudinaryService', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Delete file by public_id
     */
    public function deleteFile($publicId, $resourceType = 'image')
    {
        try {
            $result = $this->cloudinary->uploadApi()->destroy($publicId, [
                'resource_type' => $resourceType
            ]);

            \Log::info('File deleted successfully', [
                'public_id' => $publicId,
                'resource_type' => $resourceType,
                'result' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            \Log::error('File deletion failed', [
                'public_id' => $publicId,
                'resource_type' => $resourceType,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function deleteImageByPublicId($url)
    {
        $parts = explode('/', parse_url($url, PHP_URL_PATH));
        $filename = end($parts);
        $publicId = pathinfo($filename, PATHINFO_FILENAME);

        return $this->cloudinary->uploadApi()->destroy($publicId);
    }

    /**
     * Delete PDF file by public_id
     */
    public function deletePdf($publicId)
    {
        return $this->deleteFile($publicId, 'raw');
    }

    /**
     * Get file info by public_id
     */
    public function getFileInfo($publicId, $resourceType = 'image')
    {
        try {
            return $this->cloudinary->adminApi()->asset($publicId, [
                'resource_type' => $resourceType
            ]);
        } catch (\Exception $e) {
            \Log::error('Get file info failed', [
                'public_id' => $publicId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}