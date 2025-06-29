@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">✏️ Chỉnh sửa sách</h1>

    {{-- Hiển thị lỗi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.books.update', $book) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Tiêu đề -->
        <div class="mb-3">
            <label>Tiêu đề sách</label>
            <input type="text" name="title" value="{{ old('title', $book->title) }}" class="form-control" required>
        </div>

        <!-- Tác giả -->
        <div class="mb-3">
            <label>Tác giả</label>
            <select name="author_id" class="form-control" required>
                @foreach ($authors as $author)
                    <option value="{{ $author->id }}" {{ old('author_id', $book->author_id) == $author->id ? 'selected' : '' }}>
                        {{ $author->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Nhà xuất bản -->
        <div class="mb-3">
            <label>Nhà xuất bản</label>
            <select name="publisher_id" class="form-control" required>
                @foreach ($publishers as $publisher)
                    <option value="{{ $publisher->id }}" {{ old('publisher_id', $book->publisher_id) == $publisher->id ? 'selected' : '' }}>
                        {{ $publisher->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Danh mục -->
        <div class="mb-3">
            <label>Danh mục</label>
            <select name="category_id" class="form-control" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Giá -->
        <div class="mb-3">
            <label>Giá</label>
            <input type="number" name="price" step="1000" min="0" value="{{ old('price', $book->price) }}" class="form-control" required>
        </div>

        <!-- Tồn kho -->
        <div class="mb-3">
            <label>Số lượng tồn kho</label>
            <input type="number" name="stock" min="0" value="{{ old('stock', $book->stock) }}" class="form-control" required>
        </div>

        <!-- Mô tả -->
        <div class="mb-3">
            <label>Mô tả</label>
            <textarea name="description" class="form-control my-editor">{{ old('description', $book->description) }}</textarea>
        </div>

        <!-- Ảnh bìa hiện tại -->
        @if ($book->cover_image)
            <div class="mb-3">
                <label>Ảnh bìa hiện tại:</label><br>
                <img src="{{ $book->cover_image }}" alt="Ảnh bìa" style="height: 200px;" class="rounded">
            </div>
        @endif

        <!-- Thay ảnh bìa -->
        <div class="mb-3">
            <label>Thay ảnh bìa (không bắt buộc)</label>
            <input type="file" name="cover_image" class="form-control" accept="image/*">
        </div>

        <!-- Ảnh phụ hiện tại -->
        @if ($book->images && $book->images->count())
            <div class="mb-3">
                <label>Ảnh phụ hiện tại:</label><br>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($book->images as $img)
                        <img src="{{ $img->image_url }}" alt="Ảnh phụ" style="height: 100px;" class="rounded">
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Thêm ảnh phụ mới -->
        <div class="mb-3">
            <label>Thêm ảnh phụ mới (có thể chọn nhiều)</label>
            <input type="file" name="images[]" class="form-control" multiple accept="image/*">
        </div>

        <button class="btn btn-primary">💾 Cập nhật</button>
        <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">⬅️ Quay lại</a>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('.my-editor'))
        .catch(error => {
            console.error(error);
        });
</script>
@endpush
