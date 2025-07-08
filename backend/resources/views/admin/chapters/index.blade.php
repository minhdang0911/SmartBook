@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">📚 Danh sách chương</h2>

    <a href="{{ route('admin.chapters.create') }}" class="btn btn-primary mb-3">➕ Thêm chương</a>

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
</div>
@endsection
