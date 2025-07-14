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

    .table-responsive {
        border-radius: 12px;
        overflow: hidden;
    }

    .book-cover {
        width: 60px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #ccc;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .btn-group-action {
        display: flex;
        justify-content: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    /* Phần pagination + tổng số */
    .book-pagination-summary {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-top: 24px;
        gap: 6px;
        text-align: center;
    }

    .book-pagination-summary .pagination {
        margin: 0;
    }

    .book-pagination-summary .book-summary {
        font-size: 0.92rem;
        color: #555;
    }

    /* Card view cho mobile */
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

    .empty-state {
        text-align: center;
        color: #666;
        padding: 32px 0;
        font-size: 1rem;
    }

    .additional-image-preview {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #ddd;
        box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 767.98px) {
        .table-responsive {
            display: none;
        }
    }

    @media (min-width: 768px) {
        .book-card {
            display: none;
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

    {{-- Bảng desktop --}}
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
                    <th>Loại sách</th>
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
                            <img src="{{ $book->cover_image ?? 'https://via.placeholder.com/60x80?text=No+Image' }}"
                            alt="Ảnh bìa"
                            class="book-cover">

                        </td>
                        <td>{{ $book->title }}</td>
                        <td>{{ $book->author->name ?? '—' }}</td>
                        <td>{{ $book->publisher->name ?? '—' }}</td>
                        <td>{{ $book->category->name ?? '—' }}</td>
                        <td class="text-center">
                            {{ $book->is_physical ? 'Sách giấy' : 'Sách điện tử' }}
                        </td>
                        <td class="text-end">
                            {{ $book->is_physical ? number_format($book->price, 0, ',', '.') . 'đ' : 'Miễn phí' }}
                        </td>
                        <td class="text-center">
                            {{ $book->is_physical ? $book->stock : '—' }}
                        </td>
                        <td class="text-center">
                            <div class="btn-group-action">
                                <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-warning btn-sm" title="Sửa">✏️</a>
                                <form action="{{ route('admin.books.destroy', $book) }}" method="POST" onsubmit="return confirm('Xóa sách này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Xóa">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">Không có sách nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile: card view --}}
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
                        🏷️ Loại: {{ $book->is_physical ? 'Sách giấy' : 'Sách điện tử' }}<br>
                        💰 Giá: {{ $book->is_physical ? number_format($book->price, 0, ',', '.') . 'đ' : 'Miễn phí' }}<br>
                        📦 Tồn kho: {{ $book->is_physical ? $book->stock : '—' }}
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

    {{-- Empty state --}}
    @if ($books->isEmpty())
        <div class="empty-state">
            😕 Không tìm thấy sách nào.
            @if (request('search'))
                <br>Với từ khóa: <strong>"{{ request('search') }}"</strong>
            @endif
        </div>
    @endif

                {{-- Phân trang + tổng số sách --}}
@if ($books->total() > 0)
    <div class="book-pagination-summary">
        {{ $books->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
        <div class="book-summary">
            Sách đang hiển thị <strong>{{ $books->firstItem() }}–{{ $books->lastItem() }}</strong> trên tổng <strong>{{ $books->total() }}</strong> sách
        </div>
    </div>
@endif

@endsection
