@extends('layouts.app')

@section('title', 'Chỉnh sửa người dùng')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-out',
                        'fade-out': 'fadeOut 0.5s ease-out'
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; visibility: hidden; }
        }
        .fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-white transition-all duration-300">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 rounded-lg shadow-sm p-6 mb-6 animate-fade-in">
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Chỉnh sửa người dùng
                </h1>
                <p class="text-sm text-gray-600 mt-1">Cập nhật thông tin người dùng trong hệ thống</p>
            </div>

            <!-- Alert -->
            @include('components.alert')

            <!-- Form -->
            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 animate-fade-in">
                @csrf
                @method('PUT')

                <!-- Họ tên -->
                <div class="mb-4">
                    <label class="block text-base font-medium text-gray-700 mb-2">
                        👤 Họ tên <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="name"
                        class="block w-full py-3 px-4 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-black focus:border-transparent transition"
                        value="{{ old('name', $user->name) }}" placeholder="Nhập họ tên...">
                    @error('name')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label class="block text-base font-medium text-gray-700 mb-2">📧 Email</label>
                    <input type="email"
                        class="block w-full py-3 px-4 border border-gray-300 rounded-lg bg-gray-100 text-gray-900 cursor-not-allowed"
                        value="{{ $user->email }}" readonly>
                </div>

                <!-- Số điện thoại -->
                <div class="mb-4">
                    <label class="block text-base font-medium text-gray-700 mb-2">📞 Số điện thoại</label>
                    <input type="text" name="phone"
                        class="block w-full py-3 px-4 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-black focus:border-transparent transition"
                        value="{{ old('phone', $user->phone) }}" placeholder="Nhập số điện thoại...">
                    @error('phone')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Vai trò -->
                <div class="mb-4">
                    <label class="block text-base font-medium text-gray-700 mb-2">🛡️ Vai trò</label>
                    <select name="role"
                        class="block w-full py-3 px-4 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-black focus:border-transparent transition">
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Người dùng</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Quản trị viên</option>
                    </select>
                    @error('role')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nút -->
                <div class="flex gap-3 justify-end">
                    <a href="{{ route('admin.users.index') }}"
                        class="w-full sm:w-auto bg-gray-100 text-gray-700 px-4 py-2.5 rounded-lg font-medium hover:bg-gray-200 transition-colors text-center flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Quay lại
                    </a>
                    <button type="submit"
                        class="w-full sm:w-auto bg-black text-white px-4 py-2.5 rounded-lg font-medium hover:bg-gray-800 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Auto scroll alert lỗi và tự ẩn sau 5s
        document.addEventListener('DOMContentLoaded', () => {
            const errorAlert = document.getElementById('formErrorAlert');
            if (errorAlert) {
                errorAlert.scrollIntoView({ behavior: 'smooth' });
                setTimeout(() => {
                    errorAlert.classList.add('fade-out');
                }, 5000);
            }
        });
    </script>
@endpush
