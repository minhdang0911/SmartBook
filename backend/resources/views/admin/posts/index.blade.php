@extends('layouts.app')

@section('title', 'Danh sách Bài viết')

@push('styles')
    <style>
        .container {
            max-width: 1200px;
            padding: 24px;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 32px 24px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1), transparent);
            opacity: 0.3;
        }

        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
        }

        .search-form {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .search-form .form-control,
        .search-form .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 0.95rem;
            flex: 1;
            min-width: 200px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .search-form .form-control:focus,
        .search-form .form-select:focus {
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
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .search-form .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .search-form .btn-success {
            background: linear-gradient(135deg, #28a745, #34c759);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .search-form .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .table {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table thead {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        .table th,
        .table td {
            padding: 16px;
            font-size: 0.95rem;
            vertical-align: middle;
            color: #333;
        }

        .table tr {
            transition: background 0.2s ease;
        }

        .table tr:hover {
            background: #f0f4ff;
        }

        .btn-sm {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-warning {
            background: #ffc107;
            border: none;
            color: #333;
        }

        .btn-warning:hover {
            background: #ffca2c;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
        }

        .btn-danger {
            background: #dc3545;
            border: none;
            color: white;
        }

        .btn-danger:hover {
            background: #e4606d;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        .empty-state {
            padding: 32px;
            text-align: center;
            color: #666;
            font-size: 1rem;
        }

        .empty-state i {
            font-size: 2rem;
            color: #999;
        }

        .pagination {
            justify-content: center;
            margin-top: 24px;
        }

        .pagination .page-link {
            border-radius: 6px;
            margin: 0 4px;
            color: #667eea;
            font-weight: 500;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .pagination .page-link:hover {
            background: #f0f4ff;
            color: #764ba2;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-color: #667eea;
            color: white;
        }

        .badge {
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 6px;
            transition: transform 0.2s ease;
        }

        .badge:hover {
            transform: scale(1.05);
        }

        .badge.bg-warning {
            background: #ffc107;
            color: #333;
        }

        .badge.bg-success {
            background: #28a745;
            color: white;
        }

        .badge.bg-secondary {
            background: #6c757d;
            color: white;
        }

        .thumbnail-img {
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
            transition: transform 0.2s ease;
        }

        .thumbnail-img:hover {
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }

            .table th,
            .table td {
                padding: 12px;
                font-size: 0.85rem;
            }

            .btn-sm {
                padding: 5px 10px;
                font-size: 0.8rem;
            }

            .thumbnail-img {
                height: 50px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="page-header">
            <h1><i class="bi bi-journal-text"></i> Danh sách Bài viết</h1>
        </div>

        @include('components.alert')

        <form method="GET" action="{{ route('admin.posts.index') }}" class="search-form">
            <input type="text" name="keyword" class="form-control" placeholder="🔍 Tìm theo tiêu đề..." value="{{ request('keyword') }}">
            <select name="topic_id" class="form-select">
                <option value="">-- Tất cả chủ đề --</option>
                @foreach ($topics as $topic)
                    <option value="{{ $topic->id }}" {{ request('topic_id') == $topic->id ? 'selected' : '' }}>
                        {{ $topic->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="form-select">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Xuất bản</option>
            </select>
            <button type="submit" class="btn btn-primary">Tìm</button>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-success ms-auto">
                ➕ Thêm mới
            </a>
        </form>

        <div class="table-responsive">
            <x-admin.table :headers="['STT', 'Ảnh', 'Tiêu đề', 'Ghim', 'Chủ đề', 'Trạng thái', 'Ngày tạo', 'Hành động']">
                @forelse($posts as $index => $post)
                    <tr>
                        <td>{{ $posts->firstItem() + $index }}</td>
                        <td>
                            @if ($post->thumbnail)
                                <img src="{{ $post->thumbnail }}" alt="thumb" class="thumbnail-img shadow-sm">
                            @else
                                <span class="text-muted">Không có</span>
                            @endif
                        </td>
                        <td class="text-start">
                            <strong>{{ $post->title }}</strong><br>
                            <small class="text-muted">{{ $post->slug }}</small>
                        </td>
                        <td>
                            @if ($post->is_pinned)
                                <span class="badge bg-warning"><i class="bi bi-pin-angle-fill me-1"></i>Ghim</span>
                            @else
                                <span class="text-muted"><i class="bi bi-dash-circle"></i></span>
                            @endif
                        </td>
                        <td>
                            @forelse($post->topics as $topic)
                                <span class="badge bg-primary me-1">{{ $topic->name }}</span>
                            @empty
                                <span class="text-muted">-</span>
                            @endforelse
                        </td>
                        <td>
                            <span class="badge {{ $post->status === 'published' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $post->status === 'published' ? 'Xuất bản' : 'Nháp' }}
                            </span>
                        </td>
                        <td>
                            <div>{{ $post->created_at->format('d/m/Y') }}</div>
                            <div class="small text-muted">
                                👁️ {{ $post->views ?? 0 }} | ❤️ {{ $post->likes ?? 0 }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i> Sửa
                                </a>
                                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST"
                                      onsubmit="return confirm('Bạn chắc chắn muốn xóa?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="bi bi-folder-x"></i><br>
                            😕 Không tìm thấy bài viết nào
                            @if (request('keyword'))
                                với từ khóa <strong>"{{ request('keyword') }}"</strong>.
                            @endif
                            <p class="text-muted">Hãy thử lại với từ khóa khác.</p>
                        </td>
                    </tr>
                @endforelse
            </x-admin.table>
        </div>

        <div class="pagination">
            {{ $posts->appends(['keyword' => request('keyword'), 'topic_id' => request('topic_id'), 'status' => request('status')])->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
