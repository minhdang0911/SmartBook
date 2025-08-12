@extends('layouts.app')

@section('title', 'Sửa bài viết')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-out',
                        'fade-out': 'fadeOut 0.5s ease-out'
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; visibility: hidden; }
        }
        .fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }
        .ck-editor__editable_inline {
            min-height: 400px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            background: #ffffff !important;
        }
        .dark .ck-editor__editable_inline {
            background: #374151 !important;
            border-color: #4b5563 !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            background: #ffffff !important;
            padding: 0.5rem !important;
        }
        .dark .select2-container--default .select2-selection--multiple {
            background: #374151 !important;
            border-color: #4b5563 !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            padding: 0 !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: #e5e7eb !important;
            color: #1f2937 !important;
            border-radius: 0.375rem !important;
            padding: 0.25rem 0.5rem !important;
            font-size: 0.75rem !important;
        }
        .dark .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: #4b5563 !important;
            color: #e5e7eb !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #6b7280 !important;
        }
        .dark .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #9ca3af !important;
        }
        .select2-container--default .select2-search--inline .select2-search__field {
            font-size: 0.875rem !important;
            color: #1f2937 !important;
        }
        .dark .select2-container--default .select2-search--inline .select2-search__field {
            color: #e5e7eb !important;
        }
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-white dark:bg-gray-900 transition-all duration-300">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-6 mb-6 flex justify-between items-center animate-fade-in">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sửa bài viết</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Chỉnh sửa bài viết trong hệ thống</p>
                </div>
            </div>

            <!-- Success Messages -->
            @if (session('success'))
                <div id="successAlert" class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg p-4 mb-6 animate-fade-in">
                    <div class="text-sm">{{ session('success') }}</div>
                </div>
            @endif

            <!-- Error Messages -->
            @if ($errors->any())
                <div id="formErrorAlert" class="bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-lg p-4 mb-6 animate-fade-in">
                    <ul class="mb-0 text-sm">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 animate-fade-in">
                @csrf
                @method('PUT')

                <!-- Tiêu đề -->
                <div class="mb-4">
                    <label class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">Tiêu đề</label>
                    <input type="text" name="title" id="titleInput"
                        class="block w-full py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent transition"
                        value="{{ old('title', $post->title) }}" placeholder="Nhập tiêu đề bài viết..." autofocus>
                    @error('title')
                        <div class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="mb-4">
                    <label class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">Slug (tuỳ chọn)</label>
                    <input type="text" name="slug" id="slugInput"
                        class="block w-full py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent transition"
                        value="{{ old('slug', $post->slug) }}" placeholder="Tự sinh từ tiêu đề hoặc nhập slug tùy chỉnh...">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Không nhập sẽ tự sinh từ tiêu đề. Bạn có thể sửa slug theo ý muốn.</div>
                    @error('slug')
                        <div class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Tóm tắt -->
                <div class="mb-4">
                    <label class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">Tóm tắt (excerpt)</label>
                    <textarea name="excerpt"
                        class="block w-full py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent transition"
                        rows="4" placeholder="Nhập tóm tắt bài viết...">{{ old('excerpt', $post->excerpt) }}</textarea>
                    @error('excerpt')
                        <div class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nội dung -->
                <div class="mb-4">
                    <label class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">Nội dung</label>
                    <textarea name="content" id="contentEditor"
                        class="block w-full py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent transition">{{ old('content', $post->content) }}</textarea>
                    @error('content')
                        <div class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Chủ đề -->
                <div class="mb-4">
                    <label class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">Chủ đề</label>
                    <select name="topics[]" id="topicSelect" class="block w-full" multiple>
                        @foreach ($topics as $topic)
                            <option value="{{ $topic->id }}"
                                {{ in_array($topic->id, old('topics', $selectedTopics)) ? 'selected' : '' }}>
                                {{ $topic->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Giữ Ctrl (hoặc Cmd) để chọn nhiều chủ đề.</div>
                    @error('topics')
                        <div class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Ảnh đại diện -->
                <div class="mb-4">
                    <label class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">Ảnh đại diện (thumbnail)</label>

                    <!-- Ảnh hiện tại -->
                    @if ($post->thumbnail)
                        <div class="mb-3">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Ảnh hiện tại:</div>
                            <img src="{{ $post->thumbnail }}" alt="Current thumbnail"
                                class="rounded-lg border border-gray-200 dark:border-gray-600 max-h-[150px] object-cover">
                        </div>
                    @endif

                    <input type="file" name="thumbnail" id="thumbnailInput"
                        class="block w-full py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent transition"
                        accept="image/*">
                    @error('thumbnail')
                        <div class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</div>
                    @enderror

                    <!-- Preview ảnh mới -->
                    <div class="mt-3">
                        <img id="thumbnailPreview" src="#" alt="Preview thumbnail"
                            class="rounded-lg border border-gray-200 dark:border-gray-600 max-h-[150px] object-cover hidden">
                    </div>
                </div>

                <!-- Trạng thái -->
                <div class="mb-4">
                    <label class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">Trạng thái</label>
                    <select name="status"
                        class="block w-full py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent transition">
                        <option value="draft" {{ old('status', $post->status) == 'draft' ? 'selected' : '' }}>Nháp</option>
                        <option value="published" {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>Xuất bản</option>
                    </select>
                    @error('status')
                        <div class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Ghim bài -->
                <div class="mb-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_pinned" id="isPinned"
                            class="h-4 w-4 text-black dark:text-white focus:ring-black dark:focus:ring-white border-gray-300 dark:border-gray-600 rounded"
                            {{ old('is_pinned', $post->is_pinned) ? 'checked' : '' }}>
                        <span class="text-base font-medium text-gray-700 dark:text-gray-300">Ghim bài viết</span>
                    </label>
                </div>

                <!-- Nút -->
                <div class="flex gap-3 justify-end">
                    <a href="{{ route('admin.posts.index') }}"
                        class="w-full sm:w-auto bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2.5 rounded-lg font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-center">
                        Quay lại
                    </a>
                    <button type="submit"
                        class="w-full sm:w-auto bg-black dark:bg-white text-white dark:text-black px-4 py-2.5 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        $(document).ready(function() {
            // Khởi tạo Select2
            $('#topicSelect').select2({
                placeholder: 'Chọn chủ đề...',
                width: '100%',
                theme: 'default',
                allowClear: true,
                dropdownCssClass: 'dark:bg-gray-800 dark:text-white'
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
                        preview.classList.remove('hidden');
                    }
                    reader.readAsDataURL(file);
                } else {
                    preview.src = '#';
                    preview.classList.add('hidden');
                }
            });

            // Auto scroll alert lỗi và tự ẩn
            const errorAlert = document.getElementById('formErrorAlert');
            const successAlert = document.getElementById('successAlert');

            if (errorAlert) {
                errorAlert.scrollIntoView({ behavior: 'smooth' });
                setTimeout(() => {
                    errorAlert.classList.add('fade-out');
                }, 5000);
            }

            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.add('fade-out');
                }, 3000);
            }

            // Khởi tạo CKEditor
            ClassicEditor.create(document.querySelector('#contentEditor'), {
                ckfinder: {
                    uploadUrl: '{{ route('admin.upload-image') . '?_token=' . csrf_token() }}'
                },
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                        'outdent', 'indent', '|',
                        'imageUpload', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo'
                    ]
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
