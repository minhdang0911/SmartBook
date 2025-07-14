<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Trang quản trị')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    @stack('styles')

    <style>
        :root {
            --primary-color: #1890ff;
            --primary-hover: #40a9ff;
            --primary-active: #096dd9;
            --success-color: #52c41a;
            --warning-color: #faad14;
            --error-color: #f5222d;
            --text-primary: #262626;
            --text-secondary: #8c8c8c;
            --text-disabled: #bfbfbf;
            --border-color: #d9d9d9;
            --border-color-light: #f0f0f0;
            --bg-color: #fafafa;
            --bg-white: #ffffff;
            --shadow-light: 0 2px 8px rgba(0, 0, 0, 0.06);
            --shadow-medium: 0 4px 12px rgba(0, 0, 0, 0.15);
            --shadow-heavy: 0 6px 16px rgba(0, 0, 0, 0.12);
            --border-radius: 6px;
            --border-radius-lg: 8px;
            --sidebar-width: 260px;
            --header-height: 64px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            background-color: var(--bg-color);
            color: var(--text-primary);
            line-height: 1.5715;
            font-size: 14px;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-white);
            border-right: 1px solid var(--border-color-light);
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.78, 0.14, 0.15, 0.86);
            box-shadow: var(--shadow-light);
            overflow-y: auto;
        }

        .sidebar-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--border-color-light);
            background: var(--bg-white);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .sidebar-header h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-header .logo {
            font-size: 24px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-nav {
            padding: 16px 8px;
        }

        .nav-item {
            margin-bottom: 4px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: all 0.3s cubic-bezier(0.78, 0.14, 0.15, 0.86);
            position: relative;
            font-weight: 500;
            gap: 12px;
            font-size: 14px;
        }

        .nav-link:hover {
            background-color: #e6f7ff;
            color: var(--primary-color);
            text-decoration: none;
        }

        .nav-link.active {
            background-color: #e6f7ff;
            color: var(--primary-color);
            position: relative;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: var(--primary-color);
            border-radius: 2px 0 0 2px;
        }

        .nav-link i {
            font-size: 16px;
            width: 16px;
            text-align: center;
            flex-shrink: 0;
            display: inline-block;
        }

        .nav-link.logout-btn {
            color: var(--error-color);
            margin-top: 16px;
            border-top: 1px solid var(--border-color-light);
            padding-top: 16px;
        }

        .nav-link.logout-btn:hover {
            background-color: #fff2f0;
            color: var(--error-color);
        }

        /* Main Content */
        .main-layout {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-header {
            height: var(--header-height);
            background: var(--bg-white);
            border-bottom: 1px solid var(--border-color-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            box-shadow: var(--shadow-light);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 18px;
            color: var(--text-primary);
            cursor: pointer;
            padding: 8px;
            border-radius: var(--border-radius);
            transition: all 0.3s;
        }

        .mobile-menu-btn:hover {
            background-color: var(--border-color-light);
        }

        .header-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .user-dropdown {
            position: relative;
        }

        .user-dropdown .dropdown-toggle {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: var(--border-radius);
            transition: all 0.3s;
            color: var(--text-primary);
            font-size: 14px;
        }

        .user-dropdown .dropdown-toggle:hover {
            background-color: var(--border-color-light);
        }

        .user-dropdown .dropdown-toggle::after {
            margin-left: 8px;
        }

        .user-dropdown .dropdown-menu {
            border: 1px solid var(--border-color-light);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-medium);
            padding: 8px 0;
            margin-top: 8px;
            min-width: 160px;
        }

        .user-dropdown .dropdown-item {
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-primary);
            font-size: 14px;
            transition: all 0.3s;
        }

        .user-dropdown .dropdown-item:hover {
            background-color: var(--border-color-light);
            color: var(--text-primary);
        }

        .user-dropdown .dropdown-item.logout-btn {
            color: var(--error-color);
        }

        .user-dropdown .dropdown-item.logout-btn:hover {
            background-color: #fff2f0;
            color: var(--error-color);
        }

        .user-dropdown .dropdown-divider {
            margin: 8px 0;
            border-color: var(--border-color-light);
        }

        .main-content {
            flex: 1;
            padding: 24px;
            background: var(--bg-color);
        }

        .content-wrapper {
            background: var(--bg-white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-light);
            padding: 24px;
            min-height: calc(100vh - var(--header-height) - 48px);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-layout {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block;
            }

            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.45);
                z-index: 999;
                opacity: 0;
                transition: opacity 0.3s;
            }

            .overlay.active {
                display: block;
                opacity: 1;
            }

            .main-content {
                padding: 16px;
            }

            .content-wrapper {
                padding: 16px;
            }
        }

        /* Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: var(--border-color-light);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: var(--text-secondary);
        }

        /* Loading Animation */
        .loading-text {
            color: var(--text-secondary);
            font-style: italic;
        }

        /* Hover Effects */
        .nav-link,
        .dropdown-item,
        .mobile-menu-btn,
        .user-dropdown .dropdown-toggle {
            cursor: pointer;
        }

        /* Focus States */
        .nav-link:focus,
        .dropdown-item:focus,
        .mobile-menu-btn:focus,
        .user-dropdown .dropdown-toggle:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* Animation for sidebar toggle */
        .sidebar {
            will-change: transform;
        }

        @media (prefers-reduced-motion: reduce) {

            .sidebar,
            .nav-link,
            .dropdown-item,
            .mobile-menu-btn,
            .user-dropdown .dropdown-toggle {
                transition: none;
            }
        }
    </style>
