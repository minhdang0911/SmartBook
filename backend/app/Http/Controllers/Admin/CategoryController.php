<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Book;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;

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

    public function store(StoreCategoryRequest $request)
    {
        Category::create($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Danh mục đã được thêm thành công!');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

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
