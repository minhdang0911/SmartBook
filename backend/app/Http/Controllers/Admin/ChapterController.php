<?php 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookChapter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChapterController extends Controller
{
    public function index()
    {
        $chapters = BookChapter::with('book')->orderBy('book_id')->orderBy('chapter_order')->get();
        return view('admin.chapters.index', compact('chapters'));
    }

    public function create()
    {
        $books = Book::all();
        return view('admin.chapters.create', compact('books'));
    }

    
            public function store(Request $request)
            {
                $validated = $request->validate([
                    'book_id' => 'required|exists:books,id',
                    // Trong $validated
                    'title' => [
                        'required', 'string', 'max:255',
                        function ($attribute, $value, $fail) use ($request) {
                            $exists = BookChapter::where('book_id', $request->book_id)
                                ->where('title', $value)
                                ->exists();
                            if ($exists) {
                                $fail('Tiêu đề chương đã tồn tại trong sách này.');
                            }
                        },
                    ],
                    'chapter_order' => [
                        'required',
                        'integer',
                        // Kiểm tra trùng thứ tự chương trong cùng 1 sách
                        function ($attribute, $value, $fail) use ($request) {
                            $exists = BookChapter::where('book_id', $request->book_id)
                                ->where('chapter_order', $value)
                                ->exists();
                            if ($exists) {
                                $fail('Thứ tự chương này đã tồn tại trong sách đã chọn.');
                            }
                        },
                    ],
                    'content' => 'required|string',
                ]);

                $slug = \Str::slug($validated['title']);

                BookChapter::create([
                    ...$validated,
                    'slug' => $slug,
                ]);

                return redirect()->route('admin.chapters.index')->with('success', 'Thêm chương thành công!');
            }

    public function edit(BookChapter $chapter)
    {
        $books = Book::all();
        return view('admin.chapters.edit', compact('chapter', 'books'));
    }

    public function update(Request $request, BookChapter $chapter)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'title' => 'required|string',
            'chapter_order' => 'required|integer',
            'content' => 'required|string',
        ]);

        $chapter->update([
            'book_id' => $request->book_id,
            'title' => $request->title,
            'chapter_order' => $request->chapter_order,
            'content' => $request->content,
            'slug' => Str::slug($request->title),
        ]);

        return redirect()->route('admin.chapters.index')->with('success', 'Cập nhật chương thành công!');
    }

    public function destroy(BookChapter $chapter)
    {
        $chapter->delete();
        return redirect()->route('admin.chapters.index')->with('success', 'Xóa chương thành công!');
    }
    public function show(BookChapter $chapter)
{
    $previous = $chapter->previousChapter();
    $next = $chapter->nextChapter();

    return view('admin.chapters.show', compact('chapter', 'previous', 'next'));
}
public function getChapterOrders($bookId)
{
    $orders = \App\Models\BookChapter::where('book_id', $bookId)
        ->orderBy('chapter_order')
        ->pluck('chapter_order');

    return response()->json($orders);
}


}
