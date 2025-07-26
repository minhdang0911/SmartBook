@extends('layouts.app')

@section('title', 'Quản lý Người dùng')

@push('styles')
    <style>
        /* Layout chuẩn SmartBook */
        .container-fluid {
            max-width: 1200px;
            padding: 24px;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        .search-form input.form-control {
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.95rem;
            flex: 1;
        }

        .search-form button.btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
        }

        .search-form button.btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .table {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
        }

        .table thead {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        .table th,
        .table td {
            padding: 12px;
            vertical-align: middle;
            font-size: 0.9rem;
            color: #333;
        }

        .table tr:hover {
            background: #f0f4ff;
        }

        .badge {
            font-size: 0.85rem;
            padding: 6px 10px;
            border-radius: 0.5rem;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 6px;
            font-weight: 500;
            line-height: 1.2;
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
            border: none;
        }

        .btn-warning:hover {
            background: #ffca2c;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #6c757d;
            color: #fff;
            border: none;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-success {
            background: #28a745;
            color: white;
            border: none;
        }

        .btn-success:hover {
            background: #218838;
        }

        .rounded-circle {
            border: 2px solid #ccc;
            width: 40px;
            height: 40px;
            object-fit: cover;
        }

        .pagination {
            margin-top: 20px;
            font-size: 0.9rem;
        }

        /* Card Mobile View */
        .user-card {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 16px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .user-card h6 {
            margin-bottom: 6px;
            font-weight: bold;
        }

        .user-card img {
            border: 2px solid #ccc;
            width: 48px;
            height: 48px;
            object-fit: cover;
        }

        .user-card .badge {
            font-size: 0.75rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.5rem;
                flex-direction: column;
            }

            .search-form input.form-control {
                font-size: 0.9rem;
                padding: 8px;
            }

            .search-form button.btn {
                font-size: 0.9rem;
                padding: 8px 16px;
            }

            .table th,
            .table td {
                font-size: 0.85rem;
                padding: 10px;
            }

            .btn-sm {
                padding: 5px 10px;
                font-size: 0.8rem;
            }

            .badge {
                font-size: 0.8rem;
                padding: 5px 8px;
            }

            .rounded-circle {
                width: 32px;
                height: 32px;
            }
        }

        @media (max-width: 576px) {
            .container-fluid {
                padding: 16px;
            }

            .page-header {
                padding: 16px;
            }

            .page-header h1 {
                font-size: 1.3rem;
                flex-direction: column;
                text-align: center;
            }

            .search-form {
                flex-direction: column;
                gap: 8px;
            }

            .table th,
            .table td {
                font-size: 0.78rem;
                padding: 8px;
            }

            .btn-sm {
                padding: 4px 8px;
                font-size: 0.75rem;
            }

            .pagination {
                font-size: 0.85rem;
            }

            .rounded-circle {
                width: 28px;
                height: 28px;
            }

            .desktop-table {
                display: none;
            }
        }

        @media (max-width: 390px) {
            .page-header h1 {
                font-size: 1.2rem;
            }

            .search-form input.form-control {
                font-size: 0.85rem;
                padding: 6px;
            }

            .search-form button.btn {
                font-size: 0.85rem;
                padding: 6px 12px;
            }

            .table th,
            .table td {
                font-size: 0.75rem;
                padding: 6px;
            }

            .btn-sm {
                padding: 3px 6px;
                font-size: 0.7rem;
            }

            .badge {
                font-size: 0.75rem;
                padding: 4px 6px;
            }

            .rounded-circle {
                width: 24px;
                height: 24px;
            }

            .action-buttons {
                gap: 4px;
            }
        }

        @media (min-width: 577px) {
            .mobile-cards {
                display: none;
            }
        }
    </style>
@endpush


@section('content')
    <div class="container-fluid mt-4">
        <div class="page-header bg-gradient-primary text-white p-4 rounded shadow mb-4">
            <h1 class="mb-0"><i class="bi bi-people"></i> Danh sách người dùng</h1>
        </div>

        @include('components.alert')

        <form method="GET" action="{{ route('admin.users.index') }}" class="search-form mb-3 d-flex gap-2 flex-wrap">
            <input type="text" name="search" class="form-control" placeholder="🔍 Tìm theo tên hoặc email..."
                value="{{ $search }}">
            <button type="submit" class="btn btn-primary">Tìm</button>
        </form>

        {{-- Bảng desktop --}}
        <div class="table-responsive desktop-table">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>STT</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Xác thực</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $index => $user)
                        <tr class="{{ $user->deleted_at ? 'table-warning' : '' }}">
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'secondary' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                @if ($user->deleted_at)
                                    <span class="badge bg-dark">Đã khóa</span>
                                @else
                                    <span class="badge bg-success">Hoạt động</span>
                                @endif
                            </td>
                            <td>
                                @if ($user->email_verified_at)
                                    <span class="badge bg-primary">Đã xác thực</span>
                                @else
                                    <span class="badge bg-warning text-dark">Chưa xác thực</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @if (!$user->deleted_at)
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if ($user->role !== 'admin')
                                            <form action="{{ route('admin.users.lock', $user) }}" method="POST"
                                                class="d-inline">
                                                @csrf @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-secondary">
                                                    <i class="bi bi-lock"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <form action="{{ route('admin.users.unlock', $user->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-unlock"></i>
                                            </button>
                                        </form>
                                    @endif
                                    {{-- Nút xoá (vĩnh viễn) nếu không phải admin --}}
                                    @if ($user->role !== 'admin')
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                            onsubmit="return confirm('Xóa vĩnh viễn tài khoản này? Dữ liệu sẽ không thể khôi phục!')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">😕 Không tìm thấy người dùng nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Card mobile --}}
        <div class="mobile-cards">
            @forelse ($users as $user)
                <div class="user-card mb-3">
                    <div class="d-flex gap-3 align-items-center mb-2">
                        <div>
                            <h6 class="mb-0">{{ $user->name }}</h6>
                            <small class="text-muted">{{ $user->email }}</small>
                        </div>
                    </div>
                    <div class="small">
                        📱 <strong>SĐT:</strong> {{ $user->phone ?? '—' }}<br>
                        🛡️ <strong>Vai trò:</strong>
                        <span
                            class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'secondary' }}">{{ ucfirst($user->role) }}</span><br>
                        🔒 <strong>Trạng thái:</strong>
                        @if ($user->deleted_at)
                            <span class="badge bg-dark">Đã khóa</span>
                        @else
                            <span class="badge bg-success">Hoạt động</span>
                        @endif
                        <br>
                        ✅ <strong>Xác thực:</strong>
                        @if ($user->email_verified_at)
                            <span class="badge bg-primary">Đã xác thực</span>
                        @else
                            <span class="badge bg-warning text-dark">Chưa xác thực</span>
                        @endif
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        @if (!$user->deleted_at)
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if ($user->role !== 'admin')
                                <form action="{{ route('admin.users.lock', $user) }}" method="POST" class="d-inline">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-secondary">
                                        <i class="bi bi-lock"></i>
                                    </button>
                                </form>
                            @endif
                        @else
                            <form action="{{ route('admin.users.unlock', $user->id) }}" method="POST" class="d-inline">
                                @csrf @method('PUT')
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-unlock"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-muted text-center py-4">😕 Không tìm thấy người dùng nào.</div>
            @endforelse
        </div>

        <div class="pagination justify-content-center mt-4">
            {{ $users->appends(['search' => $search])->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
