
@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>🏢 Quản lý Nhà xuất bản</h1>
    <p>Danh sách các nhà xuất bản và công cụ quản lý</p>
</div>

<div class="publisher-container">
    <!-- Flash messages -->
    @include('components.alert')

    <!-- Action Bar -->
    <div class="publisher-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; color: #333;">Danh sách Nhà xuất bản</h3>
                <p style="margin: 4px 0 0 0; color: #666;">Tổng cộng: <strong>{{ $publishers->total() }}</strong> nhà xuất bản</p>
            </div>
            <x-admin.button.modal-button target="addPublisherModal" text="➕ Thêm Nhà xuất bản Mới" class="btn-success" />
        </div>
    </div>

    <!-- Search Form -->
    <div class="publisher-card">
        <form method="GET" action="{{ route('admin.publishers.index') }}" class="d-flex" role="search">
            <input type="text" name="search" class="form-control me-2" placeholder="🔍 Tìm nhà xuất bản..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary">Tìm</button>
        </form>
    </div>

    <!-- Publishers Table -->
    <div class="publisher-card">
        <table class="publisher-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên nhà xuất bản</th>
                    <th style="text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($publishers as $index => $publisher)
                    <tr>
                        <td style="font-weight: 600; color: #667eea;">{{ $publishers->firstItem() + $index }}</td>
                        <td>{{ $publisher->name }}</td>
                        <td style="text-align: center;">
                            <x-admin.button.modal-button
                                target="editPublisherModal{{ $publisher->id }}"
                                text="✏️ Sửa"
                                class="btn-warning btn-sm" />
                            <form action="{{ route('admin.publishers.destroy', $publisher) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">🗑️ Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">
                            😕 Không tìm thấy nhà xuất bản nào
                            @if(request('search'))
                                với từ khóa <strong>"{{ request('search') }}"</strong>.
                            @endif
                            <p class="text-muted small mt-1">Hãy thử tìm với tên khác hoặc kiểm tra lại chính tả nha!</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 text-center">
        {{ $publishers->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
    </div>

    <!-- Edit Publisher Modals -->
    @foreach ($publishers as $publisher)
        <x-admin.modal.edit-publisher :publisher="$publisher" />
    @endforeach

    <!-- Add Publisher Modal -->
    <x-admin.modal.add-publisher />
</div>

@push('styles')
<style>
    /* Container */
    .publisher-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px;
    }

    /* Card Styles */
    .publisher-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 24px;
        padding: 24px;
    }

    /* Button Styles */
    .btn-primary, .btn-success, .btn-warning, .btn-danger {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .btn-success {
        background: linear-gradient(135deg, #52c41a, #389e0d);
    }

    .btn-warning {
 Bowman       background: linear-gradient(135deg, #fa8c16, #d46b08);
    }

    .btn-danger {
        background: linear-gradient(135deg, #ff4d4f, #cf1322);
    }

    .btn-primary:hover, .btn-success:hover, .btn-warning:hover, .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .ant-btn {
        background: #f5f5f5;
        border: 1px solid #d9d9d9;
        color: #333;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s ease;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Table Styles */
    .publisher-table {
        width: 100%;
        border-collapse: collapse;
    }

    .publisher-table th, .publisher-table td {
        padding: 16px;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
    }

    .publisher-table th {
        background: #fafafa;
        font-weight: 600;
        color: #333;
    }

    .publisher-table tr:hover {
        background: #f9f9f9;
    }

    /* Modal Styles */
    .modal-header {
        padding: 24px 24px 0 24px;
        border-bottom: 1px solid #f0f0f0;
        margin-bottom: 24px;
    }

    .ant-modal-body {
        padding: 0 24px 24px 24px;
        max-height: 60vh;
        overflow-y: auto;
    }

    .ant-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #f0f0f0;
        text-align: right;
        background: #fafafa;
    }
</style>
@endpush
@endsection

