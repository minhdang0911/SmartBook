<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookChapter;
use Illuminate\Http\Request;

class ChapterApiController extends Controller
{
    /**
     * Lấy danh sách tất cả chương theo sách
     */
    public function getChaptersByBook($bookId)
    {
        try {
            $book = Book::with(['author:id,name', 'category:id,name', 'publisher:id,name'])
                ->findOrFail($bookId);
            
            $chapters = BookChapter::where('book_id', $bookId)
                ->orderBy('chapter_order')
                ->select([
                    'id',
                    'book_id',
                    'title',
                    'slug',
                    'chapter_order',
                    'content_type',
                    'pdf_url',
                    'created_at'
                ])
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'book' => [
                        'id' => $book->id,
                        'title' => $book->title,
                        'cover_image' => $book->cover_image,
                        'author' => $book->author,
                        'category' => $book->category,
                        'publisher' => $book->publisher,
                        'price' => $book->price,
                        'discount_price' => $book->discount_price,
                        'final_price' => $book->discount_price ?? $book->price,
                        'rating_avg' => $book->rating_avg,
                        'pdf_type' => $book->pdf_type,
                        'full_pdf_url' => $book->full_pdf_url,
                        'has_full_pdf' => $book->hasFullPdf(),
                        'has_chapter_pdfs' => $book->hasChapterPdfs(),
                    ],
                    'chapters' => $chapters
                ],
                'total' => $chapters->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sách',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Lấy thông tin chi tiết một chương
     */
    public function getChapter($id)
    {
        try {
            $chapter = BookChapter::with('book:id,title,slug')
                ->findOrFail($id);

            // Lấy chương trước và sau
            $previous = $chapter->previousChapter();
            $next = $chapter->nextChapter();

            return response()->json([
                'success' => true,
                'data' => [
                    'chapter' => [
                        'id' => $chapter->id,
                        'book_id' => $chapter->book_id,
                        'title' => $chapter->title,
                        'slug' => $chapter->slug,
                        'chapter_order' => $chapter->chapter_order,
                        'content_type' => $chapter->content_type,
                        'content' => $chapter->content,
                        'pdf_url' => $chapter->pdf_url,
                        'display_content' => $chapter->display_content,
                        'is_pdf' => $chapter->isPdfContent(),
                        'created_at' => $chapter->created_at,
                        'book' => $chapter->book
                    ],
                    'navigation' => [
                        'previous' => $previous ? [
                            'id' => $previous->id,
                            'title' => $previous->title,
                            'slug' => $previous->slug,
                            'chapter_order' => $previous->chapter_order
                        ] : null,
                        'next' => $next ? [
                            'id' => $next->id,
                            'title' => $next->title,
                            'slug' => $next->slug,
                            'chapter_order' => $next->chapter_order
                        ] : null
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy chương',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Lấy chương theo slug
     */
    public function getChapterBySlug($bookSlug, $chapterSlug)
    {
        try {
            $book = Book::with(['author:id,name', 'category:id,name', 'publisher:id,name'])
                ->where('slug', $bookSlug)
                ->firstOrFail();
            
            $chapter = BookChapter::with('book:id,title,slug')
                ->where('book_id', $book->id)
                ->where('slug', $chapterSlug)
                ->firstOrFail();

            $previous = $chapter->previousChapter();
            $next = $chapter->nextChapter();

            return response()->json([
                'success' => true,
                'data' => [
                    'chapter' => [
                        'id' => $chapter->id,
                        'book_id' => $chapter->book_id,
                        'title' => $chapter->title,
                        'slug' => $chapter->slug,
                        'chapter_order' => $chapter->chapter_order,
                        'content_type' => $chapter->content_type,
                        'content' => $chapter->content,
                        'pdf_url' => $chapter->pdf_url,
                        'display_content' => $chapter->display_content,
                        'is_pdf' => $chapter->isPdfContent(),
                        'created_at' => $chapter->created_at,
                        'book' => $chapter->book
                    ],
                    'navigation' => [
                        'previous' => $previous ? [
                            'id' => $previous->id,
                            'title' => $previous->title,
                            'slug' => $previous->slug,
                            'chapter_order' => $previous->chapter_order
                        ] : null,
                        'next' => $next ? [
                            'id' => $next->id,
                            'title' => $next->title,
                            'slug' => $next->slug,
                            'chapter_order' => $next->chapter_order
                        ] : null
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy chương',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Tìm kiếm chương
     */
    public function searchChapters(Request $request)
    {
        try {
            $query = BookChapter::with('book:id,title,slug');

            // Tìm theo title
            if ($request->filled('title')) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }

            // Tìm theo book_id
            if ($request->filled('book_id')) {
                $query->where('book_id', $request->book_id);
            }

            // Tìm theo content_type
            if ($request->filled('content_type')) {
                $query->where('content_type', $request->content_type);
            }

            // Sắp xếp
            $query->orderBy('book_id')->orderBy('chapter_order');

            // Phân trang
            $perPage = $request->get('per_page', 10);
            $chapters = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $chapters->items(),
                'pagination' => [
                    'current_page' => $chapters->currentPage(),
                    'per_page' => $chapters->perPage(),
                    'total' => $chapters->total(),
                    'last_page' => $chapters->lastPage(),
                    'has_more' => $chapters->hasMorePages()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tìm kiếm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách sách có chương
     */
    public function getBooksWithChapters()
    {
        try {
            $books = Book::whereHas('chapters')
                ->with([
                    'chapters:id,book_id,title,slug,chapter_order,content_type',
                    'author:id,name',
                    'category:id,name',
                    'publisher:id,name'
                ])
                ->select('id', 'title', 'cover_image', 'author_id', 'category_id', 'publisher_id', 'price', 'discount_price', 'rating_avg', 'pdf_type', 'full_pdf_url')
                ->get();

            $books->each(function ($book) {
                $book->chapters_count = $book->chapters->count();
                $book->chapters = $book->chapters->sortBy('chapter_order')->values();
                
                // Thêm thông tin giá cuối (sau discount)
                $book->final_price = $book->discount_price ?? $book->price;
                
                // Thêm thông tin có PDF không
                $book->has_full_pdf = $book->hasFullPdf();
                $book->has_chapter_pdfs = $book->hasChapterPdfs();
            });

            return response()->json([
                'success' => true,
                'data' => $books,
                'total' => $books->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh sách sách',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}