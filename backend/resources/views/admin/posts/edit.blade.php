@extends('layouts.app')

@section('title', 'Sửa bài viết')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .ck-editor__editable_inline {
            min-height: 400px !important;
        }
    </style>
@endpush

@section('content')
    <div class="container" style="max-width: 800px; padding: 24px">
        <div class="page-header mb-4 p-3 rounded text-white" style="background: linear-gradient(to right, #00c6ff, #0072ff);">
            <h4 class="mb-0 fw-bold">✏️ Sửa bài viết</h4>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Tiêu đề --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Tiêu đề</label>
                <input type="text" name="title" id="titleInput"
                    class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $post->title) }}"
                    required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Slug --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Slug (tuỳ chọn)</label>
                <input type="text" name="slug" id="slugInput" class="form-control @error('slug') is-invalid @enderror"
                    value="{{ old('slug', $post->slug) }}">
                <div class="form-text">Không nhập sẽ tự sinh từ tiêu đề. Bạn có thể sửa slug theo ý muốn.</div>
                @error('slug')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tóm tắt --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Tóm tắt (excerpt)</label>
                <textarea name="excerpt" class="form-control" rows="3">{{ old('excerpt', $post->excerpt) }}</textarea>
            </div>

            {{-- Nội dung --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Nội dung</label>
                <textarea name="content" id="contentEditor" class="form-control">{{ old('content', $post->content) }}</textarea>
            </div>

            {{-- Trạng thái --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="draft" {{ old('status', $post->status) == 'draft' ? 'selected' : '' }}>Nháp</option>
                    <option value="published" {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>Xuất bản
                    </option>
                </select>
            </div>

            {{-- Ghim --}}
            <div class="form-check mb-3">
                <input type="checkbox" name="is_pinned" class="form-check-input" id="isPinned"
                    {{ old('is_pinned', $post->is_pinned) ? 'checked' : '' }}>
                <label class="form-check-label" for="isPinned">Ghim bài viết</label>
            </div>

            {{-- Chủ đề --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Chủ đề</label>
                <select name="topics[]" id="topicSelect" class="form-select" multiple>
                    @foreach ($topics as $topic)
                        <option value="{{ $topic->id }}"
                            {{ in_array($topic->id, old('topics', $selectedTopics)) ? 'selected' : '' }}>
                            {{ $topic->name }}
                        </option>
                    @endforeach
                </select>
                @error('topics')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ảnh đại diện --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Ảnh đại diện (thumbnail)</label>
                @if ($post->thumbnail)
                    <div class="mb-2">
                        <img src="{{ $post->thumbnail }}" alt="thumb" class="img-thumbnail" style="height: 100px;">
                    </div>
                @endif
                <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror"
                    accept="image/*" id="thumbnailInput">
                @error('thumbnail')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                <div class="mt-3">
                    <img id="thumbnailPreview" src="#" alt="Preview" class="img-thumbnail d-none"
                        style="max-height: 200px;">
                </div>
            </div>

            {{-- Nút --}}
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Quay lại</a>
                <button class="btn btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <script>
        $(document).ready(function() {
            // Select2
            $('#topicSelect').select2({
                placeholder: 'Chọn chủ đề',
                width: '100%',
                allowClear: true
            });

            // Auto slug
            $('#titleInput').on('input', function() {
                const slugInput = $('#slugInput');
                if (!slugInput.data('touched') || slugInput.val() === '') {
                    slugInput.val(toSlug(this.value));
                }
            });

            $('#slugInput').on('input', function() {
                $(this).data('touched', true);
                if ($(this).val() === '') {
                    $(this).data('touched', false);
                }
            });

            // Thumbnail preview
            $('#thumbnailInput').on('change', function() {
                const file = this.files[0];
                const preview = $('#thumbnailPreview');
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.attr('src', e.target.result).removeClass('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.addClass('d-none').attr('src', '#');
                }
            });

            // CKEditor setup
            ClassicEditor.create(document.querySelector('#contentEditor'), {
                ckfinder: {
                    uploadUrl: '{{ route('admin.upload-image') . '?_token=' . csrf_token() }}'
                }
            }).catch(error => {
                console.error(error);
            });
        });

        function toSlug(str) {
            str = str.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            str = str.replace(/đ/g, 'd').replace(/[^a-z0-9\s-]/g, '');
            str = str.replace(/\s+/g, '-').replace(/-+/g, '-');
            return str.trim('-');
        }
    </script>
@endpush
