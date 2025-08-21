@extends('layouts.app')

@section('title', 'Quản lý Người dùng')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-out',
                        'slide-up': 'slideUp 0.4s ease-out',
                        'scale-in': 'scaleIn 0.2s ease-out',
                        'shake': 'shake 0.5s ease-in-out'
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

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 400px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.7);
            transition: transform 0.3s ease;
        }

        .modal-overlay.active .modal-content {
            transform: scale(1);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .role-admin {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .role-user {
            background-color: #f3f4f6;
            color: #374151;
        }
        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-locked {
            background-color: #f3f4f6;
            color: #374151;
        }
        .verified-yes {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .verified-no {
            background-color: #fef3c7;
            color: #92400e;
        }

        /* Giới hạn chiều rộng cột */
        .table-fixed th, .table-fixed td {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table-fixed th:nth-child(1), .table-fixed td:nth-child(1) { width: 10%; } /* STT */
        .table-fixed th:nth-child(2), .table-fixed td:nth-child(2) { width: 20%; } /* Họ tên */
        .table-fixed th:nth-child(3), .table-fixed td:nth-child(3) { width: 20%; } /* Email */
        .table-fixed th:nth-child(4), .table-fixed td:nth-child(4) { width: 15%; } /* SĐT */
        .table-fixed th:nth-child(5), .table-fixed td:nth-child(5) { width: 15%; } /* Vai trò */
        .table-fixed th:nth-child(6), .table-fixed td:nth-child(6) { width: 10%; } /* Trạng thái */
        .table-fixed th:nth-child(7), .table-fixed td:nth-child(7) { width: 10%; } /* Xác thực */
        .table-fixed th:nth-child(8), .table-fixed td:nth-child(8) { width: 20%; } /* Hành động */

        /* Sửa lỗi phân trang */
        .pagination {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            justify-content: center;
        }

        .pagination a {
            min-width: 2.5rem;
            text-align: center;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border: 1px solid #d1d5db;
            color: #374151;
            background-color: #ffffff;
            transition: all 0.2s ease;
        }

        .pagination a:hover:not([aria-current="page"]):not(.disabled) {
            background-color: #f3f4f6;
        }

        .pagination a[aria-current="page"] {
            background-color: #000000 !important;
            color: #ffffff !important;
            border-color: #000000 !important;
            z-index: 10;
        }

        .pagination a.disabled, .pagination a[aria-disabled="true"] {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 640px) {
            .pagination a {
                font-size: 0.75rem;
                padding: 0.5rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-white transition-all duration-300">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Quản lý Người dùng</h1>
                        <p class="text-sm text-gray-600 mt-1">Quản lý danh sách người dùng trong hệ thống</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @include('components.alert')

            <!-- Search Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 animate-fade-in">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input
                                type="text"
                                name="search"
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-black focus:border-transparent"
                                placeholder="Tìm theo tên hoặc email..."
                                value="{{ $search }}"
                            >
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-black text-white px-4 py-2.5 rounded-lg font-medium hover:bg-gray-800 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>

            <!-- Desktop Table -->
            <div class="hidden lg:block bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden animate-slide-up max-w-full">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 table-fixed">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Họ tên</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SĐT</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vai trò</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Xác thực</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($users as $index => $user)
                                <tr class="hover:bg-gray-50 transition-colors {{ $user->deleted_at ? 'bg-yellow-50' : '' }}">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        {{ $users->firstItem() + $index }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap truncate">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-3 truncate">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 truncate">{{ $user->email }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 truncate">{{ $user->phone ?? '—' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="status-badge {{ $user->role === 'admin' ? 'role-admin' : 'role-user' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if ($user->deleted_at)
                                            <span class="status-badge status-locked">Đã khóa</span>
                                        @else
                                            <span class="status-badge status-active">Hoạt động</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if ($user->email_verified_at)
                                            <span class="status-badge verified-yes">Đã xác thực</span>
                                        @else
                                            <span class="status-badge verified-no">Chưa xác thực</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2 flex-wrap">
                                            @if (!$user->deleted_at)
                                                <a href="{{ route('admin.users.edit', $user) }}" class="bg-gray-100 text-gray-700 px-2 py-1 rounded-lg text-xs hover:bg-gray-200 transition-colors">
                                                    Sửa
                                                </a>
                                                @if ($user->role !== 'admin')
                                                    <button onclick="confirmAction('lock', '{{ $user->name }}', '{{ route('admin.users.lock', $user) }}')" class="bg-gray-100 text-gray-700 px-2 py-1 rounded-lg text-xs hover:bg-gray-200 transition-colors">
                                                        Khóa
                                                    </button>
                                                @endif
                                            @else
                                                <button onclick="confirmAction('unlock', '{{ $user->name }}', '{{ route('admin.users.unlock', $user->id) }}')" class="bg-green-100 text-green-700 px-2 py-1 rounded-lg text-xs hover:bg-green-200 transition-colors">
                                                    Mở khóa
                                                </button>
                                            @endif
                                            @if ($user->role !== 'admin')
                                                <button onclick="confirmAction('delete', '{{ $user->name }}', '{{ route('admin.users.destroy', $user) }}')" class="bg-red-100 text-red-700 px-2 py-1 rounded-lg text-xs hover:bg-red-200 transition-colors">
                                                    Xóa
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900 mb-1">Không có người dùng</h3>
                                            <p class="text-gray-500 text-sm">
                                                @if (request('search'))
                                                    Không tìm thấy kết quả cho "{{ request('search') }}"
                                                @else
                                                    Chưa có người dùng nào trong hệ thống
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Cards -->
            <div class="lg:hidden space-y-4">
                @forelse ($users as $user)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 {{ $user->deleted_at ? 'bg-yellow-50' : '' }}">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</h3>
                                <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                            </div>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">SĐT:</span>
                                <span class="text-gray-900 truncate">{{ $user->phone ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Vai trò:</span>
                                <span class="status-badge {{ $user->role === 'admin' ? 'role-admin' : 'role-user' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Trạng thái:</span>
                                @if ($user->deleted_at)
                                    <span class="status-badge status-locked">Đã khóa</span>
                                @else
                                    <span class="status-badge status-active">Hoạt động</span>
                                @endif
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Xác thực:</span>
                                @if ($user->email_verified_at)
                                    <span class="status-badge verified-yes">Đã xác thực</span>
                                @else
                                    <span class="status-badge verified-no">Chưa xác thực</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex gap-2 mt-4 pt-3 border-t border-gray-200 flex-wrap">
                            @if (!$user->deleted_at)
                                <a href="{{ route('admin.users.edit', $user) }}" class="flex-1 bg-gray-100 text-gray-700 px-2 py-1 rounded-lg text-xs text-center hover:bg-gray-200 transition-colors">
                                    Sửa
                                </a>
                                @if ($user->role !== 'admin')
                                    <button onclick="confirmAction('lock', '{{ $user->name }}', '{{ route('admin.users.lock', $user) }}')" class="flex-1 bg-gray-100 text-gray-700 px-2 py-1 rounded-lg text-xs hover:bg-gray-200 transition-colors">
                                        Khóa
                                    </button>
                                @endif
                            @else
                                <button onclick="confirmAction('unlock', '{{ $user->name }}', '{{ route('admin.users.unlock', $user->id) }}')" class="flex-1 bg-green-100 text-green-700 px-2 py-1 rounded-lg text-xs hover:bg-green-200 transition-colors">
                                    Mở khóa
                                </button>
                            @endif
                            @if ($user->role !== 'admin')
                                <button onclick="confirmAction('delete', '{{ $user->name }}', '{{ route('admin.users.destroy', $user) }}')" class="flex-1 bg-red-100 text-red-700 px-2 py-1 rounded-lg text-xs hover:bg-red-200 transition-colors">
                                    Xóa
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Không có người dùng</h3>
                        <p class="text-gray-500 text-sm">
                            @if (request('search'))
                                Không tìm thấy kết quả cho "{{ request('search') }}"
                            @else
                                Chưa có người dùng nào trong hệ thống
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="mt-6 flex justify-center pagination">
                    {{ $users->appends(['search' => request('search')])->links() }}
                </div>
            @endif
        </div>

        <!-- Custom Confirm Dialog -->
        <div id="confirmModal" class="modal-overlay">
            <div class="modal-content max-w-md">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full" id="confirmIcon">
                            <!-- Icon will be set by JavaScript -->
                        </div>
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900 mb-2" id="confirmTitle">Xác nhận</h3>
                        <p class="text-sm text-gray-500 mb-6" id="confirmMessage">
                            <!-- Message will be set by JavaScript -->
                        </p>
                        <div class="flex gap-3 justify-center">
                            <button type="button" onclick="closeModal('confirmModal')" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                Hủy
                            </button>
                            <button type="button" id="confirmActionBtn" class="px-4 py-2 rounded-lg transition-colors">
                                Xác nhận
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Confirm action
        function confirmAction(action, userName, actionUrl) {
            const icon = document.getElementById('confirmIcon');
            const title = document.getElementById('confirmTitle');
            const message = document.getElementById('confirmMessage');
            const actionBtn = document.getElementById('confirmActionBtn');

            // Set content based on action type
            if (action === 'lock') {
                icon.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100';
                icon.innerHTML = '<svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>';
                title.textContent = 'Xác nhận khóa tài khoản';
                message.innerHTML = `Bạn có chắc chắn muốn khóa tài khoản của <span class="font-semibold">${userName}</span>?`;
                actionBtn.className = 'px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors';
                actionBtn.textContent = 'Khóa';
            } else if (action === 'unlock') {
                icon.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100';
                icon.innerHTML = '<svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>';
                title.textContent = 'Xác nhận mở khóa tài khoản';
                message.innerHTML = `Bạn có chắc chắn muốn mở khóa tài khoản của <span class="font-semibold">${userName}</span>?`;
                actionBtn.className = 'px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors';
                actionBtn.textContent = 'Mở khóa';
            } else if (action === 'delete') {
                icon.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100';
                icon.innerHTML = '<svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>';
                title.textContent = 'Xác nhận xóa tài khoản';
                message.innerHTML = `Bạn có chắc chắn muốn xóa vĩnh viễn tài khoản của <span class="font-semibold">${userName}</span>? Hành động này không thể hoàn tác.`;
                actionBtn.className = 'px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors';
                actionBtn.textContent = 'Xóa';
            }

            actionBtn.onclick = function() {
                // Create and submit form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = actionUrl;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';

                if (action === 'delete') {
                    methodField.value = 'DELETE';
                } else {
                    methodField.value = 'PUT';
                }

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            };

            openModal('confirmModal');
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                e.target.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });

        // ESC key to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                    modal.classList.remove('active');
                    document.body.style.overflow = 'auto';
                });
            }
        });

        // Ensure table layout consistency on page load
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.querySelector('table');
            if (table) {
                table.classList.add('table-fixed');
            }
        });
    </script>
@endsection
