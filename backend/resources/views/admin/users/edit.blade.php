@extends('layouts.app')

@section('title', 'Chỉnh sửa người dùng')

@push('styles')
    <style>
        .container {
            max-width: 800px;
            padding: 24px;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
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
            gap: 10px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
        }

        .form-label {
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.95rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn {
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary:hover {
            opacity: 0.9;
        }

        @media (max-width: 576px) {
            .page-header h1 {
                font-size: 1.4rem;
                flex-direction: column;
                gap: 4px;
            }

            .btn {
                font-size: 0.875rem;
                padding: 8px 16px;
            }

            .form-control,
            .form-select {
                font-size: 0.9rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container mt-4">
        <div class="page-header">
            <h1><i class="bi bi-person-lines-fill"></i> Chỉnh sửa người dùng</h1>
        </div>

        @include('components.alert')

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="card">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">👤 Họ tên <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">📧 Email</label>
                <input type="email" class="form-control" value="{{ $user->email }}" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">📞 Số điện thoại</label>
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                    value="{{ old('phone', $user->phone) }}">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">🛡️ Vai trò</label>
                <select name="role" class="form-select @error('role') is-invalid @enderror">
                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Người dùng</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Quản trị viên
                    </option>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    ⬅️ Quay lại
                </a>
                <button type="submit" class="btn btn-primary">
                    💾 Cập nhật
                </button>
            </div>
        </form>
    </div>
@endsection
