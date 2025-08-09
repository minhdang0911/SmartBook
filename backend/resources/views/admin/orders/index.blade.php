@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/antd/5.0.0/reset.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --primary: #0f172a;
            --secondary: #1e293b;
            --accent: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --surface: #ffffff;
            --border: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            color: var(--primary);
        }

        .glass-effect {
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .animated-gradient {
            background: linear-gradient(-45deg, #667eea, #764ba2, #3b82f6, #1e40af);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .status-indicator {
            position: relative;
            overflow: hidden;
        }

        .status-indicator::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .modal-backdrop {
            background: rgba(0, 0, 0, 0.6);
           
        }

        .slide-up {
            animation: slideUp 0.3s ease-out forwards;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pulse-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .neo-button {
            background: linear-gradient(145deg, #ffffff, #f1f5f9);
            box-shadow: 5px 5px 15px #d1d5db, -5px -5px 15px #ffffff;
            border: none;
            transition: all 0.3s ease;
        }

        .neo-button:hover {
            box-shadow: 2px 2px 8px #d1d5db, -2px -2px 8px #ffffff;
        }

        .neo-button:active {
            box-shadow: inset 2px 2px 8px #d1d5db, inset -2px -2px 8px #ffffff;
        }

        @media (max-width: 768px) {
            .mobile-optimized {
                padding: 1rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Main Container -->
    <div class="min-h-screen">
        <!-- Header Section -->
        <div class="bg-white border-b border-gray-200 sticky top-0 z-40 glass-effect">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-shopping-bag text-white text-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-900">Quản Lý Đơn Hàng</h1>
                      
                            </div>
                        </div>
                    </div>
                    
                   
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" id="stats-container">
                <div class="bg-white rounded-2xl p-6 hover-lift glass-effect">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Tổng đơn hàng</p>
                            <p class="text-3xl font-bold text-gray-900" id="total-orders">-</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl p-6 hover-lift glass-effect">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Chờ xử lý</p>
                            <p class="text-3xl font-bold text-orange-600" id="pending-orders">-</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-clock text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl p-6 hover-lift glass-effect">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Đang xử lý</p>
                            <p class="text-3xl font-bold text-blue-600" id="processing-orders">-</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-sync text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl p-6 hover-lift glass-effect">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Đã giao</p>
                            <p class="text-3xl font-bold text-green-600" id="delivered-orders">-</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Table Section -->
            <div class="bg-white rounded-2xl glass-effect overflow-hidden">
                <!-- Table Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Danh Sách Đơn Hàng</h2>
                            <p class="text-sm text-gray-600 mt-1">Quản lý tất cả đơn hàng của khách hàng</p>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <div class="relative">
                                <select id="payment-filter" class="pl-4 pr-10 py-2 bg-white border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer" onchange="filterOrders()">
                                    <option value="">Tất cả thanh toán</option>
                                    <option value="qr">QR Code</option>
                                    <option value="cod">COD</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                                </div>
                            </div>
                            
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors flex items-center space-x-2">
                                <i class="fas fa-download text-sm"></i>
                                <span class="text-sm font-medium">Export</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Khách hàng</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Thanh toán</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">COD</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tổng tiền</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Số SP</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ngày tạo</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="orders-table-body" class="divide-y divide-gray-200">
                            <tr>
                                <td colspan="9" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 font-medium">Đang tải dữ liệu...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
                    <div id="pagination-container" class="flex items-center justify-center space-x-2" style="display: none;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div id="orderModal" class="fixed inset-0 z-50 hidden">
         
        <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-2xl slide-up">
                <!-- Modal Header -->
                <div class="animated-gradient px-8 py-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold">Chi Tiết Đơn Hàng</h2>
                            <p class="text-white/80 text-sm mt-1">Thông tin chi tiết và quản lý đơn hàng</p>
                        </div>
                        <button onclick="closeModal()" class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors">
                            <i class="fas fa-times text-white"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-8 custom-scrollbar overflow-y-auto max-h-[70vh]">
                    <!-- Order Info Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div class="bg-gray-50 rounded-xl p-4 border-l-4 border-blue-500">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Mã đơn hàng</p>
                            <p class="text-lg font-bold text-gray-900" id="modal-order-id"></p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-4 border-l-4 border-green-500">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Khách hàng</p>
                            <p class="text-lg font-bold text-gray-900" id="modal-customer-name"></p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-4 border-l-4 border-purple-500">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Email</p>
                            <p class="text-sm font-medium text-gray-900" id="modal-customer-email"></p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-4 border-l-4 border-yellow-500">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Số điện thoại</p>
                            <p class="text-sm font-medium text-gray-900" id="modal-customer-phone"></p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-4 border-l-4 border-indigo-500">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Trạng thái</p>
                            <div id="modal-status"></div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-4 border-l-4 border-pink-500">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Thanh toán</p>
                            <p class="text-sm font-medium text-gray-900 uppercase" id="modal-payment"></p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-4 border-l-4 border-red-500">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Ngày tạo</p>
                            <p class="text-sm font-medium text-gray-900" id="modal-created-at"></p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-4 border-l-4 border-teal-500 md:col-span-2">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Địa chỉ giao hàng</p>
                            <p class="text-sm font-medium text-gray-900" id="modal-address"></p>
                        </div>
                    </div>

                    <!-- Pricing Info -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                        <div class="bg-blue-50 rounded-xl p-4 text-center">
                            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-2">Tổng tiền hàng</p>
                            <p class="text-xl font-bold text-blue-900" id="modal-price"></p>
                        </div>
                        <div class="bg-orange-50 rounded-xl p-4 text-center">
                            <p class="text-xs font-semibold text-orange-600 uppercase tracking-wide mb-2">Phí vận chuyển</p>
                            <p class="text-xl font-bold text-orange-900" id="modal-shipping-fee"></p>
                        </div>
                        <div class="bg-green-50 rounded-xl p-4 text-center">
                            <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-2">Tổng thanh toán</p>
                            <p class="text-2xl font-bold text-green-900" id="modal-total-price"></p>
                        </div>
                        <div class="bg-purple-50 rounded-xl p-4 text-center">
                            <p class="text-xs font-semibold text-purple-600 uppercase tracking-wide mb-2">Mã vận đơn</p>
                            <p class="text-lg font-bold text-purple-900" id="modal-shipping-code"></p>
                        </div>
                    </div>

                    <!-- Shipping Status -->
                    <div id="shipping-status-container" class="hidden mb-6">
                    </div>

                    <!-- Order Items -->
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-box-open text-blue-600 mr-3"></i>
                            Sản Phẩm Đã Đặt
                        </h3>
                        <div id="modal-items" class="space-y-4">
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-8 py-6 border-t border-gray-200 bg-gray-50/50 flex justify-end space-x-4">
                    <button id="create-shipping-btn" onclick="createShippingOrder()" class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all flex items-center space-x-2 neo-button">
                        <i class="fas fa-shipping-fast"></i>
                        <span>Tạo đơn ship</span>
                    </button>
                    <button onclick="closeModal()" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Đóng</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_BASE_URL = 'http://localhost:8000/api';
        let currentPage = 1;
        let allOrdersData = [];
        let filteredOrdersData = [];
        let currentOrderDetail = null;
        let currentFilter = '';
        const ITEMS_PER_PAGE = 10;

        document.addEventListener('DOMContentLoaded', function() {
            checkAuth();
            loadStats();
            loadAllOrders();
            initializeUserInfo();
        });

        async function checkAuth() {
            const token = localStorage.getItem('access_token');
            if (!token) {
                window.location.href = '/login';
                return;
            }
        }

        async function initializeUserInfo() {
            const userNameEl = document.getElementById('user-name');
            const token = localStorage.getItem('access_token');
            if (token) {
                try {
                    const response = await fetch(`${API_BASE_URL}/me`, {
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    userNameEl.textContent = data.user?.name || 'Admin';
                } catch (error) {
                    userNameEl.textContent = 'Admin';
                }
            }
        }

        async function loadStats() {
            try {
                const token = localStorage.getItem('access_token');
                const response = await fetch(`${API_BASE_URL}/orders/stats`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();

                if (data.success) {
                    const stats = data.data;
                    document.getElementById('total-orders').textContent = stats.total_orders || 0;
                    document.getElementById('pending-orders').textContent = stats.pending_orders || 0;
                    document.getElementById('processing-orders').textContent = stats.processing_orders || 0;
                    document.getElementById('delivered-orders').textContent = stats.delivered_orders || 0;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
                showError('Không thể tải thống kê');
            }
        }

        async function loadAllOrders() {
            try {
                showLoading();
                const token = localStorage.getItem('access_token');
                let allOrders = [];
                let page = 1;
                let hasMore = true;

                while (hasMore) {
                    const response = await fetch(`${API_BASE_URL}/admin/orders?page=${page}&per_page=100`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();

                    if (data.success && data.data.orders.length > 0) {
                        allOrders = allOrders.concat(data.data.orders);
                        hasMore = data.data.pagination.current_page < data.data.pagination.last_page;
                        page++;
                    } else {
                        hasMore = false;
                    }
                }

                allOrdersData = allOrders;
                applyCurrentFilter();
                
            } catch (error) {
                console.error('Error loading orders:', error);
                showError('Không thể tải danh sách đơn hàng');
            }
        }

        function filterOrders() {
            const filterValue = document.getElementById('payment-filter').value;
            currentFilter = filterValue;
            currentPage = 1;
            applyCurrentFilter();
        }

        function applyCurrentFilter() {
            if (!currentFilter) {
                filteredOrdersData = allOrdersData;
            } else {
                filteredOrdersData = allOrdersData.filter(order => order.payment === currentFilter);
            }
            renderOrders();
            renderPagination();
        }

        function renderOrders() {
            const tbody = document.getElementById('orders-table-body');

            if (filteredOrdersData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                                </div>
                                <p class="text-gray-500 font-medium">${currentFilter ? 'Không có đơn hàng nào với phương thức thanh toán đã chọn' : 'Không có đơn hàng nào'}</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
            const endIndex = startIndex + ITEMS_PER_PAGE;
            const pageData = filteredOrdersData.slice(startIndex, endIndex);

            const rows = pageData.map(order => `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <span class="text-sm font-bold text-blue-600">#${order.id}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-semibold text-sm">${order.user.name.charAt(0).toUpperCase()}</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">${order.user.name}</p>
                                <p class="text-xs text-gray-500">${order.user.email}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        ${getStatusBadge(order.status)}
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${order.payment === 'qr' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800'}">
                            <i class="fas ${order.payment === 'qr' ? 'fa-qrcode' : 'fa-money-bill-wave'} mr-1"></i>
                            ${order.payment.toUpperCase()}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        ${getCodDisplay(order.payment, order.total_price)}
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-lg font-bold text-gray-900">${formatPrice(order.total_price)}đ</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            ${order.total_items} SP
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm">
                            <p class="font-medium text-gray-900">${formatDate(order.created_at).split(' ')[0]}</p>
                            <p class="text-gray-500">${formatDate(order.created_at).split(' ')[1]}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="showOrderDetail(${order.id})" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-eye mr-2"></i>
                            Chi tiết
                        </button>
                    </td>
                </tr>
            `).join('');

            tbody.innerHTML = rows;
        }

        function getStatusBadge(status) {
            const statusConfig = {
                'ready_to_pick': { color: 'orange', icon: 'fa-clock', text: 'Chờ lấy hàng' },
                'picking': { color: 'blue', icon: 'fa-hand-paper', text: 'Đang lấy hàng' },
                'money_collect_picking': { color: 'purple', icon: 'fa-money-bill', text: 'Thu tiền khi lấy' },
                'picked': { color: 'green', icon: 'fa-check', text: 'Đã lấy hàng' },
                'storing': { color: 'indigo', icon: 'fa-warehouse', text: 'Đã nhập kho' },
                'delivering': { color: 'blue', icon: 'fa-shipping-fast', text: 'Đang giao hàng' },
                'delivered': { color: 'green', icon: 'fa-check-circle', text: 'Đã giao hàng' },
                'delivery_fail': { color: 'red', icon: 'fa-exclamation-triangle', text: 'Giao không thành công' },
                'cancelled': { color: 'red', icon: 'fa-times-circle', text: 'Đã hủy' }
            };

            const config = statusConfig[status] || { color: 'gray', icon: 'fa-question', text: status };
            
            return `
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-${config.color}-100 text-${config.color}-800 status-indicator">
                    <i class="fas ${config.icon} mr-1"></i>
                    ${config.text}
                </span>
            `;
        }

        function getCodDisplay(paymentMethod, totalPrice) {
            if (paymentMethod === 'qr') {
                return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Không thu</span>';
            } else if (paymentMethod === 'cod') {
                return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800"><i class="fas fa-money-bill-wave mr-1"></i>${formatPrice(totalPrice)}đ</span>`;
            }
            return '-';
        }

        function renderPagination() {
            const container = document.getElementById('pagination-container');
            const totalPages = Math.ceil(filteredOrdersData.length / ITEMS_PER_PAGE);
            
            if (totalPages <= 1) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'flex';
            let paginationHTML = '';

            // Previous button
            paginationHTML += `
                <button onclick="changePage(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''} 
                        class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <i class="fas fa-chevron-left"></i>
                </button>
            `;

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    paginationHTML += `
                        <button onclick="changePage(${i})" 
                                class="px-3 py-2 text-sm font-medium rounded-lg transition-colors ${i === currentPage ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 hover:bg-gray-50 text-gray-700'}">
                            ${i}
                        </button>
                    `;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    paginationHTML += '<span class="px-2 py-2 text-gray-400">...</span>';
                }
            }

            // Next button
            paginationHTML += `
                <button onclick="changePage(${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''} 
                        class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <i class="fas fa-chevron-right"></i>
                </button>
            `;

            container.innerHTML = paginationHTML;
        }

        function changePage(page) {
            const totalPages = Math.ceil(filteredOrdersData.length / ITEMS_PER_PAGE);
            if (page < 1 || page > totalPages) return;
            
            currentPage = page;
            renderOrders();
            renderPagination();
        }

        async function showOrderDetail(orderId) {
            try {
                const token = localStorage.getItem('access_token');
                let order = allOrdersData.find(o => o.id === orderId);

                if (!order) {
                    const response = await fetch(`${API_BASE_URL}/orders/${orderId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        order = data.data;
                    } else {
                        throw new Error('Không thể tải chi tiết đơn hàng');
                    }
                }

                currentOrderDetail = order;

                // Populate modal data
                document.getElementById('modal-order-id').textContent = '#' + order.id;
                document.getElementById('modal-customer-name').textContent = order.user.name;
                document.getElementById('modal-customer-email').textContent = order.user.email;
                document.getElementById('modal-customer-phone').textContent = order.phone || 'Chưa cung cấp';
                document.getElementById('modal-status').innerHTML = getStatusBadge(order.status);
                document.getElementById('modal-payment').textContent = order.payment.toUpperCase();
                document.getElementById('modal-created-at').textContent = formatDate(order.created_at);
                document.getElementById('modal-address').textContent = order.address;
                document.getElementById('modal-price').textContent = formatPrice(order.price) + 'đ';
                document.getElementById('modal-shipping-fee').textContent = formatPrice(order.shipping_fee) + 'đ';
                document.getElementById('modal-total-price').textContent = formatPrice(order.total_price) + 'đ';
                document.getElementById('modal-shipping-code').textContent = order.shipping_code || 'Chưa có';

                updateShippingButton(order);
                hideShippingStatus();

                // Render order items
                const itemsContainer = document.getElementById('modal-items');
                itemsContainer.innerHTML = '';
                order.items.forEach((item, index) => {
                    const itemElement = document.createElement('div');
                    itemElement.className = 'bg-white border border-gray-200 rounded-xl p-6 hover-lift';
                    itemElement.innerHTML = `
                        <div class="flex items-start space-x-4">
                            <div class="w-16 h-20 bg-gradient-to-r from-blue-100 to-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-book text-blue-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-3">
                                    <h4 class="text-lg font-bold text-gray-900">${item.book.title}</h4>
                                    <span class="text-xl font-bold text-blue-600">${formatPrice(item.price)}đ</span>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-500 font-medium">Tác giả</p>
                                        <p class="text-gray-900 font-semibold">${item.book.author}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 font-medium">Thể loại</p>
                                        <p class="text-gray-900 font-semibold">${item.book.category}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 font-medium">Số lượng</p>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                            ${item.quantity}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 font-medium">Giá gốc</p>
                                        <p class="text-gray-900 font-semibold">${formatPrice(item.book.price)}đ</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    itemsContainer.appendChild(itemElement);
                });

                // Show modal
                document.getElementById('orderModal').classList.remove('hidden');

            } catch (error) {
                console.error('Error loading order detail:', error);
                alert('Không thể tải chi tiết đơn hàng: ' + error.message);
            }
        }

        function updateShippingButton(order) {
            const btn = document.getElementById('create-shipping-btn');
            const canCreateShipping = ['pending','ready_to_pick', 'picking', 'picked'].includes(order.status);
            
            if (canCreateShipping) {
                btn.disabled = false;
                btn.className = 'px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all flex items-center space-x-2 neo-button';
                btn.innerHTML = '<i class="fas fa-shipping-fast"></i><span>Tạo đơn ship</span>';
            } else {
                btn.disabled = true;
                btn.className = 'px-6 py-3 bg-gray-300 text-gray-500 rounded-xl cursor-not-allowed flex items-center space-x-2';
                btn.innerHTML = '<i class="fas fa-shipping-fast"></i><span>Không thể tạo đơn ship</span>';
            }
        }

        async function createShippingOrder() {
            if (!currentOrderDetail) {
                alert('Không có thông tin đơn hàng');
                return;
            }

            const btn = document.getElementById('create-shipping-btn');
            const originalText = btn.innerHTML;
            
            try {
                btn.disabled = true;
                btn.innerHTML = '<div class="loading-spinner w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div><span>Đang tạo đơn ship...</span>';

                const token = localStorage.getItem('access_token');
                const shippingData = {
                    customer_name: currentOrderDetail.user.name,
                    customer_phone: currentOrderDetail?.phone || '0000000000'
                };

                const response = await fetch(`${API_BASE_URL}/orders/${currentOrderDetail.id}/shipping`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(shippingData)
                });

                const data = await response.json();

                if (data.success || response.ok) {
                    showShippingStatus('Đơn ship đã được tạo thành công!', 'created');
                    btn.innerHTML = '<i class="fas fa-check-circle"></i><span>Đã tạo đơn ship</span>';
                    btn.className = 'px-6 py-3 bg-green-600 text-white rounded-xl flex items-center space-x-2';
                    
                    setTimeout(() => {
                        loadAllOrders();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Không thể tạo đơn ship');
                }

            } catch (error) {
                console.error('Error creating shipping order:', error);
                showShippingStatus(`Lỗi tạo đơn ship: ${error.message}`, 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        function showShippingStatus(message, type) {
            const container = document.getElementById('shipping-status-container');
            container.classList.remove('hidden');
            container.className = `p-4 rounded-xl mb-6 flex items-center space-x-3 ${type === 'created' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'}`;
            container.innerHTML = `
                <i class="fas ${type === 'created' ? 'fa-check-circle' : 'fa-exclamation-triangle'} text-xl"></i>
                <span class="font-medium">${message}</span>
            `;
            
            if (type === 'created') {
                setTimeout(() => {
                    hideShippingStatus();
                }, 5000);
            }
        }

        function hideShippingStatus() {
            const container = document.getElementById('shipping-status-container');
            container.classList.add('hidden');
        }

        function closeModal() {
            document.getElementById('orderModal').classList.add('hidden');
            currentOrderDetail = null;
            hideShippingStatus();
        }

        function formatPrice(price) {
            return new Intl.NumberFormat('vi-VN').format(parseFloat(price));
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('vi-VN', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function showLoading() {
            const tbody = document.getElementById('orders-table-body');
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">Đang tải dữ liệu...</p>
                        </div>
                    </td>
                </tr>
            `;
        }

        function showError(message) {
            const tbody = document.getElementById('orders-table-body');
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                            </div>
                            <p class="text-red-600 font-medium mb-4">${message}</p>
                            <button onclick="loadAllOrders()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
                                <i class="fas fa-redo"></i>
                                <span>Thử lại</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }

        // Event listeners
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Logout functionality
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.logout-btn').forEach(btn => {
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    logout();
                });
            });
        });

        function logout() {
            localStorage.removeItem('access_token');
            window.location.href = '/login';
        }
    </script>
</body>
</html>
@endsection