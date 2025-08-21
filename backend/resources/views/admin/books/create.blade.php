@extends('layouts.app')

@section('title', 'Thêm Sách Mới')

@section('content')
<div class="container mt-4 mt-md-5">
    <h1 class="mb-4 text-center text-md-start">➕ Thêm sách mới</h1>

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
                <input type="text" name="title" id="title" value="{{ old('title') }}" class="form-control" required>
            </div>

            <!-- Tác giả -->
            <div class="col-md-6">
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
            <div class="col-md-6">
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

            <!-- Loại sách -->
            <div class="col-12">
                <label class="form-label d-block">Loại sách</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="is_physical" id="ebook" value="0"
                        {{ old('is_physical', 1) == 0 ? 'checked' : '' }}>
                    <label class="form-check-label" for="ebook">📱 Ebook</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="is_physical" id="physical" value="1"
                        {{ old('is_physical', 1) == 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="physical">📚 Sách giấy</label>
                </div>
            </div>

            <!-- Giá gốc -->
            <div class="col-md-4">
                <label for="price" class="form-label">Giá gốc (VNĐ)</label>
                <input type="number" name="price" id="price" class="form-control"
                       value="{{ old('price') }}" {{ old('is_physical', 1) == 0 ? 'disabled' : 'required' }}>
            </div>

            <!-- Phần trăm giảm giá -->
            <div class="col-md-4">
                <label for="discount_percent" class="form-label">% Giảm giá</label>
                <div class="input-group">
                    <input type="number" id="discount_percent" class="form-control" 
                           value="{{ old('discount_percent', 0) }}" min="0" max="99" step="0.01"
                           {{ old('is_physical', 1) == 0 ? 'disabled' : '' }}>
                    <span class="input-group-text">%</span>
                </div>
                <small class="text-muted">Để trống hoặc 0 nếu không giảm giá</small>
            </div>

            <!-- Giá sau giảm (tự động tính) -->
            <div class="col-md-4">
                <label for="discount_price" class="form-label">Giá sau giảm (VNĐ)</label>
                <input type="number" name="discount_price" id="discount_price" class="form-control bg-light" 
                       value="{{ old('discount_price') }}" readonly>
                <small class="text-muted">Tự động tính từ % giảm giá</small>
            </div>

            <!-- Tồn kho -->
            <div class="col-md-6">
                <label for="stock" class="form-label">Tồn kho</label>
                <input type="number" name="stock" id="stock" class="form-control"
                       value="{{ old('stock') }}" {{ old('is_physical', 1) == 0 ? 'disabled' : 'required' }}>
            </div>

            <!-- Hiển thị tóm tắt giá -->
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title mb-2">💰 Tóm tắt giá</h6>
                        <div id="price-summary">
                            <div>Giá gốc: <span id="original-price-display">0₫</span></div>
                            <div class="text-success" id="discount-info" style="display: none;">
                                Giảm <span id="discount-percent-display">0</span>%: 
                                <strong><span id="final-price-display">0₫</span></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mô tả -->
            <div class="col-12">
                <label for="description" class="form-label">Mô tả</label>
                <textarea name="description" id="description" class="form-control my-editor" rows="7">{{ old('description') }}</textarea>
            </div>

            <!-- Ảnh chính -->
            <div class="col-12">
                <label for="cover_image" class="form-label">Ảnh bìa</label>
                <input type="file" name="cover_image" id="cover_image" class="form-control" accept="image/*" required>
                <img id="previewCover" class="mt-2 img-fluid rounded" style="max-height: 200px; display: none;" />
            </div>

            <!-- Ảnh phụ -->
            <div class="col-12">
                <label for="images" class="form-label">Ảnh phụ (có thể chọn nhiều)</label>
                <input type="file" name="images[]" id="images" class="form-control" accept="image/*" multiple>
                <div id="previewImages" class="d-flex flex-wrap gap-2 mt-2"></div>
            </div>

            <!-- Nút -->
            <div class="col-12 d-flex gap-2 justify-content-center justify-content-md-start">
                <button class="btn btn-success">💾 Lưu</button>
                <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">⬅️ Quay lại</a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor.create(document.querySelector('.my-editor')).catch(error => console.error(error));

    // Hiển thị preview ảnh
    document.getElementById('cover_image').addEventListener('change', e => {
        const file = e.target.files[0];
        const preview = document.getElementById('previewCover');
        preview.src = file ? URL.createObjectURL(file) : '';
        preview.style.display = file ? 'block' : 'none';
    });

    document.getElementById('images').addEventListener('change', e => {
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

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    // Tính giá giảm tự động
    function calculateDiscount() {
        const priceInput = document.getElementById('price');
        const discountPercentInput = document.getElementById('discount_percent');
        const discountPriceInput = document.getElementById('discount_price');
        
        const originalPrice = parseFloat(priceInput.value) || 0;
        const discountPercent = parseFloat(discountPercentInput.value) || 0;
        
        // Update price summary display
        document.getElementById('original-price-display').textContent = formatCurrency(originalPrice);
        
        if (discountPercent > 0 && originalPrice > 0) {
            const discountAmount = originalPrice * (discountPercent / 100);
            const finalPrice = originalPrice - discountAmount;
            
            discountPriceInput.value = Math.round(finalPrice);
            
            // Show discount info
            document.getElementById('discount-percent-display').textContent = discountPercent;
            document.getElementById('final-price-display').textContent = formatCurrency(finalPrice);
            document.getElementById('discount-info').style.display = 'block';
        } else {
            discountPriceInput.value = '';
            document.getElementById('discount-info').style.display = 'none';
        }
    }

    // Ẩn/hiện các trường theo loại sách
    function toggleFields() {
        const isPhysical = document.querySelector('input[name="is_physical"]:checked').value === '1';
        const fields = ['price', 'stock', 'discount_percent'];
        
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            field.disabled = !isPhysical;
            if (!isPhysical) {
                field.value = '';
            }
        });
        
        // Update price display
        if (isPhysical) {
            calculateDiscount();
        } else {
            document.getElementById('discount_price').value = '';
            document.getElementById('original-price-display').textContent = '0₫';
            document.getElementById('discount-info').style.display = 'none';
        }
    }

    // Event listeners
    document.getElementById('price').addEventListener('input', calculateDiscount);
    document.getElementById('discount_percent').addEventListener('input', calculateDiscount);

    document.querySelectorAll('input[name="is_physical"]').forEach(input => {
        input.addEventListener('change', toggleFields);
    });

    // Khởi tạo khi trang load
    toggleFields();
    calculateDiscount();
</script>
@endpush