@extends('layouts.app')

@section('title', 'Chỉnh sửa Sách')

@section('content')
<div class="container mt-4 mt-md-5">
    <h1 class="mb-4 text-center text-md-start">✏️ Chỉnh sửa sách</h1>

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
        <div class="row g-3">
            <!-- Tiêu đề -->
            <div class="col-12">
                <label for="title" class="form-label">Tiêu đề sách</label>
                <input type="text" name="title" id="title" value="{{ old('title', $book->title) }}" class="form-control" required>
            </div>

            <!-- Tác giả -->
            <div class="col-md-6">
                <label for="author_id" class="form-label">Tác giả</label>
                <select name="author_id" id="author_id" class="form-control" required>
                    <option disabled>-- Chọn tác giả --</option>
                    @foreach ($authors as $author)
                        <option value="{{ $author->id }}" {{ old('author_id', $book->author_id) == $author->id ? 'selected' : '' }}>
                            {{ $author->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Nhà xuất bản -->
            <div class="col-md-6">
                <label for="publisher_id" class="form-label">Nhà xuất bản</label>
                <select name="publisher_id" id="publisher_id" class="form-control" required>
                    <option disabled>-- Chọn NXB --</option>
                    @foreach ($publishers as $publisher)
                        <option value="{{ $publisher->id }}" {{ old('publisher_id', $book->publisher_id) == $publisher->id ? 'selected' : '' }}>
                            {{ $publisher->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Danh mục -->
            <div class="col-12">
                <label for="category_id" class="form-label">Danh mục</label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <option disabled>-- Chọn danh mục --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Loại sách -->
            @php $selectedType = old('is_physical', $book->is_physical); @endphp
            <div class="col-12">
                <label class="form-label d-block">Loại sách</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="is_physical" id="ebook" value="0"
                        {{ (string)$selectedType === '0' ? 'checked' : '' }}>
                    <label class="form-check-label" for="ebook">📱 Ebook</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="is_physical" id="physical" value="1"
                        {{ (string)$selectedType === '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="physical">📚 Sách giấy</label>
                </div>
            </div>

            <!-- Giá -->
            <div class="col-md-6">
                <label for="price" class="form-label">Giá (VNĐ)</label>
                <input type="number" name="price" id="price" class="form-control"
                       value="{{ old('price', $book->price) }}"
                       {{ (string)$selectedType === '0' ? 'disabled' : 'required' }}>
            </div>

            <!-- Tồn kho -->
            <div class="col-md-6">
                <label for="stock" class="form-label">Tồn kho</label>
                <input type="number" name="stock" id="stock" class="form-control"
                       value="{{ old('stock', $book->stock) }}"
                       {{ (string)$selectedType === '0' ? 'disabled' : 'required' }}>
            </div>

            <!-- Mô tả -->
            <div class="col-12">
                <label for="description" class="form-label">Mô tả</label>
                <textarea name="description" id="description" class="form-control my-editor" rows="7">{{ old('description', $book->description) }}</textarea>
            </div>

            <!-- Ảnh bìa -->
            <div class="col-12">
                <label class="form-label">Ảnh bìa hiện tại</label><br>
                <img src="{{ $book->cover_image }}" alt="Ảnh bìa" class="rounded img-fluid" style="max-height: 200px;">
            </div>

            <div class="col-12">
                <label for="cover_image" class="form-label">Thay ảnh bìa (tùy chọn)</label>
                <input type="file" name="cover_image" id="cover_image" class="form-control" accept="image/*">
                <img id="previewCover" class="mt-2 img-fluid rounded" style="max-height: 200px; display: none;" />
            </div>

            <!-- Ảnh phụ hiện tại -->
            @if ($book->images->count())
                <div class="col-12">
                    <label class="form-label">Ảnh phụ hiện tại</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($book->images as $img)
                            <img src="{{ $img->image_url }}" class="img-fluid rounded" style="height: 100px;">
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Ảnh phụ mới -->
            <div class="col-12">
                <label for="images" class="form-label">Thêm ảnh phụ mới</label>
                <input type="file" name="images[]" id="images" class="form-control" accept="image/*" multiple>
                <div id="previewImages" class="d-flex flex-wrap gap-2 mt-2"></div>
            </div>

            <!-- Nút -->
            <div class="col-12 d-flex gap-2 justify-content-center justify-content-md-start">
                <button class="btn btn-primary">💾 Cập nhật</button>
                <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">⬅️ Quay lại</a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('.my-editor'))
        .catch(error => console.error(error));

    // Preview ảnh bìa
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
        const container = document.getElementById('previewImages');
        container.innerHTML = '';
        Array.from(e.target.files).forEach(file => {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.height = '100px';
            img.classList.add('rounded', 'img-fluid');
            container.appendChild(img);
        });
    });

    // Toggle field
    function toggleFields() {
        const isPhysical = document.querySelector('input[name="is_physical"]:checked').value === '1';
        document.getElementById('price').disabled = !isPhysical;
        document.getElementById('stock').disabled = !isPhysical;
    }

    document.querySelectorAll('input[name="is_physical"]').forEach(input => {
        input.addEventListener('change', toggleFields);
    });

    toggleFields();
</script>
@endpush
