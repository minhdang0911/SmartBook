<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookChapter extends Model
{
    protected $fillable = [
        'book_id',
        'title',
        'slug',
        'chapter_order',
        'content',
        'pdf_url',
        'pdf_public_id',
        'content_type', // 'text' hoặc 'pdf'
    ];

    protected $casts = [
        'chapter_order' => 'integer',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function previousChapter()
    {
        return self::where('book_id', $this->book_id)
                    ->where('chapter_order', '<', $this->chapter_order)
                    ->orderByDesc('chapter_order')
                    ->first();
    }

    public function nextChapter()
    {
        return self::where('book_id', $this->book_id)
                    ->where('chapter_order', '>', $this->chapter_order)
                    ->orderBy('chapter_order')
                    ->first();
    }

    // Accessor để lấy content phù hợp
    public function getDisplayContentAttribute()
    {
        if ($this->content_type === 'pdf') {
            return $this->pdf_url;
        }
        return $this->content;
    }

    // Kiểm tra xem có phải là PDF không
    public function isPdfContent()
    {
        return $this->content_type === 'pdf';
    }

    // Kiểm tra xem có phải là text không
    public function isTextContent()
    {
        return $this->content_type === 'text';
    }

    /**
     * Lấy URL để xem PDF (đã được format để xem trong browser)
     */
   public function getPdfViewUrl()
{
    if (!$this->isPdfContent() || !$this->pdf_url) {
        return null;
    }

    // Loại bỏ fl_attachment nếu có trong URL
    $cleanUrl = str_replace('/upload/fl_attachment/', '/upload/', $this->pdf_url);
    
    return $cleanUrl;
}


    /**
     * Lấy URL để tải PDF về
     */
    public function getPdfDownloadUrl()
    {
        if (!$this->isPdfContent() || !$this->pdf_url) {
            return null;
        }

        // Remove fl_attachment:false nếu có
        $downloadUrl = str_replace('/upload/fl_attachment:false/', '/upload/', $this->pdf_url);
        
        // Thêm fl_attachment:true để force download
        return str_replace('/upload/', '/upload/fl_attachment:true/', $downloadUrl);
    }

    /**
     * Tạo filename cho download
     */
    public function getPdfFilename()
    {
        if (!$this->isPdfContent()) {
            return null;
        }

        // Tạo filename từ title
        $cleanTitle = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $this->title);
        $cleanTitle = preg_replace('/\s+/', '_', trim($cleanTitle));
        
        return $cleanTitle . '.pdf';
    }
}