<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Book;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $categories = Category::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%$search%");
        })->orderBy('name')->paginate(10);

        return view('admin.categories.index', compact('categories', 'search'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:100|unique:categories,name',
            ],
            [
                'name.required' => 'Tên danh mục không được để trống.',
                'name.max' => 'Tên danh mục không được vượt quá 100 ký tự.',
                'name.unique' => 'Tên danh mục đã tồn tại.',
            ]
        );

        Category::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Danh mục đã được thêm thành công!');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate(
            [
                'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            ],
            [
                'name.required' => 'Tên danh mục không được để trống.',
                'name.max' => 'Tên danh mục không được vượt quá 100 ký tự.',
                'name.unique' => 'Tên danh mục đã tồn tại.',
            ]
        );

        $category->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Danh mục đã được cập nhật.');
    }

    public function destroy(Category $category)
    {
        $hasBooks = Book::where('category_id', $category->id)->exists();

        if ($hasBooks) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Không thể xóa danh mục vì đang có sách thuộc danh mục này.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Danh mục đã bị xóa thành công.');
    }
}
