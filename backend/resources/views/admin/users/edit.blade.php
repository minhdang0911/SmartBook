@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">👤 Cập nhật thông tin người dùng</h1>

        @include('components.alert')

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="card p-4 shadow-sm">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label class="form-label">Họ tên</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email (không thể thay đổi)</label>
                <input type="email" class="form-control" value="{{ $user->email }}" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Địa chỉ</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Vai trò</label>
                <select name="role" class="form-select">
                    <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Người dùng</option>
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Quản trị viên</option>
                </select>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">⬅️ Quay lại</a>
                <button type="submit" class="btn btn-primary">💾 Cập nhật</button>
            </div>
        </form>
    </div>
@endsection