</head>

<body>
    <div class="overlay" id="sidebarOverlay"></div>

    <div class="sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <h4>
                <span class="logo">📚</span>
                SmartBook
            </h4>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-house-door"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.books.*') ? 'active' : '' }}"
                    href="{{ route('admin.books.index') }}">
                    <i class="bi bi-book"></i>
                    <span>Sách</span>
                </a>
            </div>
            <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.chapters.*') ? 'active' : '' }}"
                href="{{ route('admin.chapters.index') }}">
                <i class="bi-journal-text"></i>
                <span>Quản lý chương</span>
            </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.authors.*') ? 'active' : '' }}"
                    href="{{ route('admin.authors.index') }}">
                    <i class="bi bi-person"></i>
                    <span>Tác giả</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.publishers.*') ? 'active' : '' }}"
                    href="{{ route('admin.publishers.index') }}">
                    <i class="bi bi-buildings"></i>
                    <span>Nhà xuất bản</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                    href="{{ route('admin.categories.index') }}">
                    <i class="bi bi-tags"></i>
                    <span>Danh mục</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                    href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people"></i>
                    <span>Người dùng</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.book_images.*') ? 'active' : '' }}"
                    href="{{ route('admin.book_images.index') }}">
                    <i class="bi bi-images"></i>
                    <span>Ảnh phụ</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.topics.*') ? 'active' : '' }}"
                    href="{{ route('admin.topics.index') }}">
                    <i class="bi bi-tags"></i>
                    <span>Chủ đề</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}"
                    href="{{ route('admin.posts.index') }}">
                    <i class="bi bi-file-text"></i>
                    <span>Bài viết</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}"
                    href="{{ route('admin.banners.index') }}">
                    <i class="bi bi-easel"></i>
                    <span>Banner</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                    href="{{ route('admin.orders.index') }}">
                    <i class="bi bi-cart-check"></i>
                    <span>Đơn hàng</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.Revenue.*') ? 'active' : '' }}"
                    href="{{ route('admin.revenue.index') }}">
                    <i class="bi bi-graph-up"></i>
                    <span>Doanh thu</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.Coupons.*') ? 'active' : '' }}"
                    href="{{ route('admin.coupons.index') }}">
                    <i class="bi bi-ticket-perforated"></i>
                    <span>Mã giảm giá</span>
                </a>
            </div>
             <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.event.*') ? 'active' : '' }}"
                    href="{{ route('admin.event.index') }}">
                    <i class="bi bi-ticket-perforated"></i>
                    <span>Flash Sale & Event</span>
                </a>
            </div>
             

            <div class="nav-item">
                <a class="nav-link logout-btn" href="#">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Đăng xuất</span>
                </a>
            </div>
        </nav>
    </div>

    <div class="main-layout">
        <header class="main-header">
            <div class="d-flex align-items-center">
                <button class="mobile-menu-btn" id="toggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="header-title d-none d-md-block">SmartBook Admin</h1>
            </div>

            <div class="user-dropdown dropdown">
                <button class="dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="bi bi-person-circle"></i>
                    <span id="user-name" class="loading-text">Đang tải...</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-person"></i>
                            <span>Hồ sơ</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item logout-btn" href="#">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Đăng xuất</span>
                        </a>
                    </li>
                </ul>
            </div>
        </header>

        <main class="main-content">
            <div class="content-wrapper">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE_URL = '{{ config('app.url') }}/api';

        function logout() {
            const token = localStorage.getItem('access_token');
            fetch(`${API_BASE_URL}/logout`, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                })
                .then(() => {
                    localStorage.removeItem('access_token');
                    window.location.href = '/login';
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('toggleSidebar');

            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                    overlay.classList.toggle('active');
                });
            }

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });

            // Hiển thị user name
            const userNameEl = document.getElementById('user-name');
            const token = localStorage.getItem('access_token');
            if (token) {
                fetch(`${API_BASE_URL}/me`, {
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        userNameEl.innerText = data.user?.name || 'Người dùng';
                        userNameEl.classList.remove('loading-text');
                    })
                    .catch(() => {
                        userNameEl.innerText = 'Khách';
                        userNameEl.classList.remove('loading-text');
                    });
            } else {
                userNameEl.innerText = 'Khách';
                userNameEl.classList.remove('loading-text');
            }

            document.querySelectorAll('.logout-btn').forEach(btn => {
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    logout();
                });
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
