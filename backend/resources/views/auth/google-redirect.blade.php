<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đang chuyển hướng...</title>
</head>
<body>
    <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
        <h3>Đang xử lý đăng nhập...</h3>
        <p>Vui lòng chờ trong giây lát.</p>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('access_token');
        
        if (token) {
            // Lưu token vào localStorage
            localStorage.setItem('access_token', token);
            
            // Chuyển hướng về trang chủ frontend
            window.location.href = "/";
        } else {
            document.body.innerHTML = `
                <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
                    <h3 style="color: red;">Lỗi đăng nhập</h3>
                    <p>Không tìm thấy access token!</p>
                    <a href="/" style="color: blue; text-decoration: underline;">Quay về trang chủ</a>
                </div>
            `;
        }
    </script>
</body>
</html>