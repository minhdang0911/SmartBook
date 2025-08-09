@extends('layouts.app')

@section('title', 'Chỉnh sửa Sách')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
    }
    
    .modern-input {
        background: rgba(0, 0, 0, 0.04);
        border: 2px solid transparent;
        border-radius: 12px;
        padding: 16px 20px;
        font-size: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    
    .modern-input:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.9);
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        transform: translateY(-2px);
    }
    
    .modern-select {
        background: rgba(0, 0, 0, 0.04);
        border: 2px solid transparent;
        border-radius: 12px;
        padding: 16px 20px;
        font-size: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 16px center;
        background-repeat: no-repeat;
        background-size: 16px;
    }
    
    .modern-select:focus {
        outline: none;
        background-color: rgba(255, 255, 255, 0.9);
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        transform: translateY(-2px);
    }
    
    .modern-textarea {
        background: rgba(0, 0, 0, 0.04);
        border: 2px solid transparent;
        border-radius: 12px;
        padding: 16px 20px;
        font-size: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        resize: vertical;
    }
    
    .modern-textarea:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.9);
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        transform: translateY(-2px);
    }
    
    .modern-radio {
        width: 20px;
        height: 20px;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        position: relative;
        transition: all 0.2s ease;
    }
    
    .modern-radio:checked {
        border-color: #3b82f6;
        background-color: #3b82f6;
    }
    
    .modern-radio:checked::after {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: white;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    
    .file-upload-modern {
        position: relative;
        display: inline-block;
        overflow: hidden;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 24px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .file-upload-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .file-upload-modern input[type=file] {
        position: absolute;
        left: -9999px;
    }
    
    .modern-btn {
        background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
        color: white;
        border: none;
        padding: 14px 28px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .modern-btn:before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .modern-btn:hover:before {
        left: 100%;
    }
    
    .modern-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .modern-btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }
    
    .modern-btn-secondary {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    }
    
    .image-preview-modern {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    
    .image-preview-modern:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    }
    
    .delete-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .delete-btn:hover {
        background: rgba(220, 38, 38, 1);
        transform: scale(1.1);
    }
    
    /* Ant Design Message Styles */
    .ant-message {
        position: fixed;
        top: 24px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        pointer-events: none;
    }
    
    .ant-message-notice {
        padding: 12px 24px;
        border-radius: 12px;
        margin-bottom: 8px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        backdrop-filter: blur(20px);
        pointer-events: auto;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .ant-message-success {
        background: rgba(34, 197, 94, 0.9);
        color: white;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }
    
    .ant-message-error {
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    
    .ant-message-warning {
        background: rgba(245, 158, 11, 0.9);
        color: white;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }
    
    .floating-label {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        transition: all 0.3s ease;
        pointer-events: none;
        color: #6b7280;
        font-size: 16px;
    }
    
    .input-container {
        position: relative;
    }
    
    .input-container input:focus + .floating-label,
    .input-container input:not(:placeholder-shown) + .floating-label,
    .input-container select:focus + .floating-label,
    .input-container select:not(:placeholder-shown) + .floating-label {
        top: -8px;
        font-size: 12px;
        color: #3b82f6;
        background: white;
        padding: 0 8px;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }
    
    .message-enter {
        animation: slideIn 0.3s ease-out;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">✏️ Chỉnh sửa sách</h1>
            <p class="text-white/80 text-lg">Cập nhật thông tin sách trong hệ thống</p>
        </div>

        <!-- Main Form Card -->
        <div class="glass-card rounded-3xl p-8 mb-6">
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Có lỗi xảy ra:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.books.update', $book) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Title -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="fas fa-book mr-2"></i>Tiêu đề sách
                    </label>
                    <input 
                        type="text" 
                        name="title" 
                        id="title" 
                        value="{{ old('title', $book->title) }}" 
                        class="modern-input w-full" 
                        placeholder="Nhập tiêu đề sách..."
                        required
                    >
                </div>

                <!-- Author & Publisher Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-user-edit mr-2"></i>Tác giả
                        </label>
                        <select name="author_id" id="author_id" class="modern-select w-full" required>
                            <option value="" disabled>-- Chọn tác giả --</option>
                            @foreach ($authors as $author)
                                <option value="{{ $author->id }}" {{ old('author_id', $book->author_id) == $author->id ? 'selected' : '' }}>
                                    {{ $author->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-building mr-2"></i>Nhà xuất bản
                        </label>
                        <select name="publisher_id" id="publisher_id" class="modern-select w-full" required>
                            <option value="" disabled>-- Chọn NXB --</option>
                            @foreach ($publishers as $publisher)
                                <option value="{{ $publisher->id }}" {{ old('publisher_id', $book->publisher_id) == $publisher->id ? 'selected' : '' }}>
                                    {{ $publisher->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Category -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="fas fa-tags mr-2"></i>Danh mục
                    </label>
                    <select name="category_id" id="category_id" class="modern-select w-full" required>
                        <option value="" disabled>-- Chọn danh mục --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Book Type -->
                @php $selectedType = old('is_physical', $book->is_physical); @endphp
                <div class="space-y-4">
                    <label class="block text-sm font-semibold text-gray-700">
                        <i class="fas fa-layer-group mr-2"></i>Loại sách
                    </label>
                    <div class="flex flex-wrap gap-6">
                        <label class="flex items-center space-x-3 cursor-pointer group">
                            <input 
                                class="modern-radio" 
                                type="radio" 
                                name="is_physical" 
                                id="ebook" 
                                value="0"
                                {{ (string)$selectedType === '0' ? 'checked' : '' }}
                            >
                            <span class="text-gray-700 group-hover:text-blue-600 transition-colors font-medium">
                                📱 Ebook
                            </span>
                        </label>
                        <label class="flex items-center space-x-3 cursor-pointer group">
                            <input 
                                class="modern-radio" 
                                type="radio" 
                                name="is_physical" 
                                id="physical" 
                                value="1"
                                {{ (string)$selectedType === '1' ? 'checked' : '' }}
                            >
                            <span class="text-gray-700 group-hover:text-blue-600 transition-colors font-medium">
                                📚 Sách giấy
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Price & Stock Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-tag mr-2"></i>Giá (VNĐ)
                        </label>
                        <input 
                            type="number" 
                            name="price" 
                            id="price" 
                            class="modern-input w-full" 
                            value="{{ old('price', $book->price) }}"
                            placeholder="0"
                            {{ (string)$selectedType === '0' ? 'disabled' : 'required' }}
                        >
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-boxes mr-2"></i>Tồn kho
                        </label>
                        <input 
                            type="number" 
                            name="stock" 
                            id="stock" 
                            class="modern-input w-full" 
                            value="{{ old('stock', $book->stock) }}"
                            placeholder="0"
                            {{ (string)$selectedType === '0' ? 'disabled' : 'required' }}
                        >
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="fas fa-align-left mr-2"></i>Mô tả
                    </label>
                    <textarea 
                        name="description" 
                        id="description" 
                        class="modern-textarea w-full my-editor" 
                        rows="8"
                        placeholder="Nhập mô tả chi tiết về sách..."
                    >{{ old('description', $book->description) }}</textarea>
                </div>

                <!-- Current Cover Image -->
                <div class="space-y-4">
                    <label class="block text-sm font-semibold text-gray-700">
                        <i class="fas fa-image mr-2"></i>Ảnh bìa hiện tại
                    </label>
                    <div class="flex justify-center">
                        <div class="image-preview-modern max-w-xs">
                            <img src="{{ $book->cover_image }}" alt="Ảnh bìa" class="w-full h-64 object-cover">
                        </div>
                    </div>
                </div>

                <!-- New Cover Image -->
                <div class="space-y-4">
                    <label class="block text-sm font-semibold text-gray-700">
                        <i class="fas fa-camera mr-2"></i>Thay ảnh bìa mới
                    </label>
                    <div class="flex justify-center">
                        <label class="file-upload-modern">
                            <i class="fas fa-cloud-upload-alt mr-2"></i>
                            Chọn ảnh bìa mới
                            <input type="file" name="cover_image" id="cover_image" accept="image/*">
                        </label>
                    </div>
                    <img id="previewCover" class="mx-auto mt-4 max-w-xs h-64 object-cover rounded-2xl shadow-lg" style="display: none;" />
                </div>

                <!-- Current Additional Images -->
                @if ($book->images->count())
                <div class="space-y-4">
                    <label class="block text-sm font-semibold text-gray-700">
                        <i class="fas fa-images mr-2"></i>Ảnh phụ hiện tại
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach ($book->images as $img)
                            <div class="image-preview-modern relative">
                                <img src="{{ $img->image_url }}" class="w-full h-32 object-cover">
                                <button type="button" class="delete-btn btn-delete-image" data-id="{{ $img->id }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- New Additional Images -->
                <div class="space-y-4">
                    <label class="block text-sm font-semibold text-gray-700">
                        <i class="fas fa-plus-circle mr-2"></i>Thêm ảnh phụ mới
                    </label>
                    <div class="flex justify-center">
                        <label class="file-upload-modern">
                            <i class="fas fa-images mr-2"></i>
                            Chọn nhiều ảnh
                            <input type="file" name="images[]" id="images" accept="image/*" multiple>
                        </label>
                    </div>
                    <div id="previewImages" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4"></div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center sm:justify-start pt-6 border-t border-gray-200">
                    <button type="submit" class="modern-btn modern-btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Cập nhật sách
                    </button>
                    <a href="{{ route('admin.books.index') }}" class="modern-btn modern-btn-secondary text-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Message Container -->
<div id="messageContainer" class="ant-message"></div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    // Message System
    const messageContainer = document.getElementById('messageContainer');
    
    function showMessage(type, content, duration = 3000) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `ant-message-notice ant-message-${type} message-enter`;
        messageDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'exclamation-triangle'} mr-2"></i>
                <span>${content}</span>
            </div>
        `;
        
        messageContainer.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.style.opacity = '0';
            messageDiv.style.transform = 'translateX(-50%) translateY(-20px)';
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.parentNode.removeChild(messageDiv);
                }
            }, 300);
        }, duration);
    }

    // CKEditor
    ClassicEditor
        .create(document.querySelector('.my-editor'), {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'link', '|',
                    'bulletedList', 'numberedList', '|',
                    'outdent', 'indent', '|',
                    'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ]
            },
            language: 'vi'
        })
        .catch(error => console.error(error));

    // Cover Image Preview
    document.getElementById('cover_image').addEventListener('change', function (e) {
        const file = e.target.files[0];
        const preview = document.getElementById('previewCover');
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
            showMessage('success', '📷 Ảnh bìa đã được chọn!');
        } else {
            preview.style.display = 'none';
        }
    });

    // Multiple Images Preview
    document.getElementById('images').addEventListener('change', function (e) {
        const container = document.getElementById('previewImages');
        container.innerHTML = '';
        Array.from(e.target.files).forEach(file => {
            const div = document.createElement('div');
            div.className = 'image-preview-modern relative';
            
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = 'w-full h-32 object-cover';
            
            div.appendChild(img);
            container.appendChild(div);
        });
        
        if (e.target.files.length > 0) {
            showMessage('success', `🖼️ Đã chọn ${e.target.files.length} ảnh phụ!`);
        }
    });

    // Toggle Fields Based on Book Type
    function toggleFields() {
        const isPhysical = document.querySelector('input[name="is_physical"]:checked').value === '1';
        const priceInput = document.getElementById('price');
        const stockInput = document.getElementById('stock');
        
        priceInput.disabled = !isPhysical;
        stockInput.disabled = !isPhysical;
        
        if (!isPhysical) {
            priceInput.value = '';
            stockInput.value = '';
            priceInput.style.opacity = '0.5';
            stockInput.style.opacity = '0.5';
        } else {
            priceInput.style.opacity = '1';
            stockInput.style.opacity = '1';
        }
        
        showMessage('success', isPhysical ? '📚 Chuyển sang chế độ sách giấy' : '📱 Chuyển sang chế độ ebook');
    }

    document.querySelectorAll('input[name="is_physical"]').forEach(input => {
        input.addEventListener('change', toggleFields);
    });
    
    // Initialize on page load
    toggleFields();

    // Delete Image Functionality
    document.querySelectorAll('.btn-delete-image').forEach(btn => {
        btn.addEventListener('click', function () {
            const imageId = this.dataset.id;
            
            // Modern confirmation
            if (!confirm("🗑️ Bạn có chắc muốn xóa ảnh này không?")) return;
            
            fetch(`/admin/book-images/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', '✅ Ảnh đã được xóa thành công!');
                    this.closest('.image-preview-modern').remove();
                } else {
                    showMessage('error', '❌ Xóa ảnh thất bại: ' + (data.message || 'Lỗi không xác định'));
                }
            })
            .catch(err => {
                showMessage('error', '⚠️ Lỗi mạng khi xóa ảnh!');
                console.error(err);
            });
        });
    });

    // Form Validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const authorId = document.getElementById('author_id').value;
        const publisherId = document.getElementById('publisher_id').value;
        const categoryId = document.getElementById('category_id').value;
        
        if (!title || !authorId || !publisherId || !categoryId) {
            e.preventDefault();
            showMessage('error', '❌ Vui lòng điền đầy đủ thông tin bắt buộc!');
            return false;
        }
        
        showMessage('success', '⏳ Đang cập nhật sách...');
    });

    // Add smooth scrolling to form elements on error
    @if ($errors->any())
        setTimeout(() => {
            const firstError = document.querySelector('.ant-message-notice');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 100);
    @endif
</script>
@endpush