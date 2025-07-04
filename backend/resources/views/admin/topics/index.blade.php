@extends('layouts.app')

@section('title', 'Quản lý chủ đề')

@section('content')
<div class="container" style="max-width: 1200px; padding: 24px;">
    {{-- PAGE HEADER --}}
    <div class="page-header mb-4 p-4 rounded-3 shadow-sm" style="background: linear-gradient(135deg, #6b7280, #4b5563);">
        <h4 class="mb-0 fw-bold text-white"><i class="bi bi-grid-fill me-2"></i> Quản lý chủ đề</h4>
    </div>

    {{-- FLASH MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- SEARCH + ADD --}}
    <form class="search-form d-flex flex-wrap gap-3 align-items-center mb-4" method="GET">
        <div class="input-group" style="max-width: 400px;">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" name="keyword" class="form-control" placeholder="Tìm tên chủ đề..." value="{{ request('keyword') }}">
        </div>
        <button class="btn btn-primary px-4"><i class="bi bi-search me-2"></i>Tìm kiếm</button>
        <button type="button" class="btn btn-success px-4 ms-auto" data-bs-toggle="modal" data-bs-target="#addTopicModal">
            <i class="bi bi-plus-circle me-2"></i>Thêm mới
        </button>
    </form>

    {{-- TABLE --}}
    <div class="table-responsive shadow-sm rounded-3">
        <table class="table table-hover table-bordered align-middle text-center mb-0">
            <thead style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 30%">Tên chủ đề</th>
                    <th style="width: 30%">Slug</th>
                    <th style="width: 15%">Ngày tạo</th>
                    <th style="width: 20%">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topics as $index => $topic)
                <tr>
                    <td>{{ $index + $topics->firstItem() }}</td>
                    <td class="text-start">{{ $topic->name }}</td>
                    <td class="text-start">{{ $topic->slug }}</td>
                    <td>{{ $topic->created_at->format('d/m/Y') }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning px-3" data-bs-toggle="modal" data-bs-target="#editTopicModal{{ $topic->id }}">
                            <i class="bi bi-pencil-square me-1"></i>Sửa
                        </button>
                        <form action="{{ route('admin.topics.destroy', $topic) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Xác nhận xoá chủ đề này?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger px-3"><i class="bi bi-trash me-1"></i>Xoá</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-muted text-center py-5">
                        <i class="bi bi-folder-x" style="font-size: 2.5rem;"></i><br>
                        Không có chủ đề nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $topics->withQueryString()->links('pagination::bootstrap-5') }}
    </div>

    {{-- EDIT MODALS --}}
    @foreach($topics as $topic)
    <div class="modal fade" id="editTopicModal{{ $topic->id }}" tabindex="-1" aria-labelledby="editTopicModalLabel{{ $topic->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <form action="{{ route('admin.topics.update', $topic) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header border-0 bg-light">
                        <h5 class="modal-title fw-bold" id="editTopicModalLabel{{ $topic->id }}">Sửa chủ đề</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tên chủ đề</label>
                            <input type="text" name="name" class="form-control" value="{{ $topic->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Slug (tùy chọn)</label>
                            <input type="text" name="slug" class="form-control" value="{{ $topic->slug }}">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ADD MODAL --}}
<div class="modal fade" id="addTopicModal" tabindex="-1" aria-labelledby="addTopicModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3">
            <form action="{{ route('admin.topics.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title fw-bold" id="addTopicModalLabel">Thêm chủ đề</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên chủ đề</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Slug (tùy chọn)</label>
                        <input type="text" name="slug" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.table-responsive {
    border-radius: 0.5rem;
    overflow: hidden;
}
.table th, .table td {
    padding: 12px;
}
.btn-sm {
    transition: all 0.2s ease;
}
.btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.modal-content {
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}
.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}
</style>
@endsection
