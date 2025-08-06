{{-- Trong view show.blade.php (hiển thị 1 chapter) --}}

@extends('layouts.app')

@section('title', $chapter->title)

@section('content')
<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">{{ $chapter->title }}</h3>
            <p class="text-muted mb-0">
                <strong>{{ $chapter->book->title }}</strong> - 
                Chương {{ $chapter->chapter_order }}
                @if($chapter->book->author)
                    | Tác giả: {{ $chapter->book->author->name }}
                @endif
            </p>
        </div>
        
        {{-- Navigation --}}
        <div class="btn-group">
            @if($previous)
                <a href="{{ route('admin.chapters.show', $previous) }}" 
                   class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Chương trước
                </a>
            @endif
            
            @if($next)
                <a href="{{ route('admin.chapters.show', $next) }}" 
                   class="btn btn-outline-secondary">
                    Chương sau <i class="bi bi-arrow-right"></i>
                </a>
            @endif
        </div>
    </div>

    {{-- Content --}}
    @if($chapter->isPdfContent())
        {{-- PDF Content --}}
        <div class="pdf-viewer-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>📄 Nội dung chương (PDF)</h5>
                <div class="btn-group">
                    <a href="{{ $chapter->getPdfViewUrl() }}" 
                       target="_blank" 
                       class="btn btn-primary">
                        <i class="bi bi-eye"></i> Xem PDF
                    </a>
                    <a href="{{ $chapter->getPdfDownloadUrl() }}" 
                       download="{{ $chapter->getPdfFilename() }}"
                       class="btn btn-outline-secondary">
                        <i class="bi bi-download"></i> Tải về
                    </a>
                </div>
            </div>
            
            {{-- Embed PDF viewer trong page --}}
            <div class="pdf-embed-container" style="height: 600px; border: 1px solid #ddd; border-radius: 8px;">
                <iframe src="{{ $chapter->getPdfViewUrl() }}#toolbar=1&navpanes=1&scrollbar=1" 
                        width="100%" 
                        height="100%" 
                        style="border: none; border-radius: 8px;">
                    <div class="alert alert-warning m-3">
                        <h6>Không thể hiển thị PDF</h6>
                        <p>Trình duyệt của bạn không hỗ trợ hiển thị PDF embedded.</p>
                        <a href="{{ $chapter->getPdfViewUrl() }}" 
                           target="_blank" 
                           class="btn btn-primary">
                            Mở PDF trong tab mới
                        </a>
                    </div>
                </iframe>
            </div>
        </div>
    @else
        {{-- Text Content --}}
        <div class="text-content bg-white p-4 rounded shadow-sm">
            {!! $chapter->content !!}
        </div>
    @endif

    {{-- Actions --}}
    <div class="mt-4 pt-3 border-top">
        <div class="btn-group">
            <a href="{{ route('admin.chapters.index') }}" 
               class="btn btn-secondary">
                <i class="bi bi-list"></i> Danh sách chương
            </a>
            
            <a href="{{ route('admin.chapters.edit', $chapter) }}" 
               class="btn btn-warning">
                <i class="bi bi-pencil"></i> Sửa chương
            </a>
            
            <form action="{{ route('admin.chapters.destroy', $chapter) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Bạn có chắc muốn xóa chương này?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Xóa
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

 