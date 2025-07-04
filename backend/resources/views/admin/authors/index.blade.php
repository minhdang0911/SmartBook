@extends('layouts.app')

@section('title', 'Danh sách Tác giả')

<!-- Ant Design CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/antd/4.16.13/antd.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

<style>
    .ant-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
    }

    .ant-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .ant-page-title {
        font-size: 24px;
        font-weight: 600;
        color: #1677ff;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ant-table {
        border: 1px solid #f0f0f0;
        border-radius: 6px;
        overflow: hidden;
    }

    .ant-table th,
    .ant-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
    }

    .ant-table th {
        background-color: #fafafa;
        font-weight: 600;
        color: #595959;
    }

    .ant-table tr:hover {
        background-color: #f5f5f5;
    }

    .ant-search-form {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
    }

    .ant-search-form input {
        padding: 8px 12px;
        border: 1px solid #d9d9d9;
        border-radius: 4px;
        width: 280px;
        transition: border 0.3s, box-shadow 0.3s;
    }

    .ant-search-form input:focus {
        outline: none;
        border-color: #1677ff;
        box-shadow: 0 0 0 2px rgba(22, 119, 255, 0.2);
    }

    .ant-btn {
        padding: 6px 16px;
        font-size: 14px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
    }

    .ant-btn-primary {
        background-color: #1677ff;
        color: white;
    }

    .ant-btn-primary:hover {
        background-color: #4096ff;
    }

    .ant-btn-warning {
        background-color: #faad14;
        color: white;
    }

    .ant-btn-danger {
        background-color: #ff4d4f;
        color: white;
    }

    .ant-btn-danger:hover {
        background-color: #ff7875;
    }

    .ant-empty {
        text-align: center;
        color: #999;
        padding: 30px;
        font-size: 14px;
    }

    .ant-pagination {
        margin-top: 24px;
    }
</style>

@section('content')
<div class="ant-container">

    <!-- Header -->
    <div class="ant-page-header">
        <h1 class="ant-page-title">
            <i class="fas fa-user"></i> Danh sách Tác giả
        </h1>
        <x-admin.button.modal-button
            target="addAuthorModal"
            text="➕ Thêm mới"
            class="ant-btn ant-btn-primary" />
    </div>

    @include('components.alert')

    <!-- Form tìm kiếm -->
    <form method="GET" action="{{ route('admin.authors.index') }}" class="ant-search-form">
        <input
            type="text"
            name="search"
            placeholder="🔍 Tìm tác giả..."
            value="{{ request('search') }}">
        <button type="submit" class="ant-btn ant-btn-primary">Tìm</button>
    </form>

    <!-- Bảng dữ liệu -->
    <div class="ant-table">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên Tác giả</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($authors as $index => $author)
                    <tr>
                        <td>{{ $authors->firstItem() + $index }}</td>
                        <td>{{ $author->name }}</td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <x-admin.button.modal-button
                                    target="editAuthorModal{{ $author->id }}"
                                    text="Sửa"
                                    class="ant-btn ant-btn-warning" />
                                <form action="{{ route('admin.authors.destroy', $author) }}" method="POST"
                                      onsubmit="return confirm('Bạn chắc chắn muốn xóa?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ant-btn ant-btn-danger">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="ant-empty">
                            😕 Không tìm thấy tác giả nào
                            @if (request('search'))
                                với từ khóa <strong>"{{ request('search') }}"</strong>.
                            @endif
                            <p>Hãy thử lại với từ khoá khác.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="ant-pagination">
        {{ $authors->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
    </div>

    <!-- Modal -->
    @foreach ($authors as $author)
        <x-admin.modal.edit-author :author="$author" />
    @endforeach

    <x-admin.modal.add-author />
</div>
@endsection
