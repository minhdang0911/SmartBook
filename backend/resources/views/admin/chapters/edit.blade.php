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

    <form id="chapter-form" action="{{ route('admin.chapters.update', $chapter->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Chỉ hiển thị tên sách --}}
        <div class="mb-3">
            <label class="form-label">Sách</label>
            <input type="text" class="form-control" value="{{ $chapter->book->title ?? 'Không xác định' }} - {{ $chapter->book->author->name ?? 'Không rõ tác giả' }}" readonly>
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

        {{-- Loại nội dung hiện tại --}}
        <div class="mb-3">
            <label class="form-label">Loại nội dung hiện tại</label>
            <div class="alert alert-info">
                @if($chapter->content_type === 'pdf')
                    <i class="bi bi-file-earmark-pdf text-danger"></i>
                    <strong>PDF:</strong> {{ basename($chapter->pdf_url ?? 'N/A') }}
                    @if($chapter->pdf_url)
                        <a href="{{ $chapter->pdf_url }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="bi bi-eye"></i> Xem PDF hiện tại
                        </a>
                    @endif
                @else
                    <i class="bi bi-file-earmark-text text-secondary"></i>
                    <strong>Nội dung văn bản</strong>
                @endif
            </div>
        </div>

        {{-- Chuyển đổi loại nội dung --}}
        <div class="mb-3">
            <label class="form-label">Thay đổi loại nội dung</label>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="content_type" id="content_text" value="text" {{ old('content_type', $chapter->content_type) === 'text' ? 'checked' : '' }}>
                        <label class="form-check-label" for="content_text">
                            <i class="bi bi-file-earmark-text"></i> Nội dung văn bản
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="content_type" id="content_pdf" value="pdf" {{ old('content_type', $chapter->content_type) === 'pdf' ? 'checked' : '' }}>
                        <label class="form-check-label" for="content_pdf">
                            <i class="bi bi-file-earmark-pdf"></i> File PDF
                        </label>
                    </div>
                </div>
            </div>
            @error('content_type')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        {{-- Nội dung văn bản --}}
        <div class="mb-3" id="text-content-section">
            <label for="content" class="form-label">Nội dung chương</label>
            <textarea name="content" id="editor" class="form-control @error('content') is-invalid @enderror" rows="10">{{ old('content', $chapter->content) }}</textarea>
            @error('content')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Upload PDF --}}
        <div class="mb-3" id="pdf-content-section">
            <label for="pdf_file" class="form-label">
                @if($chapter->content_type === 'pdf')
                    Thay đổi file PDF (để trống nếu không muốn thay đổi)
                @else
                    Tải lên file PDF mới
                @endif
            </label>
            <input type="file" name="pdf_file" id="pdf_file" class="form-control @error('pdf_file') is-invalid @enderror" accept=".pdf">
            <div class="form-text">
                <i class="bi bi-info-circle"></i> 
                Chỉ chấp nhận file PDF, tối đa 10MB. File sẽ được lưu trữ trên Cloudinary.
                @if($chapter->content_type === 'pdf')
                    <br><strong>Lưu ý:</strong> Nếu upload file mới, file cũ sẽ bị xóa vĩnh viễn.
                @endif
            </div>
            @error('pdf_file')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            
            {{-- Preview PDF --}}
            <div id="pdf-preview" class="mt-2" style="display: none;">
                <div class="alert alert-success">
                    <i class="bi bi-file-earmark-pdf"></i>
                    <strong>File mới đã chọn:</strong> <span id="pdf-filename"></span>
                    <br>
                    <small><strong>Kích thước:</strong> <span id="pdf-filesize"></span></small>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle"></i> Cập nhật
            </button>
            <a href="{{ route('admin.chapters.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
            
            @if($chapter->content_type === 'pdf' && $chapter->pdf_url)
                <a href="{{ $chapter->pdf_url }}" target="_blank" class="btn btn-info">
                    <i class="bi bi-eye"></i> Xem PDF hiện tại
                </a>
            @endif
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .form-check-label {
        cursor: pointer;
        font-weight: 500;
    }
    
    .form-check-input:checked ~ .form-check-label {
        color: #0d6efd;
    }

    #pdf-preview {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 15px;
        background-color: #f8f9fa;
    }
    
    .alert {
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const textContentSection = document.getElementById('text-content-section');
        const pdfContentSection = document.getElementById('pdf-content-section');
        const pdfFileInput = document.getElementById('pdf_file');
        const pdfPreview = document.getElementById('pdf-preview');
        const pdfFilename = document.getElementById('pdf-filename');
        const pdfFilesize = document.getElementById('pdf-filesize');
        const form = document.getElementById('chapter-form');
        
        let editorInstance;

        // ✅ Toggle content type sections
        function toggleContentSections() {
            const selectedType = document.querySelector('input[name="content_type"]:checked').value;
            
            if (selectedType === 'pdf') {
                textContentSection.style.display = 'none';
                pdfContentSection.style.display = 'block';
                // Clear text content when switching to PDF
                if (editorInstance) {
                    editorInstance.setData('');
                }
            } else {
                textContentSection.style.display = 'block';
                pdfContentSection.style.display = 'none';
                // Clear PDF input when switching to text
                pdfFileInput.value = '';
                pdfPreview.style.display = 'none';
            }
        }

        // ✅ Content type radio change event
        document.querySelectorAll('input[name="content_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'text' && '{{ $chapter->content_type }}' === 'pdf') {
                    const confirmSwitch = confirm('⚠️ Bạn có chắc muốn chuyển từ PDF sang văn bản? File PDF hiện tại sẽ bị xóa vĩnh viễn!');
                    if (!confirmSwitch) {
                        document.getElementById('content_pdf').checked = true;
                        return;
                    }
                }
                
                if (this.value === 'pdf' && '{{ $chapter->content_type }}' === 'text') {
                    const confirmSwitch = confirm('⚠️ Bạn có chắc muốn chuyển từ văn bản sang PDF? Nội dung văn bản hiện tại sẽ bị mất!');
                    if (!confirmSwitch) {
                        document.getElementById('content_text').checked = true;
                        return;
                    }
                }
                
                toggleContentSections();
            });
        });

        // ✅ Initialize content sections
        toggleContentSections();

        // ✅ PDF file input change event
        pdfFileInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                // Validate file type
                if (file.type !== 'application/pdf') {
                    alert('❌ Chỉ chấp nhận file PDF!');
                    this.value = '';
                    pdfPreview.style.display = 'none';
                    return;
                }
                
                // Validate file size (10MB = 10 * 1024 * 1024 bytes)
                const maxSize = 40 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert('❌ File PDF không được vượt quá 10MB!');
                    this.value = '';
                    pdfPreview.style.display = 'none';
                    return;
                }
                
                // Show preview
                pdfFilename.textContent = file.name;
                pdfFilesize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                pdfPreview.style.display = 'block';
            } else {
                pdfPreview.style.display = 'none';
            }
        });

        // ✅ Form validation
        form.addEventListener('submit', function (e) {
            const contentType = document.querySelector('input[name="content_type"]:checked').value;

            if (contentType === 'text') {
                const content = editorInstance.getData();
                if (!content.trim()) {
                    e.preventDefault();
                    alert('⚠️ Nội dung chương không được để trống.');
                    return;
                }
            } else if (contentType === 'pdf') {
                // Nếu chapter hiện tại không phải PDF và chuyển sang PDF, bắt buộc phải có file
                if ('{{ $chapter->content_type }}' !== 'pdf' && !pdfFileInput.files[0]) {
                    e.preventDefault();
                    alert('⚠️ Vui lòng chọn file PDF để upload.');
                    return;
                }
            }
        });

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