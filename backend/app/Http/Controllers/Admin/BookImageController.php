<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookImage;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;

class BookImageController extends Controller
{
    protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    public function index()
    {
        $images = BookImage::with('book')->latest()->paginate(10); // hoặc cái m đang dùng
        $books = Book::select('id', 'title')->get(); // LẤY TOÀN BỘ SÁCH

        return view('admin.book_images.index', compact('images', 'books'));
    }


    public function create()
    {
        $books = Book::select('id', 'title')->get();
        return view('admin.book_images.create', compact('books'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'image_url' => 'required|image|max:5120',
        ]);

        $imageUrl = $this->cloudinary->uploadImage($request->file('image_url'), 'book_images');

        BookImage::create([
            'book_id' => $request->book_id,
            'image_url' => $imageUrl,
        ]);

        return redirect()->route('admin.book_images.index')->with('success', '🖼️ Đã thêm ảnh phụ thành công!');
    }

    public function edit(BookImage $book_image)
    {
        $books = Book::select('id', 'title')->get();
        return view('admin.book_images.edit', compact('book_image', 'books'));
    }

    public function update(Request $request, BookImage $book_image)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'image_url' => 'nullable|image|max:5120',
        ]);

        $data = ['book_id' => $request->book_id];

        if ($request->hasFile('image_url')) {
            if ($book_image->image_url) {
                $this->cloudinary->deleteImageByPublicId($book_image->image_url);
            }

            $newUrl = $this->cloudinary->uploadImage($request->file('image_url'), 'book_images');
            $data['image_url'] = $newUrl;
        }

        $book_image->update($data);

        return redirect()->route('admin.book_images.index')->with('success', '🔁 Ảnh phụ đã được cập nhật!');
    }

    public function destroy(BookImage $book_image)
    {
        if ($book_image->image_url) {
            $this->cloudinary->deleteImageByPublicId($book_image->image_url);
        }

        $book_image->delete();

        return redirect()->route('admin.book_images.index')->with('success', '🗑️ Ảnh phụ đã được xoá.');
    }
}
