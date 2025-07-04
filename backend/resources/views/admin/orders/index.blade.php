@extends('layouts.app')

@section('content')
   <!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/antd/5.0.0/reset.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
         * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
 
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            color:#333
        }

        .page-description {
            font-size: 1.1rem;
            opacity: 0.9;
               color:#333
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1890ff;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1rem;
            color: #666;
            font-weight: 500;
        }

        .orders-table {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
        }

        .filter-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .filter-label {
            font-weight: 500;
            color: #666;
        }

        .filter-select {
            padding: 8px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: #1890ff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-tag {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-ready_to_pick { background: #fff2e8; color: #fa8c16; }
        .status-picking { background: #e6f7ff; color: #1890ff; }
        .status-picked { background: #f6ffed; color: #52c41a; }
        .status-delivering { background: #e6f7ff; color: #1890ff; }
        .status-delivered { background: #f6ffed; color: #52c41a; }
        .status-cancelled { background: #fff2f0; color: #ff4d4f; }

        .cod-tag {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .cod-collect {
            background: #fff2e8;
            color: #fa8c16;
        }

        .cod-none {
            background: #f6ffed;
            color: #52c41a;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #1890ff;
            color: white;
        }

        .btn-primary:hover {
            background: #40a9ff;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #52c41a;
            color: white;
        }

        .btn-success:hover {
            background: #73d13d;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .pagination button:hover:not(:disabled) {
            background: #1890ff;
            color: white;
            border-color: #1890ff;
        }

        .pagination button.active {
            background: #1890ff;
            color: white;
            border-color: #1890ff;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            margin: 2% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .close {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.3s ease;
        }

        .close:hover {
            background: rgba(255,255,255,0.2);
        }

        .modal-body {
            padding: 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #1890ff;
        }

        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .info-value {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #1890ff;
            padding-bottom: 10px;
        }

        .item-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #52c41a;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .item-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .item-price {
            font-size: 16px;
            font-weight: 600;
            color: #1890ff;
        }

        .item-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            font-size: 14px;
            color: #666;
        }

        .modal-actions {
            padding: 20px 30px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .shipping-status {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .shipping-status.created {
            background: #f6ffed;
            color: #52c41a;
            border: 1px solid #b7eb8f;
        }

        .shipping-status.error {
            background: #fff2f0;
            color: #ff4d4f;
            border: 1px solid #ffccc7;
        }

        .loading-spinner {
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #1890ff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
            }
            
            .orders-table {
                padding: 15px;
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
            
            .modal-content {
                width: 95%;
                margin: 5% auto;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Quản Lý Đơn Hàng</h1>
            <p class="page-description">Theo dõi và quản lý tất cả đơn hàng của khách hàng</p>
        </div>

        <!-- Statistics -->
        <div class="stats-row" id="stats-container">
            <div class="stat-card">
                <div class="stat-number" id="total-orders">-</div>
                <div class="stat-label">Tổng đơn hàng</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="pending-orders">-</div>
                <div class="stat-label">Chờ xử lý</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="processing-orders">-</div>
                <div class="stat-label">Đang xử lý</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="delivered-orders">-</div>
                <div class="stat-label">Đã giao</div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="orders-table">
            <div class="table-header">
                <h2 class="table-title">Danh Sách Đơn Hàng</h2>
                <div class="filter-section">
                    <label for="payment-filter" class="filter-label">Lọc theo thanh toán:</label>
                    <select id="payment-filter" class="filter-select" onchange="filterOrders()">
                        <option value="">Tất cả</option>
                        <option value="qr">QR Code</option>
                        <option value="cod">COD</option>
                    </select>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                        <th>COD</th>
                        <th>Tổng tiền</th>
                        <th>Số sản phẩm</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body">
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: #8c8c8c;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                            Đang tải dữ liệu...
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination" id="pagination-container" style="display: none;">
            </div>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Chi Tiết Đơn Hàng</h2>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Mã đơn hàng</div>
                        <div class="info-value" id="modal-order-id"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Khách hàng</div>
                        <div class="info-value" id="modal-customer-name"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value" id="modal-customer-email"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Trạng thái</div>
                        <div class="info-value" id="modal-status"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phương thức thanh toán</div>
                        <div class="info-value" id="modal-payment"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Ngày tạo</div>
                        <div class="info-value" id="modal-created-at"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Địa chỉ giao hàng</div>
                        <div class="info-value" id="modal-address"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tổng tiền hàng</div>
                        <div class="info-value" id="modal-price"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phí vận chuyển</div>
                        <div class="info-value" id="modal-shipping-fee"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tổng thanh toán</div>
                        <div class="info-value" id="modal-total-price" style="color: #1890ff; font-size: 16px;"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Mã vận đơn</div>
                        <div class="info-value" id="modal-shipping-code" style="color: #1890ff; font-size: 16px;"></div>
                    </div>
                </div>

                <!-- Shipping Status -->
                <div id="shipping-status-container" style="display: none;">
                </div>

                <div class="items-section">
                    <h3 class="section-title">Sản Phẩm Đã Đặt</h3>
                    <div id="modal-items"></div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-success" id="create-shipping-btn" onclick="createShippingOrder()">
                    <i class="fas fa-truck"></i> Tạo đơn ship
                </button>
                <button class="btn btn-primary" onclick="closeModal()">
                    <i class="fas fa-times"></i> Đóng
                </button>
            </div>
        </div>
    </div>

    <script>
        const API_BASE_URL = 'http://localhost:8000/api';
        let currentPage = 1;
        let allOrdersData = []; // Lưu trữ tất cả dữ liệu
        let filteredOrdersData = []; // Dữ liệu sau khi lọc
        let currentOrderDetail = null;
        let currentFilter = '';
        let currentUser = null;
        const ITEMS_PER_PAGE = 10;

        document.addEventListener('DOMContentLoaded', function() {
            checkAuth();
            loadUserInfo();
            loadStats();
            loadAllOrders();
        });

        async function checkAuth() {
            const token = localStorage.getItem('access_token');
            if (!token) {
                window.location.href = '/login';
                return;
            }
        }

        async function loadUserInfo() {
            try {
                const token = localStorage.getItem('access_token');
                const response = await fetch(`${API_BASE_URL}/me`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    currentUser = data.data || data;
                    console.log('User info:', currentUser);
                } else {
                    console.error('Failed to load user info');
                    if (response.status === 401) {
                        localStorage.removeItem('access_token');
                        window.location.href = '/login';
                    }
                }
            } catch (error) {
                console.error('Error loading user info:', error);
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

                // Lấy tất cả đơn hàng từ tất cả các trang
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
            currentPage = 1; // Reset về trang 1 khi lọc
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
                        <td colspan="9" style="text-align: center; padding: 40px; color: #8c8c8c;">
                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                            ${currentFilter ? 'Không có đơn hàng nào với phương thức thanh toán đã chọn' : 'Không có đơn hàng nào'}
                        </td>
                    </tr>
                `;
                return;
            }

            // Tính toán dữ liệu cho trang hiện tại
            const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
            const endIndex = startIndex + ITEMS_PER_PAGE;
            const pageData = filteredOrdersData.slice(startIndex, endIndex);

            const rows = pageData.map(order => `
                <tr>
                    <td>#${order.id}</td>
                    <td>
                        <div>
                            <strong>${order.user.name}</strong><br>
                            <small style="color: #8c8c8c;">${order.user.email}</small>
                        </div>
                    </td>
                    <td>
                        <span class="status-tag status-${order.status}">
                            ${getStatusText(order.status)}
                        </span>
                    </td>
                    <td><span style="text-transform: uppercase;">${order.payment}</span></td>
                    <td>${getCodDisplay(order.payment, order.total_price)}</td>
                    <td><strong>${formatPrice(order.total_price)}đ</strong></td>
                    <td>${order.total_items}</td>
                    <td>${formatDate(order.created_at)}</td>
                    <td>
                        <button class="btn btn-primary" onclick="showOrderDetail(${order.id})">
                            <i class="fas fa-eye"></i> Chi tiết
                        </button>
                    </td>
                </tr>
            `).join('');

            tbody.innerHTML = rows;
        }

        function getCodDisplay(paymentMethod, totalPrice) {
            if (paymentMethod === 'qr') {
                return '<span class="cod-tag cod-none">Không thu</span>';
            } else if (paymentMethod === 'cod') {
                return `<span class="cod-tag cod-collect">${formatPrice(totalPrice)}đ</span>`;
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

            // Nút Previous
            paginationHTML += `
                <button onclick="changePage(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i> Trước
                </button>
            `;

            // Các nút số trang
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    paginationHTML += `
                        <button onclick="changePage(${i})" class="${i === currentPage ? 'active' : ''}">
                            ${i}
                        </button>
                    `;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    paginationHTML += '<span>...</span>';
                }
            }

            // Nút Next
            paginationHTML += `
                <button onclick="changePage(${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''}>
                    Sau <i class="fas fa-chevron-right"></i>
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

                document.getElementById('modal-order-id').textContent = '#' + order.id;
                document.getElementById('modal-customer-name').textContent = order.user.name;
                document.getElementById('modal-customer-email').textContent = order.user.email;
                document.getElementById('modal-status').innerHTML = `<span class="status-tag status-${order.status}">${getStatusText(order.status)}</span>`;
                document.getElementById('modal-payment').textContent = order.payment.toUpperCase();
                document.getElementById('modal-created-at').textContent = formatDate(order.created_at);
                document.getElementById('modal-address').textContent = order.address;
                document.getElementById('modal-price').textContent = formatPrice(order.price) + 'đ';
                document.getElementById('modal-shipping-fee').textContent = formatPrice(order.shipping_fee) + 'đ';
                document.getElementById('modal-total-price').textContent = formatPrice(order.total_price) + 'đ';
                document.getElementById('modal-shipping-code').textContent = order.shipping_code || '-';

                updateShippingButton(order);
                hideShippingStatus();

                const itemsContainer = document.getElementById('modal-items');
                itemsContainer.innerHTML = '';
                order.items.forEach(item => {
                    const itemElement = document.createElement('div');
                    itemElement.className = 'item-card';
                    itemElement.innerHTML = `
                        <div class="item-header">
                            <div class="item-title">${item.book.title}</div>
                            <div class="item-price">${formatPrice(item.price)}đ</div>
                        </div>
                        <div class="item-details">
                            <div><strong>Tác giả:</strong> ${item.book.author}</div>
                            <div><strong>Thể loại:</strong> ${item.book.category}</div>
                            <div><strong>Số lượng:</strong> ${item.quantity}</div>
                            <div><strong>Giá gốc:</strong> ${formatPrice(item.book.price)}đ</div>
                        </div>
                    `;
                    itemsContainer.appendChild(itemElement);
                });

                document.getElementById('orderModal').style.display = 'block';

            } catch (error) {
                console.error('Error loading order detail:', error);
                alert('Không thể tải chi tiết đơn hàng: ' + error.message);
            }
        }

        function updateShippingButton(order) {
            const btn = document.getElementById('create-shipping-btn');
            
            const canCreateShipping = ['ready_to_pick', 'picking', 'picked'].includes(order.status);
            
            if (canCreateShipping) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-truck"></i> Tạo đơn ship';
            } else {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-truck"></i> Không thể tạo đơn ship';
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
                btn.innerHTML = '<div class="loading-spinner"></div> Đang tạo đơn ship...';

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
                    btn.innerHTML = '<i class="fas fa-check"></i> Đã tạo đơn ship';
                    
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
            container.style.display = 'block';
            container.className = `shipping-status ${type}`;
            container.innerHTML = `
                <i class="fas ${type === 'created' ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i>
                ${message}
            `;
            
            if (type === 'created') {
                setTimeout(() => {
                    hideShippingStatus();
                }, 5000);
            }
        }

        function hideShippingStatus() {
            const container = document.getElementById('shipping-status-container');
            container.style.display = 'none';
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
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
                    <td colspan="9" style="text-align: center; padding: 40px; color: #8c8c8c;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                        Đang tải dữ liệu...
                    </td>
                </tr>
            `;
        }

        function showError(message) {
            const tbody = document.getElementById('orders-table-body');
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px; color: #ff4d4f;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                        ${message}
                        <br><br>
                        <button class="btn btn-primary" onclick="loadAllOrders()">
                            <i class="fas fa-redo"></i> Thử lại
                        </button>
                    </td>
                </tr>
            `;
        }

        function getStatusText(status) {
            const statusMap = {
                'ready_to_pick': 'Chờ lấy hàng',
                'picking': 'Đang lấy hàng',
                'money_collect_picking': 'Thu tiền khi lấy',
                'picked': 'Đã lấy hàng',
                'storing': 'Đã nhập kho',
                'delivering': 'Đang giao hàng',
                'delivered': 'Đã giao hàng',
                'delivery_fail': 'Giao không thành công',
                'cancelled': 'Đã hủy'
            };
            return statusMap[status] || status;
        }

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
    </script>
</html>
@endsection