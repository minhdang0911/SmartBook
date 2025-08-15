@extends('layouts.app')

@section('title', 'Flash Sale Management')

@section('content')
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Flash Sale Management</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
     
    
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <!-- <style>
        .sale-card {
            @apply transition-all duration-300 hover:shadow-lg border border-gray-200 rounded-lg;
        }
        
        .sale-card:hover {
            @apply transform -translate-y-1;
        }
        
        .status-live {
            @apply bg-green-100 text-green-800 border border-green-200;
        }
        
        .status-scheduled {
            @apply bg-blue-100 text-blue-800 border border-blue-200;
        }
        
        .status-inactive {
            @apply bg-gray-100 text-gray-600 border border-gray-200;
        }
        
        .discount-badge {
            @apply bg-orange-100 text-orange-800 px-2 py-1 rounded-full text-xs font-medium;
        }
        
        .sale-price {
            @apply text-green-600 font-bold;
        }
        
        .original-price {
            @apply text-gray-500 line-through;
        }
        
        .loading-spinner {
            @apply animate-spin;
        }
        
        .hidden {
            display: none !important;
        }
        
        @media (max-width: 640px) {
            .modal-container {
                margin: 10px !important;
                max-width: calc(100vw - 20px) !important;
            }
        }
    </style> -->
</head>

