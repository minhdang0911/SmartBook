<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'thumbnail',
        'is_pinned',
        'status',
        'views',
        'like_count',
    ];

    // Auto tạo slug nếu chưa có
    public static function booted()
    {
        static::creating(function ($post) {
            if (!$post->slug) {
                $post->slug = Str::slug($post->title);
            }
        });

        static::updating(function ($post) {
            if (!$post->slug) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    // Sau này dùng để lấy topics (many-to-many)
    public function topics()
    {
        return $this->belongsToMany(Topic::class, 'post_topics');
    }

    // Scope để lấy các bài viết đã xuất bản
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // Scope để lấy các bài viết đã được ghim
    public function scopePinnedFirst($query)
    {
        return $query->orderByDesc('is_pinned')->latest(); // ghim trước, mới sau
    }
}
