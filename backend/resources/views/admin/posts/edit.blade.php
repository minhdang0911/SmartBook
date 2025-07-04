@extends('layouts.app')

@section('title', 'Sửa bài viết')

@section('content')
<div class="container" style="max-width: 800px; padding: 24px">
    <div class="page-header mb-4 p-3 rounded text-white" style="background: linear-gradient(to right, #00c6ff, #0072ff);">
        <h4 class="mb-0 fw-bold">✏️ Sửa bài viết</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label fw-semibold">Tiêu đề</label>
            <input type="text" name="title" id="titleInput" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $post->title) }}" required>
            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Slug (tuỳ chọn)</label>
            <input type="text" name="slug" id="slugInput" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $post->slug) }}">
            <div class="form-text">Không nhập sẽ tự sinh từ tiêu đề. Bạn có thể sửa slug theo ý muốn.</div>
            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Tóm tắt (excerpt)</label>
            <textarea name="excerpt" class="form-control" rows="3">{{ old('excerpt', $post->excerpt) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Nội dung</label>
            <textarea name="content" class="form-control" rows="6">{{ old('content', $post->content) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Trạng thái</label>
            <select name="status" class="form-select">
                <option value="draft" {{ old('status', $post->status) == 'draft' ? 'selected' : '' }}>Nháp</option>
                <option value="published" {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>Xuất bản</option>
            </select>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_pinned" class="form-check-input" id="isPinned" {{ old('is_pinned', $post->is_pinned) ? 'checked' : '' }}>
            <label class="form-check-label" for="isPinned">Ghim bài viết</label>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Chủ đề</label>
            <select name="topics[]" class="form-select" multiple>
                @foreach($topics as $topic)
                    <option value="{{ $topic->id }}" {{ in_array($topic->id, old('topics', $selectedTopics)) ? 'selected' : '' }}>
                        {{ $topic->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Ảnh đại diện (thumbnail)</label>
            @if ($post->thumbnail)
                <div class="mb-2">
                    <img src="{{ $post->thumbnail }}" alt="thumb" class="img-thumbnail" style="height: 100px;">
                </div>
            @endif
            <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" accept="image/*">
            @error('thumbnail') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Quay lại</a>
            <button class="btn btn-primary">Cập nhật</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function toSlug(str) {
        str = str.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        str = str.replace(/đ/g, 'd').replace(/[^a-z0-9\s-]/g, '');
        str = str.replace(/\s+/g, '-').replace(/-+/g, '-');
        return str.trim('-');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const titleInput = document.getElementById('titleInput');
        const slugInput = document.getElementById('slugInput');

        titleInput.addEventListener('input', function () {
            if (!slugInput.dataset.touched) {
                slugInput.value = toSlug(this.value);
            }
        });

        slugInput.addEventListener('input', function () {
            this.dataset.touched = true;
        });
    });
</script>
@endpush
