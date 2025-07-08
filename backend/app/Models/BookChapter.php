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

}
