{{-- Trong view show.blade.php (hiển thị 1 chapter) --}}

@extends('layouts.app')

@section('title', $chapter ? $chapter->title : 'Chọn chương')

@section('content')
<div class="container py-4">
    @if($chapter)
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
                    <a href="{{ route('admin.chapters.byBook', ['bookId' => $chapter->book_id, 'chapter_id' => $previous->id]) }}" 
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Chương trước
                    </a>
                @endif
                
                @if($next)
                    <a href="{{ route('admin.chapters.byBook', ['bookId' => $chapter->book_id, 'chapter_id' => $next->id]) }}" 
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
    @else
        {{-- No chapter selected or no chapters available --}}
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-book" style="font-size: 4rem; color: #6c757d;"></i>
            </div>
            <h4 class="text-muted mb-3">Không có chương nào để hiển thị</h4>
            @if($chapters->count() > 0)
                <p class="text-muted mb-4">Vui lòng chọn một chương để đọc:</p>
                <div class="list-group" style="max-width: 600px; margin: 0 auto;">
                    @foreach($chapters as $chapterItem)
                        <a href="{{ route('admin.chapters.byBook', ['bookId' => $book->id, 'chapter_id' => $chapterItem->id]) }}" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $chapterItem->title }}</h6>
                                <small class="text-muted">Chương {{ $chapterItem->chapter_order }}</small>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-{{ $chapterItem->content_type === 'pdf' ? 'file-pdf' : 'file-text' }}"></i>
                                {{ $chapterItem->content_type === 'pdf' ? 'PDF' : 'Text' }}
                            </small>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-4">Sách này chưa có chương nào.</p>
                <a href="{{ route('admin.chapters.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Thêm chương mới
                </a>
            @endif
        </div>
    @endif
</div>
@endsection