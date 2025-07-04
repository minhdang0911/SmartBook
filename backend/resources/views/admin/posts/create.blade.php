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
            <div class="alert alert-danger">
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
                    class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}">
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
                <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="6">{{ old('content') }}</textarea>
                @error('content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Danh mục --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Chủ đề</label>
                <select name="topics[]" class="form-select" multiple>
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

                {{-- ✅ PREVIEW --}}
                <div class="mt-3">
                    <img id="thumbnailPreview" src="#" alt="Preview" class="img-thumbnail d-none"
                        style="max-height: 200px;">
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

@push('scripts')
    <script>
        function toSlug(str) {
            str = str.toLowerCase();
            str = str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            str = str.replace(/đ/g, 'd');
            str = str.replace(/Đ/g, 'd');
            str = str.replace(/[^a-z0-9\s-]/g, '');
            str = str.replace(/\s+/g, '-');
            str = str.replace(/-+/g, '-');
            return str.trim('-');
        }

        document.addEventListener('DOMContentLoaded', function() {
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
        });

        document.getElementById('thumbnailInput').addEventListener('change', function(e) {
            const preview = document.getElementById('thumbnailPreview');
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
    </script>
@endpush
