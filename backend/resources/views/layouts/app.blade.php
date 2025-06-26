<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Trang quản trị')</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    @stack('styles')
    <style>
        body {
            background: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .banner-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        .banner-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 32px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .banner-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .banner-image {
            width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #f0f0f0;
        }

        .status-tag {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 12px;
        }

        .action-btn {
            margin: 0 4px;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin: 0;
            padding: 20px 24px;
        }

        .ant-modal-body {
            padding: 24px;
        }

        .ant-modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #f0f0f0;
            text-align: right;
            background: #fafafa;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .upload-area {
            border: 2px dashed #d9d9d9;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background: #fafafa;
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-area:hover {
            border-color: #667eea;
            background: #f0f2ff;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
        }
    </style>
</head>

<body>
    {{-- Navbar --}}
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Admin1</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.books.index') }}">Sách</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.authors.index') }}">Tác giả</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.publishers.index') }}">NXB</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.categories.index') }}">Danh mục</a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.banners.index') }}">Banner</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.orders.index') }}">Order</a></li>
                </ul>

                {{-- Dropdown người dùng --}}
                <div class="ms-auto text-white d-flex align-items-center">
                    <div class="dropdown">
                        <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="userDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            👤 <span id="user-name">Loading...</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#">Hồ sơ</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="#" id="logout-btn">Đăng xuất</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>


    {{-- Nội dung chính --}}
    <main class="py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Script gọi API /api/me --}}
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

            // Bắt sự kiện logout
            const logoutBtn = document.getElementById('logout-btn');
            logoutBtn.addEventListener('click', function (e) {
                e.preventDefault();
                localStorage.removeItem('access_token');
                window.location.href = '/login'; // hoặc route login của mày
            });
        });
    </script>


    @stack('scripts')
</body>

</html>