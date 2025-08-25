<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:100|unique:authors,name',
            ],
            [
                'name.required' => 'Tên tác giả không được để trống.',
                'name.max' => 'Tên tác giả không được vượt quá 100 ký tự.',
                'name.unique' => 'Tên tác giả đã tồn tại.',
            ]
        );

        Author::create($validated);

        return redirect()->route('admin.authors.index')
            ->with('success', 'Tác giả đã được thêm thành công!');
    }

    public function edit(Author $author)
    {
        return view('admin.authors.edit', compact('author'));
    }

    public function update(Request $request, Author $author)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:100|unique:authors,name,' . $author->id,
            ],
            [
                'name.required' => 'Tên tác giả không được để trống.',
                'name.max' => 'Tên tác giả không được vượt quá 100 ký tự.',
                'name.unique' => 'Tên tác giả đã tồn tại.',
            ]
        );

        $author->update($validated);

        return redirect()->route('admin.authors.index')
            ->with('success', 'Tác giả đã được cập nhật.');
    }

    public function destroy(Author $author)
    {
        $hasBooks = Book::where('author_id', $author->id)->exists();

        if ($hasBooks) {
            return redirect()->route('admin.authors.index')
                ->with('error', 'Không thể xóa tác giả vì đang có sách thuộc tác giả này.');
        }

        $author->delete();

        return redirect()->route('admin.authors.index')
            ->with('success', 'Tác giả đã bị xóa thành công.');
    }
}
