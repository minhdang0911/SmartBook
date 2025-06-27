<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Trang quản trị')</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    @stack('styles')

    <style>
        body {
            background: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .sidebar {
            width: 240px;
            min-height: 100vh;
            background-color: #343a40;
            color: #fff;
            padding: 24px 0;
            position: fixed;
            top: 0;
            left: 0;
        }

        .sidebar .nav-link {
            color: #ccc;
            padding: 10px 20px;
            display: block;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #495057;
            color: #fff;
        }

        .content {
            margin-left: 240px;
            padding: 24px;
        }

        /* Improved user dropdown styling */
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background-color: #fff;
            border-radius: 50px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .user-dropdown:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .user-dropdown .btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            color: #343a40;
            font-weight: 500;
            padding: 0;
        }

        .user-dropdown .btn i {
            font-size: 1.2rem;
        }

        .user-dropdown .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: none;
            margin-top: 8px;
        }

        .user-dropdown .dropdown-item {
            padding: 10px 20px;
            color: #343a40;
        }

        .user-dropdown .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .user-dropdown .dropdown-divider {
            margin: 4px 0;
        }
    </style>
</head>

<body>
    {{-- Sidebar --}}
    <div class="sidebar">
        <h4 class="text-center text-white">📚 SmartBook</h4>
        <nav class="nav flex-column mt-4">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a class="nav-link" href="{{ route('admin.books.index') }}">Sách</a>
            <a class="nav-link" href="{{ route('admin.authors.index') }}">Tác giả</a>
            <a class="nav-link" href="{{ route('admin.publishers.index') }}">Nhà xuất bản</a>
            <a class="nav-link" href="{{ route('admin.categories.index') }}">Danh mục</a>
            <a class="nav-link" href="{{ route('admin.users.index') }}">Người dùng</a>
            <a class="nav-link" href="{{ route('admin.banners.index') }}">Banner</a>
            <a class="nav-link" href="{{ route('admin.orders.index') }}">Đơn hàng</a>
            <a class="nav-link" href="#" id="logout-btn">Đăng xuất</a>
        </nav>
    </div>

    {{-- Main content --}}
    <div class="content">
        <div class="d-flex justify-content-end mb-3">
            <div class="user-dropdown">
                <button class="btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i>
                    <span id="user-name">Loading...</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="#">Hồ sơ</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" id="logout-btn">Đăng xuất</a></li>
                </ul>
            </div>
        </div>

        {{-- Nội dung trang --}}
        @yield('content')
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userNameEl = document.getElementById('user-name');
            const token = localStorage.getItem('access_token');

            if (!token) {
                userNameEl.innerText = 'Khách';
                return;
            }

            fetch('/api/me', {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            })
                .then(res => {
                    if (!res.ok) throw new Error('Unauthorized');
                    return res.json();
                })
                .then(user => {
                    userNameEl.innerText = user?.user?.name || 'Người dùng';
                })
                .catch(err => {
                    console.error('Lỗi khi gọi /api/me:', err);
                    userNameEl.innerText = 'Khách';
                });

            // Logout
            const logoutBtn = document.getElementById('logout-btn');
            logoutBtn.addEventListener('click', function (e) {
                e.preventDefault();
                localStorage.removeItem('access_token');
                window.location.href = '/login';
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
