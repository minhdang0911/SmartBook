<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Topic extends Model
{
    protected $fillable = ['name', 'slug'];

    public static function booted()
    {
        static::creating(function ($topic) {
            if (!$topic->slug) {
                $topic->slug = Str::slug($topic->name);
            }
        });

        static::updating(function ($topic) {
            if (!$topic->slug) {
                $topic->slug = Str::slug($topic->name);
            }
        });
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_topics');
    }
}
