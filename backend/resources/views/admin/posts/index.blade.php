@extends('layouts.app')

@section('title', 'Danh sách Bài viết')

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

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-published {
            background-color: #dcfce7;
            color: #166534;
        }
        .dark .status-published {
            background-color: #166534;
            color: #dcfce7;
        }
        .status-draft {
            background-color: #f3f4f6;
            color: #374151;
        }
        .dark .status-draft {
            background-color: #374151;
            color: #f3f4f6;
        }
        .pin-badge {
            background-color: #fef3c7;
            color: #92400e;
        }
        .dark .pin-badge {
            background-color: #92400e;
            color: #fef3c7;
        }
        .topic-badge {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .dark .topic-badge {
            background-color: #1e40af;
            color: #dbeafe;
        }

        /* CSS cho bảng */
        .table-fixed th, .table-fixed td {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table-fixed th:nth-child(1), .table-fixed td:nth-child(1) { width: 5%; } /* STT */
        .table-fixed th:nth-child(2), .table-fixed td:nth-child(2) { width: 8%; } /* Ảnh */
        .table-fixed th:nth-child(3), .table-fixed td:nth-child(3) { width: 30%; } /* Tiêu đề */
        .table-fixed th:nth-child(4), .table-fixed td:nth-child(4) { width: 8%; } /* Ghim */
        .table-fixed th:nth-child(5), .table-fixed td:nth-child(5) { width: 15%; } /* Chủ đề */
        .table-fixed th:nth-child(6), .table-fixed td:nth-child(6) { width: 8%; } /* Trạng thái */
        .table-fixed th:nth-child(7), .table-fixed td:nth-child(7) { width: 12%; } /* Ngày tạo */
        .table-fixed th:nth-child(8), .table-fixed td:nth-child(8) { width: 14%; } /* Hành động */
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-white dark:bg-gray-900 transition-all duration-300">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Quản lý Bài viết</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Quản lý danh sách bài viết trong hệ thống</p>
                    </div>
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

            <!-- Search & Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6 animate-fade-in">
                <form method="GET" action="{{ route('admin.posts.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tìm kiếm</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    name="keyword"
                                    class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent"
                                    placeholder="Tìm theo tiêu đề..."
                                    value="{{ request('keyword') }}"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Chủ đề</label>
                            <select name="topic_id" class="block w-full py-2.5 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent">
                                <option value="">-- Tất cả chủ đề --</option>
                                @foreach ($topics as $topic)
                                    <option value="{{ $topic->id }}" {{ request('topic_id') == $topic->id ? 'selected' : '' }}>
                                        {{ $topic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Trạng thái</label>
                            <select name="status" class="block w-full py-2.5 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent">
                                <option value="">-- Tất cả trạng thái --</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Xuất bản</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3 justify-end sm:justify-start">
                        <button type="submit" class="bg-black dark:bg-white text-white dark:text-black px-4 py-2.5 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Tìm kiếm
                        </button>

                        <a href="{{ route('admin.posts.create') }}" class="bg-black dark:bg-white text-white dark:text-black px-4 py-2.5 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Thêm mới
                        </a>
                    </div>
                </form>
            </div>

            <!-- Desktop Table -->
            <div class="hidden lg:block bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden animate-slide-up max-w-full">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-fixed">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-4 text-left text-base font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">STT</th>
                                <th class="px-6 py-4 text-left text-base font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ảnh</th>
                                <th class="px-6 py-4 text-left text-base font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tiêu đề</th>
                                <th class="px-6 py-4 text-left text-base font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ghim</th>
                                <th class="px-6 py-4 text-left text-base font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Chủ đề</th>
                                <th class="px-6 py-4 text-left text-base font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-6 py-4 text-left text-base font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ngày tạo</th>
                                <th class="px-6 py-4 text-right text-base font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($posts as $index => $post)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors min-h-[64px]">
                                    <td class="px-6 py-4 whitespace-nowrap text-base text-gray-900 dark:text-white font-medium">
                                        {{ $posts->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($post->thumbnail)
                                            <img src="{{ $post->thumbnail }}" alt="Thumbnail" class="h-12 w-12 rounded-lg object-cover border border-gray-200 dark:border-gray-600">
                                        @else
                                            <div class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-gray-600 flex items-center justify-center border border-gray-200 dark:border-gray-600">
                                                <svg class="h-6 w-6 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-base font-medium text-gray-900 dark:text-white overflow-hidden text-overflow-ellipsis whitespace-nowrap">{{ Str::limit($post->title, 50) }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 overflow-hidden text-overflow-ellipsis whitespace-nowrap">{{ Str::limit($post->slug, 40) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($post->is_pinned)
                                            <span class="status-badge pin-badge dark:pin-badge">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"></path>
                                                    <path fill-rule="evenodd" d="M3 8a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                                Ghim
                                            </span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1 max-h-[60px] overflow-y-auto">
                                            @forelse($post->topics as $topic)
                                                <span class="status-badge topic-badge dark:topic-badge overflow-hidden text-overflow-ellipsis whitespace-nowrap">{{ Str::limit($topic->name, 20) }}</span>
                                            @empty
                                                <span class="text-gray-400 dark:text-gray-400">—</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge {{ $post->status === 'published' ? 'status-published dark:status-published' : 'status-draft dark:status-draft' }}">
                                            {{ $post->status === 'published' ? 'Xuất bản' : 'Nháp' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-base text-gray-900 dark:text-white">{{ $post->created_at->format('d/m/Y') }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-3">
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                {{ $post->views ?? 0 }}
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ $post->likes ?? 0 }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2 flex-wrap">
                                            <a href="{{ route('admin.posts.edit', $post) }}" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-2 rounded-xl text-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                                Sửa
                                            </a>
                                            <button onclick="confirmDelete('{{ $post->title }}', '{{ route('admin.posts.destroy', $post) }}')" class="bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 px-3 py-2 rounded-xl text-sm hover:bg-red-200 dark:hover:bg-red-800 transition-colors">
                                                Xóa
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-gray-400 dark:text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Không có bài viết</h3>
                                            <p class="text-gray-500 dark:text-gray-400 text-sm">
                                                @if (request('keyword'))
                                                    Không tìm thấy kết quả cho "{{ request('keyword') }}"
                                                @else
                                                    Chưa có bài viết nào trong hệ thống
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
                @forelse($posts as $post)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 animate-slide-up">
                        <div class="flex space-x-3 mb-2">
                            <div class="flex-shrink-0">
                                @if ($post->thumbnail)
                                    <img src="{{ $post->thumbnail }}" alt="Thumbnail" class="h-10 w-10 rounded-lg object-cover border border-gray-200 dark:border-gray-600">
                                @else
                                    <div class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-gray-600 flex items-center justify-center border border-gray-200 dark:border-gray-600">
                                        <svg class="h-5 w-5 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ Str::limit($post->title, 50) }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Str::limit($post->slug, 40) }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="status-badge {{ $post->status === 'published' ? 'status-published dark:status-published' : 'status-draft dark:status-draft' }}">
                                        {{ $post->status === 'published' ? 'Xuất bản' : 'Nháp' }}
                                    </span>
                                    @if ($post->is_pinned)
                                        <span class="status-badge pin-badge dark:pin-badge">Ghim</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Chủ đề:</span>
                                <div class="flex flex-wrap gap-1 max-h-[60px] overflow-y-auto">
                                    @forelse($post->topics as $topic)
                                        <span class="status-badge topic-badge dark:topic-badge overflow-hidden text-overflow-ellipsis whitespace-nowrap">{{ Str::limit($topic->name, 20) }}</span>
                                    @empty
                                        <span class="text-gray-400 dark:text-gray-400">—</span>
                                    @endforelse
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Ngày tạo:</span>
                                <span class="text-gray-900 dark:text-white">{{ $post->created_at->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Thống kê:</span>
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <span class="text-gray-900 dark:text-white">{{ $post->views ?? 0 }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-gray-900 dark:text-white">{{ $post->likes ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2 mt-2 pt-2 border-t border-gray-200 dark:border-gray-700 flex-wrap">
                            <a href="{{ route('admin.posts.edit', $post) }}" class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-2 rounded-xl text-sm text-center hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                Sửa
                            </a>
                            <button onclick="confirmDelete('{{ $post->title }}', '{{ route('admin.posts.destroy', $post) }}')" class="flex-1 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 px-3 py-2 rounded-xl text-sm hover:bg-red-200 dark:hover:bg-red-800 transition-colors">
                                Xóa
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="h-12 w-12 text-gray-400 dark:text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Không có bài viết</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">
                            @if (request('keyword'))
                                Không tìm thấy kết quả cho "{{ request('keyword') }}"
                            @else
                                Chưa có bài viết nào trong hệ thống
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- Custom Pagination -->
            @if($posts->hasPages())
                <div class="mt-8 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 sm:px-6 rounded-lg">
                    <div class="flex flex-1 justify-between sm:hidden">
                        @if ($posts->onFirstPage())
                            <span class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Trước</span>
                        @else
                            <a href="{{ $posts->appends(request()->query())->previousPageUrl() }}" class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">Trước</a>
                        @endif

                        @if ($posts->hasMorePages())
                            <a href="{{ $posts->appends(request()->query())->nextPageUrl() }}" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">Sau</a>
                        @else
                            <span class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Sau</span>
                        @endif
                    </div>

                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Hiển thị
                                <span class="font-medium">{{ $posts->firstItem() }}</span>
                                đến
                                <span class="font-medium">{{ $posts->lastItem() }}</span>
                                trong tổng số
                                <span class="font-medium">{{ $posts->total() }}</span>
                                kết quả
                            </p>
                        </div>
                        <div>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                @if ($posts->onFirstPage())
                                    <span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 dark:text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:z-20 focus:outline-offset-0">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                @else
                                    <a href="{{ $posts->appends(request()->query())->previousPageUrl() }}" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 dark:text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 focus:z-20 focus:outline-offset-0">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                @endif

                                @foreach ($posts->appends(request()->query())->getUrlRange(max(1, $posts->currentPage() - 2), min($posts->lastPage(), $posts->currentPage() + 2)) as $page => $url)
                                    @if ($page == $posts->currentPage())
                                        <span class="relative z-10 inline-flex items-center bg-black dark:bg-white px-4 py-2 text-sm font-semibold text-white dark:text-black focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black dark:focus-visible:outline-white">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 focus:z-20 focus:outline-offset-0">{{ $page }}</a>
                                    @endif
                                @endforeach

                                @if ($posts->hasMorePages())
                                    <a href="{{ $posts->appends(request()->query())->nextPageUrl() }}" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 dark:text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 focus:z-20 focus:outline-offset-0">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                @else
                                    <span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 dark:text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:z-20 focus:outline-offset-0">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                @endif
                            </nav>
                        </div>
                    </div>
                </div>
            @endif
        </div>

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
                            Bạn có chắc chắn muốn xóa bài viết <span id="confirmPostTitle" class="font-semibold"></span>? Hành động này không thể hoàn tác.
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
        function confirmDelete(postTitle, deleteUrl) {
            document.getElementById('confirmPostTitle').textContent = '"' + postTitle + '"';
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

        // Ensure table layout consistency
        document.addEventListener('DOMContentLoaded', () => {
            const table = document.querySelector('table');
            if (table) {
                table.classList.add('table-fixed');
            }
        });
    </script>
@endsection
