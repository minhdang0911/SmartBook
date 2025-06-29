@extends('layouts.app')

@section('title', 'Danh sách Sách')

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

    .search-form {
        display: flex;
        align-items: center;
        gap: 12px;
        max-width: 100%;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .search-form .form-control {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 10px 16px;
        font-size: 0.95rem;
        flex: 1;
        min-width: 200px;
        transition: 0.3s ease;
    }

    .search-form .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .search-form .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: 0.3s ease;
    }

    .search-form .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .search-form .btn-success {
        background: linear-gradient(135deg, #28a745, #34c759);
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .search-form .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
    }

    .table th,
    .table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .btn-sm {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
    }

    .empty-state {
        text-align: center;
        color: #666;
        padding: 32px 0;
        font-size: 1rem;
    }

    .pagination {
        justify-content: center;
        margin-top: 24px;
    }

    .book-card {
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        background-color: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .book-header {
        display: flex;
        gap: 16px;
        align-items: center;
    }

    .book-cover {
        width: 80px;
        height: auto;
        border-radius: 8px;
        object-fit: cover;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    }

    .book-info h5 {
        margin: 0 0 8px;
        font-size: 1rem;
        font-weight: 600;
    }

    .book-meta {
        font-size: 0.875rem;
        color: #555;
        line-height: 1.4;
    }

    .book-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 12px;
        flex-wrap: wrap;
    }

    .book-actions .btn-sm {
        padding: 6px 12px;
        font-size: 0.85rem;
        border-radius: 6px;
    }

    @media (min-width: 768px) {
        .book-card {
            display: none;
        }
    }

    @media (max-width: 767.98px) {
        .table-responsive {
            display: none;
        }
    }

    @media (max-width: 576px) {
        .page-header {
            padding: 16px;
        }

        .page-header h1 {
            font-size: 1.2rem;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 0.75rem;
        }

        .book-cover {
            width: 60px;
        }

        .book-info h5 {
            font-size: 0.95rem;
        }

        .book-meta {
            font-size: 0.8rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="page-header">
        <h1><i class="bi bi-journal-bookmark"></i> Danh sách Sách</h1>
    </div>

    @include('components.alert')

    @include('admin.books.partials.filters')

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light text-center">
                <tr>
                    <th>STT</th>
                    <th>Ảnh bìa</th>
                    <th>Tên sách</th>
                    <th>Tác giả</th>
                    <th>NXB</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Tồn kho</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($books as $index => $book)
                    <tr>
                        <td class="text-center">{{ $books->firstItem() + $index }}</td>
                        <td class="text-center">
                            <img src="{{ $book->cover_image ?? 'https://via.placeholder.com/60x80?text=No+Image' }}" alt="Ảnh bìa" style="height: 60px;" class="rounded shadow-sm">
                        </td>
                        <td>{{ $book->title }}</td>
                        <td>{{ $book->author->name ?? '—' }}</td>
                        <td>{{ $book->publisher->name ?? '—' }}</td>
                        <td>{{ $book->category->name ?? '—' }}</td>
                        <td class="text-end">{{ number_format($book->price, 0, ',', '.') }}đ</td>
                        <td class="text-center">{{ $book->stock }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-warning btn-sm me-1">✏️</a>
                            <form action="{{ route('admin.books.destroy', $book) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa sách này?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">Không có sách nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @foreach ($books as $book)
        <div class="book-card">
            <div class="book-header">
                <img src="{{ $book->cover_image ?? 'https://via.placeholder.com/80x100?text=No+Image' }}" alt="Ảnh bìa" class="book-cover">
                <div class="book-info">
                    <h5>{{ $book->title }}</h5>
                    <div class="book-meta">
                        📖 Tác giả: {{ $book->author->name ?? '—' }}<br>
                        🏢 NXB: {{ $book->publisher->name ?? '—' }}<br>
                        🗂️ Danh mục: {{ $book->category->name ?? '—' }}<br>
                        💰 Giá: {{ number_format($book->price, 0, ',', '.') }}đ<br>
                        📦 Tồn kho: {{ $book->stock }}
                    </div>
                </div>
            </div>
            <div class="book-actions">
                <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-warning btn-sm">✏️ Sửa</a>
                <form action="{{ route('admin.books.destroy', $book) }}" method="POST" onsubmit="return confirm('Xóa sách này?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">🗑️ Xóa</button>
                </form>
            </div>
        </div>
    @endforeach

    @if ($books->isEmpty())
        <div class="empty-state">
            😕 Không tìm thấy sách nào.
            @if (request('search'))
                <br>Với từ khóa: <strong>"{{ request('search') }}"</strong>
            @endif
        </div>
    @endif

    <div class="pagination">
        {{ $books->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
