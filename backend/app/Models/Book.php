<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'stock',
        'views',
        'likes',
        'book_images'
    ];

    protected $casts = [
        'book_images' => 'array',
    ];

    public function author() { return $this->belongsTo(Author::class); }

    public function publisher() { return $this->belongsTo(Publisher::class); }

    public function category() { return $this->belongsTo(Category::class); }

    public function ratings() { return $this->hasMany(Rating::class); }

    public function images() { return $this->hasMany(BookImage::class); }

    public function updateRatingAvg()
    {
        $avg = $this->ratings()->avg('rating_star') ?? 0;
        $this->rating_avg = round($avg, 1);
        $this->save();
    }
}

