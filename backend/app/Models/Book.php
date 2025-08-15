<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'title',
        'description',
        'cover_image',
        'author_id',
        'publisher_id',
        'category_id',
        'is_physical',
        'price',
        'discount_price',   
        'stock',
        'views',
        'likes',
        'book_images',
        'rating_avg',
        // Thêm trường cho PDF full sách
        'full_pdf_url',
        'full_pdf_public_id',
        'pdf_type', // 'none', 'full', 'chapters'
    ];

    protected $casts = [
        'book_images' => 'array',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_physical' => 'integer',
        'stock' => 'integer',
        'views' => 'integer',
        'likes' => 'integer',
        'rating_avg' => 'decimal:1',
    ];

    public function author() 
    { 
        return $this->belongsTo(Author::class); 
    }

    public function publisher() 
    { 
        return $this->belongsTo(Publisher::class); 
    }

    public function category() 
    { 
        return $this->belongsTo(Category::class); 
    }

    public function ratings() 
    { 
        return $this->hasMany(Rating::class); 
    }

    public function images() 
    { 
        return $this->hasMany(BookImage::class); 
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(BookChapter::class)->orderBy('chapter_order');
    }

    // Quan hệ với events
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_products', 'books_id', 'event_id')
                    ->withPivot('quantity_limit', 'sold_quantity');
    }

    public function updateRatingAvg()
    {
        $avg = $this->ratings()->avg('rating_star') ?? 0;
        $this->rating_avg = round($avg, 1);
        $this->save();
    }

    // Kiểm tra sách có PDF full không
    public function hasFullPdf()
    {
        return $this->pdf_type === 'full' && !empty($this->full_pdf_url);
    }

    // Kiểm tra sách có PDF theo chương không
    public function hasChapterPdfs()
    {
        return $this->pdf_type === 'chapters';
    }

    // Kiểm tra sách không có PDF
    public function hasNoPdf()
    {
        return $this->pdf_type === 'none' || empty($this->pdf_type);
    }

    // Lấy URL PDF phù hợp
    public function getPdfUrl()
    {
        if ($this->hasFullPdf()) {
            return $this->full_pdf_url;
        }
        
        return null;
    }

    // Scope để lấy sách có PDF
    public function scopeWithPdf($query)
    {
        return $query->where(function ($q) {
            $q->where('pdf_type', 'full')
              ->whereNotNull('full_pdf_url')
              ->orWhere('pdf_type', 'chapters');
        });
    }

    // Scope để lấy sách điện tử
    public function scopeElectronic($query)
    {
        return $query->where('is_physical', false);
    }

    // === CÁC PHƯƠNG THỨC MỚI CHO LOGIC EVENT ===

    /**
     * Kiểm tra sách có đang trong event không
     */
    public function isInEvent()
    {
        return $this->discount_price !== null && $this->discount_price > 0;
    }

    /**
     * Kiểm tra sách có thể thêm vào event không
     */
    public function canAddToEvent()
    {
        return !$this->isInEvent();
    }

    /**
     * Lấy giá hiển thị (ưu tiên discount_price nếu có)
     */
    public function getDisplayPriceAttribute()
    {
        return $this->discount_price ?? $this->price;
    }

    /**
     * Tính phần trăm giảm giá
     */
    public function getDiscountPercentAttribute()
    {
        if (!$this->discount_price || $this->discount_price >= $this->price) {
            return 0;
        }

        return round((($this->price - $this->discount_price) / $this->price) * 100, 2);
    }

    /**
     * Kiểm tra có giảm giá không
     */
    public function hasDiscount()
    {
        return $this->discount_price !== null && $this->discount_price < $this->price;
    }

    /**
     * Reset giá giảm
     */
    public function resetDiscount()
    {
        $this->update(['discount_price' => null]);
    }

    /**
     * Áp dụng giá giảm theo phần trăm
     */
    public function applyDiscount($discountPercent)
    {
        $discountPrice = $this->price - ($this->price * $discountPercent / 100);
        $this->update(['discount_price' => $discountPrice]);
        return $discountPrice;
    }

    // Scope để lấy sách có thể thêm vào event
    public function scopeAvailableForEvent($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('discount_price')->orWhere('discount_price', 0);
        });
    }

    // Scope để lấy sách đang có giảm giá
    public function scopeOnSale($query)
    {
        return $query->whereNotNull('discount_price')
                    ->where('discount_price', '>', 0)
                    ->whereRaw('discount_price < price');
    }
}