@extends('layouts.app')
@section('title', 'Danh sách Chương')

@section('content')
<div class="container">
<div class="page-header">
    <h1><i class="bi bi-journals"></i> Danh sách Chương</h1>
</div>

<form method="GET" action="{{ route('admin.chapters.index') }}" class="mb-3 row g-2 align-items-center">
    <div class="col-md-4">
        <input type="text" name="book_title" value="{{ request('book_title') }}" class="form-control" placeholder="🔍 Tìm theo tên sách">
    </div>
    <div class="col-md-4">
        <input type="text" name="chapter_title" value="{{ request('chapter_title') }}" class="form-control" placeholder="🔍 Tìm theo tên chương">
    </div>
    <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Tìm kiếm</button>
        <a href="{{ route('admin.chapters.index') }}" class="btn btn-secondary">🔄 Reset</a>
        <a href="{{ route('admin.chapters.create') }}" class="btn btn-success ">➕ Thêm chương</a>
</div>
    </div>
    
</form>

<table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th scope="col">📖 Tên sách</th>
                <th scope="col">📄 Tên chương</th>
                <th scope="col">#️⃣ Thứ tự</th>
                <th scope="col" class="text-center">⚙️ Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($chapters as $chapter)
                <tr>
                    <td>{{ $chapter->book->title ?? 'Không xác định' }}</td>
                    <td>{{ $chapter->title }}</td>
                    <td>{{ $chapter->chapter_order }}</td>
                    <td class="text-center">

                        <a href="{{ route('admin.chapters.edit', $chapter->id) }}" class="btn btn-sm btn-warning">
                            ✏️ Sửa
                        </a>

                        <form action="{{ route('admin.chapters.destroy', $chapter->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa chương này?')">
                                🗑 Xóa
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Không có chương nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <center><div class="d-flex justify-content-center mt-4">
   @push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 32px 24px;
        text-align: center;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
    }

    .page-header h1 {
        font-size: 1.8rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .table thead th {
        background-color: #000 !important;
        color: white !important;
        text-align: center;
        vertical-align: middle;
    }

    .table td {
        vertical-align: middle !important;
        font-size: 0.92rem;
        text-align: center;
    }

    .btn-group-action {
        display: flex;
        justify-content: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .pagination {
        justify-content: center;
        margin-top: 24px;
    }

    .empty-state {
        text-align: center;
        color: #666;
        padding: 32px 0;
        font-size: 1rem;
    }

    .form-control {
        font-size: 0.95rem;
    }

    .btn {
        font-size: 0.9rem;
    }

    .text-muted {
        font-size: 0.9rem;
    }
</style>
@endpush


{{ $chapters->withQueryString()->links('pagination::bootstrap-4') }}
</center>
<div class="text-center text-muted mt-2">
    Đang hiển thị {{ $chapters->firstItem() }}–{{ $chapters->lastItem() }} trong tổng số {{ $chapters->total() }} chương.
</div>

</div>



</div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tự động focus vào ô tìm kiếm đầu tiên
        const firstInput = document.querySelector('input[name="book_title"]');
        if (firstInput) {
            firstInput.focus();
        }
    });
    </script>
    