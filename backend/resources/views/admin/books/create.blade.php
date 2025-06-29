@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">➕ Thêm sách mới</h1>

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

    <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Tiêu đề -->
        <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề sách</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
        </div>

        <!-- Tác giả -->
        <div class="mb-3">
            <label for="author_id" class="form-label">Tác giả</label>
            <select name="author_id" id="author_id" class="form-control" required>
                <option disabled selected>-- Chọn tác giả --</option>
                @foreach ($authors as $author)
                    <option value="{{ $author->id }}" {{ old('author_id') == $author->id ? 'selected' : '' }}>
                        {{ $author->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Nhà xuất bản -->
        <div class="mb-3">
            <label for="publisher_id" class="form-label">Nhà xuất bản</label>
            <select name="publisher_id" id="publisher_id" class="form-control" required>
                <option disabled selected>-- Chọn NXB --</option>
                @foreach ($publishers as $publisher)
                    <option value="{{ $publisher->id }}" {{ old('publisher_id') == $publisher->id ? 'selected' : '' }}>
                        {{ $publisher->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Danh mục -->
        <div class="mb-3">
            <label for="category_id" class="form-label">Danh mục</label>
            <select name="category_id" id="category_id" class="form-control" required>
                <option disabled selected>-- Chọn danh mục --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Giá -->
        <div class="mb-3">
            <label for="price" class="form-label">Giá (VNĐ)</label>
            <input type="number" name="price" id="price" class="form-control" min="0" value="{{ old('price') }}" required>
        </div>

        <!-- Tồn kho -->
        <div class="mb-3">
            <label for="stock" class="form-label">Số lượng tồn kho</label>
            <input type="number" name="stock" id="stock" class="form-control" min="0" value="{{ old('stock') }}" required>
        </div>

        <!-- Mô tả -->
        <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <textarea name="description" id="description" class="form-control my-editor" rows="5">{{ old('description') }}</textarea>
        </div>

        <!-- Ảnh bìa -->
        <div class="mb-3">
            <label for="cover_image" class="form-label">Ảnh bìa (ảnh chính)</label>
            <input type="file" name="cover_image" id="cover_image" class="form-control" accept="image/*" required>
            <img id="previewCover" class="mt-2 rounded" style="max-height: 200px; display:none;" />
        </div>

        <!-- Ảnh phụ -->
        <div class="mb-3">
            <label for="images" class="form-label">Ảnh phụ (có thể chọn nhiều)</label>
            <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*">
            <div id="previewImages" class="d-flex flex-wrap mt-2 gap-2"></div>
        </div>

        <button class="btn btn-success">💾 Lưu</button>
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

    // Preview ảnh chính
    document.getElementById('cover_image').addEventListener('change', function (e) {
        const file = e.target.files[0];
        const preview = document.getElementById('previewCover');
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    });

    // Preview ảnh phụ
    document.getElementById('images').addEventListener('change', function (e) {
        const previewContainer = document.getElementById('previewImages');
        previewContainer.innerHTML = '';
        Array.from(e.target.files).forEach(file => {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.height = '100px';
            img.classList.add('rounded');
            previewContainer.appendChild(img);
        });
    });
</script>
@endpush
