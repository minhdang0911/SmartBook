@extends('layouts.app')

@section('content')
@if(session('warning'))
    <div class="alert alert-warning">
        {{ session('warning') }}
    </div>
@endif
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="container py-4">
    <h2 class="mb-4">✏️ Sửa chương: {{ $chapter->title }}</h2>

    {{-- Hiển thị tất cả lỗi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="chapter-form" action="{{ route('admin.chapters.update', $chapter->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Chỉ hiển thị tên sách --}}
        <div class="mb-3">
            <label class="form-label">Sách</label>
            <input type="text" class="form-control" value="{{ $chapter->book->title ?? 'Không xác định' }}" readonly>
        </div>

        {{-- Hidden giữ book_id --}}
        <input type="hidden" name="book_id" value="{{ $chapter->book_id }}">

        <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề chương</label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $chapter->title) }}">
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Không cho chỉnh thứ tự chương --}}
        <div class="mb-3">
            <label class="form-label">Thứ tự chương</label>
            <input type="number" class="form-control" value="{{ $chapter->chapter_order }}" readonly>
            <input type="hidden" name="chapter_order" value="{{ $chapter->chapter_order }}">
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Nội dung chương</label>
            <textarea name="content" id="editor" class="form-control @error('content') is-invalid @enderror" rows="10">{{ old('content', $chapter->content) }}</textarea>
            @error('content')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Cập nhật</button>
        <a href="{{ route('admin.chapters.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    let editorInstance;

    ClassicEditor
        .create(document.querySelector('#editor'))
        .then(editor => {
            editorInstance = editor;
        })
        .catch(error => {
            console.error('CKEditor error:', error);
        });

    document.getElementById('chapter-form').addEventListener('submit', function (e) {
        const content = editorInstance.getData();
        if (!content.trim()) {
            e.preventDefault();
            alert("⚠️ Nội dung chương không được để trống.");
        }
    });
</script>
@endpush