<body class="bg-gray-50 min-h-screen">
    <div id="flashSaleApp" class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-bolt text-orange-500 mr-3"></i>
                    Flash Sale Management
                </h1>
                <p class="text-gray-600 mt-2">Quản lý các chương trình giảm giá đặc biệt</p>
            </div>
            
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 w-full sm:w-auto">
                <button onclick="flashSaleManager.refreshData()" 
                        class="w-full sm:w-auto px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-sync-alt mr-2" id="refresh-icon"></i>
                    Refresh
                </button>
                
                <button onclick="flashSaleManager.openCreateModal()" 
                        class="w-full sm:w-auto px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200 flex items-center justify-center shadow-md">
                    <i class="fas fa-plus mr-2"></i>
                    Tạo Flash Sale
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8" id="statistics-cards">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-calendar-alt text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Tổng Events</p>
                        <p class="text-2xl font-bold text-gray-900" id="stat-total">0</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-play-circle text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Đang Hoạt Động</p>
                        <p class="text-2xl font-bold text-green-600" id="stat-active">0</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-full">
                        <i class="fas fa-clock text-orange-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Sắp Diễn Ra</p>
                        <p class="text-2xl font-bold text-orange-600" id="stat-scheduled">0</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-box text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Tổng Sản Phẩm</p>
                        <p class="text-2xl font-bold text-purple-600" id="stat-products">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                <div class="flex-1">
                    <input type="text" 
                           id="search-input"
                           placeholder="Tìm kiếm flash sale..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           onkeyup="flashSaleManager.filterFlashSales()">
                </div>
                
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                    <select id="status-filter" 
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="flashSaleManager.filterFlashSales()">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active">Đang hoạt động</option>
                        <option value="scheduled">Sắp diễn ra</option>
                        <option value="inactive">Không hoạt động</option>
                    </select>
                    
                    <select id="sort-by" 
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="flashSaleManager.filterFlashSales()">
                        <option value="created_at">Mới nhất</option>
                        <option value="event_name">Tên A-Z</option>
                        <option value="start_date">Ngày bắt đầu</option>
                        <option value="end_date">Ngày kết thúc</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div id="loading-indicator" class="text-center py-12 hidden">
            <i class="fas fa-spinner loading-spinner text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600">Đang tải dữ liệu...</p>
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-200 hidden">
            <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Không tìm thấy flash sale nào</h3>
            <p class="text-gray-600 mb-6">Thử thay đổi bộ lọc hoặc tạo flash sale mới</p>
            <button onclick="flashSaleManager.openCreateModal()" 
                    class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Tạo Flash Sale Đầu Tiên
            </button>
        </div>

        <!-- Flash Sales List -->
        <div id="flash-sales-list" class="space-y-4">
            <!-- Flash sale items will be inserted here by JavaScript -->
        </div>

        <!-- Success/Error Messages -->
        <div id="message-container" class="fixed top-4 right-4 z-50 hidden">
            <div id="message-content" class="max-w-sm p-4 rounded-lg border shadow-lg">
                <!-- Message content -->
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div id="flash-sale-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden modal-container">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900 flex items-center" id="modal-title">
                    <i class="fas fa-bolt text-orange-500 mr-2"></i>
                    Tạo Flash Sale mới
                </h2>
                <button onclick="flashSaleManager.closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-160px)]">
                <!-- Modal Messages -->
                <div id="modal-message" class="p-4 rounded-lg border mb-6 hidden">
                    <i id="modal-message-icon" class="mr-2"></i>
                    <span id="modal-message-text"></span>
                </div>

                <!-- Form -->
                <form id="flash-sale-form" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tên Flash Sale</label>
                            <input type="text" 
                                   id="event-name"
                                   required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Nhập tên flash sale">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                            <div class="flex items-center space-x-3">
                                <span class="text-sm text-gray-600">Inactive</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="status-toggle" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                                <span class="text-sm text-gray-600">Active</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ngày bắt đầu</label>
                            <input type="datetime-local" 
                                   id="start-date"
                                   required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ngày kết thúc</label>
                            <input type="datetime-local" 
                                   id="end-date"
                                   required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </form>

                <!-- Products Section -->
                <div class="mt-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Sản phẩm trong Flash Sale</h3>
                        <button onclick="flashSaleManager.openAddProductModal()" 
                                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200 flex items-center text-sm">
                            <i class="fas fa-plus mr-2"></i>
                            Thêm sản phẩm
                        </button>
                    </div>

                    <!-- Products Table -->
                    <div class="bg-gray-50 rounded-lg overflow-hidden">
                        <div id="products-empty" class="p-8 text-center">
                            <i class="fas fa-box text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-600">Chưa có sản phẩm nào trong flash sale</p>
                            <button onclick="flashSaleManager.openAddProductModal()" 
                                    class="mt-4 px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200">
                                Thêm sản phẩm đầu tiên
                            </button>
                        </div>

                        <div id="products-table" class="overflow-x-auto hidden">
                            <table class="w-full">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá gốc</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giảm giá</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá sale</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="products-tbody" class="bg-white divide-y divide-gray-200">
                                    <!-- Product rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 p-6 border-t border-gray-200 bg-gray-50">
                <button onclick="flashSaleManager.closeModal()" 
                        class="w-full sm:w-auto px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    Hủy
                </button>
                <button onclick="flashSaleManager.saveDraft()" 
                        id="save-draft-btn"
                        class="w-full sm:w-auto px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors duration-200 disabled:opacity-50 flex items-center justify-center">
                    <i id="save-draft-spinner" class="fas fa-spinner loading-spinner mr-2 hidden"></i>
                    Lưu nháp
                </button>
                <button onclick="flashSaleManager.activateFlashSale()" 
                        id="activate-btn"
                        class="w-full sm:w-auto px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200 disabled:opacity-50 flex items-center justify-center">
                    <i id="activate-spinner" class="fas fa-spinner loading-spinner mr-2 hidden"></i>
                    <span id="activate-text">Tạo và Kích hoạt</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="add-product-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" style="z-index: 9999;">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden modal-container">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Thêm sản phẩm vào Flash Sale</h2>
                <button onclick="flashSaleManager.closeAddProductModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
                <!-- Search Box -->
                <div class="mb-6">
                    <input type="text" 
                           id="product-search"
                           placeholder="Tìm kiếm sản phẩm theo tên..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           onkeyup="flashSaleManager.filterAvailableProducts()">
                </div>

                <!-- Loading Products -->
                <div id="products-loading" class="text-center py-8 hidden">
                    <i class="fas fa-spinner loading-spinner text-2xl text-gray-400 mb-2"></i>
                    <p class="text-gray-600">Đang tải danh sách sản phẩm...</p>
                </div>

                <!-- No Products -->
                <div id="no-products" class="text-center py-8 hidden">
                    <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Không có sản phẩm khả dụng</h3>
                    <p class="text-gray-600">Tất cả sản phẩm đều đã có giá giảm hoặc đang trong flash sale khác</p>
                </div>

                <!-- Available Products -->
                <div id="available-products" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Product cards will be inserted here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Product Configuration Modal -->
    <div id="product-config-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" style="z-index: 10000;">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md modal-container">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Cấu hình sản phẩm</h2>
                <button onclick="flashSaleManager.closeProductConfigModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <!-- Selected Product Info -->
                <div id="selected-product-info" class="bg-gray-50 rounded-lg p-4 mb-6">
                    <!-- Product info will be inserted here -->
                </div>

                <!-- Configuration Form -->
                <form id="product-config-form" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phần trăm giảm giá (%)</label>
                        <input type="number" 
                               id="discount-percent"
                               min="1" 
                               max="99" 
                               required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nhập % giảm giá"
                               onkeyup="flashSaleManager.updatePricePreview()">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Giới hạn số lượng</label>
                        <input type="number" 
                               id="quantity-limit"
                               min="1" 
                               required 
                               value="50"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Số lượng tối đa">
                    </div>

                    <!-- Price Preview -->
                    <div id="price-preview" class="bg-green-50 border border-green-200 rounded-lg p-4 hidden">
                        <h5 class="font-medium text-green-800 mb-2">Xem trước giá:</h5>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span>Giá gốc:</span>
                                <span class="font-medium" id="preview-original-price"></span>
                            </div>
                            <div class="flex justify-between text-orange-600">
                                <span>Giảm giá (<span id="preview-discount-percent"></span>%):</span>
                                <span class="font-medium" id="preview-discount-amount"></span>
                            </div>
                            <div class="flex justify-between text-green-600 font-bold border-t border-green-300 pt-2">
                                <span>Giá bán:</span>
                                <span id="preview-sale-price"></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="flex space-x-3 p-6 border-t border-gray-200 bg-gray-50">
                <button onclick="flashSaleManager.closeProductConfigModal()" 
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    Hủy
                </button>
                <button onclick="flashSaleManager.addProductToSale()" 
                        id="add-product-btn"
                        class="flex-1 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200 disabled:opacity-50 flex items-center justify-center">
                    <i id="add-product-spinner" class="fas fa-spinner loading-spinner mr-2 hidden"></i>
                    Thêm vào Flash Sale
                </button>
            </div>
        </div>
    </div>

    <script>
        // Flash Sale Manager - Fixed JavaScript Implementation
        const flashSaleManager = {
            // Data storage
            flashSales: [],
            availableBooks: [],
            saleProducts: [],
            currentEditingId: null,
            selectedProduct: null,
            
            // API configuration
            API_BASE: '/api',
            
            // Initialize
            init() {
                console.log('🚀 FlashSaleManager initializing...');
                // Setup axios
                axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                console.log('✅ CSRF token set');
                this.refreshData();
            },

            // Show message
            showMessage(text, type = 'success') {
                const container = document.getElementById('message-container');
                const content = document.getElementById('message-content');
                
                container.classList.remove('hidden');
                content.className = `max-w-sm p-4 rounded-lg border shadow-lg ${
                    type === 'success' 
                        ? 'bg-green-50 border-green-200 text-green-800' 
                        : 'bg-red-50 border-red-200 text-red-800'
                }`;
                content.innerHTML = `
                    <i class="${type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'} mr-2"></i>
                    ${text}
                `;
                
                setTimeout(() => {
                    container.classList.add('hidden');
                }, 3000);
            },

            // Show modal message
            showModalMessage(text, type = 'success') {
                const container = document.getElementById('modal-message');
                const icon = document.getElementById('modal-message-icon');
                const textEl = document.getElementById('modal-message-text');
                
                container.classList.remove('hidden');
                container.className = `p-4 rounded-lg border mb-6 ${
                    type === 'success' 
                        ? 'bg-green-50 border-green-200 text-green-800' 
                        : 'bg-red-50 border-red-200 text-red-800'
                }`;
                icon.className = `mr-2 ${type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'}`;
                textEl.textContent = text;
                
                setTimeout(() => {
                    container.classList.add('hidden');
                }, 3000);
            },

            // API Calls - Events
            async loadFlashSales() {
                try {
                    console.log('📡 Loading flash sales...');
                    this.showLoading(true);
                    const response = await axios.get(`${this.API_BASE}/events`);
                    console.log('📋 Flash sales response:', response);
                    this.flashSales = response.data || [];
                    console.log('✅ Flash sales loaded:', this.flashSales.length, 'items');
                    this.updateStatistics();
                    this.renderFlashSales();
                } catch (error) {
                    console.error('❌ Error loading flash sales:', error);
                    this.showMessage('Lỗi khi tải danh sách flash sale: ' + (error.response?.data?.message || error.message), 'error');
                } finally {
                    this.showLoading(false);
                }
            },

            async loadAvailableBooks() {
                try {
                    console.log('📚 Loading available books...');
                    document.getElementById('products-loading').classList.remove('hidden');
                    const response = await axios.get(`${this.API_BASE}/books/available-for-event`);
                    console.log('📚 Books response:', response);
                    this.availableBooks = response.data.data || response.data || [];
                    console.log('✅ Available books loaded:', this.availableBooks.length, 'items');
                    this.renderAvailableProducts();
                } catch (error) {
                    console.error('❌ Error loading available books:', error);
                    this.showMessage('Lỗi khi tải danh sách sản phẩm: ' + (error.response?.data?.message || error.message), 'error');
                } finally {
                    document.getElementById('products-loading').classList.add('hidden');
                }
            },

            async loadFlashSaleDetail(id) {
                try {
                    const response = await axios.get(`${this.API_BASE}/events/${id}`);
                    return response.data.data || response.data;
                } catch (error) {
                    console.error('Error loading flash sale detail:', error);
                    throw error;
                }
            },

            async createEvent(eventData) {
                try {
                    const response = await axios.post(`${this.API_BASE}/events`, eventData);
                    return response.data;
                } catch (error) {
                    console.error('Error creating event:', error);
                    throw error;
                }
            },

            async updateEvent(eventId, eventData) {
                try {
                    const response = await axios.put(`${this.API_BASE}/events/${eventId}`, eventData);
                    return response.data;
                } catch (error) {
                    console.error('Error updating event:', error);
                    throw error;
                }
            },

            async deleteEvent(eventId) {
                try {
                    const response = await axios.delete(`${this.API_BASE}/events/${eventId}`);
                    return response.data;
                } catch (error) {
                    console.error('Error deleting event:', error);
                    throw error;
                }
            },

            async addBookToEvent(eventId, bookData) {
                try {
                    const response = await axios.post(`${this.API_BASE}/events/${eventId}/books`, bookData);
                    return response.data;
                } catch (error) {
                    console.error('Error adding book to event:', error);
                    throw error;
                }
            },

            async removeBookFromEvent(eventId, bookId) {
                try {
                    const response = await axios.delete(`${this.API_BASE}/events/${eventId}/books/${bookId}`);
                    return response.data;
                } catch (error) {
                    console.error('Error removing book from event:', error);
                    throw error;
                }
            },

            // UI Methods
            showLoading(show) {
                const loading = document.getElementById('loading-indicator');
                const refreshIcon = document.getElementById('refresh-icon');
                
                if (show) {
                    loading.classList.remove('hidden');
                    refreshIcon.classList.add('loading-spinner');
                } else {
                    loading.classList.add('hidden');
                    refreshIcon.classList.remove('loading-spinner');
                }
            },

            async refreshData() {
                console.log('🔄 Refreshing all data...');
                await Promise.all([
                    this.loadFlashSales(),
                    this.loadAvailableBooks()
                ]);
                console.log('✅ Data refresh completed');
            },

            updateStatistics() {
                const total = this.flashSales.length;
                const active = this.flashSales.filter(sale => sale.status === 'active').length;
                const scheduled = this.flashSales.filter(sale => sale.status === 'scheduled').length;
                const totalProducts = this.flashSales.reduce((sum, sale) => sum + (sale.books ? sale.books.length : 0), 0);
                
                document.getElementById('stat-total').textContent = total;
                document.getElementById('stat-active').textContent = active;
                document.getElementById('stat-scheduled').textContent = scheduled;
                document.getElementById('stat-products').textContent = totalProducts;
            },

            renderFlashSales() {
                const container = document.getElementById('flash-sales-list');
                const emptyState = document.getElementById('empty-state');
                
                if (this.flashSales.length === 0) {
                    container.classList.add('hidden');
                    emptyState.classList.remove('hidden');
                    return;
                }
                
                container.classList.remove('hidden');
                emptyState.classList.add('hidden');
                
                container.innerHTML = this.flashSales.map(sale => this.renderFlashSaleCard(sale)).join('');
            },

            renderFlashSaleCard(sale) {
                const statusClass = this.getStatusClass(sale.status);
                console.log('statusClass:', statusClass);
                
                const statusText = this.getStatusText(sale.status);
                console.log('statusText:', statusText);
                console.log(sale)
                
                return `
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 sale-card">
                        <!-- Mobile Layout -->
                        <div class="block sm:hidden p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">${sale.event_name}</h3>
                                        <span class="${statusClass} px-2 py-1 rounded-full text-xs font-medium">
                                            ${statusText}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">
                                        <i class="fas fa-calendar mr-1"></i>
                                        ${this.formatDate(sale.start_date)} - ${this.formatDate(sale.end_date)}
                                    </p>
                                    <div class="text-sm text-gray-600">
                                        <i class="fas fa-box mr-1"></i>
                                        ${sale.books ? sale.books.length : 0} sản phẩm
                                    </div>
                                </div>
                                
                                <div class="flex flex-col space-y-1">
                                    <button onclick="flashSaleManager.editFlashSale(${sale.event_id})" 
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="flashSaleManager.duplicateFlashSale(${sale.event_id})" 
                                            class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button onclick="flashSaleManager.deleteFlashSale(${sale.event_id})" 
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                            ${sale.books && sale.books.length > 0 ? this.renderMobileProducts(sale.books) : ''}
                        </div>

                        <!-- Desktop Layout -->
                        <div class="hidden sm:block p-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-3">
                                        <h3 class="text-xl font-semibold text-gray-900">${sale.event_name}</h3>
                                        <span class="${statusClass} px-3 py-1 rounded-full text-sm font-medium">
                                            ${statusText}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-6 text-sm text-gray-600 mb-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-calendar mr-2"></i>
                                            ${this.formatDate(sale.start_date)} - ${this.formatDate(sale.end_date)}
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-box mr-2"></i>
                                            ${sale.books ? sale.books.length : 0} sản phẩm
                                        </div>
                                        ${sale.total_sold ? `<div class="flex items-center">
                                            <i class="fas fa-chart-line mr-2"></i>
                                            ${sale.total_sold} đã bán
                                        </div>` : ''}
                                    </div>

                                    ${sale.books && sale.books.length > 0 ? this.renderDesktopProducts(sale.books) : ''}
                                </div>

                                <!-- Desktop Actions -->
                                <div class="flex space-x-2 ml-6">
                                    <button onclick="flashSaleManager.editFlashSale(${sale.event_id})" 
                                            class="px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200 flex items-center">
                                        <i class="fas fa-edit mr-2"></i>
                                        <span class="hidden lg:inline">Chỉnh sửa</span>
                                    </button>
                                    <button onclick="flashSaleManager.duplicateFlashSale(${sale.event_id})" 
                                            class="px-4 py-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors duration-200 flex items-center">
                                        <i class="fas fa-copy mr-2"></i>
                                        <span class="hidden lg:inline">Nhân bản</span>
                                    </button>
                                    <button onclick="flashSaleManager.deleteFlashSale(${sale.event_id})" 
                                            class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200 flex items-center">
                                        <i class="fas fa-trash mr-2"></i>
                                        <span class="hidden lg:inline">Xóa</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            },

            renderMobileProducts(books) {
                const displayBooks = books.slice(0, 2);
                const remaining = books.length - 2;
                
                return `
                    <div class="border-t pt-3">
                        <div class="grid grid-cols-1 gap-2">
                            ${displayBooks.map(book => `
                                <div class="flex items-center space-x-3 p-2 bg-gray-50 rounded-lg">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-book text-blue-600 text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">${book.title}</p>
                                        <div class="flex items-center space-x-2 text-xs">
                                            <span class="original-price">${this.formatPrice(book.price)}</span>
                                            <span class="discount-badge">-${book.discount_percent || this.calculateDiscountPercent(book.price, book.discount_price)}%</span>
                                            <span class="sale-price">${this.formatPrice(book.discount_price)}</span>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                            ${remaining > 0 ? `
                                <div class="text-center text-sm text-gray-500 py-2">
                                    +${remaining} sản phẩm khác
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            },

            renderDesktopProducts(books) {
                const displayBooks = books.slice(0, 4);
                const remaining = books.length - 4;
                
                return `
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        ${displayBooks.map(book => `
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                       
                                    <img src="${book?.thumb || '/images/default-book-cover.png'}" alt="${book.title}" class="w-20 h-20 object-cover rounded-lg">
                                        
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 truncate text-sm">${book.title}</p>
                                        <div class="flex items-center space-x-2 text-xs mt-1">
                                            <span class="original-price">${this.formatPrice(book.price)}</span>
                                            <span class="discount-badge">-${book.discount_percent || this.calculateDiscountPercent(book.price, book.discount_price)}%</span>
                                        </div>
                                        <div class="sale-price text-sm mt-1">${this.formatPrice(book.discount_price)}</div>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                        ${remaining > 0 ? `
                            <div class="bg-gray-100 p-3 rounded-lg flex items-center justify-center">
                                <span class="text-gray-600 text-sm">+${remaining} sản phẩm khác</span>
                            </div>
                        ` : ''}
                    </div>
                `;
            },

            filterFlashSales() {
                const searchQuery = document.getElementById('search-input').value.toLowerCase();
                const statusFilter = document.getElementById('status-filter').value;
                const sortBy = document.getElementById('sort-by').value;
                
                let filtered = [...this.flashSales];
                
                // Apply search filter
                if (searchQuery) {
                    filtered = filtered.filter(sale => 
                        sale.event_name.toLowerCase().includes(searchQuery)
                    );
                }
                
                // Apply status filter
                if (statusFilter) {
                    filtered = filtered.filter(sale => sale.status === statusFilter);
                }
                
                // Apply sorting
                filtered.sort((a, b) => {
                    switch (sortBy) {
                        case 'event_name':
                            return a.event_name.localeCompare(b.event_name);
                        case 'start_date':
                            return new Date(a.start_date) - new Date(b.start_date);
                        case 'end_date':
                            return new Date(a.end_date) - new Date(b.end_date);
                        default:
                            return new Date(b.created_at) - new Date(a.created_at);
                    }
                });
                
                // Render filtered results
                const container = document.getElementById('flash-sales-list');
                const emptyState = document.getElementById('empty-state');
                
                if (filtered.length === 0) {
                    container.classList.add('hidden');
                    emptyState.classList.remove('hidden');
                } else {
                    container.classList.remove('hidden');
                    emptyState.classList.add('hidden');
                    container.innerHTML = filtered.map(sale => this.renderFlashSaleCard(sale)).join('');
                }
            },

            // Modal Management
            openCreateModal() {
                this.currentEditingId = null;
                this.clearForm();
                this.saleProducts = [];
                this.updateModalTitle();
                this.renderSaleProducts();
                document.getElementById('flash-sale-modal').classList.remove('hidden');
            },

            closeModal() {
                document.getElementById('flash-sale-modal').classList.add('hidden');
                document.getElementById('modal-message').classList.add('hidden');
            },

            updateModalTitle() {
                const title = this.currentEditingId ? 'Chỉnh sửa Flash Sale' : 'Tạo Flash Sale mới';
                document.getElementById('modal-title').innerHTML = `
                    <i class="fas fa-bolt text-orange-500 mr-2"></i>
                    ${title}
                `;
                
                const activateText = this.currentEditingId ? 'Cập nhật' : 'Tạo và Kích hoạt';
                document.getElementById('activate-text').textContent = activateText;
            },

            clearForm() {
                document.getElementById('event-name').value = '';
                document.getElementById('start-date').value = '';
                document.getElementById('end-date').value = '';
                document.getElementById('status-toggle').checked = false;
            },

            // Flash Sale Management
            async editFlashSale(id) {
                try {
                    const sale = await this.loadFlashSaleDetail(id);
                    
                    this.currentEditingId = id;
                    document.getElementById('event-name').value = sale.event_name;
                    document.getElementById('start-date').value = this.formatDateForInput(sale.start_date);
                    document.getElementById('end-date').value = this.formatDateForInput(sale.end_date);
                    document.getElementById('status-toggle').checked = sale.status === 'active';
                    
                    this.saleProducts = sale.books || [];
                    this.updateModalTitle();
                    this.renderSaleProducts();
                    document.getElementById('flash-sale-modal').classList.remove('hidden');
                } catch (error) {
                    console.error('Error loading flash sale:', error);
                    this.showMessage('Lỗi khi tải thông tin flash sale', 'error');
                }
            },

            async duplicateFlashSale(id) {
                try {
                    const sale = await this.loadFlashSaleDetail(id);
                    
                    this.currentEditingId = null;
                    document.getElementById('event-name').value = sale.event_name + ' - Copy';
                    document.getElementById('start-date').value = '';
                    document.getElementById('end-date').value = '';
                    document.getElementById('status-toggle').checked = false;
                    
                    this.saleProducts = (sale.books || []).map(book => ({
                        ...book,
                        quantity_limit: book.quantity_limit || 50,
                        sold_quantity: 0
                    }));
                    
                    this.updateModalTitle();
                    this.renderSaleProducts();
                    document.getElementById('flash-sale-modal').classList.remove('hidden');
                } catch (error) {
                    console.error('Error duplicating flash sale:', error);
                    this.showMessage('Lỗi khi nhân bản flash sale', 'error');
                }
            },

            async deleteFlashSale(id) {
                if (!confirm('Bạn có chắc chắn muốn xóa flash sale này? Tất cả sản phẩm sẽ được reset giá.')) {
                    return;
                }
                
                try {
                    await this.deleteEvent(id);
                    this.showMessage('Đã xóa flash sale thành công', 'success');
                    await this.loadFlashSales();
                    await this.loadAvailableBooks();
                } catch (error) {
                    console.error('Error deleting flash sale:', error);
                    this.showMessage('Lỗi khi xóa flash sale: ' + (error.response?.data?.message || error.message), 'error');
                }
            },

            async saveDraft() {
                await this.saveFlashSale('inactive');
            },

            async activateFlashSale() {
                await this.saveFlashSale('active');
            },

            async saveFlashSale(status = null) {
                try {
                    this.setSaving(true);
                    
                    const formData = {
                        event_name: document.getElementById('event-name').value,
                        start_date: document.getElementById('start-date').value,
                        end_date: document.getElementById('end-date').value,
                        status: status || (document.getElementById('status-toggle').checked ? 'active' : 'inactive')
                    };
                    
                    let eventId = this.currentEditingId;
                    let response;
                    
                    if (this.currentEditingId) {
                        // Update existing event
                        response = await this.updateEvent(this.currentEditingId, formData);
                        this.showModalMessage('Cập nhật flash sale thành công', 'success');
                    } else {
                        // Create new event
                        response = await this.createEvent(formData);
                        eventId = response.event_id;
                        this.currentEditingId = eventId;
                        this.showModalMessage('Tạo flash sale thành công', 'success');
                        
                        // Add products to new event
                        if (eventId && this.saleProducts.length > 0) {
                            await this.saveProductsToNewEvent(eventId);
                        }
                    }
                    
                    setTimeout(() => {
                        this.closeModal();
                        this.loadFlashSales();
                        this.loadAvailableBooks();
                    }, 1500);
                    
                } catch (error) {
                    console.error('Error saving flash sale:', error);
                    this.showModalMessage('Lỗi khi lưu flash sale: ' + (error.response?.data?.message || error.message), 'error');
                } finally {
                    this.setSaving(false);
                }
            },

            async saveProductsToNewEvent(eventId) {
                try {
                    for (const product of this.saleProducts) {
                        const productData = {
                            book_id: product.id,                    
                            discount_percent: product.discount_percent,
                            quantity_limit: product.quantity_limit || 50
                        };
                        
                        await this.addBookToEvent(eventId, productData);
                    }
                } catch (error) {
                    console.error('Error saving products to new event:', error);
                    throw error;
                }
            },

            // ✅ Fixed setSaving method
            setSaving(saving) {
                const draftBtn = document.getElementById('save-draft-btn');
                const activateBtn = document.getElementById('activate-btn');
                const draftSpinner = document.getElementById('save-draft-spinner');
                const activateSpinner = document.getElementById('activate-spinner');
                
                if (saving) {
                    draftBtn.disabled = true;
                    activateBtn.disabled = true;
                    draftSpinner.classList.remove('hidden');
                    activateSpinner.classList.remove('hidden');
                } else {
                    draftBtn.disabled = false;
                    activateBtn.disabled = false;
                    draftSpinner.classList.add('hidden');
                    activateSpinner.classList.add('hidden');
                }
            },

            // Product Management
            async openAddProductModal() {
                if (this.availableBooks.length === 0) {
                    await this.loadAvailableBooks();
                }
                document.getElementById('add-product-modal').classList.remove('hidden');
            },

            closeAddProductModal() {
                document.getElementById('add-product-modal').classList.add('hidden');
                document.getElementById('product-search').value = '';
                this.renderAvailableProducts();
            },

            renderAvailableProducts() {
                const container = document.getElementById('available-products');
                const noProducts = document.getElementById('no-products');
                
                if (this.availableBooks.length === 0) {
                    container.classList.add('hidden');
                    noProducts.classList.remove('hidden');
                    return;
                }
                
                container.classList.remove('hidden');
                noProducts.classList.add('hidden');
                
                container.innerHTML = this.availableBooks.map(book => `
                    <div onclick="flashSaleManager.selectProduct(${JSON.stringify(book).replace(/"/g, '&quot;')})"
                         class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow-md transition-all duration-200 cursor-pointer">
                        <div class="flex items-start space-x-3">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                  <img src="${book?.cover_image || '/images/default-book-cover.png'}" alt="${book.title}" class="w-20 h-20 object-cover rounded-lg">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-gray-900 truncate">${book.title}</h4>
                                <p class="text-sm text-gray-600 mt-1">${this.formatPrice(book.price)}</p>
                                <div class="flex items-center mt-2 text-xs text-gray-500">
                                    <i class="fas fa-warehouse mr-1"></i>
                                    Kho: ${book.stock || 0}
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            },

            filterAvailableProducts() {
                const searchQuery = document.getElementById('product-search').value.toLowerCase();
                const filtered = this.availableBooks.filter(book => 
                    book.title.toLowerCase().includes(searchQuery)
                );
                
                const container = document.getElementById('available-products');
                container.innerHTML = filtered.map(book => `
                    <div onclick="flashSaleManager.selectProduct(${JSON.stringify(book).replace(/"/g, '&quot;')})"
                         class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow-md transition-all duration-200 cursor-pointer">
                        <div class="flex items-start space-x-3">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <img src="${book?.cover_image || '/images/default-book-cover.png'}" alt="${book.title}" class="w-20 h-20 object-cover rounded-lg">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-gray-900 truncate">${book.title}</h4>
                                <p class="text-sm text-gray-600 mt-1">${this.formatPrice(book.price)}</p>
                                <div class="flex items-center mt-2 text-xs text-gray-500">
                                    <i class="fas fa-warehouse mr-1"></i>
                                    Kho: ${book.stock || 0}
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            },

            selectProduct(product) {
                this.selectedProduct = product;
                document.getElementById('discount-percent').value = '';
                document.getElementById('quantity-limit').value = '50';
                document.getElementById('price-preview').classList.add('hidden');
                this.closeAddProductModal();
                this.showProductConfigModal();
            },

            showProductConfigModal() {
                document.getElementById('selected-product-info').innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <img src="${this.selectedProduct?.cover_image || '/images/default-book-cover.png'}" alt="this.selectedProduct.title}" class="w-20 h-20 object-cover rounded-lg">
                        </div>
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">${this.selectedProduct.title}</h4>
                            <p class="text-sm text-gray-600">Giá gốc: ${this.formatPrice(this.selectedProduct.price)}</p>
                        </div>
                    </div>
                `;
                document.getElementById('product-config-modal').classList.remove('hidden');
            },

            closeProductConfigModal() {
                document.getElementById('product-config-modal').classList.add('hidden');
                this.selectedProduct = null;
                document.getElementById('discount-percent').value = '';
                document.getElementById('quantity-limit').value = '50';
                document.getElementById('price-preview').classList.add('hidden');
            },

            updatePricePreview() {
                const discountPercent = parseFloat(document.getElementById('discount-percent').value);
                
                if (this.selectedProduct && discountPercent) {
                    const originalPrice = parseFloat(this.selectedProduct.price);
                    const discount = originalPrice * discountPercent / 100;
                    const salePrice = originalPrice - discount;
                    
                    document.getElementById('preview-original-price').textContent = this.formatPrice(originalPrice);
                    document.getElementById('preview-discount-percent').textContent = discountPercent;
                    document.getElementById('preview-discount-amount').textContent = '-' + this.formatPrice(discount);
                    document.getElementById('preview-sale-price').textContent = this.formatPrice(salePrice);
                    document.getElementById('price-preview').classList.remove('hidden');
                } else {
                    document.getElementById('price-preview').classList.add('hidden');
                }
            },

            async addProductToSale() {
                const discountPercent = parseFloat(document.getElementById('discount-percent').value);
                const quantityLimit = parseInt(document.getElementById('quantity-limit').value);
                
                if (!this.selectedProduct || !discountPercent || !quantityLimit) {
                    this.showMessage('Vui lòng điền đầy đủ thông tin', 'error');
                    return;
                }
                
                this.setProductSaving(true);
                
                try {
                    if (this.currentEditingId) {
                        const productData = {
                            book_id: this.selectedProduct.id,
                            discount_percent: discountPercent,
                            quantity_limit: quantityLimit
                        };
                        
                        await this.addBookToEvent(this.currentEditingId, productData);
                        this.showMessage('Thêm sản phẩm vào flash sale thành công', 'success');
                        
                        // Reload event detail để cập nhật danh sách sản phẩm
                        const eventDetail = await this.loadFlashSaleDetail(this.currentEditingId);
                        this.saleProducts = eventDetail.books || [];
                        
                        // Refresh available books
                        await this.loadAvailableBooks();
                        
                    } else {
                        // Nếu đang tạo event mới, thêm vào danh sách tạm
                        const originalPrice = parseFloat(this.selectedProduct.price);
                        const discountPrice = originalPrice - (originalPrice * discountPercent / 100);
                        
                        const productData = {
                            ...this.selectedProduct,
                            discount_percent: discountPercent,
                            discount_price: discountPrice,
                            quantity_limit: quantityLimit,
                            sold_quantity: 0
                        };
                        
                        // Check if product already exists
                        const existingIndex = this.saleProducts.findIndex(p => p.id === this.selectedProduct.id);
                        if (existingIndex !== -1) {
                            this.saleProducts[existingIndex] = productData;
                            this.showMessage('Cập nhật sản phẩm thành công', 'success');
                        } else {
                            this.saleProducts.push(productData);
                            this.showMessage('Thêm sản phẩm thành công', 'success');
                        }
                    }
                    
                    this.renderSaleProducts();
                    this.closeProductConfigModal();
                    
                } catch (error) {
                    console.error('Error adding product to sale:', error);
                    this.showMessage('Lỗi khi thêm sản phẩm: ' + (error.response?.data?.message || error.message), 'error');
                } finally {
                    this.setProductSaving(false);
                }
            },

            setProductSaving(saving) {
                const btn = document.getElementById('add-product-btn');
                const spinner = document.getElementById('add-product-spinner');
                
                if (saving) {
                    btn.disabled = true;
                    spinner.classList.remove('hidden');
                } else {
                    btn.disabled = false;
                    spinner.classList.add('hidden');
                }
            },

            renderSaleProducts() {
                const emptyDiv = document.getElementById('products-empty');
                const tableDiv = document.getElementById('products-table');
                const tbody = document.getElementById('products-tbody');
                
                if (this.saleProducts.length === 0) {
                    emptyDiv.classList.remove('hidden');
                    tableDiv.classList.add('hidden');
                    return;
                }
                
                emptyDiv.classList.add('hidden');
                tableDiv.classList.remove('hidden');
                
                tbody.innerHTML = this.saleProducts.map(product => `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-book text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">${product.title}</div>
                                    <div class="text-sm text-gray-500">ID: ${product.id}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${this.formatPrice(product.price)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="discount-badge">${product.discount_percent}%</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm sale-price">${this.formatPrice(product.discount_price)}</div>
                            <div class="text-xs text-gray-500">Tiết kiệm: ${this.formatPrice(product.price - product.discount_price)}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>Giới hạn: ${product.quantity_limit || 50}</div>
                            <div class="text-gray-500">Đã bán: ${product.sold_quantity || 0}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            ${this.currentEditingId ? 
                                `<button onclick="flashSaleManager.removeProductFromSale(${this.currentEditingId}, ${product.id})" 
                                         class="text-red-600 hover:text-red-900 transition-colors duration-200">
                                    <i class="fas fa-trash"></i>
                                </button>` :
                                `<button onclick="flashSaleManager.removeProduct(${product.id})" 
                                         class="text-red-600 hover:text-red-900 transition-colors duration-200">
                                    <i class="fas fa-trash"></i>
                                </button>`
                            }
                        </td>
                    </tr>
                `).join('');
            },

            removeProduct(productId) {
                this.saleProducts = this.saleProducts.filter(p => p.id !== productId);
                this.renderSaleProducts();
                this.showMessage('Đã xóa sản phẩm khỏi flash sale', 'success');
            },

            async removeProductFromSale(eventId, bookId) {
                if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi flash sale?')) {
                    return;
                }
                
                try {
                    await this.removeBookFromEvent(eventId, bookId);
                    this.showMessage('Đã xóa sản phẩm khỏi flash sale', 'success');
                    
                    // Reload event detail để cập nhật danh sách
                    if (this.currentEditingId === eventId) {
                        const eventDetail = await this.loadFlashSaleDetail(eventId);
                        this.saleProducts = eventDetail.books || [];
                        this.renderSaleProducts();
                    }
                    
                    await this.loadFlashSales();
                    await this.loadAvailableBooks();
                } catch (error) {
                    console.error('Error removing product:', error);
                    this.showMessage('Lỗi khi xóa sản phẩm: ' + (error.response?.data?.message || error.message), 'error');
                }
            },

            // Utility functions
            formatPrice(price) {
                return parseFloat(price).toLocaleString('vi-VN') + '₫';
            },

            formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('vi-VN', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },

            formatDateForInput(dateString) {
                const date = new Date(dateString);
                return date.toISOString().slice(0, 16);
            },

            getStatusClass(status) {
                switch (status.toLowerCase()) {
                    case 'active': return 'status-live';
                    case 'scheduled': return 'status-scheduled';
                    case 'inactive': return 'status-inactive';
                    default: return 'status-inactive';
                }
            },

            getStatusText(status) {
                switch (status.toLowerCase()) {
                    case 'active': return 'Đang hoạt động';
                    case 'scheduled': return 'Sắp diễn ra';
                    case 'inactive': return 'Không hoạt động';
                    default: return 'Không hoạt động';
                }
            },

            calculateDiscountPercent(originalPrice, discountPrice) {
                if (!discountPrice || discountPrice >= originalPrice) return 0;
                return Math.round(((originalPrice - discountPrice) / originalPrice) * 100);
            }
        };

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            flashSaleManager.init();
        });

        // Handle form submission
        document.getElementById('flash-sale-form').addEventListener('submit', function(e) {
            e.preventDefault();
            flashSaleManager.activateFlashSale();
        });

        // Handle product config form submission
        document.getElementById('product-config-form').addEventListener('submit', function(e) {
            e.preventDefault();
            flashSaleManager.addProductToSale();
        });
    </script>
</body>
</html>
@endsection