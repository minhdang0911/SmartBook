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

    <form id="chapter-form" action="{{ route('admin.chapters.store') }}" method="POST">
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

        {{-- Nội dung chương --}}
        <div class="mb-3">
            <label for="content" class="form-label">Nội dung chương</label>
            <textarea name="content" id="editor" class="form-control @error('content') is-invalid @enderror" rows="10">{{ old('content') }}</textarea>
            @error('content')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Lưu chương</button>
        <a href="{{ route('admin.chapters.index') }}" class="btn btn-secondary">Huỷ</a>
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
        const content = editorInstance.getData();

        if (existingOrders.includes(order)) {
            e.preventDefault();
            alert('⚠️ Thứ tự chương này đã tồn tại. Vui lòng chọn số khác.');
            return;
        }

        if (!content.trim()) {
            e.preventDefault();
            alert('⚠️ Nội dung chương không được để trống.');
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
