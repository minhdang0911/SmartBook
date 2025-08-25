<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use App\Models\Book;
use Illuminate\Http\Request;
use App\Services\CloudinaryService;

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

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name'  => 'required|string|max:100|unique:publishers,name',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ],
            [
                'name.required' => 'Tên nhà xuất bản không được để trống.',
                'name.max'      => 'Tên nhà xuất bản không được vượt quá 100 ký tự.',
                'name.unique'   => 'Tên nhà xuất bản đã tồn tại.',
            ]
        );

        if ($request->hasFile('image')) {
            $cloudinaryService = new CloudinaryService();
            $imageUrl = $cloudinaryService->uploadImageAvoidDuplicate(
                $request->file('image'),
                'publishers'
            );
            $validated['image_url'] = $imageUrl;
        }

        Publisher::create($validated);

        return redirect()->route('admin.publishers.index')
            ->with('success', 'Nhà xuất bản đã được thêm thành công!');
    }

    public function edit(Publisher $publisher)
    {
        return view('admin.publishers.edit', compact('publisher'));
    }

    public function update(Request $request, Publisher $publisher)
    {
        $validated = $request->validate(
            [
                'name'  => 'required|string|max:100|unique:publishers,name,' . $publisher->id,
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ],
            [
                'name.required' => 'Tên nhà xuất bản không được để trống.',
                'name.max'      => 'Tên nhà xuất bản không được vượt quá 100 ký tự.',
                'name.unique'   => 'Tên nhà xuất bản đã tồn tại.',
            ]
        );

        if ($request->hasFile('image')) {
            $cloudinaryService = new CloudinaryService();
            $imageUrl = $cloudinaryService->uploadImageAvoidDuplicate(
                $request->file('image'),
                'publishers'
            );
            $validated['image_url'] = $imageUrl;
        }

        $publisher->update($validated);

        return redirect()->route('admin.publishers.index')
            ->with('success', 'Nhà xuất bản đã được cập nhật.');
    }

    public function destroy(Publisher $publisher)
    {
        $hasBooks = Book::where('publisher_id', $publisher->id)->exists();

        if ($hasBooks) {
            return redirect()->route('admin.publishers.index')
                ->with('error', 'Không thể xóa nhà xuất bản vì đang có sách thuộc nhà xuất bản này.');
        }

        // Xóa ảnh trên Cloudinary nếu có
        if (!empty($publisher->image_url)) {
            try {
                $cloudinaryService = new CloudinaryService();
                $cloudinaryService->deleteImageByUrl($publisher->image_url);
            } catch (\Exception $e) {
                \Log::error('Không thể xóa ảnh trên Cloudinary', [
                    'error' => $e->getMessage(),
                    'url'   => $publisher->image_url,
                ]);
            }
        }

        $publisher->delete();

        return redirect()->route('admin.publishers.index')
            ->with('success', 'Nhà xuất bản đã bị xóa thành công.');
    }

    public function apiIndex()
    {
        $publishers = Publisher::orderBy('name')
            ->orderBy('image_url')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $publishers,
        ]);
    }
}
