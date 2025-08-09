@extends('layouts.app')

@section('title', 'Danh sách Tác giả')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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

        .dark .modal-content {
            background: #1f2937;
        }

        .modal-overlay.active .modal-content {
            transform: scale(1);
        }

        /* CSS cho bảng */
        .table-fixed th, .table-fixed td {
            max-width: 350px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table-fixed th:nth-child(1), .table-fixed td:nth-child(1) { width: 10%; } /* STT */
        .table-fixed th:nth-child(2), .table-fixed td:nth-child(2) { width: 60%; } /* Tên Tác giả */
        .table-fixed th:nth-child(3), .table-fixed td:nth-child(3) { width: 30%; } /* Hành động */
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-white dark:bg-gray-900 transition-all duration-300">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Quản lý Tác giả</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Quản lý danh sách tác giả trong hệ thống</p>
                    </div>

                    <!-- Dark Mode Toggle -->
                    <button onclick="toggleDarkMode()" class="p-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                        <svg class="w-5 h-5 text-yellow-500 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @include('components.alert')

            <!-- Search & Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6 animate-fade-in">
                <form method="GET" action="{{ route('admin.authors.index') }}" class="flex flex-col gap-4">
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
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent"
                                placeholder="Tìm kiếm tác giả..."
                                value="{{ request('search') }}"
                            >
                        </div>
                    </div>

                    <div class="flex gap-3 justify-end sm:justify-start">
                        <button type="submit" class="bg-black dark:bg-white text-white dark:text-black px-4 py-2.5 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Tìm kiếm
                        </button>

                        <button type="button" onclick="openModal('addAuthorModal')" class="bg-black dark:bg-white text-white dark:text-black px-4 py-2.5 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Thêm mới
                        </button>
                    </div>
                </form>
            </div>

            <!-- Desktop Table -->
            <div class="hidden lg:block bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden animate-slide-up max-w-full">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-fixed">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-4 text-left text-lg font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">STT</th>
                                <th class="px-6 py-4 text-left text-lg font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tên Tác giả</th>
                                <th class="px-6 py-4 text-right text-lg font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($authors as $index => $author)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors min-h-[80px]">
                                    <td class="px-6 py-4 whitespace-nowrap text-lg text-gray-900 dark:text-white font-medium">
                                        {{ $authors->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap truncate">
                                        <div class="flex items-center">
                                            <div class="h-12 w-12 flex-shrink-0">
                                                <div class="h-12 w-12 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                                    <svg class="h-6 w-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4 truncate">
                                                <div class="text-lg font-medium text-gray-900 dark:text-white">{{ $author->name }}</div>
                                                <div class="text-base text-gray-500 dark:text-gray-400">Tác giả</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-lg font-medium">
                                        <div class="flex justify-end gap-3 flex-wrap">
                                            <button onclick="openModal('editAuthorModal{{ $author->id }}')" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-2 rounded-xl text-base hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                                Sửa
                                            </button>
                                            <button onclick="confirmDelete('{{ $author->name }}', '{{ route('admin.authors.destroy', $author) }}')" class="bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 px-3 py-2 rounded-xl text-base hover:bg-red-200 dark:hover:bg-red-800 transition-colors">
                                                Xóa
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Không có tác giả</h3>
                                            <p class="text-gray-500 dark:text-gray-400 text-sm">
                                                @if (request('search'))
                                                    Không tìm thấy kết quả cho "{{ request('search') }}"
                                                @else
                                                    Chưa có tác giả nào trong hệ thống
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
                @forelse ($authors as $index => $author)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 animate-slide-up">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="h-12 w-12 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-medium text-gray-900 dark:text-white truncate">{{ $author->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tác giả</p>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 flex-wrap">
                            <button onclick="openModal('editAuthorModal{{ $author->id }}')" class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-2 rounded-lg text-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                Sửa
                            </button>
                            <button onclick="confirmDelete('{{ $author->name }}', '{{ route('admin.authors.destroy', $author) }}')" class="flex-1 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 px-3 py-2 rounded-lg text-sm hover:bg-red-200 dark:hover:bg-red-800 transition-colors">
                                Xóa
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Không có tác giả</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">
                            @if (request('search'))
                                Không tìm thấy kết quả cho "{{ request('search') }}"
                            @else
                                Chưa có tác giả nào trong hệ thống
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($authors->hasPages())
                <div class="mt-6 flex justify-center">
                    {{ $authors->appends(['search' => request('search')])->links() }}
                </div>
            @endif
        </div>

        <!-- Add Modal -->
        <div id="addAuthorModal" class="modal-overlay">
            <div class="modal-content">
                <form id="addAuthorForm" action="{{ route('admin.authors.store') }}" method="POST">
                    @csrf
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Thêm Tác giả Mới</h3>
                            <button type="button" onclick="closeModal('addAuthorModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tên tác giả</label>
                            <input type="text" name="name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent" placeholder="Nhập tên tác giả..." required>
                        </div>
                    </div>
                    <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <button type="button" onclick="closeModal('addAuthorModal')" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Hủy
                        </button>
                        <button type="submit" class="px-4 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors">
                            Thêm mới
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Modals -->
        @foreach ($authors as $author)
            <div id="editAuthorModal{{ $author->id }}" class="modal-overlay">
                <div class="modal-content">
                    <form action="{{ route('admin.authors.update', $author) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Chỉnh sửa Tác giả</h3>
                                <button type="button" onclick="closeModal('editAuthorModal{{ $author->id }}')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tên tác giả</label>
                                <input type="text" name="name" value="{{ old('name', $author->name) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent" required>
                            </div>
                        </div>
                        <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                            <button type="button" onclick="closeModal('editAuthorModal{{ $author->id }}')" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                Hủy
                            </button>
                            <button type="submit" class="px-4 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors">
                                Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

        <!-- Custom Confirm Dialog -->
        <div id="confirmModal" class="modal-overlay">
            <div class="modal-content max-w-md">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Xác nhận xóa</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                            Bạn có chắc chắn muốn xóa tác giả <span id="confirmAuthorName" class="font-semibold"></span>? Hành động này không thể hoàn tác.
                        </p>
                        <div class="flex gap-3 justify-center">
                            <button type="button" onclick="closeModal('confirmModal')" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                Hủy
                            </button>
                            <button type="button" id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                Xóa
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dark mode toggle
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
        }

        // Initialize dark mode
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }

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

        // Confirm delete
        function confirmDelete(authorName, deleteUrl) {
            document.getElementById('confirmAuthorName').textContent = authorName;
            document.getElementById('confirmDeleteBtn').onclick = function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = deleteUrl;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

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
