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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        .page-header {
            background: white;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #262626;
            margin-bottom: 8px;
        }

        .page-description {
            color: #8c8c8c;
            font-size: 14px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #8c8c8c;
            font-size: 14px;
        }

        .orders-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 16px 24px;
            border-bottom: 1px solid #f0f0f0;
        }

        .table-title {
            font-size: 16px;
            font-weight: 600;
            color: #262626;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #fafafa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #262626;
            border-bottom: 1px solid #f0f0f0;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        tr:hover {
            background-color: #fafafa;
        }

        .status-tag {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 80px;
        }

        .status-ready_to_pick { background-color: #e6f7ff; color: #1890ff; }
        .status-picking { background-color: #fff7e6; color: #fa8c16; }
        .status-picked { background-color: #f6ffed; color: #52c41a; }
        .status-delivering { background-color: #fff1f0; color: #ff4d4f; }
        .status-delivered { background-color: #f6ffed; color: #52c41a; }
        .status-cancelled { background-color: #fff2f0; color: #ff4d4f; }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #1890ff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #40a9ff;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.45);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 16px 24px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 16px;
            font-weight: 600;
            color: #262626;
        }

        .close {
            color: #8c8c8c;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background: none;
        }

        .close:hover {
            color: #262626;
        }

        .modal-body {
            padding: 24px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 12px;
            color: #8c8c8c;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-weight: 500;
        }

        .info-value {
            font-size: 14px;
            color: #262626;
            font-weight: 500;
        }

        .items-section {
            margin-top: 24px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #262626;
            margin-bottom: 16px;
        }

        .item-card {
            border: 1px solid #f0f0f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            background-color: #fafafa;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .item-title {
            font-weight: 600;
            color: #262626;
        }

        .item-price {
            font-weight: 600;
            color: #1890ff;
        }

        .item-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 8px;
            font-size: 12px;
            color: #8c8c8c;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 16px;
            gap: 8px;
        }

        .pagination button {
            padding: 6px 12px;
            border: 1px solid #d9d9d9;
            background: white;
            color: #262626;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination button:hover {
            border-color: #1890ff;
            color: #1890ff;
        }

        .pagination button.active {
            background-color: #1890ff;
            color: white;
            border-color: #1890ff;
        }

        .pagination button:disabled {
            background-color: #f5f5f5;
            color: #bfbfbf;
            border-color: #d9d9d9;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .container {
                padding: 12px;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                width: 95%;
                margin: 2% auto;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 8px 4px;
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
            <div class="stat-card">
                <div class="stat-number" id="total-spent">-</div>
                <div class="stat-label">Tổng doanh thu</div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="orders-table">
            <div class="table-header">
                <h2 class="table-title">Danh Sách Đơn Hàng</h2>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                        <th>Tổng tiền</th>
                        <th>Số sản phẩm</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #8c8c8c;">
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
                </div>

                <div class="items-section">
                    <h3 class="section-title">Sản Phẩm Đã Đặt</h3>
                    <div id="modal-items"></div>
                </div>
            </div>
        </div>
    </div>

  <script>
    const API_BASE_URL = 'http://localhost:8000/api';
    let currentPage = 1;
    let ordersData = [];
    let paginationData = {};

    document.addEventListener('DOMContentLoaded', function() {
        loadStats();
        loadOrders(1);
    });

    async function loadStats() {
        try {
            const token = localStorage.getItem('access_token');
            console.log(token)
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
                document.getElementById('total-spent').textContent = formatPrice(stats.total_spent || 0) + 'đ';
            }
        } catch (error) {
            console.error('Error loading stats:', error);
            showError('Không thể tải thống kê');
        }
    }

    async function loadOrders(page = 1) {
        try {
            showLoading();
            const token = localStorage.getItem('access_token');
            const response = await fetch(`${API_BASE_URL}/admin/orders?page=${page}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();

            if (data.success) {
                ordersData = data.data.orders;
                paginationData = data.data.pagination;
                currentPage = page;
                renderOrders();
                renderPagination();
            } else {
                showError(data.message || 'Không thể tải danh sách đơn hàng');
            }
        } catch (error) {
            console.error('Error loading orders:', error);
            showError('Không thể tải danh sách đơn hàng');
        }
    }

    function renderOrders() {
        const tbody = document.getElementById('orders-table-body');

        if (ordersData.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #8c8c8c;">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                        Không có đơn hàng nào
                    </td>
                </tr>
            `;
            return;
        }

        const rows = ordersData.map(order => `
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

    function renderPagination() {
        const container = document.getElementById('pagination-container');
        if (paginationData.last_page <= 1) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'flex';
        let paginationHTML = '';

        paginationHTML += `
            <button onclick="changePage(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}>
                <i class="fas fa-chevron-left"></i> Trước
            </button>
        `;

        for (let i = 1; i <= paginationData.last_page; i++) {
            if (i === 1 || i === paginationData.last_page || (i >= currentPage - 2 && i <= currentPage + 2)) {
                paginationHTML += `
                    <button onclick="changePage(${i})" class="${i === currentPage ? 'active' : ''}">
                        ${i}
                    </button>
                `;
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                paginationHTML += '<span>...</span>';
            }
        }

        paginationHTML += `
            <button onclick="changePage(${currentPage + 1})" ${currentPage >= paginationData.last_page ? 'disabled' : ''}>
                Sau <i class="fas fa-chevron-right"></i>
            </button>
        `;

        container.innerHTML = paginationHTML;
    }

    async function showOrderDetail(orderId) {
        try {
            const token = localStorage.getItem('access_token');
            let order = ordersData.find(o => o.id === orderId);

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

    function closeModal() {
        document.getElementById('orderModal').style.display = 'none';
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

    function changePage(page) {
        if (page < 1 || page > paginationData.last_page) return;
        loadOrders(page);
    }

    function showLoading() {
        const tbody = document.getElementById('orders-table-body');
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: #8c8c8c;">
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
                <td colspan="8" style="text-align: center; padding: 40px; color: #ff4d4f;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                    ${message}
                    <br><br>
                    <button class="btn btn-primary" onclick="loadOrders(currentPage)">
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

    setInterval(() => {
        loadStats();
        loadOrders(currentPage);
    }, 30000);
</script>

</body>
</html>
@endsection
