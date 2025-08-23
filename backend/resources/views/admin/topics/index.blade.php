@extends('layouts.app')

@section('title', 'Danh sách Chủ đề')

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
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.95);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
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
            max-width: 500px;
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
        .table-fixed th,
        .table-fixed td {
            max-width: 300px;
            /* Tăng từ 250px */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table-fixed th:nth-child(1),
        .table-fixed td:nth-child(1) {
            width: 10%;
        }

        /* STT */
        .table-fixed th:nth-child(2),
        .table-fixed td:nth-child(2) {
            width: 30%;
        }

        /* Tên Chủ đề */
        .table-fixed th:nth-child(3),
        .table-fixed td:nth-child(3) {
            width: 30%;
        }

        /* Slug */
        .table-fixed th:nth-child(4),
        .table-fixed td:nth-child(4) {
            width: 20%;
        }

        /* Ngày tạo */
        .table-fixed th:nth-child(5),
        .table-fixed td:nth-child(5) {
            width: 20%;
        }

        /* Hành động */
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-white dark:bg-gray-900 transition-all duration-300">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Quản lý Chủ đề</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Quản lý chủ đề sách trong hệ thống</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @include('components.alert')

            <!-- Search & Actions -->
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6 animate-fade-in">
                <form method="GET" action="{{ route('admin.topics.index') }}" class="flex flex-col gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="keyword"
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent"
                                placeholder="Tìm kiếm chủ đề..." value="{{ request('keyword') }}">
                        </div>
                    </div>
                    <div class="flex gap-3 justify-end sm:justify-start">
                        <button type="submit"
                            class="bg-black dark:bg-white text-white dark:text-black px-4 py-2.5 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Tìm kiếm
                        </button>
                        <button type="button" onclick="openModal('addTopicModal')"
                            class="bg-black dark:bg-white text-white dark:text-black px-4 py-2.5 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Thêm mới
                        </button>
                    </div>
                </form>
            </div>

            <!-- Desktop Table -->
            <div
                class="hidden lg:block bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden animate-slide-up max-w-full">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-fixed">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-base font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    STT</th>
                                <th
                                    class="px-6 py-4 text-left text-base font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Tên Chủ đề</th>
                                <th
                                    class="px-6 py-4 text-left text-base font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Slug</th>
                                <th
                                    class="px-6 py-4 text-left text-base font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Ngày tạo</th>
                                <th
                                    class="px-6 py-4 text-right text-base font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($topics as $index => $topic)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors min-h-[64px]">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-base text-gray-900 dark:text-white font-medium">
                                        {{ $topics->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                                    <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                                        </path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4 truncate">
                                                <div class="text-base font-medium text-gray-900 dark:text-white">
                                                    {{ $topic->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">Chủ đề</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                            {{ $topic->slug }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-base text-gray-900 dark:text-white">
                                        {{ $topic->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-base font-medium">
                                        <div class="flex justify-end gap-2 flex-wrap">
                                            <button onclick="openModal('editTopicModal{{ $topic->id }}')"
                                                class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-2 rounded-xl text-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                                Sửa
                                            </button>
                                            <button
                                                onclick="confirmDelete('{{ $topic->name }}', '{{ route('admin.topics.destroy', $topic) }}')"
                                                class="bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 px-3 py-2 rounded-xl text-sm hover:bg-red-200 dark:hover:bg-red-800 transition-colors">
                                                Xóa
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                                </path>
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Không có chủ
                                                đề</h3>
                                            <p class="text-gray-500 dark:text-gray-400 text-sm">
                                                @if (request('keyword'))
                                                    Không tìm thấy kết quả cho "{{ request('keyword') }}"
                                                @else
                                                    Chưa có chủ đề nào trong hệ thống
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
            <div class="lg:hidden space-y-3">
                @forelse ($topics as $index => $topic)
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 animate-slide-up">
                        <div class="flex items-center space-x-3 mb-3">
                            <div
                                class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
                                <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                    </path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $topic->name }}
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Chủ đề</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                    {{ $topic->slug }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ $topic->created_at->format('d/m/Y') }}
                            </div>
                        </div>
                        <div class="flex gap-2 mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 flex-wrap">
                            <button onclick="openModal('editTopicModal{{ $topic->id }}')"
                                class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-2 rounded-xl text-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                Sửa
                            </button>
                            <button
                                onclick="confirmDelete('{{ $topic->name }}', '{{ route('admin.topics.destroy', $topic) }}')"
                                class="flex-1 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 px-3 py-2 rounded-xl text-sm hover:bg-red-200 dark:hover:bg-red-800 transition-colors">
                                Xóa
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                            </path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Không có chủ đề</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">
                            @if (request('keyword'))
                                Không tìm thấy kết quả cho "{{ request('keyword') }}"
                            @else
                                Chưa có chủ đề nào trong hệ thống
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($topics->hasPages())
                <div class="mt-6 flex justify-center">
                    {{ $topics->appends(['keyword' => request('keyword')])->links() }}
                </div>
            @endif
        </div>

        <!-- Add Modal -->
        <div id="addTopicModal" class="modal-overlay">
            <div class="modal-content">
                <form id="addTopicForm" action="{{ route('admin.topics.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_form" value="add">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Thêm Chủ đề Mới</h3>
                            <button type="button" onclick="closeModal('addTopicModal')"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tên chủ
                                đề</label>
                            <input type="text" name="name" id="addTopicName"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent"
                                placeholder="Nhập tên chủ đề..." value="{{ old('_form') === 'add' ? old('name') : '' }}"
                                required>

                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Slug</label>
                            <input type="text" name="slug" id="addTopicSlug"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent"
                                placeholder="Slug sẽ được tạo tự động..."
                                value="{{ old('_form') === 'add' ? old('slug') : '' }}">
                        </div>
                    </div>
                    <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <button type="button" onclick="closeModal('addTopicModal')"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Hủy
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors">
                            Thêm mới
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Modals -->
        @foreach ($topics as $topic)
            <div id="editTopicModal{{ $topic->id }}" class="modal-overlay">
                <div class="modal-content">
                    <form action="{{ route('admin.topics.update', $topic) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="edit">
                        <input type="hidden" name="_edit_id" value="{{ $topic->id }}">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Chỉnh sửa Chủ đề</h3>
                                <button type="button" onclick="closeModal('editTopicModal{{ $topic->id }}')"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tên chủ
                                    đề</label>
                                <input type="text" name="name"
                                    class="editTopicName w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent"
                                    value="{{ old('_form') === 'edit' && old('_edit_id') == $topic->id ? old('name') : $topic->name }}"
                                    required>

                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Slug</label>
                                <input type="text" name="slug"
                                    class="editTopicSlug w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent"
                                    value="{{ old('_form') === 'edit' && old('_edit_id') == $topic->id ? old('slug') : $topic->slug }}">
                            </div>
                        </div>
                        <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                            <button type="button" onclick="closeModal('editTopicModal{{ $topic->id }}')"
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                Hủy
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors">
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
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Xác nhận xóa</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                            Bạn có chắc chắn muốn xóa chủ đề <span id="confirmTopicName" class="font-semibold"></span>?
                            Hành động này không thể hoàn tác.
                        </p>
                        <div class="flex gap-3 justify-center">
                            <button type="button" onclick="closeModal('confirmModal')"
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                Hủy
                            </button>
                            <button type="button" id="confirmDeleteBtn"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                Xóa
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

        // Đóng tất cả modal
        function closeAllModals() {
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.classList.remove('active');
            });
            document.body.style.overflow = 'auto';
        }

        // Confirm delete
        function confirmDelete(topicName, deleteUrl) {
            document.getElementById('confirmTopicName').textContent = topicName;
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

        // Slug generator
        function slugify(text) {
            return text.toString().toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        // Auto-generate slug for add form
        document.addEventListener('DOMContentLoaded', () => {
            const addName = document.getElementById('addTopicName');
            const addSlug = document.getElementById('addTopicSlug');
            if (addName && addSlug) {
                addName.addEventListener('input', () => {
                    addSlug.value = slugify(addName.value);
                });
            }

            // Auto-generate slug for edit forms
            document.querySelectorAll('.editTopicName').forEach((nameInput, index) => {
                const slugInput = document.querySelectorAll('.editTopicSlug')[index];
                if (nameInput && slugInput) {
                    nameInput.addEventListener('input', () => {
                        slugInput.value = slugify(nameInput.value);
                    });
                }
            });

            // Ensure table layout consistency
            const table = document.querySelector('table');
            if (table) {
                table.classList.add('table-fixed');
            }

            // Kiểm tra có lỗi validation không, nếu có thì đóng modal
            @if ($errors->any())
                // Đóng tất cả modal khi có lỗi
                closeAllModals();

                // Cuộn lên trên để xem thông báo lỗi
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

                // Làm nổi bật thông báo lỗi với animation
                setTimeout(() => {
                    const alertElement = document.querySelector('.alert, [class*="alert"], [class*="error"], .bg-red');
                    if (alertElement) {
                        alertElement.classList.add('animate-pulse');
                        setTimeout(() => {
                            alertElement.classList.remove('animate-pulse');
                        }, 2000);
                    }
                }, 500);
            @endif
        });

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
                closeAllModals();
            }
        });

        // Handle form submission errors
        document.addEventListener('DOMContentLoaded', function() {
            // Nếu có lỗi validation, hiển thị thông báo và đóng modal
            @if ($errors->any())
                console.log('Có lỗi validation, đóng modal và hiển thị thông báo');
            @endif
        });
    </script>
@endsection
