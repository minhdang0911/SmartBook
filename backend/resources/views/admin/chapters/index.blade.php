@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">📚 Danh sách chương</h2>

    <a href="{{ route('admin.chapters.create') }}" class="btn btn-primary mb-3">➕ Thêm chương</a>
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
    </div>
</form>

    <table class="table table-bordered table-striped align-middle">
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
                        <a href="{{ route('admin.chapters.show', $chapter) }}" class="btn btn-sm btn-info">👁 Xem</a>

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
    <style>
        .pagination {
            display: flex;
            gap: 6px;
            padding: 0;
            list-style: none;
            margin: 0;
        }

        .pagination li {
            display: inline;
        }

        .pagination li a,
        .pagination li span {
            display: inline-block;
            padding: 6px 12px;
            font-size: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            color: #007bff;
            background-color: #fff;
            text-decoration: none;
            transition: 0.2s all;
        }

        .pagination li.active span {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        .pagination li.disabled span {
            background-color: #f1f1f1;
            color: #999;
            cursor: not-allowed;
        }

        .pagination li a:hover {
            background-color: #e9ecef;
            color: #0056b3;
        }
    </style>

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
    