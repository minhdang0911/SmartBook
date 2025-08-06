@extends('layouts.app')

@section('title', 'Danh sách Chủ đề')

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

        .search-form .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 0.95rem;
            flex: 1;
            min-width: 200px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
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

        .modal-content {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-bottom: none;
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.25rem;
        }

        .modal-body .form-label {
            font-weight: 600;
            color: #333;
        }

        .modal-body .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 0.95rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .modal-body .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .modal-footer {
            border-top: none;
        }

        .modal-footer .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            transition: transform 0.2s ease;
        }

        .modal-footer .btn-secondary:hover {
            transform: translateY(-2px);
        }

        .modal-footer .btn-primary,
        .modal-footer .btn-success {
            border-radius: 8px;
            padding: 10px 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .modal-footer .btn-primary:hover,
        .modal-footer .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="page-header">
            <h1><i class="bi bi-grid-fill"></i> Danh sách Chủ đề</h1>
        </div>

        @include('components.alert')

        <form method="GET" action="{{ route('admin.topics.index') }}" class="search-form">
            <input type="text" name="keyword" class="form-control" placeholder="🔍 Tìm chủ đề..." value="{{ request('keyword') }}">
            <button type="submit" class="btn btn-primary">Tìm</button>
            <x-admin.button.modal-button target="addTopicModal" text="➕ Thêm mới" class="btn-success ms-auto" />
        </form>

        <div class="table-responsive">
            <x-admin.table :headers="['STT', 'Tên chủ đề', 'Slug', 'Ngày tạo', 'Hành động']">
                @forelse ($topics as $index => $topic)
                    <tr>
                        <td>{{ $topics->firstItem() + $index }}</td>
                        <td>{{ $topic->name }}</td>
                        <td>{{ $topic->slug }}</td>
                        <td>{{ $topic->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <x-admin.button.modal-button
                                    target="editTopicModal{{ $topic->id }}"
                                    text="Sửa"
                                    class="btn-warning btn-sm" />
                                <form action="{{ route('admin.topics.destroy', $topic) }}" method="POST"
                                      onsubmit="return confirm('Bạn chắc chắn muốn xóa?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="bi bi-folder-x"></i><br>
                            😕 Không tìm thấy chủ đề nào
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
            {{ $topics->appends(['keyword' => request('keyword')])->links('pagination::bootstrap-5') }}
        </div>

        @foreach ($topics as $topic)
            <div class="modal fade" id="editTopicModal{{ $topic->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="{{ route('admin.topics.update', $topic) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_form" value="edit">
                            <input type="hidden" name="_edit_id" value="{{ $topic->id }}">
                            <div class="modal-header">
                                <h5 class="modal-title">Sửa Chủ đề</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Tên chủ đề</label>
                                    <input type="text" name="name" class="form-control"
                                           value="{{ old('_form') === 'edit' && old('_edit_id') == $topic->id ? old('name') : $topic->name }}">
                                    @if(old('_form') === 'edit' && old('_edit_id') == $topic->id)
                                        @error('name')
                                            <div class="text-danger mt-1 small">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" class="form-control"
                                           value="{{ old('_form') === 'edit' && old('_edit_id') == $topic->id ? old('slug') : $topic->slug }}">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-primary">Lưu</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="modal fade" id="addTopicModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('admin.topics.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_form" value="add">
                        <div class="modal-header">
                            <h5 class="modal-title">Thêm Chủ đề</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Tên chủ đề</label>
                                <input type="text" name="name" class="form-control"
                                       value="{{ old('_form') === 'add' ? old('name') : '' }}">
                                @if(old('_form') === 'add')
                                    @error('name')
                                        <div class="text-danger mt-1 small">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" class="form-control"
                                       value="{{ old('_form') === 'add' ? old('slug') : '' }}">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-success">Thêm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if(old('_form') === 'add')
                    new bootstrap.Modal(document.getElementById('addTopicModal')).show();
                @elseif(old('_form') === 'edit' && old('_edit_id'))
                    new bootstrap.Modal(document.getElementById('editTopicModal{{ old('_edit_id') }}')).show();
                @endif
            });
        </script>
    @endif

    <script>
        function slugify(text) {
            return text.toString().toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const addName = document.querySelector('#addTopicModal input[name="name"]');
            const addSlug = document.querySelector('#addTopicModal input[name="slug"]');
            if (addName && addSlug) {
                addName.addEventListener('input', () => addSlug.value = slugify(addName.value));
            }

            document.querySelectorAll('[id^="editTopicModal"]').forEach(modal => {
                const name = modal.querySelector('input[name="name"]');
                const slug = modal.querySelector('input[name="slug"]');
                if (name && slug) {
                    name.addEventListener('input', () => slug.value = slugify(name.value));
                }
            });
        });
    </script>
@endsection
