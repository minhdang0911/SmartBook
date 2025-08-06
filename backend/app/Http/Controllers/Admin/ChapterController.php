<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookChapter;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChapterController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    public function index(Request $request)
    {
        $query = BookChapter::with(['book:id,title,author_id', 'book.author:id,name'])
            ->orderBy('book_id')
            ->orderBy('chapter_order');

        if ($request->filled('book_title')) {
            $query->whereHas('book', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->book_title . '%');
            });
        }

        if ($request->filled('chapter_title')) {
            $query->where('title', 'like', '%' . $request->chapter_title . '%');
        }

        if ($request->filled('content_type')) {
            $query->where('content_type', $request->content_type);
        }

        $chapters = $query->paginate(10);

        return view('admin.chapters.index', compact('chapters'));
    }

    public function create()
    {
        $books = Book::with('author:id,name')
            ->where('is_physical', 0)
            ->select('id', 'title', 'author_id')
            ->get();
        return view('admin.chapters.create', compact('books'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'title' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = BookChapter::where('book_id', $request->book_id)
                        ->where('title', $value)
                        ->exists();
                    if ($exists) {
                        $fail('❌ Tiêu đề chương đã tồn tại trong sách này.');
                    }
                },
            ],
            'chapter_order' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = BookChapter::where('book_id', $request->book_id)
                        ->where('chapter_order', $value)
                        ->exists();
                    if ($exists) {
                        $fail('❌ Thứ tự chương đã tồn tại trong sách này.');
                    }
                },
            ],
            'content_type' => 'required|in:text,pdf',
            'content' => 'required_if:content_type,text|string|nullable',
            'pdf_file' => 'required_if:content_type,pdf|file|mimes:pdf|max:10240', // 10MB
        ]);

        // Kiểm tra sách có phải là sách điện tử không
        $book = Book::findOrFail($validated['book_id']);
        if ($book->is_physical) {
            return back()->withErrors(['book_id' => '❌ Chỉ có thể thêm chương cho sách điện tử']);
        }

        $slug = Str::slug($validated['title']);

        // Đảm bảo slug unique trong cùng một book
        $originalSlug = $slug;
        $counter = 1;
        while (BookChapter::where('book_id', $validated['book_id'])->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $chapterData = [
            'book_id' => $validated['book_id'],
            'title' => $validated['title'],
            'slug' => $slug,
            'chapter_order' => $validated['chapter_order'],
            'content_type' => $validated['content_type'],
        ];

        // Xử lý upload PDF nếu có
        if ($validated['content_type'] === 'pdf' && $request->hasFile('pdf_file')) {
            try {
                $pdfFile = $request->file('pdf_file');
                
                // Tạo public_id không có đuôi .pdf (Cloudinary sẽ tự thêm)
                $basePublicId = 'chapter_' . $slug . '_' . uniqid() . '_' . time();
                
                // Sử dụng CloudinaryService thay vì Facade
                $uploadResult = $this->cloudinaryService->uploadPdf($pdfFile, 'book_chapters', $basePublicId);
                
                $chapterData['pdf_url'] = $uploadResult['view_url']; // Sử dụng view_url thay vì url
                $chapterData['pdf_public_id'] = $uploadResult['public_id'];
                $chapterData['content'] = null;

                \Log::info('PDF uploaded successfully via CloudinaryService', [
                    'original_url' => $uploadResult['url'],
                    'view_url' => $uploadResult['view_url'],
                    'public_id' => $uploadResult['public_id'],
                    'chapter_title' => $validated['title']
                ]);

            } catch (\Exception $e) {
                \Log::error('PDF upload error via CloudinaryService', [
                    'message' => $e->getMessage(),
                    'chapter_title' => $validated['title']
                ]);
                return back()->withErrors(['pdf_file' => '❌ Lỗi upload PDF: ' . $e->getMessage()]);
            }
        } else {
            $chapterData['content'] = $validated['content'] ?? null;
            $chapterData['pdf_url'] = null;
            $chapterData['pdf_public_id'] = null;
        }

        // Tạo chapter mới
        BookChapter::create($chapterData);

        // Cập nhật pdf_type của book nếu cần
        if ($validated['content_type'] === 'pdf' && $book->pdf_type === 'none') {
            $book->update(['pdf_type' => 'chapters']);
        }

        return redirect()->route('admin.chapters.index')->with('success', '✅ Thêm chương thành công!');
    }

    public function edit(BookChapter $chapter)
    {
        $books = Book::with('author:id,name')
            ->where('is_physical', 0)
            ->select('id', 'title', 'author_id')
            ->get();
        return view('admin.chapters.edit', compact('chapter', 'books'));
    }

    public function update(Request $request, BookChapter $chapter)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'title' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request, $chapter) {
                    $exists = BookChapter::where('book_id', $request->book_id)
                        ->where('title', $value)
                        ->where('id', '!=', $chapter->id)
                        ->exists();
                    if ($exists) {
                        $fail('❌ Tiêu đề chương đã tồn tại trong sách này.');
                    }
                },
            ],
            'chapter_order' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($request, $chapter) {
                    $exists = BookChapter::where('book_id', $request->book_id)
                        ->where('chapter_order', $value)
                        ->where('id', '!=', $chapter->id)
                        ->exists();
                    if ($exists) {
                        $fail('❌ Thứ tự chương đã tồn tại trong sách này.');
                    }
                },
            ],
            'content_type' => 'required|in:text,pdf',
            'content' => 'required_if:content_type,text|string|nullable',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB
        ]);

        // Kiểm tra sách có phải là sách điện tử không
        $book = Book::findOrFail($request->book_id);
        if ($book->is_physical) {
            return back()->withErrors(['book_id' => '❌ Chỉ có thể cập nhật chương cho sách điện tử']);
        }

        $slug = Str::slug($request->title);

        // Đảm bảo slug unique trong cùng một book (trừ chapter hiện tại)
        $originalSlug = $slug;
        $counter = 1;
        while (
            BookChapter::where('book_id', $request->book_id)
                ->where('slug', $slug)
                ->where('id', '!=', $chapter->id)
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $updateData = [
            'book_id' => $request->book_id,
            'title' => $request->title,
            'chapter_order' => $request->chapter_order,
            'content_type' => $request->content_type,
            'slug' => $slug,
        ];

        // Xử lý thay đổi loại content
        if ($request->content_type === 'pdf') {
            if ($request->hasFile('pdf_file')) {
                // Xóa file PDF cũ nếu có
                if ($chapter->pdf_public_id) {
                    try {
                        $this->cloudinaryService->deletePdf($chapter->pdf_public_id);
                        \Log::info('Deleted old PDF: ' . $chapter->pdf_public_id);
                    } catch (\Exception $e) {
                        \Log::error('Error deleting old PDF: ' . $e->getMessage());
                    }
                }

                // Upload file PDF mới
                try {
                    $pdfFile = $request->file('pdf_file');
                    $basePublicId = 'chapter_' . $slug . '_' . uniqid() . '_' . time();
                    
                    $uploadResult = $this->cloudinaryService->uploadPdf($pdfFile, 'book_chapters', $basePublicId);
                    
                    $updateData['pdf_url'] = $uploadResult['view_url']; // Sử dụng view_url
                    $updateData['pdf_public_id'] = $uploadResult['public_id'];
                    $updateData['content'] = null;

                    \Log::info('PDF updated successfully via CloudinaryService', [
                        'view_url' => $uploadResult['view_url'],
                        'public_id' => $uploadResult['public_id']
                    ]);

                } catch (\Exception $e) {
                    \Log::error('PDF upload error in update via CloudinaryService: ' . $e->getMessage());
                    return back()->withErrors(['pdf_file' => '❌ Lỗi upload PDF: ' . $e->getMessage()]);
                }
            } else {
                // Giữ nguyên PDF cũ
                $updateData['pdf_url'] = $chapter->pdf_url;
                $updateData['pdf_public_id'] = $chapter->pdf_public_id;
                $updateData['content'] = null;
            }
        } else {
            // Chuyển sang text, xóa PDF nếu có
            if ($chapter->pdf_public_id) {
                try {
                    $this->cloudinaryService->deletePdf($chapter->pdf_public_id);
                    \Log::info('Deleted PDF when switching to text: ' . $chapter->pdf_public_id);
                } catch (\Exception $e) {
                    \Log::error('Error deleting PDF when switching to text: ' . $e->getMessage());
                }
            }

            $updateData['content'] = $request->content;
            $updateData['pdf_url'] = null;
            $updateData['pdf_public_id'] = null;
        }

        $chapter->update($updateData);

        // Cập nhật pdf_type của book nếu cần
        if ($request->content_type === 'pdf' && $book->pdf_type === 'none') {
            $book->update(['pdf_type' => 'chapters']);
        }

        return redirect()->route('admin.chapters.index')->with('success', '✅ Cập nhật chương thành công!');
    }

