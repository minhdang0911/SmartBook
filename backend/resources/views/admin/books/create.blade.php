@extends('layouts.app')

@section('title', 'Thêm Sách Mới')

@section('content')
    <div class="container mt-4 mt-md-5">
        <h1 class="mb-4 text-center text-md-start">➕ Thêm sách mới</h1>

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
            <div class="row g-3">
                <!-- Tiêu đề -->
                <div class="col-12">
                    <label for="title" class="form-label">Tiêu đề sách</label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}"
                        required>
                </div>

                <!-- Tác giả -->
                <div class="col-12 col-md-6">
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
                <div class="col-12 col-md-6">
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
                <div class="col-12">
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

                <!-- Giá và Tồn kho -->
                <div class="col-12 col-md-6">
                    <label for="price" class="form-label">Giá (VNĐ)</label>
                    <input type="number" name="price" id="price" class="form-control" min="0"
                        value="{{ old('price') }}" required>
                </div>
                <div class="col-12 col-md-6">
                    <label for="stock" class="form-label">Số lượng tồn kho</label>
                    <input type="number" name="stock" id="stock" class="form-control" min="0"
                        value="{{ old('stock') }}" required>
                </div>

                <!-- Mô tả -->
                <div class="col-12">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea name="description" id="description" class="form-control my-editor" rows="5">{{ old('description') }}</textarea>
                </div>

                <!-- Ảnh bìa -->
                <div class="col-12">
                    <label for="cover_image" class="form-label">Ảnh bìa (ảnh chính)</label>
                    <input type="file" name="cover_image" id="cover_image" class="form-control" accept="image/*" required>
                    <img id="previewCover" class="mt-2 rounded img-fluid" style="max-height: 200px; display: none;" alt="Ảnh bìa" />
                </div>

                <!-- Ảnh phụ -->
                <div class="col-12">
                    <label for="images" class="form-label">Ảnh phụ (có thể chọn nhiều)</label>
                    <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*">
                    <div id="previewImages" class="d-flex flex-wrap mt-2 gap-2"></div>
                </div>

                <!-- Nút điều khiển -->
                <div class="col-12 d-flex gap-2 justify-content-center justify-content-md-start">
                    <button class="btn btn-success">💾 Lưu</button>
                    <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">⬅️ Quay lại</a>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <style>
        @media (max-width: 576px) {
            h1 {
                font-size: 1.5rem;
            }
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            #previewImages img {
                height: 80px;
                max-width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('.my-editor'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
            })
            .catch(error => {
                console.error(error);
            });

        // Preview ảnh chính
        document.getElementById('cover_image').addEventListener('change', function(e) {
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
        document.getElementById('images').addEventListener('change', function(e) {
            const previewContainer = document.getElementById('previewImages');
            previewContainer.innerHTML = '';
            Array.from(e.target.files).forEach(file => {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.style.height = '100px';
                img.style.maxWidth = '100%';
                img.classList.add('rounded');
                previewContainer.appendChild(img);
            });
        });
    </script>
@endpush
