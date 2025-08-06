@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">➕ Thêm chương mới</h2>

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

    <form id="chapter-form" action="{{ route('admin.chapters.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Sách --}}
        <div class="mb-3">
            <label for="book_id" class="form-label">Sách</label>
            <select name="book_id" id="book_id" class="form-select @error('book_id') is-invalid @enderror" required>
                <option value="">-- Chọn sách --</option>
                @foreach($books as $book)
                    <option value="{{ $book->id }}" {{ old('book_id') == $book->id ? 'selected' : '' }}>
                        {{ $book->title }}
                    </option>
                @endforeach
            </select>
            @error('book_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Tiêu đề chương --}}
        <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề chương</label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" required value="{{ old('title') }}">
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Các chương đã có --}}
        <div class="mb-3">
            <label class="form-label">Các chương đã có:</label>
            <div id="chapter-orders" class="text-muted fst-italic">
                Vui lòng chọn sách để xem danh sách chương...
            </div>
        </div>

        {{-- Thứ tự chương --}}
        <div class="mb-3">
            <label for="chapter_order" class="form-label">Thứ tự chương</label>
            <input type="number" name="chapter_order" id="chapter_order" class="form-control @error('chapter_order') is-invalid @enderror" required value="{{ old('chapter_order') }}">
            <div id="order-warning" class="text-danger mt-1" style="display: none;">❗ Thứ tự chương này đã tồn tại trong sách này.</div>
            @error('chapter_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Loại nội dung --}}
        <div class="mb-3">
            <label class="form-label">Loại nội dung</label>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="content_type" id="content_text" value="text" {{ old('content_type', 'text') === 'text' ? 'checked' : '' }}>
                        <label class="form-check-label" for="content_text">
                            <i class="bi bi-file-earmark-text"></i> Nội dung văn bản
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="content_type" id="content_pdf" value="pdf" {{ old('content_type') === 'pdf' ? 'checked' : '' }}>
                        <label class="form-check-label" for="content_pdf">
                            <i class="bi bi-file-earmark-pdf"></i> File PDF
                        </label>
                    </div>
                </div>
            </div>
            @error('content_type')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        {{-- Nội dung chương --}}
        <div class="mb-3" id="text-content-section">
            <label for="content" class="form-label">Nội dung chương</label>
            <textarea name="content" id="editor" class="form-control @error('content') is-invalid @enderror" rows="10">{{ old('content') }}</textarea>
            @error('content')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Upload PDF --}}
        <div class="mb-3" id="pdf-content-section" style="display: none;">
            <label for="pdf_file" class="form-label">Tải lên file PDF</label>
            <input type="file" name="pdf_file" id="pdf_file" class="form-control @error('pdf_file') is-invalid @enderror" accept=".pdf">
            <div class="form-text">
                <i class="bi bi-info-circle"></i> 
                Chỉ chấp nhận file PDF, tối đa 40MB. File sẽ được lưu trữ trên Cloudinary.
            </div>
            @error('pdf_file')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            
            {{-- Preview PDF --}}
            <div id="pdf-preview" class="mt-2" style="display: none;">
                <div class="alert alert-success">
                    <i class="bi bi-file-earmark-pdf"></i>
                    <strong>File đã chọn:</strong> <span id="pdf-filename"></span>
                    <br>
                    <small><strong>Kích thước:</strong> <span id="pdf-filesize"></span></small>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Lưu chương
            </button>
            <a href="{{ route('admin.chapters.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Huỷ
            </a>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap4 .select2-selection {
        border-radius: 8px;
        border: 1px solid #ced4da;
        padding: 1px 10px;
        min-height: 42px;
        font-size: 1rem;
    }

    .form-check-label {
        cursor: pointer;
        font-weight: 500;
    }
    
    .form-check-input:checked ~ .form-check-label {
        color: #0d6efd;
    }

    #pdf-preview {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 15px;
        background-color: #f8f9fa;
    }
    
    .alert {
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
$(document).ready(function () {
    const bookSelect = document.getElementById('book_id');
    const orderInput = document.getElementById('chapter_order');
    const orderWarning = document.getElementById('order-warning');
    const chapterOrdersDisplay = document.getElementById('chapter-orders');
    const textContentSection = document.getElementById('text-content-section');
    const pdfContentSection = document.getElementById('pdf-content-section');
    const pdfFileInput = document.getElementById('pdf_file');
    const pdfPreview = document.getElementById('pdf-preview');
    const pdfFilename = document.getElementById('pdf-filename');
    const pdfFilesize = document.getElementById('pdf-filesize');
    const form = document.getElementById('chapter-form');
    let existingOrders = [];
    let editorInstance;

    // ✅ Hàm gọi API lấy danh sách chương
    function updateChapterInfo(bookId) {
        if (!bookId) {
            chapterOrdersDisplay.textContent = 'Vui lòng chọn sách để xem danh sách chương...';
            existingOrders = [];
            orderWarning.style.display = 'none';
            return;
        }

        fetch(`/admin/books/${bookId}/chapters/orders`)
            .then(res => res.json())
            .then(data => {
                console.log("Fetched chapter orders:", data); // Debug
                existingOrders = data;

                if (data.length === 0) {
                    chapterOrdersDisplay.textContent = 'Chưa có chương nào.';
                    orderInput.value = 1;
                } else {
                    chapterOrdersDisplay.textContent = 'Đã có chương: ' + data.join(', ');
                    const maxOrder = Math.max(...data);
                    orderInput.value = maxOrder + 1;
                }

                const currentOrder = Number(orderInput.value);
                orderWarning.style.display = existingOrders.includes(currentOrder) ? 'block' : 'none';
            })
            .catch(() => {
                chapterOrdersDisplay.textContent = 'Không thể tải danh sách chương.';
                existingOrders = [];
            });
    }

    // ✅ Toggle content type sections
    function toggleContentSections() {
        const selectedType = document.querySelector('input[name="content_type"]:checked').value;
        
        if (selectedType === 'pdf') {
            textContentSection.style.display = 'none';
            pdfContentSection.style.display = 'block';
            // Clear text content when switching to PDF
            if (editorInstance) {
                editorInstance.setData('');
            }
        } else {
            textContentSection.style.display = 'block';
            pdfContentSection.style.display = 'none';
            // Clear PDF input when switching to text
            pdfFileInput.value = '';
            pdfPreview.style.display = 'none';
        }
    }

    // ✅ Content type radio change event
    document.querySelectorAll('input[name="content_type"]').forEach(radio => {
        radio.addEventListener('change', toggleContentSections);
    });

    // ✅ Initialize content sections
    toggleContentSections();

    // ✅ PDF file input change event
  pdfFileInput.addEventListener('change', function() {
    const file = this.files[0];
    
    if (file) {
        console.log('Selected file:', {
            name: file.name,
            size: file.size,
            type: file.type,
            lastModified: file.lastModified
        });
        
        // Validate file type
        if (file.type !== 'application/pdf') {
            alert('❌ Chỉ chấp nhận file PDF!');
            this.value = '';
            pdfPreview.style.display = 'none';
            return;
        }
        
        // Validate file size (10MB = 10 * 1024 * 1024 bytes) - giảm từ 40MB xuống 10MB
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('❌ File PDF không được vượt quá 10MB!');
            this.value = '';
            pdfPreview.style.display = 'none';
            return;
        }
        
        // Validate file is not corrupted
        if (file.size === 0) {
            alert('❌ File PDF bị lỗi hoặc rỗng!');
            this.value = '';
            pdfPreview.style.display = 'none';
            return;
        }
        
        // Show preview
        pdfFilename.textContent = file.name;
        pdfFilesize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        pdfPreview.style.display = 'block';
    } else {
        pdfPreview.style.display = 'none';
    }
});

// ✅ Thêm validation trước khi submit
form.addEventListener('submit', function (e) {
    const order = Number(orderInput.value);
    const contentType = document.querySelector('input[name="content_type"]:checked').value;

    if (existingOrders.includes(order)) {
        e.preventDefault();
        alert('⚠️ Thứ tự chương này đã tồn tại. Vui lòng chọn số khác.');
        return;
    }

    if (contentType === 'text') {
        const content = editorInstance.getData();
        if (!content.trim()) {
            e.preventDefault();
            alert('⚠️ Nội dung chương không được để trống.');
            return;
        }
    } else if (contentType === 'pdf') {
        if (!pdfFileInput.files[0]) {
            e.preventDefault();
            alert('⚠️ Vui lòng chọn file PDF để upload.');
            return;
        }
        
        // Kiểm tra lại file trước khi submit
        const file = pdfFileInput.files[0];
        if (file.size === 0 || file.type !== 'application/pdf') {
            e.preventDefault();
            alert('⚠️ File PDF không hợp lệ.');
            return;
        }
    }
});
    // ✅ Gọi hàm mỗi khi chọn sách
    bookSelect.addEventListener('change', function () {
        updateChapterInfo(this.value);
    });

    // ✅ Gọi khi người dùng thay đổi số thứ tự
    orderInput.addEventListener('input', function () {
        const value = Number(this.value);
        orderWarning.style.display = existingOrders.includes(value) ? 'block' : 'none';
    });

    // ✅ Validate khi submit
    form.addEventListener('submit', function (e) {
        const order = Number(orderInput.value);
        const contentType = document.querySelector('input[name="content_type"]:checked').value;

        if (existingOrders.includes(order)) {
            e.preventDefault();
            alert('⚠️ Thứ tự chương này đã tồn tại. Vui lòng chọn số khác.');
            return;
        }

        if (contentType === 'text') {
            const content = editorInstance.getData();
            if (!content.trim()) {
                e.preventDefault();
                alert('⚠️ Nội dung chương không được để trống.');
                return;
            }
        } else if (contentType === 'pdf') {
            if (!pdfFileInput.files[0]) {
                e.preventDefault();
                alert('⚠️ Vui lòng chọn file PDF để upload.');
                return;
            }
        }
    });

    // ✅ Khởi tạo Select2
    $('#book_id').select2({
        placeholder: "-- Chọn sách --",
        theme: 'bootstrap4',
        allowClear: true,
        language: {
            inputTooShort: () => "Gõ để tìm kiếm sách...",
            noResults: () => "Không tìm thấy sách phù hợp",
            searching: () => "Đang tìm..."
        }
    });

    // ✅ Gọi khi có sẵn book_id cũ (tức là old('book_id'))
    const selectedBookId = $('#book_id').val();
    if (selectedBookId) {
        updateChapterInfo(selectedBookId);
    }

    // ✅ CKEditor
    ClassicEditor
    .create(document.querySelector('#editor'), {
        toolbar: {
            items: [
                'heading', '|',
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'fontFamily', 'fontSize', '|',
                'alignment:left', 'alignment:center', 'alignment:right', '|',
                'numberedList', 'bulletedList', '|',
                'link', 'blockQuote', 'insertTable', '|',
                'undo', 'redo'
            ]
        },
        fontFamily: {
            options: [
                'default',
                'Arial, Helvetica, sans-serif',
                'Courier New, Courier, monospace',
                'Georgia, serif',
                'Roboto, sans-serif',
                'Times New Roman, Times, serif',
                'Verdana, Geneva, sans-serif'
            ]
        },
        alignment: {
            options: ['left', 'center', 'right']
        }
    })
    .then(editor => {
        editorInstance = editor;
    })
    .catch(error => {
        console.error('CKEditor error:', error);
    });
});
</script>
@endpush