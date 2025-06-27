<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use App\Models\Book;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StorePublisherRequest;
use App\Http\Requests\Admin\UpdatePublisherRequest;

class PublisherController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $publishers = Publisher::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%$search%");
        })->orderBy('name')->paginate(10);

        return view('admin.publishers.index', compact('publishers', 'search'));
    }

    public function create()
    {
        return view('admin.publishers.create');
    }

    public function store(StorePublisherRequest $request)
    {
        Publisher::create($request->validated());

        return redirect()->route('admin.publishers.index')
            ->with('success', '✅ Nhà xuất bản đã được thêm thành công!');
    }

    public function edit(Publisher $publisher)
    {
        return view('admin.publishers.edit', compact('publisher'));
    }

    public function update(UpdatePublisherRequest $request, Publisher $publisher)
    {
        $publisher->update($request->validated());

        return redirect()->route('admin.publishers.index')
            ->with('success', '✅ Nhà xuất bản đã được cập nhật.');
    }

    public function destroy(Publisher $publisher)
    {
        $hasBooks = Book::where('publisher_id', $publisher->id)->exists();

        if ($hasBooks) {
            return redirect()->route('admin.publishers.index')
                ->with('error', '❌ Không thể xóa nhà xuất bản vì đang có sách thuộc nhà xuất bản này.');
        }

        $publisher->delete();

        return redirect()->route('admin.publishers.index')
            ->with('success', '🗑️ Nhà xuất bản đã bị xóa thành công.');
    }
}
