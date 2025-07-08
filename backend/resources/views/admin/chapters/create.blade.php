@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">➕ Thêm chương mới</h2>

    <form id="chapter-form" action="{{ route('admin.chapters.store') }}" method="POST">
    @csrf

        <div class="mb-3">
            <label for="book_id" class="form-label">Sách</label>
            <select name="book_id" id="book_id" class="form-select" required>
                <option value="">-- Chọn sách --</option>
                @foreach($books as $book)
                    <option value="{{ $book->id }}">{{ $book->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề chương</label>
            <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
        </div>
        <div class="mb-3">
                <label class="form-label">Các chương đã có:</label>
                <div id="chapter-orders" class="text-muted fst-italic">
                    Vui lòng chọn sách để xem danh sách chương...
                </div>
            </div>
                <div class="mb-3">
            <label for="chapter_order" class="form-label">Thứ tự chương</label>
            <input type="number" name="chapter_order" id="chapter_order" class="form-control" required value="{{ old('chapter_order') }}">
            <div id="order-warning" class="text-danger mt-1" style="display: none;">❗ Thứ tự chương này đã tồn tại trong sách này.</div>
        </div>


                <div class="mb-3">
            <label for="content" class="form-label">Nội dung chương</label>
            <textarea name="content" id="editor" class="form-control" rows="10" required>{{ old('content') }}</textarea>
        </div>


        <button type="submit" class="btn btn-primary">Lưu chương</button>
        <a href="{{ route('admin.chapters.index') }}" class="btn btn-secondary">Huỷ</a>
    </form>
</div>
@endsection
@push('scripts')
<script>
    let existingOrders = [];

document.getElementById('book_id').addEventListener('change', function () {
    const bookId = this.value;
    const orderWarning = document.getElementById('order-warning');
    const chapterInput = document.getElementById('chapter_order');

    if (!bookId) return;

    fetch(`/admin/books/${bookId}/chapters/orders`)
        .then(res => res.json())
        .then(data => {
            existingOrders = data;

            // Kiểm tra lại nếu đã có số được nhập
            const currentOrder = chapterInput.value;
            if (existingOrders.includes(Number(currentOrder))) {
                orderWarning.style.display = 'block';
            } else {
                orderWarning.style.display = 'none';
            }
        });
});

document.getElementById('chapter_order').addEventListener('input', function () {
    const orderWarning = document.getElementById('order-warning');
    const value = Number(this.value);
    if (existingOrders.includes(value)) {
        orderWarning.style.display = 'block';
    } else {
        orderWarning.style.display = 'none';
    }
});
document.getElementById('book_id').addEventListener('change', function () {
    const bookId = this.value;
    const displayDiv = document.getElementById('chapter-orders');

    if (!bookId) {
        displayDiv.textContent = 'Vui lòng chọn sách để xem danh sách chương...';
        return;
    }

    fetch(`/admin/books/${bookId}/chapters/orders`)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                displayDiv.textContent = 'Chưa có chương nào.';
            } else {
                displayDiv.textContent = 'Đã có chương: ' + data.join(', ');
            }
        })
        .catch(() => {
            displayDiv.textContent = 'Không thể tải danh sách chương.';
        });
});
document.getElementById('chapter-form').addEventListener('submit', function (e) {
    const order = Number(document.getElementById('chapter_order').value);
    if (existingOrders.includes(order)) {
        e.preventDefault();
        alert('⚠️ Thứ tự chương này đã tồn tại. Vui lòng chọn số khác.');
    }
});

</script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#editor'))
        .catch(error => {
            console.error(error);
        });
</script>

@endpush
