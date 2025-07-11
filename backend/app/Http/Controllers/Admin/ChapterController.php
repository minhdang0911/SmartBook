<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookChapter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChapterController extends Controller
{
    public function index(Request $request)
{
    $query = BookChapter::with('book')
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

    // Thêm phân trang
    $chapters = $query->paginate(10); // Mỗi trang 10 chương

    return view('admin.chapters.index', compact('chapters'));
}


    public function create()
    {
        $books = Book::where('is_physical', 0)->get(); // Chỉ lấy sách điện tử
        return view('admin.chapters.create', compact('books'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',

            'title' => [
                'required', 'string', 'max:255',
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
                'required', 'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = BookChapter::where('book_id', $request->book_id)
                        ->where('chapter_order', $value)
                        ->exists();
                    if ($exists) {
                        $fail('❌ Thứ tự chương đã tồn tại trong sách này.');
                    }
                },
            ],

            'content' => 'required|string',
        ]);

        $slug = Str::slug($validated['title']);

        BookChapter::create([
            ...$validated,
            'slug' => $slug,
        ]);

        return redirect()->route('admin.chapters.index')->with('success', '✅ Thêm chương thành công!');
    }

    public function edit(BookChapter $chapter)
    {
        $books = Book::where('is_physical', 0)->get(); // Chỉ lấy sách điện tử
        return view('admin.chapters.edit', compact('chapter', 'books'));
    }

    public function update(Request $request, BookChapter $chapter)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',

            'title' => [
                'required', 'string', 'max:255',
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
                'required', 'integer',
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

            'content' => 'required|string',
        ]);

        // Kiểm tra nếu không thay đổi gì
        if (
            $request->title === $chapter->title &&
            $request->content === $chapter->content &&
            intval($request->book_id) === intval($chapter->book_id) &&
            intval($request->chapter_order) === intval($chapter->chapter_order)
        ) {
            return back()->with('warning', '⚠️ Bạn chưa thay đổi gì. Vui lòng quay lại.');
        }

        $chapter->update([
            'book_id' => $request->book_id,
            'title' => $request->title,
            'chapter_order' => $request->chapter_order,
            'content' => $request->content,
            'slug' => Str::slug($request->title),
        ]);

        return redirect()->route('admin.chapters.index')->with('success', '✅ Cập nhật chương thành công!');
    }

    public function destroy(BookChapter $chapter)
    {
        $chapter->delete();
        return redirect()->route('admin.chapters.index')->with('success', '🗑️ Xóa chương thành công!');
    }

    public function show(BookChapter $chapter)
    {
        $previous = $chapter->previousChapter();
        $next = $chapter->nextChapter();

        return view('admin.chapters.show', compact('chapter', 'previous', 'next'));
    }

    // app/Http/Controllers/Admin/ChapterController.php

public function getChapterOrders($bookId)
{
    $orders = \App\Models\BookChapter::where('book_id', $bookId)
        ->orderBy('chapter_order')
        ->pluck('chapter_order');

    return response()->json($orders);
}

}
