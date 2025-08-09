@extends('layouts.app')

@section('title', 'Quản lý Banner')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add Axios CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.0/axios.min.js"></script>
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
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.7);
            transition: transform 0.3s ease;
        }

        .modal-overlay.active .modal-content {
            transform: scale(1);
        }

        /* Toggle Switch Styles */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e5e7eb;
            transition: .3s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        input:checked + .toggle-slider {
            background-color: #10b981;
        }

        input:checked + .toggle-slider:before {
            transform: translateX(20px);
        }

        .upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .upload-area:hover {
            border-color: #374151;
            background: #f3f4f6;
        }

        .upload-area.dragover {
            border-color: #374151;
            background: #f3f4f6;
        }

        .book-select-container {
            position: relative;
        }
        
        .book-select-input {
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 42px;
        }
        
        .book-select-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1050;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-height: 250px;
            overflow: hidden;
            margin-top: 4px;
            display: none;
        }
        
        .book-select-dropdown.show {
            display: block;
        }
        
        .book-select-option {
            padding: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .book-select-option:hover {
            background-color: #f3f4f6;
        }
        
        .book-select-option.selected {
            background-color: #f3f4f6;
            color: #374151;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 20px;
            border-radius: 8px;
            color: white;
            z-index: 1000;
            max-width: 400px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
        }

        .notification.success {
            background-color: #10b981;
        }

        .notification.error {
            background-color: #ef4444;
        }

        .loading {
            opacity: 0.7;
            pointer-events: none;
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
                        <h1 class="text-2xl font-bold text-gray-900">Quản lý Banner</h1>
                        <p class="text-sm text-gray-600 mt-1">Quản lý các banner quảng cáo trên hệ thống</p>
                    </div>
                    
                    <button onclick="openModal('bannerModal')" class="bg-black text-white px-4 py-2.5 rounded-lg font-medium hover:bg-gray-800 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Thêm Banner
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Stats Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 animate-fade-in">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Tổng quan Banner</h3>
                        <p class="text-sm text-gray-600 mt-1">Tổng cộng: <span id="total-banners" class="font-semibold text-gray-900">0</span> banner</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-center">
                            <div id="active-banners" class="text-2xl font-bold text-green-600">0</div>
                            <div class="text-sm text-gray-500">Hiển thị</div>
                        </div>
                        <div class="text-center">
                            <div id="inactive-banners" class="text-2xl font-bold text-gray-400">0</div>
                            <div class="text-sm text-gray-500">Ẩn</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banners Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden animate-slide-up">
                <div id="banner-table" class="p-6">
                    <div class="text-center py-8">
                        <svg class="animate-spin h-8 w-8 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-500 mt-2">Đang tải...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banner Modal -->
        <div id="bannerModal" class="modal-overlay">
            <div class="modal-content">
                <form id="banner-form">
                    <input type="hidden" id="banner-id">
                    
                    <!-- Modal Header -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Thêm Banner Mới</h3>
                            <button type="button" onclick="closeModal('bannerModal')" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="p-6 space-y-6">
                        <!-- Title and Link -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tiêu đề</label>
                                <input type="text" id="title" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent" placeholder="Nhập tiêu đề banner..." required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Liên kết (URL)</label>
                                <input type="url" id="link" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent" placeholder="https://example.com">
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả</label>
                            <textarea id="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent" placeholder="Nhập mô tả chi tiết..."></textarea>
                        </div>

                        <!-- Book Select, Priority, Status -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Book Select -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ID Sách (tùy chọn)</label>
                                <div class="book-select-container">
                                    <div class="book-select-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent cursor-pointer" id="bookSelectInput">
                                        <span class="text-gray-500" id="bookSelectPlaceholder">Chọn sách...</span>
                                        <div class="flex items-center gap-2">
                                            <button type="button" class="text-gray-400 hover:text-gray-600 hidden" id="bookSelectClear">×</button>
                                            <svg class="w-4 h-4 text-gray-400 transition-transform" id="bookSelectArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <div class="book-select-dropdown" id="bookSelectDropdown">
                                        <div class="p-3 border-b border-gray-200">
                                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="bookSearchInput" placeholder="Tìm kiếm sách...">
                                        </div>
                                        <div class="max-h-48 overflow-y-auto" id="bookSelectOptions">
                                            <div class="p-3 text-center text-gray-500">Đang tải...</div>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="book_id" id="bookIdInput" value="">
                                </div>
                                <div class="mt-2 text-sm text-gray-500 hidden" id="bookSelectedInfo">
                                    Đã chọn ID: <span class="font-semibold text-gray-900" id="bookSelectedId"></span>
                                </div>
                            </div>
                            
                            <!-- Priority -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Độ ưu tiên</label>
                                <input type="number" id="priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent" placeholder="0" min="0" max="999" value="0">
                                <p class="text-xs text-gray-500 mt-1">Số càng cao càng ưu tiên</p>
                            </div>
                            
                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                                <div class="flex items-center gap-3 mt-3">
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="status" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="text-sm text-gray-700">Hiển thị banner</span>
                                </div>
                            </div>
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hình ảnh Banner</label>
                            <div class="upload-area" onclick="document.getElementById('image-input').click()">
                                <div id="upload-content">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <p class="text-gray-600 mb-2">Nhấp để chọn hoặc kéo thả hình ảnh</p>
                                    <p class="text-xs text-gray-500">Hỗ trợ: JPG, PNG, GIF (tối đa 5MB)</p>
                                </div>
                                <div id="image-preview" class="hidden">
                                    <img id="preview-img" class="max-w-full max-h-48 rounded-lg mx-auto">
                                    <p class="text-gray-600 mt-2">Nhấp để thay đổi hình ảnh</p>
                                </div>
                            </div>
                            <input type="file" id="image-input" accept="image/*" class="hidden" onchange="previewImage(this)">
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
                        <button type="button" onclick="closeModal('bannerModal')" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            Hủy
                        </button>
                        <button type="button" onclick="saveBanner()" id="saveBannerBtn" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors">
                            Lưu Banner
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Confirm Delete Modal -->
        <div id="confirmModal" class="modal-overlay">
            <div class="modal-content max-w-md">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Xác nhận xóa</h3>
                        <p class="text-sm text-gray-500 mb-6">
                            Bạn có chắc chắn muốn xóa banner này? Hành động này không thể hoàn tác.
                        </p>
                        <div class="flex gap-3 justify-center">
                            <button type="button" onclick="closeModal('confirmModal')" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
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
        // Configuration
        const CONFIG = {
            API_BASE_URL: '/api',
            MAX_IMAGE_SIZE: 5 * 1024 * 1024, // 5MB
            ALLOWED_IMAGE_TYPES: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']
        };
        
        // Global variables
        let banners = [];
        let editingBannerId = null;

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            setupCSRFToken();
            loadBanners();
            setupUploadArea();
            new BookSelect();
            setupEventListeners();
        });

        function setupCSRFToken() {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token && typeof axios !== 'undefined') {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
            }
        }

        function setupEventListeners() {
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

            // Reset form when opening modal for adding new banner
            const addButton = document.querySelector('[onclick="openModal(\'bannerModal\')"]');
            if (addButton) {
                addButton.addEventListener('click', resetForm);
            }
        }

        // API Helper functions
        async function apiRequest(method, endpoint, data = null, isFormData = false) {
            try {
                const config = {
                    method: method,
                    url: `${CONFIG.API_BASE_URL}${endpoint}`,
                    headers: {
                        'Accept': 'application/json',
                    }
                };

                if (data) {
                    if (isFormData) {
                        config.data = data;
                        config.headers['Content-Type'] = 'multipart/form-data';
                    } else {
                        config.data = data;
                        config.headers['Content-Type'] = 'application/json';
                    }
                }

                const response = await axios(config);
                return response.data;
            } catch (error) {
                console.error('API Request Error:', error);
                
                // Handle different types of errors
                if (error.response) {
                    // Server responded with error status
                    const message = error.response.data?.message || `Lỗi ${error.response.status}`;
                    throw new Error(message);
                } else if (error.request) {
                    // Request was made but no response received
                    throw new Error('Không thể kết nối tới server');
                } else {
                    // Something happened in setting up the request
                    throw new Error('Lỗi hệ thống');
                }
            }
        }

        // Load banners from API
        async function loadBanners() {
            try {
                showLoading('banner-table');
                const response = await apiRequest('GET', '/banners');
                banners = response.data || [];
                renderBannerTable();
                updateStats();
            } catch (error) {
                console.error('Error loading banners:', error);
                showNotification('Lỗi khi tải danh sách banner: ' + error.message, 'error');
                renderErrorState('banner-table', 'Lỗi khi tải danh sách banner');
            }
        }

        // Render banner table
        function renderBannerTable() {
            const tableContainer = document.getElementById('banner-table');
            
            if (banners.length === 0) {
                tableContainer.innerHTML = `
                    <div class="text-center py-12">
                        <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Không có banner</h3>
                        <p class="text-gray-500">Chưa có banner nào trong hệ thống</p>
                    </div>
                `;
                return;
            }

            const tableHTML = `
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hình ảnh</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thông tin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Liên kết</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Độ ưu tiên</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${banners.map(banner => `
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900">#${banner.id}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        ${banner.image ? `
                                            <img src="${getImageUrl(banner.image)}" alt="Banner ${banner.id}" class="h-16 w-24 object-cover rounded-lg border border-gray-200">
                                        ` : `
                                            <div class="h-16 w-24 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        `}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 mb-1">${banner.title || '<em class="text-gray-500">Chưa có tiêu đề</em>'}</div>
                                        <div class="text-sm text-gray-500">${banner.description || '<em>Chưa có mô tả</em>'}</div>
                                        ${banner.book_id ? `<div class="text-xs text-blue-600 mt-1">Sách #${banner.book_id}</div>` : ''}
                                    </td>
                                    <td class="px-6 py-4">
                                        ${banner.link ? `
                                            <a href="${banner.link}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                                ${banner.link.length > 30 ? banner.link.substring(0, 30) + '...' : banner.link}
                                            </a>
                                        ` : `
                                            <span class="text-gray-500 text-sm italic">Không có liên kết</span>
                                        `}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            ${banner.priority || 0}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <label class="toggle-switch">
                                            <input type="checkbox" ${banner.status ? 'checked' : ''} onchange="toggleBannerStatus(${banner.id}, this.checked)">
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button onclick="editBanner(${banner.id})" class="bg-gray-100 text-gray-700 px-3 py-2 rounded-lg text-sm hover:bg-gray-200 transition-colors">
                                                Sửa
                                            </button>
                                            <button onclick="confirmDelete(${banner.id})" class="bg-red-100 text-red-700 px-3 py-2 rounded-lg text-sm hover:bg-red-200 transition-colors">
                                                Xóa
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            
            tableContainer.innerHTML = tableHTML;
        }

        // Update statistics
        function updateStats() {
            const activeBanners = banners.filter(b => b.status).length;
            const inactiveBanners = banners.filter(b => !b.status).length;
            
            document.getElementById('total-banners').textContent = banners.length;
            document.getElementById('active-banners').textContent = activeBanners;
            document.getElementById('inactive-banners').textContent = inactiveBanners;
        }

        // Toggle banner status
        async function toggleBannerStatus(id, status) {
            const banner = banners.find(b => b.id === id);
            const originalStatus = banner ? banner.status : !status;
            
            try {
                // Optimistically update UI
                if (banner) {
                    banner.status = status;
                    updateStats();
                }
                
                // Make API call
                await apiRequest('PATCH', `/banners/${id}/status`, { status });
                showNotification(`Banner ${status ? 'hiển thị' : 'ẩn'} thành công!`, 'success');
                
            } catch (error) {
                console.error('Error updating banner status:', error);
                showNotification('Lỗi khi cập nhật trạng thái: ' + error.message, 'error');
                
                // Revert the toggle on error
                if (banner) {
                    banner.status = originalStatus;
                    renderBannerTable();
                    updateStats();
                }
            }
        }

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Edit banner
        function editBanner(id) {
            const banner = banners.find(b => b.id === id);
            if (!banner) return;

            editingBannerId = id;
            document.getElementById('modalTitle').textContent = 'Chỉnh sửa Banner';
            document.getElementById('banner-id').value = banner.id;
            document.getElementById('title').value = banner.title || '';
            document.getElementById('description').value = banner.description || '';
            document.getElementById('link').value = banner.link || '';
            document.getElementById('priority').value = banner.priority || 0;
            document.getElementById('status').checked = banner.status !== false;

            // Set book selection
            if (banner.book_id) {
                document.getElementById('bookIdInput').value = banner.book_id;
                document.getElementById('bookSelectPlaceholder').textContent = `Sách #${banner.book_id}`;
                document.getElementById('bookSelectPlaceholder').classList.remove('text-gray-500');
                document.getElementById('bookSelectClear').classList.remove('hidden');
                document.getElementById('bookSelectedInfo').classList.remove('hidden');
                document.getElementById('bookSelectedId').textContent = banner.book_id;
            }

            // Set image preview
            if (banner.image) {
                document.getElementById('preview-img').src = getImageUrl(banner.image);
                document.getElementById('image-preview').classList.remove('hidden');
                document.getElementById('upload-content').classList.add('hidden');
            } else {
                document.getElementById('image-preview').classList.add('hidden');
                document.getElementById('upload-content').classList.remove('hidden');
            }

            openModal('bannerModal');
        }

        // Confirm delete
        function confirmDelete(id) {
            document.getElementById('confirmDeleteBtn').onclick = function() {
                deleteBanner(id);
            };
            openModal('confirmModal');
        }

        // Delete banner
        async function deleteBanner(id) {
            try {
                showButtonLoading('confirmDeleteBtn', 'Đang xóa...');
                
                await apiRequest('DELETE', `/banners/${id}`);
                
                // Remove from local array
                banners = banners.filter(b => b.id !== id);
                renderBannerTable();
                updateStats();
                showNotification('Xóa banner thành công!', 'success');
                closeModal('confirmModal');
                
            } catch (error) {
                console.error('Error deleting banner:', error);
                showNotification('Lỗi khi xóa banner: ' + error.message, 'error');
            } finally {
                hideButtonLoading('confirmDeleteBtn', 'Xóa');
            }
        }

        // Save banner
        async function saveBanner() {
            const title = document.getElementById('title').value.trim();
            const link = document.getElementById('link').value.trim();

            if (!title) {
                showNotification('Vui lòng nhập tiêu đề banner!', 'error');
                document.getElementById('title').focus();
                return;
            }

            if (link && !isValidUrl(link)) {
                showNotification('URL không hợp lệ!', 'error');
                document.getElementById('link').focus();
                return;
            }

            const formData = new FormData();
            formData.append('title', title);
            formData.append('description', document.getElementById('description').value.trim());
            formData.append('link', link || '');
            formData.append('book_id', document.getElementById('bookIdInput').value || '');
            formData.append('priority', parseInt(document.getElementById('priority').value) || 0);
            formData.append('status', document.getElementById('status').checked ? 1 : 0);

            // Add image if selected
            const imageInput = document.getElementById('image-input');
            if (imageInput.files && imageInput.files[0]) {
                formData.append('image', imageInput.files[0]);
            }

            try {
                showButtonLoading('saveBannerBtn', 'Đang lưu...');
                
                let response;
                if (editingBannerId) {
                    // Update existing banner
                    formData.append('_method', 'PUT');
                    response = await apiRequest('POST', `/banners/${editingBannerId}`, formData, true);
                    
                    // Update in local array
                    const index = banners.findIndex(b => b.id === editingBannerId);
                    if (index !== -1) {
                        banners[index] = response.data;
                    }
                    showNotification('Cập nhật banner thành công!', 'success');
                } else {
                    // Add new banner
                    response = await apiRequest('POST', '/banners', formData, true);
                    banners.push(response.data);
                    showNotification('Thêm banner thành công!', 'success');
                }

                renderBannerTable();
                updateStats();
                closeModal('bannerModal');
                resetForm();
                    
            } catch (error) {
                console.error('Error saving banner:', error);
                showNotification('Lỗi khi lưu banner: ' + error.message, 'error');
            } finally {
                hideButtonLoading('saveBannerBtn', 'Lưu Banner');
            }
        }

        // Reset form
        function resetForm() {
            editingBannerId = null;
            document.getElementById('modalTitle').textContent = 'Thêm Banner Mới';
            document.getElementById('banner-form').reset();
            document.getElementById('priority').value = 0;
            document.getElementById('status').checked = true;
            document.getElementById('image-preview').classList.add('hidden');
            document.getElementById('upload-content').classList.remove('hidden');
            
            // Reset book selection
            document.getElementById('bookIdInput').value = '';
            document.getElementById('bookSelectPlaceholder').textContent = 'Chọn sách...';
            document.getElementById('bookSelectPlaceholder').classList.add('text-gray-500');
            document.getElementById('bookSelectClear').classList.add('hidden');
            document.getElementById('bookSelectedInfo').classList.add('hidden');
        }

        // Image preview
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];

                if (!CONFIG.ALLOWED_IMAGE_TYPES.includes(file.type)) {
                    showNotification('Vui lòng chọn file hình ảnh hợp lệ (JPG, PNG, GIF)!', 'error');
                    input.value = '';
                    return;
                }

                if (file.size > CONFIG.MAX_IMAGE_SIZE) {
                    showNotification('File quá lớn! Tối đa 5MB.', 'error');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('image-preview').classList.remove('hidden');
                    document.getElementById('upload-content').classList.add('hidden');
                };
                reader.onerror = function() {
                    showNotification('Lỗi khi đọc file hình ảnh', 'error');
                };
                reader.readAsDataURL(file);
            }
        }

        // Setup upload area drag and drop
        function setupUploadArea() {
            const uploadArea = document.querySelector('.upload-area');
            
            if (!uploadArea) return;
            
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', (e) => {
                if (!uploadArea.contains(e.relatedTarget)) {
                    uploadArea.classList.remove('dragover');
                }
            });
            
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    const imageInput = document.getElementById('image-input');
                    
                    if (imageInput) {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        imageInput.files = dt.files;
                        previewImage(imageInput);
                    }
                }
            });
        }

        // Loading states
        function showLoading(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.innerHTML = `
                    <div class="text-center py-8">
                        <svg class="animate-spin h-8 w-8 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-500 mt-2">Đang tải...</p>
                    </div>
                `;
            }
        }

        function showButtonLoading(buttonId, loadingText) {
            const button = document.getElementById(buttonId);
            if (button) {
                button.disabled = true;
                button.classList.add('loading');
                button.innerHTML = `
                    <svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    ${loadingText}
                `;
            }
        }

        function hideButtonLoading(buttonId, originalText) {
            const button = document.getElementById(buttonId);
            if (button) {
                button.disabled = false;
                button.classList.remove('loading');
                button.innerHTML = originalText;
            }
        }

        function renderErrorState(elementId, message) {
            const element = document.getElementById(elementId);
            if (element) {
                element.innerHTML = `
                    <div class="text-center py-12">
                        <svg class="h-12 w-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Lỗi tải dữ liệu</h3>
                        <p class="text-gray-500 mb-4">${message}</p>
                        <button onclick="loadBanners()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Thử lại
                        </button>
                    </div>
                `;
            }
        }

        // Utility functions
        function getImageUrl(imagePath) {
            if (!imagePath) return '';
            if (imagePath.startsWith('http')) {
                return imagePath;
            }
            return `/storage/${imagePath}`;
        }

        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }

        function showNotification(message, type = 'success') {
            // Remove existing notifications
            document.querySelectorAll('.notification').forEach(n => n.remove());
            
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <div class="flex items-center gap-2">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
        }

        // Book Select Component
        class BookSelect {
            constructor() {
                this.books = [];
                this.selectedBook = null;
                this.isOpen = false;
                this.isLoading = false;
                
                this.initElements();
                this.bindEvents();
                this.fetchBooks();
            }
            
            initElements() {
                this.selectInput = document.getElementById('bookSelectInput');
                this.placeholder = document.getElementById('bookSelectPlaceholder');
                this.clearBtn = document.getElementById('bookSelectClear');
                this.arrow = document.getElementById('bookSelectArrow');
                this.dropdown = document.getElementById('bookSelectDropdown');
                this.searchInput = document.getElementById('bookSearchInput');
                this.optionsContainer = document.getElementById('bookSelectOptions');
                this.hiddenInput = document.getElementById('bookIdInput');
                this.selectedInfo = document.getElementById('bookSelectedInfo');
                this.selectedIdSpan = document.getElementById('bookSelectedId');
            }
            
            bindEvents() {
                if (!this.selectInput) return;
                
                this.selectInput.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.toggleDropdown();
                });
                
                if (this.clearBtn) {
                    this.clearBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.clearSelection();
                    });
                }
                
                if (this.searchInput) {
                    this.searchInput.addEventListener('input', (e) => {
                        this.renderOptions(e.target.value);
                    });
                }
                
                if (this.dropdown) {
                    this.dropdown.addEventListener('click', (e) => {
                        e.stopPropagation();
                    });
                }
                
                document.addEventListener('click', () => {
                    this.closeDropdown();
                });
            }
            
            async fetchBooks() {
                this.isLoading = true;
                this.renderOptions();
                
                try {
                    const response = await apiRequest('GET', '/books/ids');
                    this.books = response.data.map(book => ({
                        value: book.id,
                        label: `${book.id} - ${book.title.trim()}`
                    }));
                } catch (error) {
                    console.error('Error fetching books:', error);
                    this.books = [];
                    showNotification('Lỗi khi tải danh sách sách: ' + error.message, 'error');
                } finally {
                    this.isLoading = false;
                    this.renderOptions();
                }
            }
            
            toggleDropdown() {
                if (this.isOpen) {
                    this.closeDropdown();
                } else {
                    this.openDropdown();
                }
            }
            
            openDropdown() {
                if (!this.dropdown) return;
                
                this.isOpen = true;
                this.dropdown.classList.add('show');
                if (this.arrow) {
                    this.arrow.style.transform = 'rotate(180deg)';
                }
                if (this.searchInput) {
                    this.searchInput.value = '';
                }
                this.renderOptions();
                setTimeout(() => {
                    if (this.searchInput) {
                        this.searchInput.focus();
                    }
                }, 100);
            }
            
            closeDropdown() {
                if (!this.dropdown) return;
                
                this.isOpen = false;
                this.dropdown.classList.remove('show');
                if (this.arrow) {
                    this.arrow.style.transform = 'rotate(0deg)';
                }
            }
            
            renderOptions(searchTerm = '') {
                if (!this.optionsContainer) return;
                
                if (this.isLoading) {
                    this.optionsContainer.innerHTML = '<div class="p-3 text-center text-gray-500">Đang tải...</div>';
                    return;
                }
                
                const filteredBooks = this.books.filter(book =>
                    book.label.toLowerCase().includes(searchTerm.toLowerCase())
                );
                
                if (filteredBooks.length === 0) {
                    const emptyMessage = searchTerm ? 'Không tìm thấy sách nào' : 'Không có dữ liệu';
                    this.optionsContainer.innerHTML = `<div class="p-3 text-center text-gray-500">${emptyMessage}</div>`;
                    return;
                }
                
                this.optionsContainer.innerHTML = filteredBooks.map(book => `
                    <div class="book-select-option ${this.selectedBook && this.selectedBook.value === book.value ? 'selected' : ''}" 
                         data-value="${book.value}" data-label="${book.label}">
                        <span class="text-sm">${book.label}</span>
                        ${this.selectedBook && this.selectedBook.value === book.value ? 
                            '<svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>' : 
                            ''
                        }
                    </div>
                `).join('');
                
                // Add click events to options
                this.optionsContainer.querySelectorAll('.book-select-option').forEach(option => {
                    option.addEventListener('click', () => {
                        this.selectBook({
                            value: parseInt(option.dataset.value),
                            label: option.dataset.label
                        });
                    });
                });
            }
            
            selectBook(book) {
                this.selectedBook = book;
                
                if (this.placeholder) {
                    this.placeholder.textContent = book.label;
                    this.placeholder.classList.remove('text-gray-500');
                }
                
                if (this.clearBtn) {
                    this.clearBtn.classList.remove('hidden');
                }
                
                if (this.hiddenInput) {
                    this.hiddenInput.value = book.value;
                }
                
                if (this.selectedIdSpan) {
                    this.selectedIdSpan.textContent = book.value;
                }
                
                if (this.selectedInfo) {
                    this.selectedInfo.classList.remove('hidden');
                }
                
                this.closeDropdown();
            }
            
            clearSelection() {
                this.selectedBook = null;
                
                if (this.placeholder) {
                    this.placeholder.textContent = 'Chọn sách...';
                    this.placeholder.classList.add('text-gray-500');
                }
                
                if (this.clearBtn) {
                    this.clearBtn.classList.add('hidden');
                }
                
                if (this.hiddenInput) {
                    this.hiddenInput.value = '';
                }
                
                if (this.selectedInfo) {
                    this.selectedInfo.classList.add('hidden');
                }
                
                this.renderOptions();
            }
        }
    </script>
@endsection