<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #000000;
            --primary-dark: #1a1a1a;
            --primary-light: #333333;
            --success: #10B981;
            --error: #EF4444;
            --warning: #F59E0B;
            --info: #666666;
            --text-primary: #000000;
            --text-secondary: #666666;
            --border: #E5E7EB;
            --bg-primary: #FFFFFF;
            --bg-secondary: #F9FAFB;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        /* Subtle grid pattern */
        body::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(0, 0, 0, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
            background-size: 20px 20px;
            pointer-events: none;
        }

        .login-container {
            background: var(--bg-primary);
            border-radius: 24px;
            box-shadow: var(--shadow-xl), 0 0 60px rgba(0, 0, 0, 0.15);
            padding: 48px;
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            background: #000000;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
        }

        .logo svg {
            width: 32px;
            height: 32px;
            color: white;
        }

        .logo-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .logo-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 15px;
            color: var(--text-primary);
            background: var(--bg-secondary);
            transition: all 0.2s;
            outline: none;
        }

        .form-input:focus {
            border-color: #000000;
            background: var(--bg-primary);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
        }

        .form-input:focus + .input-icon {
            color: #000000;
        }

        .form-input.error {
            border-color: var(--error);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: var(--text-primary);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }

        .checkbox-input {
            width: 18px;
            height: 18px;
            border: 2px solid var(--border);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .checkbox-input:checked {
            background: #000000;
            border-color: #000000;
        }

        .checkbox-label {
            margin-left: 8px;
            font-size: 14px;
            color: var(--text-secondary);
            cursor: pointer;
            user-select: none;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: #000000;
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover:not(:disabled) {
            background: #1a1a1a;
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-secondary {
            background: white;
            color: var(--text-primary);
            border: 2px solid var(--border);
            margin-top: 12px;
        }

        .btn-secondary:hover:not(:disabled) {
            background: var(--bg-secondary);
            border-color: var(--text-secondary);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-loading .btn-text {
            opacity: 0;
        }

        .btn-spinner {
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0;
            color: var(--text-secondary);
            font-size: 13px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border);
        }

        .divider span {
            padding: 0 16px;
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .toast {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-xl);
            min-width: 340px;
            max-width: 420px;
            transform: translateX(450px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            border-left: 4px solid;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.success { border-left-color: var(--success); }
        .toast.error { border-left-color: var(--error); }
        .toast.warning { border-left-color: var(--warning); }
        .toast.info { border-left-color: var(--info); }

        .toast-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .toast.success .toast-icon { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .toast.error .toast-icon { background: rgba(239, 68, 68, 0.1); color: var(--error); }
        .toast.warning .toast-icon { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .toast.info .toast-icon { background: rgba(59, 130, 246, 0.1); color: var(--info); }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
            margin-bottom: 4px;
        }

        .toast-message {
            color: var(--text-secondary);
            font-size: 13px;
            line-height: 1.5;
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 4px;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .toast-close:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: currentColor;
            opacity: 0.3;
            border-radius: 0 0 12px 12px;
            animation: progress 4s linear forwards;
        }

        @keyframes progress {
            from { width: 100%; }
            to { width: 0%; }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 32px 24px;
            }

            .logo-title {
                font-size: 24px;
            }

            .toast {
                min-width: 300px;
            }

            .toast-container {
                right: 16px;
                left: 16px;
            }
        }

        /* Additional animations */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <div class="logo">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h1 class="logo-title">Chào mừng trở lại</h1>
            <p class="logo-subtitle">Đăng nhập vào Admin Dashboard</p>
        </div>

        <form id="login-form">
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <div class="input-wrapper">
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="admin@example.com"
                        required
                        autocomplete="email"
                    >
                    <span class="input-icon">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Mật khẩu</label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    <span class="input-icon">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                    <button type="button" class="password-toggle" id="toggle-password">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="eye-open">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="eye-closed" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="remember" name="remember" class="checkbox-input">
                <label for="remember" class="checkbox-label">Ghi nhớ đăng nhập</label>
            </div>

            <button type="submit" class="btn btn-primary" id="login-btn">
                <span class="btn-text">Đăng nhập</span>
            </button>

            <div class="divider">
                <span>hoặc</span>
            </div>

            <button type="button" class="btn btn-secondary" id="google-login-btn">
                <svg width="20" height="20" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span>Đăng nhập với Google</span>
            </button>
        </form>
    </div>

    <div class="toast-container" id="toast-container"></div>

    <script>
        // Toast Notification System
        class ToastNotification {
            constructor() {
                this.container = document.getElementById('toast-container');
            }

            show(type, title, message, duration = 4000) {
                const toast = this.createToast(type, title, message);
                this.container.appendChild(toast);
                
                setTimeout(() => toast.classList.add('show'), 10);
                
                const progressBar = toast.querySelector('.toast-progress');
                if (progressBar) {
                    progressBar.style.animationDuration = `${duration}ms`;
                }
                
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
                    <button class="toast-close" onclick="toastNotification.remove(this.parentElement)">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <div class="toast-progress"></div>
                `;
                
                return toast;
            }

            getIcon(type) {
                const icons = {
                    success: `<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>`,
                    error: `<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>`,
                    warning: `<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>`,
                    info: `<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>`
                };
                return icons[type] || icons.info;
            }

            remove(toast) {
                toast.classList.remove('show');
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 400);
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

        const toastNotification = new ToastNotification();

        // Password toggle
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            
            this.querySelector('.eye-open').style.display = type === 'password' ? 'block' : 'none';
            this.querySelector('.eye-closed').style.display = type === 'password' ? 'none' : 'block';
        });

        // Form validation
        function validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function validatePassword(password) {
            return password.length >= 6;
        }

        // Form handling
        const loginForm = document.getElementById('login-form');
        const loginBtn = document.getElementById('login-btn');
        const emailInput = document.getElementById('email');
        const passwordInputField = document.getElementById('password');

        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = emailInput.value.trim();
            const password = passwordInputField.value;

            // Client-side validation
            if (!validateEmail(email)) {
                emailInput.classList.add('error');
                toastNotification.error('Email không hợp lệ', 'Vui lòng nhập địa chỉ email đúng định dạng');
                return;
            }
            emailInput.classList.remove('error');

            if (!validatePassword(password)) {
                passwordInputField.classList.add('error');
                toastNotification.error('Mật khẩu quá ngắn', 'Mật khẩu phải có ít nhất 6 ký tự');
                return;
            }
            passwordInputField.classList.remove('error');

            // Show loading state
            loginBtn.disabled = true;
            loginBtn.classList.add('btn-loading');
            loginBtn.innerHTML = '<span class="btn-spinner"></span><span class="btn-text">Đăng nhập</span>';

            try {
                const res = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await res.json();

                if (data.status) {
                    if (data.user && data.user.role === 'admin') {
                        localStorage.setItem('access_token', data.access_token);
                        toastNotification.success('Đăng nhập thành công!', 'Chào mừng quay trở lại, Admin!', 2000);
                        
                        setTimeout(() => {
                            window.location.href = '/admin/dashboard';
                        }, 1500);
                    } else {
                        toastNotification.error('Truy cập bị từ chối', 'Chỉ tài khoản Admin mới có thể đăng nhập vào hệ thống này', 5000);
                        resetButton();
                    }
                } else {
                    toastNotification.error('Đăng nhập thất bại', data.message || 'Email hoặc mật khẩu không chính xác', 4000);
                    resetButton();
                }
            } catch (error) {
                console.error('Login error:', error);
                toastNotification.error('Lỗi kết nối', 'Không thể kết nối đến máy chủ. Vui lòng thử lại sau!', 4000);
                resetButton();
            }
        });

        function resetButton() {
            loginBtn.disabled = false;
            loginBtn.classList.remove('btn-loading');
            loginBtn.innerHTML = '<span class="btn-text">Đăng nhập</span>';
        }

        // Google login
        document.getElementById('google-login-btn').addEventListener('click', function() {
            toastNotification.info('Đang chuyển hướng...', 'Vui lòng chờ trong giây lát', 2000);
            setTimeout(() => {
                window.location.href = '/api/login/google';
            }, 500);
        });

        // Input animations
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('fade-in');
            });
        });
    </script>
</body>
</html>
