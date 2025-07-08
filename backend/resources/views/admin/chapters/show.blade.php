@extends('layouts.app')

@section('title', 'Xem chương: ' . $chapter->title)

@section('content')
<div class="container py-4">
    <h2>{{ $chapter->title }}</h2>

    <div class="text-muted mb-3">
        Trong sách: <strong>{{ $chapter->book->title }}</strong><br>
        Cập nhật: {{ $chapter->updated_at->format('H:i d/m/Y') }}
    </div>

    <hr>

    {{-- ✅ Hiển thị nội dung HTML từ CKEditor --}}
    <div class="chapter-content" style="line-height: 1.8; font-size: 1.1rem;">
        {!! $chapter->content !!}
    </div>

    <hr>

    <div class="d-flex justify-content-between">
        @if ($previous)
            <a href="{{ route('admin.chapters.show', $previous->id) }}" class="btn btn-outline-primary">⬅️ Chương trước</a>
        @else
            <span></span>
        @endif

        @if ($next)
            <a href="{{ route('admin.chapters.show', $next->id) }}" class="btn btn-outline-primary">Chương tiếp ➡️</a>
        @else
            <span></span>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Tiêu đề căn giữa (ví dụ: GIỚI THIỆU VẮN TẮT) */
    .title-center {
        text-align: center;
        font-weight: bold;
        font-size: 20px;
        color: #e6d6ff;
        margin-bottom: 30px;
    }

    /* Dropcap - chữ cái đầu tiên to ra */
    .dropcap::first-letter {
        font-size: 42px;
        font-weight: bold;
        float: left;
        line-height: 1;
        margin-right: 8px;
        color: #d7c3f6;
    }

    /* Tùy chọn nền tím nhẹ cho đoạn (nếu muốn giống ảnh mẫu) */
    .highlight-section {
        background-color: #6c5a84;
        padding: 16px 24px;
        border-radius: 4px;
        color: #fff;
        margin-bottom: 18px;
    }
</style>
@endpush
