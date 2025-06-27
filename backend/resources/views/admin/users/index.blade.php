@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">👤 Danh sách người dùng</h1>

        @include('components.alert')

        <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3 d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="🔍 Tìm người dùng..."
                value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-primary">Tìm</button>
        </form>

        <x-admin.table :headers="['#', 'Tên', 'Email', 'SĐT', 'Vai trò', 'Trạng thái', 'Hành động']">
            @forelse ($users as $index => $user)
                <tr>
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
                        <form action="{{ route('admin.users.toggleStatus', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="btn btn-sm {{ $user->email_verified_at ? 'btn-success' : 'btn-secondary' }}">
                                <x-status-badge :status="$user->email_verified_at" />
                            </button>
                        </form>
                    </td>
                    <td>
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info">Xem</a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">Sửa</a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">😢 Không có người dùng nào.</td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-4 text-center">
            {{ $users->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
