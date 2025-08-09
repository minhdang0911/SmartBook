<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Trang quản trị')</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />

    @stack('styles')

    <style>
        :root {
            --primary: #1890ff;
            --bg: #fafafa;
            --white: #fff;
            --text: #262626;
            --sidebar-width: 260px;
            --header-height: 64px;
            --border-light: #f0f0f0;
            --radius: 6px;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            line-height: 1.5;
        }
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--white);
            border-right: 1px solid var(--border-light);
            box-shadow: var(--shadow);
            overflow-y: auto;
            transition: transform 0.3s cubic-bezier(0.78, 0.14, 0.15, 0.86);
            z-index: 1000;
        }
        .sidebar-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--border-light);
            position: sticky;
            top: 0;
            background: var(--white);
            z-index: 10;
        }
        .sidebar-header .logo {
            font-size: 24px;
            background: linear-gradient(135deg, var(--primary), #40a9ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        nav.sidebar-nav {
            padding: 1rem 0.5rem;
        }
        .nav-item {
            margin-bottom: 4px;
        }
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--text);
            text-decoration: none;
            border-radius: var(--radius);
            gap: 0.75rem;
            font-weight: 500;
            font-size: 14px;
            transition: background-color 0.3s, color 0.3s;
            position: relative;
        }
        .nav-link i {
            width: 16px;
            text-align: center;
            font-size: 16px;
        }
        .nav-link:hover,
        .nav-link.active {
            background: #e6f7ff;
            color: var(--primary);
            text-decoration: none;
        }
        .nav-link.active::after {
            content: "";
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: var(--primary);
            border-radius: 2px 0 0 2px;
        }
        .nav-link.logout-btn {
            margin-top: 1rem;
            border-top: 1px solid var(--border-light);
            color: #f5222d;
        }
        .nav-link.logout-btn:hover {
            background: #fff2f0;
            color: #f5222d;
        }

        .main-layout {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .main-header {
            height: var(--header-height);
            background: var(--white);
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            color: var(--text);
            padding: 0.5rem;
            border-radius: var(--radius);
            transition: background-color 0.3s;
        }
        .mobile-menu-btn:hover {
            background: var(--border-light);
        }
        .header-title {
            font-weight: 600;
            font-size: 1rem;
            color: var(--text);
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
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius);
            color: var(--text);
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .user-dropdown .dropdown-toggle:hover {
            background: var(--border-light);
        }
        .user-dropdown .dropdown-menu {
            border: 1px solid var(--border-light);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            margin-top: 0.5rem;
            min-width: 160px;
            padding: 0.5rem 0;
        }
        .user-dropdown .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text);
            transition: background-color 0.3s;
            cursor: pointer;
        }
        .user-dropdown .dropdown-item.logout-btn {
            color: #f5222d;
        }
        .user-dropdown .dropdown-item:hover {
            background: var(--border-light);
            color: var(--text);
        }
        .user-dropdown .dropdown-item.logout-btn:hover {
            background: #fff2f0;
            color: #f5222d;
        }
        .main-content {
            flex: 1;
            padding: 1.5rem;
            background: var(--bg);
        }
        .content-wrapper {
            background: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 1.5rem;
            min-height: calc(100vh - var(--header-height) - 3rem);
        }

        /* Mobile */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-layout {
                margin-left: 0 !important;
            }
            .mobile-menu-btn {
                display: block;
            }
            .overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.45);
                opacity: 0;
                z-index: 999;
                transition: opacity 0.3s;
            }
            .overlay.active {
                display: block;
                opacity: 1;
            }
            .main-content, .content-wrapper {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="overlay" id="sidebarOverlay"></div>

    <aside class="sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <h4><span class="logo">📚</span> SmartBook</h4>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-door"></i><span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.books.index') }}" class="nav-link {{ request()->routeIs('admin.books.*') ? 'active' : '' }}">
                    <i class="bi bi-book"></i><span>Sách</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.chapters.index') }}" class="nav-link {{ request()->routeIs('admin.chapters.*') ? 'active' : '' }}">
                    <i class="bi-journal-text"></i><span>Quản lý chương</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.authors.index') }}" class="nav-link {{ request()->routeIs('admin.authors.*') ? 'active' : '' }}">
                    <i class="bi bi-person"></i><span>Tác giả</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.publishers.index') }}" class="nav-link {{ request()->routeIs('admin.publishers.*') ? 'active' : '' }}">
                    <i class="bi bi-buildings"></i><span>Nhà xuất bản</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i><span>Danh mục</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i><span>Người dùng</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.topics.index') }}" class="nav-link {{ request()->routeIs('admin.topics.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i><span>Chủ đề</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.posts.index') }}" class="nav-link {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                    <i class="bi bi-file-text"></i><span>Bài viết</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.banners.index') }}" class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                    <i class="bi bi-easel"></i><span>Banner</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <i class="bi bi-cart-check"></i><span>Đơn hàng</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.revenue.index') }}" class="nav-link {{ request()->routeIs('admin.Revenue.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i><span>Doanh thu</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ request()->routeIs('admin.Coupons.*') ? 'active' : '' }}">
                    <i class="bi bi-ticket-perforated"></i><span>Mã giảm giá</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.event.index') }}" class="nav-link {{ request()->routeIs('admin.event.*') ? 'active' : '' }}">
                    <i class="bi bi-ticket-perforated"></i><span>Flash Sale & Event</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link logout-btn">
                    <i class="bi bi-box-arrow-right"></i><span>Đăng xuất</span>
                </a>
            </div>
        </nav>
    </aside>

    <div class="main-layout">
        <header class="main-header">
            <div class="d-flex align-items-center">
                <button id="toggleSidebar" class="mobile-menu-btn"><i class="bi bi-list"></i></button>
                <h1 class="header-title d-none d-md-block">SmartBook Admin</h1>
            </div>

            <div class="user-dropdown dropdown">
                <button class="dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i>
                    <span id="user-name" class="loading-text">Đang tải...</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a href="#" class="dropdown-item"><i class="bi bi-person"></i> Hồ sơ</a></li>
                    <li><hr class="dropdown-divider" /></li>
                    <li><a href="#" class="dropdown-item logout-btn"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
                </ul>
            </div>
        </header>

        <main class="main-content">
            <div class="content-wrapper">
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE_URL = '{{ config('app.url') }}/api';

        function logout() {
            const token = localStorage.getItem('access_token');
            fetch(`${API_BASE_URL}/logout`, {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + token, Accept: 'application/json' },
            }).finally(() => {
                localStorage.removeItem('access_token');
                window.location.href = '/login';
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('toggleSidebar');

            toggleBtn?.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });

            const userNameEl = document.getElementById('user-name');
            const token = localStorage.getItem('access_token');

            if (token) {
                fetch(`${API_BASE_URL}/me`, {
                    headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
                }).then(res => res.json())
                  .then(data => {
                    userNameEl.innerText = data.user?.name || 'Người dùng';
                    userNameEl.classList.remove('loading-text');
                }).catch(() => {
                    userNameEl.innerText = 'Khách';
                    userNameEl.classList.remove('loading-text');
                });
            } else {
                userNameEl.innerText = 'Khách';
                userNameEl.classList.remove('loading-text');
            }

            document.querySelectorAll('.logout-btn').forEach(btn =>
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    logout();
                })
            );
        });
    </script>

    @stack('scripts')
</body>

</html>
