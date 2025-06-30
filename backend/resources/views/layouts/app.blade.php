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
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            background-color: #f5f5f5;
        }

        .sidebar {
            background: linear-gradient(180deg, #343a40 0%, #495057 100%);
            color: #fff;
            padding: 1rem;
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: #d1d4dc;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #495057;
            color: #fff;
            border-radius: 8px;
        }

        .sidebar .nav-link i {
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -260px;
                width: 240px;
                z-index: 1050;
                transition: left 0.3s ease;
            }

            .sidebar.active {
                left: 0;
            }

            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1049;
            }

            .overlay.active {
                display: block;
            }

            .content {
                margin-left: 0 !important;
            }
        }
    </style>
</head>

<body>
    <div class="overlay" id="sidebarOverlay"></div>
    <div class="d-flex">
        <div class="sidebar" id="adminSidebar">
            <h4 class="text-center">📚 SmartBook</h4>
            <nav class="nav flex-column mt-4">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>

                <a class="nav-link {{ request()->routeIs('admin.books.*') ? 'active' : '' }}"
                    href="{{ route('admin.books.index') }}">
                    <i class="bi bi-book"></i> Sách
                </a>

                <a class="nav-link {{ request()->routeIs('admin.authors.*') ? 'active' : '' }}"
                    href="{{ route('admin.authors.index') }}">
                    <i class="bi bi-person"></i> Tác giả
                </a>

                <a class="nav-link {{ request()->routeIs('admin.publishers.*') ? 'active' : '' }}"
                    href="{{ route('admin.publishers.index') }}">
                    <i class="bi bi-buildings"></i> Nhà xuất bản
                </a>

                <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                    href="{{ route('admin.categories.index') }}">
                    <i class="bi bi-tags"></i> Danh mục
                </a>

                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                    href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people"></i> Người dùng
                </a>

                <a class="nav-link {{ request()->routeIs('admin.book_images.*') ? 'active' : '' }}"
                    href="{{ route('admin.book_images.index') }}">
                    <i class="bi bi-images"></i> Ảnh phụ
                </a>

                <a class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}"
                    href="{{ route('admin.banners.index') }}">
                    <i class="bi bi-easel"></i> Banner
                </a>

                <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                    href="{{ route('admin.orders.index') }}">
                    <i class="bi bi-cart-check"></i> Đơn hàng
                </a>

                <a class="nav-link {{ request()->routeIs('admin.Coupons.*') ? 'active' : '' }}"
                    href="{{ route('admin.coupons.index') }}">
                    <i class="bi bi-ticket-perforated"></i> Mã giảm giá
                </a>

                <a class="nav-link logout-btn" href="#">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                </a>
            </nav>

        </div>

        <div class="content flex-grow-1">
            <nav class="navbar navbar-light bg-light d-md-none px-3">
                <button class="btn btn-outline-secondary" id="toggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <span class="navbar-brand mb-0 h1">SmartBook Admin</span>
            </nav>

            <div class="d-flex justify-content-end mt-3 me-3">
                <div class="user-dropdown dropdown">
                    <button class="btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        <span id="user-name">Loading...</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Hồ sơ</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item logout-btn" href="#"><i class="bi bi-box-arrow-right"></i>
                                Đăng xuất</a></li>
                    </ul>
                </div>
            </div>

            <div class="p-4">
                @yield('content')
            </div>
        </div>
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
            document.getElementById('toggleSidebar').addEventListener('click', () => {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });
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
                    });
            } else {
                userNameEl.innerText = 'Khách';
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