public function show($id, Request $request)
{
    $book = Book::with('author')->findOrFail($id);

    $chapters = BookChapter::where('book_id', $id)
        ->orderBy('chapter_order')
        ->get();

    $selectedChapterId = $request->input('chapter_id');
    $chapter = null;
    $previous = null;
    $next = null;

    if ($selectedChapterId) {
        $chapter = BookChapter::with('book.author')
            ->where('book_id', $id)
            ->findOrFail($selectedChapterId);

        $previous = BookChapter::where('book_id', $id)
            ->where('chapter_order', '<', $chapter->chapter_order)
            ->orderByDesc('chapter_order')
            ->first();

        $next = BookChapter::where('book_id', $id)
            ->where('chapter_order', '>', $chapter->chapter_order)
            ->orderBy('chapter_order')
            ->first();
    } else {
        // If no chapter_id is provided, select the first chapter by default
        $chapter = BookChapter::with('book.author')
            ->where('book_id', $id)
            ->orderBy('chapter_order')
            ->first();
            
        if ($chapter) {
            $next = BookChapter::where('book_id', $id)
                ->where('chapter_order', '>', $chapter->chapter_order)
                ->orderBy('chapter_order')
                ->first();
        }
    }

    return view('admin.chapters.book-chapters', compact('book', 'chapters', 'chapter', 'previous', 'next'));
}

    public function destroy(BookChapter $chapter)
    {
        // Xóa file PDF từ Cloudinary nếu có
        if ($chapter->pdf_public_id) {
            try {
                $this->cloudinaryService->deletePdf($chapter->pdf_public_id);
                \Log::info('Deleted PDF on destroy: ' . $chapter->pdf_public_id);
            } catch (\Exception $e) {
                \Log::error('Error deleting PDF on destroy: ' . $e->getMessage());
            }
        }

        $bookId = $chapter->book_id;
        $chapter->delete();

        // Kiểm tra và cập nhật pdf_type của book nếu không còn chapter nào có PDF
        $book = Book::find($bookId);
        if ($book && $book->pdf_type === 'chapters') {
            $hasChapterPdfs = BookChapter::where('book_id', $bookId)
                ->where('content_type', 'pdf')
                ->exists();
            if (!$hasChapterPdfs) {
                $book->update(['pdf_type' => 'none']);
            }
        }

        return redirect()->route('admin.chapters.index')->with('success', '🗑️ Xóa chương thành công!');
    }

    public function getChapterOrders($bookId)
    {
        $orders = BookChapter::where('book_id', $bookId)
            ->orderBy('chapter_order')
            ->pluck('chapter_order');

        return response()->json($orders);
    }

    public function getChaptersByBookId($bookId)
    {
        $chapters = BookChapter::where('book_id', $bookId)
            ->select('id', 'title', 'chapter_order', 'content_type')
            ->orderBy('chapter_order')
            ->get();

        return response()->json([
            'success' => true,
            'chapters' => $chapters
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'chapter_ids' => 'required|array',
            'chapter_ids.*' => 'exists:book_chapters,id'
        ]);

        $chapters = BookChapter::whereIn('id', $request->chapter_ids)->get();

        foreach ($chapters as $chapter) {
            // Xóa file PDF từ Cloudinary nếu có
            if ($chapter->pdf_public_id) {
                try {
                    $this->cloudinaryService->deletePdf($chapter->pdf_public_id);
                } catch (\Exception $e) {
                    \Log::error('Error in bulk delete PDF: ' . $e->getMessage());
                }
            }
        }

        BookChapter::whereIn('id', $request->chapter_ids)->delete();

        return response()->json([
            'success' => true,
            'message' => '✅ Đã xóa ' . count($request->chapter_ids) . ' chương thành công!'
        ]);
    }

   

}