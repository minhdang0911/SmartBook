<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookChapter;

class BookChapterApiController extends Controller
{
    /**
     * Lấy danh sách tất cả sách
     */
    public function listBooks()
    {
        $books = Book::select('id', 'title', 'cover_image')->get();

        return response()->json($books);
    }

    /**
     * Lấy danh sách chương theo sách
     */
    public function getChapters($bookId)
    {
        $chapters = BookChapter::where('book_id', $bookId)
            ->orderBy('chapter_order')
            ->get();

        return response()->json($chapters);
    }


    /**
     * Lấy chi tiết một chương
     */
    public function getChapter($chapterId)
    {
        $chapter = BookChapter::with('book:id,title')->findOrFail($chapterId);

        return response()->json([
            'id' => $chapter->id,
            'title' => $chapter->title,
            'slug' => $chapter->slug,
            'content' => $chapter->content, // Nội dung có thể là HTML
            'chapter_order' => $chapter->chapter_order,
            'created_at' => $chapter->created_at,
            'book' => $chapter->book,
        ]);
    }
}
