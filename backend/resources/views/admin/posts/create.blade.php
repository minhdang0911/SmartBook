@extends('layouts.app')

@section('title', 'Thêm bài viết mới')

@section('content')
    <div class="container" style="max-width: 800px; padding: 24px">
        {{-- HEADER --}}
        <div class="page-header mb-4 p-3 rounded text-white" style="background: linear-gradient(to right, #00c6ff, #0072ff);">
            <h4 class="mb-0 fw-bold">➕ Thêm bài viết mới</h4>
        </div>

        {{-- ERROR MESSAGES --}}
        @if ($errors->any())
            <div class="alert alert-danger" id="formErrorAlert">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Tiêu đề --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Tiêu đề</label>
                <input type="text" name="title" id="titleInput"
                    class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" autofocus>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Slug --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Slug (tuỳ chọn)</label>
                <input type="text" name="slug" id="slugInput" class="form-control @error('slug') is-invalid @enderror"
                    value="{{ old('slug') }}">
                <div class="form-text">Không nhập sẽ tự sinh từ tiêu đề. Bạn có thể sửa slug theo ý muốn.</div>
                @error('slug')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tóm tắt --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Tóm tắt (excerpt)</label>
                <textarea name="excerpt" class="form-control @error('excerpt') is-invalid @enderror" rows="3">{{ old('excerpt') }}</textarea>
                @error('excerpt')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nội dung --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Nội dung</label>
                <textarea name="content" id="contentEditor" class="form-control">{{ old('content', $post->content ?? '') }}</textarea>
            </div>


            {{-- Chủ đề --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Chủ đề</label>
                <select name="topics[]" id="topicSelect" class="form-select" multiple>
                    @foreach ($topics as $topic)
                        <option value="{{ $topic->id }}"
                            {{ collect(old('topics'))->contains($topic->id) ? 'selected' : '' }}>
                            {{ $topic->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Giữ Ctrl (hoặc Cmd) để chọn nhiều chủ đề.</div>
            </div>

            {{-- Ảnh đại diện --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Ảnh đại diện (thumbnail)</label>
                <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror"
                    accept="image/*" id="thumbnailInput">
                @error('thumbnail')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                <div class="mt-3">
                    <img id="thumbnailPreview" src="#" alt="Preview thumbnail" class="img-thumbnail d-none"
                        style="max-height: 200px; object-fit: cover;">
                </div>
            </div>

            {{-- Trạng thái --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Trạng thái</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                    <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Xuất bản</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ghim bài --}}
            <div class="form-check mb-3">
                <input type="checkbox" name="is_pinned" class="form-check-input" id="isPinned"
                    {{ old('is_pinned') ? 'checked' : '' }}>
                <label class="form-check-label" for="isPinned">Ghim bài viết</label>
            </div>

            {{-- Nút --}}
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Quay lại</a>
                <button class="btn btn-success">Lưu bài viết</button>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .ck-editor__editable_inline {
            min-height: 400px !important;
        }
    </style>
@endpush

@push('scripts')
    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- CKEditor 5 --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <script>
        $(document).ready(function() {
            // Khởi tạo Select2
            $('#topicSelect').select2({
                placeholder: 'Chọn chủ đề...',
                width: '100%'
            });

            // Auto tạo slug
            const titleInput = document.getElementById('titleInput');
            const slugInput = document.getElementById('slugInput');
            titleInput.addEventListener('input', function() {
                if (!slugInput.dataset.touched) {
                    slugInput.value = toSlug(this.value);
                }
            });
            slugInput.addEventListener('input', function() {
                this.dataset.touched = true;
            });

            // Thumbnail preview
            const preview = document.getElementById('thumbnailPreview');
            document.getElementById('thumbnailInput').addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.classList.remove('d-none');
                    }
                    reader.readAsDataURL(file);
                } else {
                    preview.src = '#';
                    preview.classList.add('d-none');
                }
            });

            // Auto scroll alert lỗi
            if (document.getElementById('formErrorAlert')) {
                document.getElementById('formErrorAlert').scrollIntoView({
                    behavior: 'smooth'
                });
            }

            // Khởi tạo CKEditor
            ClassicEditor.create(document.querySelector('textarea[name="content"]'), {
                ckfinder: {
                    uploadUrl: '{{ route('admin.upload-image') . '?_token=' . csrf_token() }}'
                }
            }).catch(error => {
                console.error(error);
            });
        });

        function toSlug(str) {
            str = str.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            str = str.replace(/đ/g, 'd').replace(/Đ/g, 'd');
            str = str.replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
            return str.trim('-');
        }
    </script>
@endpush
