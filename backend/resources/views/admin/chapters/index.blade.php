@extends('layouts.app')
@section('title', 'Danh sách Chương')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-book"></i> Danh sách Chương</h2>
        <div>
            <a href="{{ route('admin.chapters.create') }}" class="btn btn-primary">
                <i class="bi bi-plus"></i> Thêm Chương
            </a>
        </div>
    </div>

    <!-- Search/Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.chapters.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="book_title" class="form-label">Tên Sách</label>
                        <input type="text" 
                               class="form-control" 
                               id="book_title" 
                               name="book_title" 
                               value="{{ request('book_title') }}" 
                               placeholder="Tìm theo tên sách...">
                    </div>
                    <div class="col-md-4">
                        <label for="chapter_title" class="form-label">Tên Chương</label>
                        <input type="text" 
                               class="form-control" 
                               id="chapter_title" 
                               name="chapter_title" 
                               value="{{ request('chapter_title') }}" 
                               placeholder="Tìm theo tên chương...">
                    </div>
                    <div class="col-md-4">
                        <label for="content_type" class="form-label">Loại Nội Dung</label>
                        <select class="form-select" id="content_type" name="content_type">
                            <option value="">Tất cả loại</option>
                            <option value="text" {{ request('content_type') == 'text' ? 'selected' : '' }}>Text</option>
                            <option value="video" {{ request('content_type') == 'video' ? 'selected' : '' }}>Video</option>
                            <option value="audio" {{ request('content_type') == 'audio' ? 'selected' : '' }}>Audio</option>
                            <option value="mixed" {{ request('content_type') == 'mixed' ? 'selected' : '' }}>Hỗn hợp</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Tìm kiếm
                    </button>
                    <a href="{{ route('admin.chapters.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Đặt lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Toggle View Button -->
    <div class="d-flex justify-content-end mb-3">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary active" id="groupedView">
                <i class="bi bi-folder"></i> Nhóm theo sách
            </button>
            <button type="button" class="btn btn-outline-primary" id="listView">
                <i class="bi bi-list"></i> Danh sách
            </button>
        </div>
    </div>

    <!-- Grouped View (Default) -->
    <div id="grouped-container">
        @if($chaptersGrouped->count() > 0)
            @foreach($chaptersGrouped as $book)
                <div class="card mb-4 book-folder">
                    <div class="card-header bg-light" style="cursor: pointer;" onclick="toggleBookChapters({{ $book->id }})">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-folder-fill text-primary me-2"></i>
                                <h5 class="mb-0">{{ $book->title }}</h5>
                                <span class="badge bg-secondary ms-2">{{ $book->chapters->count() }} chương</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-3">Tác giả: {{ $book->author->name ?? 'N/A' }}</small>
                                <i class="bi bi-chevron-down toggle-icon" id="toggle-{{ $book->id }}"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="collapse book-chapters" id="chapters-{{ $book->id }}">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">#</th>
                                            <th>Tên Chương</th>
                                            <th width="80">Thứ tự</th>
                                            <th width="120">Loại nội dung</th>
                                            <th width="120">Trạng thái</th>
                                            <th width="150">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($book->chapters->sortBy('chapter_order') as $chapter)
                                            <tr>
                                                <td>
                                                    <i class="bi bi-file-text text-info"></i>
                                                </td>
                                                <td>
                                                    <strong>{{ $chapter->title }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $chapter->chapter_order }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge 
                                                        @switch($chapter->content_type)
                                                            @case('text') bg-primary @break
                                                            @case('video') bg-danger @break
                                                            @case('audio') bg-warning @break
                                                            @case('mixed') bg-success @break
                                                            @default bg-secondary
                                                        @endswitch">
                                                        {{ ucfirst($chapter->content_type ?? 'N/A') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($chapter->is_published ?? true)
                                                        <span class="badge bg-success">Đã xuất bản</span>
                                                    @else
                                                        <span class="badge bg-warning">Bản nháp</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('admin.chapters.show', $chapter->id) }}" 
                                                           class="btn btn-info" 
                                                           title="Xem">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.chapters.edit', $chapter->id) }}" 
                                                           class="btn btn-warning" 
                                                           title="Sửa">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <form action="{{ route('admin.chapters.destroy', $chapter->id) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa chương này?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-danger" 
                                                                    title="Xóa">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-5">
                <i class="bi bi-folder text-muted" style="font-size: 4rem;"></i>
                <h4 class="text-muted mt-3">Không tìm thấy sách nào có chương</h4>
                <p class="text-muted">Thử thay đổi bộ lọc tìm kiếm hoặc thêm chương mới.</p>
                <a href="{{ route('admin.chapters.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Thêm Chương Đầu Tiên
                </a>
            </div>
        @endif
    </div>

    <!-- List View (Hidden by default) -->
    <div id="list-container" style="display: none;">
        <div class="card">
            <div class="card-body">
                @if($chapters->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Sách</th>
                                    <th>Tác giả</th>
                                    <th>Tên Chương</th>
                                    <th>Thứ tự</th>
                                    <th>Loại nội dung</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chapters as $index => $chapter)
                                    <tr>
                                        <td>{{ $chapters->firstItem() + $index }}</td>
                                        <td>
                                            <strong>{{ $chapter->book->title ?? 'N/A' }}</strong>
                                        </td>
                                        <td>{{ $chapter->book->author->name ?? 'N/A' }}</td>
                                        <td>{{ $chapter->title }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $chapter->chapter_order }}</span>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @switch($chapter->content_type)
                                                    @case('text') bg-primary @break
                                                    @case('video') bg-danger @break
                                                    @case('audio') bg-warning @break
                                                    @case('mixed') bg-success @break
                                                    @default bg-secondary
                                                @endswitch">
                                                {{ ucfirst($chapter->content_type ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($chapter->is_published ?? true)
                                                <span class="badge bg-success">Đã xuất bản</span>
                                            @else
                                                <span class="badge bg-warning">Bản nháp</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.chapters.show', $chapter->id) }}" 
                                                   class="btn btn-info" 
                                                   title="Xem">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.chapters.edit', $chapter->id) }}" 
                                                   class="btn btn-warning" 
                                                   title="Sửa">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.chapters.destroy', $chapter->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa chương này?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-danger" 
                                                            title="Xóa">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $chapters->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-book text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-2">Không tìm thấy chương nào</h5>
                        <p class="text-muted">Thử thay đổi bộ lọc tìm kiếm hoặc thêm chương mới.</p>
                        <a href="{{ route('admin.chapters.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Thêm Chương Đầu Tiên
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Toggle between grouped and list view
document.getElementById('groupedView').addEventListener('click', function() {
    document.getElementById('grouped-container').style.display = 'block';
    document.getElementById('list-container').style.display = 'none';
    this.classList.add('active');
    document.getElementById('listView').classList.remove('active');
    localStorage.setItem('chapterView', 'grouped');
});

document.getElementById('listView').addEventListener('click', function() {
    document.getElementById('grouped-container').style.display = 'none';
    document.getElementById('list-container').style.display = 'block';
    this.classList.add('active');
    document.getElementById('groupedView').classList.remove('active');
    localStorage.setItem('chapterView', 'list');
});

// Remember user preference
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('chapterView');
    if (savedView === 'list') {
        document.getElementById('listView').click();
    }
});

// Toggle book chapters
function toggleBookChapters(bookId) {
    const chaptersDiv = document.getElementById('chapters-' + bookId);
    const toggleIcon = document.getElementById('toggle-' + bookId);
    
    if (chaptersDiv.classList.contains('show')) {
        chaptersDiv.classList.remove('show');
        toggleIcon.classList.remove('bi-chevron-up');
        toggleIcon.classList.add('bi-chevron-down');
    } else {
        chaptersDiv.classList.add('show');
        toggleIcon.classList.remove('bi-chevron-down');
        toggleIcon.classList.add('bi-chevron-up');
    }
}

// Expand/Collapse all
function expandAll() {
    document.querySelectorAll('.book-chapters').forEach(function(element) {
        element.classList.add('show');
    });
    document.querySelectorAll('.toggle-icon').forEach(function(icon) {
        icon.classList.remove('bi-chevron-down');
        icon.classList.add('bi-chevron-up');
    });
}

function collapseAll() {
    document.querySelectorAll('.book-chapters').forEach(function(element) {
        element.classList.remove('show');
    });
    document.querySelectorAll('.toggle-icon').forEach(function(icon) {
        icon.classList.remove('bi-chevron-up');
        icon.classList.add('bi-chevron-down');
    });
}
</script>

<style>
.book-folder {
    border-left: 4px solid #0d6efd;
}

.book-folder .card-header:hover {
    background-color: #f8f9fa !important;
}

.toggle-icon {
    transition: transform 0.2s ease;
}

.book-chapters.show {
    display: block !important;
}

.book-chapters:not(.show) {
    display: none;
}

.btn-group .btn.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}
</style>
@endsectio