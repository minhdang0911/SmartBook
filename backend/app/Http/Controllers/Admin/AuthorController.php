<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAuthorRequest;
use App\Http\Requests\Admin\UpdateAuthorRequest;
use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\Request;


class AuthorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $authors = Author::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%$search%");
        })->orderBy('id', 'desc')->paginate(10);

        return view('admin.authors.index', compact('authors', 'search'));
    }

    public function create()
    {
        return view('admin.authors.create');
    }

    public function store(StoreAuthorRequest $request)
    {
        Author::create($request->validated());

        return redirect()->route('admin.authors.index')
            ->with('success', '✅ Tác giả đã được thêm thành công!');
    }

    public function edit(Author $author)
    {
        return view('admin.authors.edit', compact('author'));
    }

    public function update(UpdateAuthorRequest $request, Author $author)
    {
        $author->update($request->validated());

        return redirect()->route('admin.authors.index')
            ->with('success', '✅ Tác giả đã được cập nhật.');
    }

    public function destroy(Author $author)
    {
        $hasBooks = Book::where('author_id', $author->id)->exists();

        if ($hasBooks) {
            return redirect()->route('admin.authors.index')
                ->with('error', '❌ Không thể xóa tác giả vì đang có sách thuộc tác giả này.');
        }

        $author->delete();

        return redirect()->route('admin.authors.index')
            ->with('success', '🗑️ Tác giả đã bị xóa thành công.');
    }
}
