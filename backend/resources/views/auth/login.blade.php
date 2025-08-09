<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
        }

        .toast {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            background: white;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 8px;
            min-width: 320px;
            max-width: 400px;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.645, 0.045, 0.355, 1);
            border-left: 4px solid;
            font-size: 14px;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.success {
            border-left-color: #52c41a;
            color: #52c41a;
        }

        .toast.error {
            border-left-color: #ff4d4f;
            color: #ff4d4f;
        }

        .toast.warning {
            border-left-color: #faad14;
            color: #faad14;
        }

        .toast.info {
            border-left-color: #1890ff;
            color: #1890ff;
        }

        .toast-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .toast-message {
            color: #666;
            font-size: 13px;
            line-height: 1.4;
        }

        .toast-close {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            padding: 0;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .toast-close:hover {
            color: #333;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #333;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Button Loading State */
        .btn-loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-loading .btn-text {
            opacity: 0;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body>
    <x-guest-layout>
        <div id="login-status" class="mb-4 text-red-600"></div>

        <form id="login-form">
            @csrf

            <!-- Email -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" required />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
            </div>

            <!-- Remember -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600" />
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end mt-4 w-full" style="width: 100%">
                <button type="submit" id="login-btn" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800 transition-colors relative w-full">
                    <span class="btn-text">Đăng nhập</span>
                </button>
            </div>

            <!-- Google -->
            <div class="mt-4">
                <button type="button" id="google-login-btn" class="w-full flex items-center justify-center px-4 py-2 bg-gray-900 text-white rounded hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 48 48">
                        <path fill="#fff" d="M22.56 12.25c0-.72-.15-1.39-.19-2.1H12v3.88h5.91c-.26 1.37-1.04 2.58-2.21 3.39v2.8h3.57c2.08-1.92 3.28-4.74 3.28-8.07z"/>
                        <path fill="#fff" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.69-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#fff" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#fff" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Đăng nhập bằng Google
                </button>
            </div>
        </form>

        <!-- Toast Container -->
        <div class="toast-container" id="toast-container"></div>
    </x-guest-layout>

    <script>
        // Toast Notification System
        class ToastNotification {
            constructor() {
                this.container = document.getElementById('toast-container');
                if (!this.container) {
                    this.container = document.createElement('div');
                    this.container.className = 'toast-container';
                    this.container.id = 'toast-container';
                    document.body.appendChild(this.container);
                }
            }

            show(type, title, message, duration = 4000) {
                const toast = this.createToast(type, title, message);
                this.container.appendChild(toast);

                // Trigger animation
                setTimeout(() => toast.classList.add('show'), 10);

                // Auto remove
                setTimeout(() => this.remove(toast), duration);

                return toast;
            }

            createToast(type, title, message) {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;

                const icon = this.getIcon(type);
                
                toast.innerHTML = `
                    <div class="toast-icon">${icon}</div>
                    <div class="toast-content">
                        <div class="toast-title">${title}</div>
                        <div class="toast-message">${message}</div>
                    </div>
                    <button class="toast-close" onclick="toastNotification.remove(this.parentElement)">&times;</button>
                `;

                return toast;
            }

            getIcon(type) {
                const icons = {
                    success: `<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                    </svg>`,
                    error: `<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                    </svg>`,
                    warning: `<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                    </svg>`,
                    info: `<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                    </svg>`
                };
                return icons[type] || icons.info;
            }

            remove(toast) {
                toast.classList.remove('show');
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.parentElement.removeChild(toast);
                    }
                }, 300);
            }

            success(title, message, duration) {
                return this.show('success', title, message, duration);
            }

            error(title, message, duration) {
                return this.show('error', title, message, duration);
            }

            warning(title, message, duration) {
                return this.show('warning', title, message, duration);
            }

            info(title, message, duration) {
                return this.show('info', title, message, duration);
            }
        }

        // Initialize toast notification
        const toastNotification = new ToastNotification();

        // Form handling
        const loginForm = document.getElementById('login-form');
        const loginStatus = document.getElementById('login-status');
        const loginBtn = document.getElementById('login-btn');

        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Show loading state
            loginBtn.classList.add('btn-loading');
     

            try {
                const res = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await res.json();

                if (data.status) {
                    // Check if user is admin
                    if (data.user && data.user.role === 'admin') {
                        localStorage.setItem('access_token', data.access_token);
                        toastNotification.success('Đăng nhập thành công', 'Chào mừng Admin! Đang chuyển hướng...', 2000);
                        
                        // Delay redirect to show toast
                        setTimeout(() => {
                            window.location.href = '/admin/dashboard';
                        }, 1500);
                    } else {
                        toastNotification.error('Truy cập bị từ chối', 'Chỉ Admin mới có thể đăng nhập vào hệ thống này!', 5000);
                        loginBtn.classList.remove('btn-loading');
                    }
                } else {
                    toastNotification.error('Đăng nhập thất bại', data.message || 'Email hoặc mật khẩu không đúng!', 4000);
                    loginBtn.classList.remove('btn-loading');
                }
            } catch (error) {
                console.error('Login error:', error);
                toastNotification.error('Lỗi hệ thống', 'Có lỗi xảy ra khi đăng nhập. Vui lòng thử lại!', 4000);
                loginBtn.classList.remove('btn-loading');
            }
        });

        document.getElementById('google-login-btn').addEventListener('click', async function () {
            toastNotification.info('Chuyển hướng', 'Đang chuyển đến Google để xác thực...', 2000);
            setTimeout(() => {
                window.location.href = `/api/login/google`;
            }, 500);
        });

        // Demo toast notifications (remove in production)
        // setTimeout(() => {
        //     toastNotification.success('Chào mừng!', 'Hệ thống đã sẵn sàng để sử dụng');
        // }, 1000);
    </script>
</body>
</html>