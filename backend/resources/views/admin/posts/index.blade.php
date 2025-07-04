@extends('layouts.app')

@section('title', 'Danh sách bài viết')

@section('content')
<div class="container" style="max-width: 1200px; padding: 24px">
    <div class="page-header mb-4 p-3 rounded text-white" style="background: linear-gradient(to right, #00c6ff, #0072ff);">
        <h4 class="mb-0 fw-bold">📚 Danh sách bài viết</h4>
    </div>

    {{-- FLASH --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-3 text-end">
        <a href="{{ route('admin.posts.create') }}" class="btn btn-success">+ Thêm bài viết</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Thumbnail</th>
                    <th>Tiêu đề</th>
                    <th>Chủ đề</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $index => $post)
                <tr>
                    <td>{{ $index + $posts->firstItem() }}</td>

                    <td>
                        @if($post->thumbnail)
                            <img src="{{ $post->thumbnail }}" alt="thumb" class="img-thumbnail" style="height: 80px;">
                        @else
                            <span class="text-muted">Không có</span>
                        @endif
                    </td>

                    <td class="text-start">
                        <strong>{{ $post->title }}</strong><br>
                        <small class="text-muted">{{ $post->slug }}</small>
                        @if($post->is_pinned)
                            <span class="badge bg-warning text-dark ms-1">Ghim</span>
                        @endif
                    </td>

                    <td>
                        @foreach($post->topics as $topic)
                            <span class="badge bg-primary">{{ $topic->name }}</span>
                        @endforeach
                    </td>

                    <td>
                        @if($post->status === 'published')
                            <span class="badge bg-success">Xuất bản</span>
                        @else
                            <span class="badge bg-secondary">Nháp</span>
                        @endif
                    </td>

                    <td>{{ $post->created_at->format('d/m/Y') }}</td>

                    <td>
                        <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-warning">Sửa</a>
                        <form action="#" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Xoá bài viết này?')">Xoá</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-muted text-center py-4">
                        <i class="bi bi-emoji-frown" style="font-size: 2rem;"></i><br>
                        Không có bài viết nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $posts->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
