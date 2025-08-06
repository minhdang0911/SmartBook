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

    <!-- Chapters Table -->
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
                                       <a href="{{ route('admin.chapters.byBook', $chapter->book_id) }}" class="btn btn-sm btn-info">
    📚 Xem chương
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
@endsection