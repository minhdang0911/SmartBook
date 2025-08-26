<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title','description','cover_image','author_id','publisher_id','category_id',
        'is_physical','price','discount_price','stock','views','likes','book_images',
        'rating_avg','full_pdf_url','full_pdf_public_id','pdf_type',
    ];

    protected $casts = [
        'book_images'     => 'array',
        'price'           => 'decimal:2',
        'discount_price'  => 'decimal:2',
        'is_physical'     => 'integer',
        'stock'           => 'integer',
        'views'           => 'integer',
        'likes'           => 'integer',
        'rating_avg'      => 'decimal:1',
    ];

    // ====== Hooks xoá mềm & xoá vĩnh viễn ======
    protected static function booted()
    {
        static::deleting(function (Book $book) {
            // Xoá MỀM: dọn cart thường cho sạch UI (tuỳ business)
            if (! $book->isForceDeleting()) {
                \App\Models\CartItem::where('book_id', $book->id)->delete();
                // Nếu muốn group cart cũng bay theo khi xoá mềm, mở dòng sau:
                // \App\Models\GroupOrderItem::where('book_id', $book->id)->delete();
                return;
            }

            // Xoá VĨNH VIỄN: PHẢI dọn hết phụ thuộc để không bị FK chặn
            \DB::transaction(function () use ($book) {
                // 1) group_order_items (đang chặn xoá)
                \App\Models\GroupOrderItem::where('book_id', $book->id)->delete();

                // 2) cart_items (nếu có)
                if (class_exists(\App\Models\CartItem::class)) {
                    \App\Models\CartItem::where('book_id', $book->id)->delete();
                }

                // 3) pivot event_products
                // events() đã định nghĩa belongsToMany ở dưới
                $book->events()->detach();

                // 4) Nếu có bảng nào khác trỏ vào books (ví dụ wishlist_items), dọn tại đây

                // Lưu ý: Nếu sách đã nằm trong ORDER_ITEMS (đơn đã chốt),
                // FK mặc định thường RESTRICT -> sẽ vẫn chặn.
                // Muốn xoá luôn, bạn phải đổi FK order_items -> SET NULL hoặc snapshot đầy đủ (xem "Ghi chú" cuối).
            });
        });
    }

    // ====== Quan hệ ======
    public function author()     { return $this->belongsTo(Author::class); }
    public function publisher()  { return $this->belongsTo(Publisher::class); }
    public function category()   { return $this->belongsTo(Category::class); }
    public function ratings()    { return $this->hasMany(Rating::class); }
    public function images()     { return $this->hasMany(BookImage::class); }
    public function chapters(): HasMany { return $this->hasMany(BookChapter::class)->orderBy('chapter_order'); }

    // Gợi ý thêm quan hệ để tiện maintain:
    public function groupOrderItems(): HasMany { return $this->hasMany(\App\Models\GroupOrderItem::class); }
    public function cartItems(): HasMany { return $this->hasMany(\App\Models\CartItem::class); }

    // Pivot events (đang dùng cột books_id ở bảng event_products)
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_products', 'books_id', 'event_id')
                    ->withPivot('quantity_limit', 'sold_quantity');
    }

    // ====== Logic phụ ======
    public function updateRatingAvg()
    {
        $avg = $this->ratings()->avg('rating_star') ?? 0;
        $this->rating_avg = round($avg, 1);
        $this->save();
    }

    public function hasFullPdf()      { return $this->pdf_type === 'full' && !empty($this->full_pdf_url); }
    public function hasChapterPdfs()  { return $this->pdf_type === 'chapters'; }
    public function hasNoPdf()        { return $this->pdf_type === 'none' || empty($this->pdf_type); }

    public function getPdfUrl()
    {
        return $this->hasFullPdf() ? $this->full_pdf_url : null;
    }

    public function scopeWithPdf($q)
    {
        return $q->where(function ($qq) {
            $qq->where('pdf_type', 'full')->whereNotNull('full_pdf_url')
               ->orWhere('pdf_type', 'chapters');
        });
    }

    public function scopeElectronic($q) { return $q->where('is_physical', false); }

    public function isInEvent()        { return $this->discount_price !== null && $this->discount_price > 0; }
    public function canAddToEvent()    { return ! $this->isInEvent(); }
    public function getDisplayPriceAttribute() { return $this->discount_price ?? $this->price; }

    public function getDiscountPercentAttribute()
    {
        if (! $this->discount_price || $this->discount_price >= $this->price) return 0;
        return round((($this->price - $this->discount_price) / $this->price) * 100, 2);
    }

    public function hasDiscount()      { return $this->discount_price !== null && $this->discount_price < $this->price; }
    public function resetDiscount()    { $this->update(['discount_price' => null]); }

    public function applyDiscount($percent)
    {
        $discountPrice = $this->price - ($this->price * $percent / 100);
        $this->update(['discount_price' => $discountPrice]);
        return $discountPrice;
    }

    public function scopeAvailableForEvent($q)
    {
        return $q->where(function ($qq) {
            $qq->whereNull('discount_price')->orWhere('discount_price', 0);
        });
    }

    public function scopeOnSale($q)
    {
        return $q->whereNotNull('discount_price')
                 ->where('discount_price', '>', 0)
                 ->whereRaw('discount_price < price');
    }
}
