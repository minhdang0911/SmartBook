@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">✏️ Sửa chương: {{ $chapter->title }}</h2>

    <form action="{{ route('admin.chapters.update', $chapter->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Chỉ hiển thị tên sách chứ không cho chỉnh --}}
        <div class="mb-3">
            <label class="form-label">Sách</label>
            <input type="text" class="form-control" value="{{ $chapter->book->title ?? 'Không xác định' }}" readonly>
        </div>

        {{-- Hidden input để giữ book_id không mất --}}
        <input type="hidden" name="book_id" value="{{ $chapter->book_id }}">

        <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề chương</label>
            <input type="text" name="title" class="form-control" required value="{{ old('title', $chapter->title) }}">
        </div>

        {{-- Không cho chỉnh thứ tự chương --}}
        <div class="mb-3">
            <label class="form-label">Thứ tự chương</label>
            <input type="number" class="form-control" value="{{ $chapter->chapter_order }}" readonly>
            <input type="hidden" name="chapter_order" value="{{ $chapter->chapter_order }}">
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Nội dung chương</label>
            <textarea name="content" id="editor" class="form-control" rows="10" required>{{ old('content', $chapter->content) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Cập nhật</button>
        <a href="{{ route('admin.chapters.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#editor'))
        .catch(error => {
            console.error(error);
        });
</script>
@endpush
