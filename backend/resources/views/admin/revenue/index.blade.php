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
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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

        .status-pending {
            color: #f59e0b;
        }

        .status-completed {
            color: #10b981;
        }

        .status-cancelled {
            color: #ef4444;
        }

        .metric-positive {
            color: #10b981;
        }

        .metric-negative {
            color: #ef4444;
        }

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
            document.addEventListener('DOMContentLoaded', () => {
                const API_BASE_URL = @json(url('/api/revenue'));

                // Màu theo trạng thái (color-blind khá ổn)
const STATUS_COLORS = {
  pending:       '#f59e0b', // cam - chờ xác nhận
  confirmed:     '#3b82f6', // xanh dương - đã xác nhận
  processing:    '#06b6d4', // cyan - đang xử lý
  ready_to_pick: '#a855f7', // tím - sẵn sàng lấy
  shipping:      '#0ea5e9', // sky - đang giao
  delivered:     '#22c55e', // XANH LÁ - HOÀN THÀNH (vui tươi)
  cancelled:     '#ef4444'  // ĐỎ - huỷ (nguy hiểm)
};

const STATUS_LABELS = {
  pending:       'Chờ xác nhận',
  confirmed:     'Đã xác nhận',
  processing:    'Đang xử lý',
  ready_to_pick: 'Sẵn sàng lấy hàng',
  shipping:      'Đang giao hàng',
  delivered:     'Đã giao hàng',
  cancelled:     'Đã hủy'
};


                const yearSelect = document.getElementById('yearSelect');
                const monthSelect = document.getElementById('monthSelect');
                const quarterSelect = document.getElementById('quarterSelect');
                const channelSelect = document.getElementById('channelSelect');

                // Channel table
                const elShopOrders = document.getElementById('shopeeOrders');
                const elShopRevenue = document.getElementById('shopeeRevenue');
                const elWebOrders = document.getElementById('webOrders');
                const elWebRevenue = document.getElementById('webRevenue');
                const elTtkOrders = document.getElementById('tiktokOrders');
                const elTtkRevenue = document.getElementById('tiktokRevenue');

                let ordersChart, revenueChart, statusChart, quarterlyChart, monthlyChart;

                const QUARTER_MONTHS = { '1': [1, 2, 3], '2': [4, 5, 6], '3': [7, 8, 9], '4': [10, 11, 12] };
                let currentFilters = {
                    year: parseInt(yearSelect?.value || new Date().getFullYear(), 10),
                    month: monthSelect?.value || '',
                    quarter: quarterSelect?.value || '',
                    channel: channelSelect?.value || ''
                };

                function formatCurrency(a) { return new Intl.NumberFormat('vi-VN').format(Math.abs(Number(a) || 0)) + ' đ'; }
                function showLoading() { document.getElementById('loadingIndicator').style.display = 'block'; }
                function hideLoading() { document.getElementById('loadingIndicator').style.display = 'none'; }

                // MONTH options follow quarter
                function setMonthOptionsForQuarter(q, keep = '') {
                    const selected = keep ? String(keep) : '';
                    const months = q && QUARTER_MONTHS[String(q)] ? QUARTER_MONTHS[String(q)] : [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
                    let html = `<option value="">Tất cả</option>`;
                    months.forEach(m => html += `<option value="${m}" ${selected === String(m) ? 'selected' : ''}>Tháng ${m}</option>`);
                    monthSelect.innerHTML = html;
                }

                function lastDayOfMonth(y, m) { return new Date(y, m, 0).getDate(); }
                function getRangeQuery() {
                    const p = new URLSearchParams();
                    if (currentFilters.month) {
                        const y = currentFilters.year, m = parseInt(currentFilters.month, 10), ld = lastDayOfMonth(y, m);
                        p.append('start_date', `${y}-${String(m).padStart(2, '0')}-01`);
                        p.append('end_date', `${y}-${String(m).padStart(2, '0')}-${String(ld).padStart(2, '0')}`);
                    } else if (currentFilters.quarter) {
                        const y = currentFilters.year, [sm, , em] = QUARTER_MONTHS[String(currentFilters.quarter)], ld = lastDayOfMonth(y, em);
                        p.append('start_date', `${y}-${String(sm).padStart(2, '0')}-01`);
                        p.append('end_date', `${y}-${String(em).padStart(2, '0')}-${String(ld).padStart(2, '0')}`);
                    }
                    const qs = p.toString(); return qs ? `?${qs}` : '';
                }
                function getYearRangeQuery(year) {
                    const p = new URLSearchParams(); p.append('year', String(year)); return `?${p.toString()}`;
                }

                async function fetchAPI(endpoint) {
                    try { const r = await fetch(`${API_BASE_URL}${endpoint}`); return await r.json(); }
                    catch (e) { console.error('[Revenue] fetch err', endpoint, e); return null; }
                }

                // ------- API wrappers
                async function getTotalRevenue() { return fetchAPI('/total' + getRangeQuery()); }
                async function getMonthlyRevenue() {
                    let m = currentFilters.month; if (!m && currentFilters.quarter) m = QUARTER_MONTHS[String(currentFilters.quarter)][0];
                    if (!m) m = new Date().getMonth() + 1;
                    return fetchAPI(`/monthly?year=${currentFilters.year}&month=${m}`);
                }
                async function getQuarterlyRevenue() { return fetchAPI(`/quarterly?year=${currentFilters.year}`); }
                async function getYearlyRevenue() { return fetchAPI(`/yearly?year=${currentFilters.year}`); }
                async function getRevenueByStatus() { return fetchAPI('/by-status' + getRangeQuery()); }
                async function getQuarterDetail(q) { return fetchAPI(`/quarter?year=${currentFilters.year}&quarter=${q}`); }

                // NEW: monthly-by-year & top orders
                async function getRevenueByMonthInYear(year) { return fetchAPI('/by-month' + getYearRangeQuery(year)); }
                async function getTopOrdersData() {
                    // dùng khoảng tháng/quý nếu có, ngược lại dùng năm
                    const qs = getRangeQuery() || getYearRangeQuery(currentFilters.year);
                    return fetchAPI('/top-orders' + qs);
                }

                // ------- Update UI
                function updateOverviewStats(data) {
                    if (!data || !data.success) return;
                    const s = data.data;
                    document.getElementById('ordersCount').textContent = s.total_orders || 0;
                    document.getElementById('netRevenue').textContent = formatCurrency(s.total_revenue || 0);
                    document.getElementById('actualRevenue').textContent = formatCurrency(s.total_revenue || 0);
                    document.getElementById('revenueCardValue').textContent = formatCurrency(s.total_revenue || 0);
                }

                function updateTopOrdersTableFromList(list) {
                    const tbody = document.getElementById('topOrdersTable');
                    tbody.innerHTML = '';
                    if (Array.isArray(list) && list.length) {
                        list.slice(0, 10).forEach(o => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `<td>#${o.id}</td><td>${formatCurrency(o.total_price)}</td><td>${new Date(o.created_at).toLocaleDateString('vi-VN')}</td>`;
                            tbody.appendChild(tr);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="3" class="no-data">Không có dữ liệu</td></tr>';
                    }
                }

                function updateOrdersChart(data) {
                    const ctx = document.getElementById('ordersChart'); if (!ctx) return;
                    if (ordersChart) ordersChart.destroy();
                    let labels = [], vals = [];
                    if (data && data.data) {
                        if (data.data.daily_breakdown) {
                            data.data.daily_breakdown.forEach(it => {
                                const d = new Date(it.date); labels.push(`${d.getDate()}/${d.getMonth() + 1}`); vals.push(it.orders_count || 0);
                            });
                        } else if (Array.isArray(data.data)) {
                            data.data.forEach(it => {
                                if (it.quarter) { labels.push(`Q${it.quarter}`); vals.push(it.orders_count || 0); }
                                else if (it.year) { labels.push(String(it.year)); vals.push(it.orders_count || 0); }
                            });
                        }
                    }
                    ordersChart = new Chart(ctx, {
                        type: 'line', data: { labels, datasets: [{ label: 'Số đơn hàng', data: vals, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', tension: 0.4, fill: true }] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                    });
                }

                function updateRevenueChart(data) {
                    const ctx = document.getElementById('revenueChart'); if (!ctx) return;
                    if (revenueChart) revenueChart.destroy();
                    let labels = [], vals = [];
                    if (data && data.data && data.data.daily_breakdown) {
                        data.data.daily_breakdown.forEach(it => {
                            const d = new Date(it.date); labels.push(`${d.getDate()}/${d.getMonth() + 1}`); vals.push(parseFloat(it.revenue || 0));
                        });
                    }
                    revenueChart = new Chart(ctx, {
                        type: 'bar', data: { labels, datasets: [{ label: 'Doanh thu', data: vals, backgroundColor: '#10b981', borderRadius: 4 }] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => formatCurrency(v) } } } }
                    });
                }

                // NEW: Doanh thu theo 12 tháng của năm
                function updateMonthlyByYearChart(data) {
                    const ctx = document.getElementById('monthlyChart'); if (!ctx) return;
                    if (monthlyChart) monthlyChart.destroy();
                    const monthNames = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
                    const map = new Map();
                    (data?.data || []).forEach(r => map.set(Number(r.month), Number(r.revenue)));
                    const vals = monthNames.map((_, idx) => map.get(idx + 1) || 0);

                    monthlyChart = new Chart(ctx, {
                        type: 'bar',
                        data: { labels: monthNames.map(m => `T${m}`), datasets: [{ label: 'Doanh thu theo tháng', data: vals, backgroundColor: '#60a5fa' }] },
                        options: {
                            responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, ticks: { callback: v => formatCurrency(v) } } }
                        }
                    });
                }

             function updateStatusChart(data) {
  const ctx = document.getElementById('statusChart');
  if (!ctx) return;
  if (statusChart) statusChart.destroy();

  let labels = [], vals = [], colors = [];
  let deliveredAmount = 0;

  if (data && data.data) {
    // đảm bảo thứ tự ổn định để màu không nhảy
    const order = ['pending','confirmed','processing','ready_to_pick','shipping','delivered','cancelled'];
    const map = new Map(data.data.map(i => [i.status, i]));
    order.forEach(k => {
      const it = map.get(k) || { status: k, revenue: 0, orders_count: 0 };
      labels.push(k);
      const r = parseFloat(it.revenue || 0);
      vals.push(r);
      colors.push(STATUS_COLORS[k] || '#9ca3af');
      if (k === 'delivered') deliveredAmount = r;
    });
  }

  // số giữa: chỉ tổng delivered
  document.getElementById('statusTotalAmount').textContent = formatCurrency(deliveredAmount);

  statusChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data: vals,
        backgroundColor: colors,
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => {
              const key = ctx.label;
              const name = STATUS_LABELS[key] || key;
              return `${name}: ${formatCurrency(ctx.parsed)}`;
            },
            title: () => '' // khỏi hiện title cho gọn
          }
        }
      }
    }
  });
}

                function updateQuarterlyChart(data) {
                    const ctx = document.getElementById('quarterlyChart'); if (!ctx) return;
                    if (quarterlyChart) quarterlyChart.destroy();
                    let labels = [], vals = [];
                    if (data && data.data) { data.data.forEach(it => { labels.push(`Q${it.quarter}`); vals.push(parseFloat(it.revenue || 0)); }); }
                    quarterlyChart = new Chart(ctx, {
                        type: 'bar', data: { labels, datasets: [{ label: 'Doanh thu', data: vals, backgroundColor: '#8b5cf6' }] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => formatCurrency(v) } } } }
                    });
                }

                function updateYearlyStats(data) {
                    if (data && data.data && data.data.length > 0) {
                        const y = data.data[0];
                        document.getElementById('currentYear').textContent = y.year;
                        document.getElementById('yearlyRevenue').textContent = formatCurrency(y.revenue || 0);
                        document.getElementById('yearlyOrders').textContent = `${y.orders_count || 0} đơn hàng`;
                    }
                }

                // Channel table (Web thật theo NĂM, Shopee/TikTok = 0)
                async function updateChannelRevenueYearly(year) {
                    const yearly = await fetchAPI('/total' + getYearRangeQuery(year));
                    const orders = yearly?.data?.total_orders || 0;
                    const revenue = yearly?.data?.total_revenue || 0;
                    if (elWebOrders) elWebOrders.textContent = orders;
                    if (elWebRevenue) elWebRevenue.textContent = formatCurrency(revenue);
                    if (elShopOrders) elShopOrders.textContent = 0;
                    if (elShopRevenue) elShopRevenue.textContent = formatCurrency(0);
                    if (elTtkOrders) elTtkOrders.textContent = 0;
                    if (elTtkRevenue) elTtkRevenue.textContent = formatCurrency(0);
                }

                // ------- Load all
                async function loadDashboardData() {
                    showLoading();
                    try {
                        const [
                            totalData, monthlyData, quarterlyData, yearlyData, statusData,
                            monthlyByYearData, topOrdersData
                        ] = await Promise.all([
                            getTotalRevenue(),
                            getMonthlyRevenue(),
                            getQuarterlyRevenue(),
                            getYearlyRevenue(),
                            getRevenueByStatus(),
                            getRevenueByMonthInYear(currentFilters.year),
                            getTopOrdersData()
                        ]);

                        updateOverviewStats(totalData);
                        updateOrdersChart(monthlyData);
                        updateRevenueChart(monthlyData);
                        updateQuarterlyChart(quarterlyData);
                        updateStatusChart(statusData);
                        updateYearlyStats(yearlyData);

                        // NEW
                        updateMonthlyByYearChart(monthlyByYearData);
                        updateTopOrdersTableFromList(topOrdersData?.data || []);

                        // Bảng kênh bán theo NĂM
                        await updateChannelRevenueYearly(currentFilters.year);
                    } catch (e) {
                        console.error('[Revenue] loadDashboardData error:', e);
                    } finally { hideLoading(); }
                }

                // ------- Filters
                yearSelect.addEventListener('change', (e) => { currentFilters.year = parseInt(e.target.value, 10); loadDashboardData(); });
                monthSelect.addEventListener('change', (e) => {
                    currentFilters.month = e.target.value;
                    if (currentFilters.month) { quarterSelect.value = ''; currentFilters.quarter = ''; setMonthOptionsForQuarter(null, currentFilters.month); }
                    loadDashboardData();
                });
                quarterSelect.addEventListener('change', (e) => {
                    currentFilters.quarter = e.target.value;
                    if (currentFilters.quarter) {
                        const allowed = QUARTER_MONTHS[String(currentFilters.quarter)];
                        if (currentFilters.month && !allowed.includes(parseInt(currentFilters.month, 10))) currentFilters.month = '';
                        setMonthOptionsForQuarter(currentFilters.quarter, currentFilters.month || '');
                    } else {
                        setMonthOptionsForQuarter(null, currentFilters.month || '');
                    }
                    loadDashboardData();
                });
                channelSelect.addEventListener('change', (e) => { currentFilters.channel = e.target.value; loadDashboardData(); });

                // Init
                setMonthOptionsForQuarter(currentFilters.quarter || null, currentFilters.month || '');
                loadDashboardData();
                setInterval(loadDashboardData, 300000);
            });
        </script>


    </body>

    </html>
@endsection