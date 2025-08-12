@extends('layouts.app')

@section('content')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>

        .filters {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-group label {
            font-size: 14px;
            color: #64748b;
        }

        .filter-group select {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }

        .card-value {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin: 10px 0;
        }

        .card-subtitle {
            font-size: 14px;
            color: #64748b;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .stat-card .value {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 20px;
        }

        .chart-container-small {
            position: relative;
            height: 200px;
            margin-top: 20px;
        }

        .doughnut-container {
            position: relative;
            height: 250px;
            margin-top: 20px;
        }

        .large-chart {
            grid-column: span 2;
        }

        .revenue-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .revenue-table th,
        .revenue-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e5e5;
        }

        .revenue-table th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .revenue-table td {
            font-size: 14px;
            color: #64748b;
        }

        .revenue-table tr:hover {
            background-color: #f8fafc;
        }

        .no-data {
            text-align: center;
            color: #9ca3af;
            font-size: 14px;
            padding: 40px;
        }

        .legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }

        .total-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .total-center .amount {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
        }

        .total-center .label {
            font-size: 12px;
            color: #64748b;
        }

        .status-pending { color: #f59e0b; }
        .status-completed { color: #10b981; }
        .status-cancelled { color: #ef4444; }

        .metric-positive { color: #10b981; }
        .metric-negative { color: #ef4444; }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: #64748b;
        }

        .loading.show {
            display: block;
        }

        .filter-group.period-filter {
            display: none;
        }

        .filter-group.period-filter.show {
            display: flex;
        }

        .employee-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
            margin-right: 10px;
        }

        .employee-row {
            display: flex;
            align-items: center;
        }

        .employee-info {
            flex: 1;
        }

        .employee-name {
            font-weight: 500;
            color: #1e293b;
        }

        .employee-stats {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }

            .sidebar {
                display: none;
            }

            .filters {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                justify-content: space-between;
            }

            .large-chart {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1>Revenue Dashboard</h1>
            <div class="filters">
                <div class="filter-group">
                    <label>Năm:</label>
                    <select id="yearSelect">
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Tháng:</label>
                    <select id="monthSelect">
                        <option value="">Tất cả</option>
                        <option value="1">Tháng 1</option>
                        <option value="2">Tháng 2</option>
                        <option value="3">Tháng 3</option>
                        <option value="4">Tháng 4</option>
                        <option value="5">Tháng 5</option>
                        <option value="6">Tháng 6</option>
                        <option value="7">Tháng 7</option>
                        <option value="8">Tháng 8</option>
                        <option value="9">Tháng 9</option>
                        <option value="10">Tháng 10</option>
                        <option value="11">Tháng 11</option>
                        <option value="12">Tháng 12</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Quý:</label>
                    <select id="quarterSelect">
                        <option value="">Tất cả</option>
                        <option value="1">Quý 1</option>
                        <option value="2">Quý 2</option>
                        <option value="3">Quý 3</option>
                        <option value="4">Quý 4</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Kênh bán:</label>
                    <select id="channelSelect">
                        <option value="">Tất cả</option>
                        <option value="shopee">Shopee</option>
                        <option value="web">Web</option>
                        <option value="tiktok">TikTok Shop</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="loading" id="loadingIndicator">
            <div>🔄 Đang tải dữ liệu...</div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Số đơn hàng</h3>
                <div class="value" id="ordersCount">0</div>
            </div>
            <div class="stat-card">
                <h3>Hoàn trả</h3>
                <div class="value metric-negative" id="returnsAmount">0 đ</div>
            </div>
            <div class="stat-card">
                <h3>Doanh thu thuần</h3>
                <div class="value" id="netRevenue">0 đ</div>
            </div>
            <div class="stat-card">
                <h3>Thực nhận</h3>
                <div class="value" id="actualRevenue">0 đ</div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Orders Chart -->
            <div class="card large-chart">
                <div class="card-header">
                    <h3 class="card-title">Lượng đơn hàng</h3>
                </div>
                <div class="chart-container">
                    <canvas id="ordersChart"></canvas>
                </div>
            </div>

            <!-- Revenue Chart -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Doanh thu thuần</h3>
                </div>
                <div class="card-value" id="revenueCardValue">0 đ</div>
                <div class="chart-container-small">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Channel Revenue Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Doanh thu thuần - từ kênh bán hàng</h3>
                </div>
                <table class="revenue-table">
                    <thead>
                        <tr>
                            <th>Kênh</th>
                            <th>Đơn hàng</th>
                            <th>Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody id="channelRevenueTable">
                        <tr>
                            <td>Shopee</td>
                            <td id="shopeeOrders">0</td>
                            <td id="shopeeRevenue">0 đ</td>
                        </tr>
                        <tr>
                            <td>Web</td>
                            <td id="webOrders">0</td>
                            <td id="webRevenue">0 đ</td>
                        </tr>
                        <tr>
                            <td>TikTok Shop</td>
                            <td id="tiktokOrders">0</td>
                            <td id="tiktokRevenue">0 đ</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Top Orders -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top đơn hàng</h3>
                </div>
                <table class="revenue-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Giá trị</th>
                            <th>Ngày</th>
                        </tr>
                    </thead>
                    <tbody id="topOrdersTable">
                        <tr>
                            <td colspan="3" class="no-data">Đang tải...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Revenue by Status -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Doanh thu theo trạng thái</h3>
                </div>
                <div class="doughnut-container">
                    <canvas id="statusChart"></canvas>
                    <div class="total-center">
                        <div class="amount" id="statusTotalAmount">0 đ</div>
                        <div class="label">Tổng doanh thu</div>
                    </div>
                </div>
            </div>

            <!-- Quarterly Revenue -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Doanh thu theo quý</h3>
                </div>
                <div class="chart-container-small">
                    <canvas id="quarterlyChart"></canvas>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Doanh thu theo tháng</h3>
                </div>
                <div class="chart-container-small">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <!-- Yearly Revenue -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Doanh thu theo năm</h3>
                </div>
                <div class="yearly-stats">
                    <div class="year-stat">
                        <h4 id="currentYear">2025</h4>
                        <div class="year-value" id="yearlyRevenue">0 đ</div>
                        <div class="year-orders" id="yearlyOrders">0 đơn hàng</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // API Base URL
        const API_BASE_URL = '{{ config('app.url') }}/api/revenue';

        // Charts variables
        let ordersChart, revenueChart, statusChart, quarterlyChart, monthlyChart;

        // Current filters
        let currentFilters = {
            year: 2025,
            month: '',
            quarter: '',
            channel: ''
        };

        // Format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN').format(Math.abs(amount)) + ' đ';
        }

        // Show loading
        function showLoading() {
            document.getElementById('loadingIndicator').style.display = 'block';
        }

        // Hide loading
        function hideLoading() {
            document.getElementById('loadingIndicator').style.display = 'none';
        }

        // API calls
        async function fetchAPI(endpoint) {
            try {
                const response = await fetch(`${API_BASE_URL}${endpoint}`);
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('API Error:', error);
                return null;
            }
        }

        // Get total revenue
        async function getTotalRevenue() {
            let endpoint = '/total';
            const params = new URLSearchParams();

            if (currentFilters.month) {
                params.append('start_date', `${currentFilters.year}-${currentFilters.month.padStart(2, '0')}-01`);
                params.append('end_date', `${currentFilters.year}-${currentFilters.month.padStart(2, '0')}-31`);
            }

            if (params.toString()) {
                endpoint += '?' + params.toString();
            }

            return await fetchAPI(endpoint);
        }

        // Get monthly revenue
        async function getMonthlyRevenue() {
            const month = currentFilters.month || new Date().getMonth() + 1;
            return await fetchAPI(`/monthly?year=${currentFilters.year}&month=${month}`);
        }

        // Get quarterly revenue
        async function getQuarterlyRevenue() {
            return await fetchAPI(`/quarterly?year=${currentFilters.year}`);
        }

        // Get quarter detail
        async function getQuarterDetail(quarter) {
            return await fetchAPI(`/quarter?year=${currentFilters.year}&quarter=${quarter}`);
        }

        // Get yearly revenue
        async function getYearlyRevenue() {
            return await fetchAPI(`/yearly?year=${currentFilters.year}`);
        }

        // Get revenue by status
        async function getRevenueByStatus() {
            return await fetchAPI(`/by-status?year=${currentFilters.year}`);
        }

        // Update overview stats
        function updateOverviewStats(data) {
            if (!data || !data.success) return;

            const stats = data.data;
            document.getElementById('ordersCount').textContent = stats.total_orders || 0;
            document.getElementById('netRevenue').textContent = formatCurrency(stats.total_revenue || 0);
            document.getElementById('actualRevenue').textContent = formatCurrency(stats.total_revenue || 0);
            document.getElementById('revenueCardValue').textContent = formatCurrency(stats.total_revenue || 0);
        }

        // Update top orders table
        function updateTopOrdersTable(data) {
            const tbody = document.getElementById('topOrdersTable');
            tbody.innerHTML = '';

            if (data && data.top_orders) {
                data.top_orders.slice(0, 10).forEach(order => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>#${order.id}</td>
                        <td>${formatCurrency(order.total_price)}</td>
                        <td>${new Date(order.created_at).toLocaleDateString('vi-VN')}</td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="3" class="no-data">Không có dữ liệu</td></tr>';
            }
        }

        // Update orders chart
        function updateOrdersChart(data) {
            const ctx = document.getElementById('ordersChart');
            if (!ctx) return;

            if (ordersChart) {
                ordersChart.destroy();
            }

            let chartData = { labels: [], data: [] };

            if (data && data.data) {
                if (data.data.daily_breakdown) {
                    // Monthly data with daily breakdown
                    data.data.daily_breakdown.forEach(item => {
                        const date = new Date(item.date);
                        chartData.labels.push(`${date.getDate()}/${date.getMonth() + 1}`);
                        chartData.data.push(item.orders_count || 0);
                    });
                } else if (Array.isArray(data.data)) {
                    // Quarterly or yearly data
                    data.data.forEach(item => {
                        if (item.quarter) {
                            chartData.labels.push(`Q${item.quarter}`);
                            chartData.data.push(item.orders_count || 0);
                        } else if (item.year) {
                            chartData.labels.push(item.year.toString());
                            chartData.data.push(item.orders_count || 0);
                        }
                    });
                }
            }

            ordersChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Số đơn hàng',
                        data: chartData.data,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Update revenue chart
        function updateRevenueChart(data) {
            const ctx = document.getElementById('revenueChart');
            if (!ctx) return;

            if (revenueChart) {
                revenueChart.destroy();
            }

            let chartData = { labels: [], data: [] };

            if (data && data.data) {
                if (data.data.daily_breakdown) {
                    data.data.daily_breakdown.forEach(item => {
                        const date = new Date(item.date);
                        chartData.labels.push(`${date.getDate()}/${date.getMonth() + 1}`);
                        chartData.data.push(parseFloat(item.revenue || 0));
                    });
                }
            }

            revenueChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Doanh thu',
                        data: chartData.data,
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        }
                    }
                }
            });
        }

        // Update status chart
        function updateStatusChart(data) {
            const ctx = document.getElementById('statusChart');
            if (!ctx) return;

            if (statusChart) {
                statusChart.destroy();
            }

            let chartData = { labels: [], data: [], total: 0 };

            if (data && data.data) {
                data.data.forEach(item => {
                    chartData.labels.push(item.status);
                    const revenue = parseFloat(item.revenue || 0);
                    chartData.data.push(revenue);
                    chartData.total += revenue;
                });
            }

            document.getElementById('statusTotalAmount').textContent = formatCurrency(chartData.total);

            statusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.data,
                        backgroundColor: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Update quarterly chart
        function updateQuarterlyChart(data) {
            const ctx = document.getElementById('quarterlyChart');
            if (!ctx) return;

            if (quarterlyChart) {
                quarterlyChart.destroy();
            }

            let chartData = { labels: [], data: [] };

            if (data && data.data) {
                data.data.forEach(item => {
                    chartData.labels.push(`Q${item.quarter}`);
                    chartData.data.push(parseFloat(item.revenue || 0));
                });
            }

            quarterlyChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Doanh thu',
                        data: chartData.data,
                        backgroundColor: '#8b5cf6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        }
                    }
                }
            });
        }

        // Update yearly stats
        function updateYearlyStats(data) {
            if (data && data.data && data.data.length > 0) {
                const yearData = data.data[0];
                document.getElementById('currentYear').textContent = yearData.year;
                document.getElementById('yearlyRevenue').textContent = formatCurrency(yearData.revenue || 0);
                document.getElementById('yearlyOrders').textContent = `${yearData.orders_count || 0} đơn hàng`;
            }
        }

        // Load all dashboard data
        async function loadDashboardData() {
            showLoading();

            try {
                // Load data parallel
                const [
                    totalData,
                    monthlyData,
                    quarterlyData,
                    yearlyData,
                    statusData
                ] = await Promise.all([
                    getTotalRevenue(),
                    getMonthlyRevenue(),
                    getQuarterlyRevenue(),
                    getYearlyRevenue(),
                    getRevenueByStatus()
                ]);

                // Update UI
                updateOverviewStats(totalData);
                updateOrdersChart(monthlyData);
                updateRevenueChart(monthlyData);
                updateQuarterlyChart(quarterlyData);
                updateStatusChart(statusData);
                updateYearlyStats(yearlyData);

                // Update top orders if available
                if (currentFilters.quarter) {
                    const quarterDetail = await getQuarterDetail(currentFilters.quarter);
                    if (quarterDetail && quarterDetail.top_orders) {
                        updateTopOrdersTable(quarterDetail);
                    }
                }

            } catch (error) {
                console.error('Error loading dashboard data:', error);
            } finally {
                hideLoading();
            }
        }

        // Event listeners
        function setupEventListeners() {
            // Year filter
            document.getElementById('yearSelect').addEventListener('change', function(e) {
                currentFilters.year = parseInt(e.target.value);
                loadDashboardData();
            });

            // Month filter
            document.getElementById('monthSelect').addEventListener('change', function(e) {
                currentFilters.month = e.target.value;
                loadDashboardData();
            });

            // Quarter filter
            document.getElementById('quarterSelect').addEventListener('change', function(e) {
                currentFilters.quarter = e.target.value;
                loadDashboardData();
            });

            // Channel filter
            document.getElementById('channelSelect').addEventListener('change', function(e) {
                currentFilters.channel = e.target.value;
                loadDashboardData();
            });
        }

        // Initialize dashboard
        function initDashboard() {
            setupEventListeners();
            loadDashboardData();
        }

        // Load dashboard when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            initDashboard();
        });

        // Auto refresh every 5 minutes
        setInterval(loadDashboardData, 300000);
    </script>
</body>
</html>
@endsection
