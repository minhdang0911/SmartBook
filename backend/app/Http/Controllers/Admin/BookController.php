<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Category;
use App\Models\BookImage;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with(['author', 'publisher', 'category'])->paginate(10);
        return view('admin.books.index', compact('books'));
    }

    public function create()
    {
        $authors = Author::all();
        $publishers = Publisher::all();
        $categories = Category::all();
        return view('admin.books.create', compact('authors', 'publishers', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'cover_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'author_id' => 'required|exists:authors,id',
            'publisher_id' => 'required|exists:publishers,id',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => 'dz7y2yufu',
                'api_key'    => '155772835832488',
                'api_secret' => 'Ho_6ApwWCE5s1dYtBzHAbPlSSD0',
            ],
            'url' => ['secure' => true]
        ]);

        $coverUrl = null;
        if ($request->hasFile('cover_image')) {
            $coverUpload = $cloudinary->uploadApi()->upload(
                $request->file('cover_image')->getRealPath(),
                ['folder' => 'book_covers']
            );
            $coverUrl = $coverUpload['secure_url'] ?? null;
        }

        $book = Book::create([
            'title' => $request->title,
            'author_id' => $request->author_id,
            'publisher_id' => $request->publisher_id,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'stock' => $request->stock,
            'description' => $request->description,
            'cover_image' => $coverUrl,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $uploaded = $cloudinary->uploadApi()->upload(
                    $image->getRealPath(),
                    ['folder' => 'book_images']
                );

                if (isset($uploaded['secure_url'])) {
                    BookImage::create([
                        'book_id' => $book->id,
                        'image_url' => $uploaded['secure_url'],
                        'is_main' => $index === 0 ? 1 : 0,
                    ]);
                }
            }
        }

        return redirect()->route('admin.books.index')->with('success', 'Đã thêm sách và ảnh.');
    }

   public function edit(Book $book)
{
    $book->load('images'); // Nạp luôn quan hệ

    $authors = Author::all();
    $publishers = Publisher::all();
    $categories = Category::all();

    return view('admin.books.edit', compact('book', 'authors', 'publishers', 'categories'));
}



public function update(Request $request, Book $book)
{
    $request->validate([
        'title' => 'required|max:255',
        'author_id' => 'required|exists:authors,id',
        'publisher_id' => 'required|exists:publishers,id',
        'category_id' => 'required|exists:categories,id',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    // ✅ Khởi tạo Cloudinary
    $cloudinary = new Cloudinary([
        'cloud' => [
            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
            'api_key'    => env('CLOUDINARY_API_KEY'),
            'api_secret' => env('CLOUDINARY_API_SECRET'),
        ],
        'url' => ['secure' => true]
    ]);

    // ✅ Cập nhật thông tin cơ bản
    $book->update($request->only([
        'title', 'author_id', 'publisher_id', 'category_id', 'price', 'stock', 'description'
    ]));

    // ✅ Nếu có ảnh bìa mới
    if ($request->hasFile('cover_image')) {
        $upload = $cloudinary->uploadApi()->upload(
            $request->file('cover_image')->getRealPath(),
            ['folder' => 'book_covers']
        );
        $book->update(['cover_image' => $upload['secure_url']]);
    }

    // ✅ Nếu có ảnh phụ mới
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $uploaded = $cloudinary->uploadApi()->upload(
                $image->getRealPath(),
                ['folder' => 'book_images']
            );
            \App\Models\BookImage::create([
                'book_id' => $book->id,
                'image_url' => $uploaded['secure_url'],
                'is_main' => 0,
            ]);
        }
    }

    return redirect()->route('admin.books.index')->with('success', 'Cập nhật sách thành công.');
}



    public function destroy(Book $book)
    {
        $book->delete();
        return redirect()->route('admin.books.index')->with('success', 'Đã xóa sách.');
    }
}
