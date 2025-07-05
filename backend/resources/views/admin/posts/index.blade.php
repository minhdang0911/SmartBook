@extends('layouts.app')

@section('title', 'Danh sách bài viết')

@section('content')
    <div class="container py-4" style="max-width: 1200px;">

        {{-- HEADER --}}
        <div class="mb-4 p-4 rounded-3 shadow-sm d-flex align-items-center justify-content-between"
            style="background: linear-gradient(135deg, #00c6ff, #0072ff); color: white;">
            <h4 class="fw-bold mb-0"><i class="bi bi-journal-text me-2"></i>Danh sách bài viết</h4>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-light fw-semibold">
                <i class="bi bi-plus-circle me-1"></i>Thêm mới
            </a>
        </div>

        {{-- FORM TÌM KIẾM --}}
        <form method="GET" class="row g-3 align-items-end mb-4">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Tìm theo tiêu đề</label>
                <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Chủ đề</label>
                <select name="topic_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    @foreach ($topics as $topic)
                        <option value="{{ $topic->id }}" {{ request('topic_id') == $topic->id ? 'selected' : '' }}>
                            {{ $topic->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Xuất bản</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Tìm kiếm</button>
            </div>
        </form>

        {{-- FLASH MESSAGE --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- TABLE --}}
        <div class="table-responsive rounded-3 shadow-sm">
            <table class="table align-middle text-center table-bordered mb-0">
                <thead class="table-light">
                    <tr class="align-middle">
                        <th style="width: 5%">STT</th>
                        <th style="width: 12%">Ảnh</th>
                        <th style="width: 25%">Tiêu đề</th>
                        <th style="width: 10%">Ghim</th>
                        <th style="width: 20%">Chủ đề</th>
                        <th style="width: 10%">Trạng thái</th>
                        <th style="width: 10%">Ngày tạo</th>
                        <th style="width: 15%">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $index => $post)
                        <tr>
                            <td>{{ $index + $posts->firstItem() }}</td>

                            {{-- ẢNH --}}
                            <td>
                                @if ($post->thumbnail)
                                    <img src="{{ $post->thumbnail }}" alt="thumb" class="rounded shadow-sm"
                                        style="height: 70px; object-fit: cover;">
                                @else
                                    <span class="text-muted fst-italic">Không có</span>
                                @endif
                            </td>

                            {{-- TIÊU ĐỀ --}}
                            <td class="text-start">
                                <strong>{{ $post->title }}</strong><br>
                                <small class="text-muted">{{ $post->slug }}</small>
                            </td>

                            {{-- GHIM --}}
                            <td>
                                @if ($post->is_pinned)
                                    <span class="badge bg-warning text-dark"><i
                                            class="bi bi-pin-angle-fill me-1"></i>Ghim</span>
                                @else
                                    <span class="text-muted"><i class="bi bi-dash-circle"></i></span>
                                @endif
                            </td>

                            {{-- CHỦ ĐỀ --}}
                            <td>
                                @forelse($post->topics as $topic)
                                    <span class="badge bg-primary me-1">{{ $topic->name }}</span>
                                @empty
                                    <span class="text-muted">-</span>
                                @endforelse
                            </td>

                            {{-- TRẠNG THÁI --}}
                            <td>
                                <span class="badge {{ $post->status === 'published' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $post->status === 'published' ? 'Xuất bản' : 'Nháp' }}
                                </span>
                            </td>

                            {{-- NGÀY + VIEW + LIKE --}}
                            <td>
                                <div>{{ $post->created_at->format('d/m/Y') }}</div>
                                <div class="small text-muted">
                                    👁️ {{ $post->views ?? 0 }} | ❤️ {{ $post->likes ?? 0 }}
                                </div>
                            </td>

                            {{-- ACTION --}}
                            <td>
                                <a href="{{ route('admin.posts.edit', $post) }}"
                                    class="btn btn-sm btn-outline-warning me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Xoá bài viết này?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-muted text-center py-5">
                                <i class="bi bi-folder-x" style="font-size: 2rem;"></i><br>
                                Không có bài viết nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $posts->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- CUSTOM STYLE --}}
    <style>
        .table td,
        .table th {
            vertical-align: middle;
            padding: 12px;
        }

        .btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
@endsection
