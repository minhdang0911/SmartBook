<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Book, Author, Publisher, Category, BookImage};
use App\Services\CloudinaryService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    public function index(Request $request)
    {
        $query = Book::with(['author', 'publisher', 'category']);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('publisher_id')) {
            $query->where('publisher_id', $request->publisher_id);
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'in_stock') {
                $query->where('stock', '>', 0);
            } elseif ($request->stock_status === 'out_of_stock') {
                $query->where('stock', '=', 0);
            }
        }

        if ($request->filled('is_physical')) {
            $query->where('is_physical', $request->is_physical);
        }

        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'views':
                    $query->orderByDesc('views');
                    break;
                case 'likes':
                    $query->orderByDesc('likes');
                    break;
                case 'rating':
                    $query->orderByDesc('rating_avg');
                    break;
                case 'price_asc':
                    $query->orderBy('price');
                    break;
                case 'price_desc':
                    $query->orderByDesc('price');
                    break;
                case 'latest':
                default:
                    $query->orderByDesc('created_at');
                    break;
            }
        } else {
            $query->orderByDesc('created_at');
        }

        $books = $query->paginate(10)->withQueryString();

        return view('admin.books.index', [
            'books' => $books,
            'authors' => Author::all(),
            'categories' => Category::all(),
            'publishers' => Publisher::all(),
        ]);
    }

    public function create()
    {
        return view('admin.books.create', [
            'authors' => Author::all(),
            'publishers' => Publisher::all(),
            'categories' => Category::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'cover_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'author_id' => 'required|exists:authors,id',
            'publisher_id' => 'required|exists:publishers,id',
            'category_id' => 'required|exists:categories,id',
            'is_physical' => 'required|boolean',
            'description' => 'nullable|string',
            'price' => $request->is_physical ? 'required|numeric|min:0' : 'nullable',
            'stock' => $request->is_physical ? 'required|integer|min:0' : 'nullable',
        ]);

        $coverUrl = $this->cloudinary->uploadImage($request->file('cover_image'), 'book_covers');

        $book = Book::create([
            'title' => $request->title,
            'author_id' => $request->author_id,
            'publisher_id' => $request->publisher_id,
            'category_id' => $request->category_id,
            'is_physical' => $request->input('is_physical'), // ✅ thêm dòng này
            'price' => $request->price,
            'stock' => $request->stock,
            'description' => $request->description,
            'cover_image' => $coverUrl,
            ]);

        BookImage::create([
            'book_id' => $book->id,
            'image_url' => $coverUrl,
            'is_main' => 1,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageUrl = $this->cloudinary->uploadImage($image, 'book_images');
                BookImage::create([
                    'book_id' => $book->id,
                    'image_url' => $imageUrl,
                    'is_main' => 0,
                ]);
            }
        }

        return redirect()->route('admin.books.index')->with('success', 'Đã thêm sách và ảnh.');
    }

    public function edit(Book $book)
    {
        $book->load('images');
        return view('admin.books.edit', [
            'book' => $book,
            'authors' => Author::all(),
            'publishers' => Publisher::all(),
            'categories' => Category::all(),
        ]);
    }

public function update(Request $request, Book $book)
{
    $isPhysical = $request->input('is_physical') == 1;

    $request->validate([
        'title' => 'required|max:255',
        'author_id' => 'required|exists:authors,id',
        'publisher_id' => 'required|exists:publishers,id',
        'category_id' => 'required|exists:categories,id',
        'is_physical' => 'required|boolean',
        'price' => $isPhysical ? 'required|numeric|min:0' : 'nullable',
        'stock' => $isPhysical ? 'required|integer|min:0' : 'nullable',
        'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
    ]);

    $data = $request->only([
        'title',
        'author_id',
        'publisher_id',
        'category_id',
        'price',
        'stock',
        'description',
    ]);

    // ⚠️ Thêm dòng này để cập nhật đúng loại sách
    $data['is_physical'] = $request->input('is_physical');

    // Xử lý ảnh bìa
    if ($request->hasFile('cover_image')) {
        if ($book->cover_image) {
            $this->cloudinary->deleteImageByPublicId($book->cover_image);
            BookImage::where('book_id', $book->id)->where('is_main', 1)->delete();
        }

        $newCover = $this->cloudinary->uploadImage($request->file('cover_image'), 'book_covers');
        $data['cover_image'] = $newCover;

        BookImage::create([
            'book_id' => $book->id,
            'image_url' => $newCover,
            'is_main' => 1,
        ]);
    } else {
        $data['cover_image'] = $book->cover_image;
    }

    $book->update($data);

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $imageUrl = $this->cloudinary->uploadImage($image, 'book_images');
            BookImage::create([
                'book_id' => $book->id,
                'image_url' => $imageUrl,
                'is_main' => 0,
            ]);
        }
    }

    return redirect()->route('admin.books.index')->with('success', 'Cập nhật sách thành công.');
}



    public function destroy(Book $book)
    {
        if ($book->cover_image) {
            $this->cloudinary->deleteImageByPublicId($book->cover_image);
        }

        foreach ($book->images as $img) {
            $this->cloudinary->deleteImageByPublicId($img->image_url);
            $img->delete();
        }

        $book->delete();
        return redirect()->route('admin.books.index')->with('success', 'Đã xóa sách.');
    }

    // ✅ Thêm hàm upload CKEditor
    public function upload(Request $request)
    {
        if ($request->hasFile('upload')) {
            $uploadedFile = $request->file('upload');
            $url = $this->cloudinary->uploadImage($uploadedFile, 'ckeditor_uploads');

            return response()->json([
                'url' => $url
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
