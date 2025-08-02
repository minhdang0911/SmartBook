<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .order-title {
            font-size: 20px;
            font-weight: 300;
            opacity: 0.9;
        }

        .order-code-highlight {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 25px;
            margin: 15px 0;
            font-size: 18px;
            font-weight: 600;
            display: inline-block;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .check-icon {
            display: inline-block;
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            margin: 20px 0;
            position: relative;
        }

        .check-icon::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            font-weight: bold;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .greeting strong {
            color: #667eea;
        }

        .thank-you {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(102,126,234,0.05) 0%, transparent 70%);
        }

        .info-card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card h3::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #667eea;
            border-radius: 50%;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #34495e;
        }

        .info-value {
            color: #2c3e50;
        }

        .order-code-value {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 1px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge::before {
            content: '●';
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .order-items {
            margin: 30px 0;
        }

        .item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .item-info {
            flex: 1;
        }

        .item-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 16px;
        }

        .item-details {
            color: #7f8c8d;
            font-size: 14px;
            line-height: 1.4;
        }

        .item-price {
            text-align: right;
            color: #2c3e50;
        }

        .price-main {
            font-weight: 700;
            font-size: 16px;
            color: #e74c3c;
        }

        .price-sub {
            font-size: 14px;
            color: #7f8c8d;
        }

        .total-section {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin: 25px 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .total-main {
            border-top: 2px solid rgba(255,255,255,0.2);
            margin-top: 10px;
            padding-top: 15px;
            font-size: 20px;
            font-weight: 700;
        }

        .next-steps {
            background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%);
            border: 1px solid #27ae60;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }

        .next-steps h3 {
            color: #27ae60;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .next-steps h3::before {
            content: 'ℹ';
            background: #27ae60;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .step {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin: 12px 0;
            color: #2c3e50;
        }

        .step-number {
            background: #27ae60;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .footer-content {
            max-width: 400px;
            margin: 0 auto;
        }

        .contact-info {
            margin: 15px 0;
            padding: 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }

        .social-links {
            margin-top: 20px;
        }

        .social-link {
            display: inline-block;
            margin: 0 10px;
            padding: 8px 12px;
            background: rgba(255,255,255,0.1);
            border-radius: 6px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .email-container {
                border-radius: 8px;
            }

            .header, .content, .footer {
                padding: 20px;
            }

            .item {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .total-row {
                flex-direction: column;
                gap: 5px;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .info-card {
                background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
                color: #ecf0f1;
            }

            .item {
                background: #34495e;
                border-color: #2c3e50;
                color: #ecf0f1;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="header-content">
                <div class="logo">📚 SmartBook App</div>
                <div class="check-icon"></div>
                <div class="order-title">Xác nhận đơn hàng</div>
                <div class="order-code-highlight">
                    📋 Mã đơn hàng: {{ $order->order_code }}
                </div>
            </div>
        </div>

        <div class="content">
            <div class="greeting">
                Xin chào <strong>{{ $user->name }}</strong>! 👋
            </div>
            
            <div class="thank-you">
                Cảm ơn bạn đã tin tưởng và đặt hàng tại SmartBook App. Đơn hàng của bạn đã được tiếp nhận thành công và đang được xử lý với sự chăm sóc tối đa.
            </div>

            <div class="info-card">
                <h3>📋 Thông tin đơn hàng</h3>
                <div class="info-row">
                    <span class="info-label">Mã đơn hàng:</span>
                    <span class="order-code-value">{{ $order->order_code }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">ID đơn hàng:</span>
                    <span class="info-value">#{{ $order->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày đặt:</span>
                    <span class="info-value">{{ $order->created_at->format('d/m/Y H:i:s') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Trạng thái:</span>
                    <span class="status-badge">{{ $order->status_label }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phương thức thanh toán:</span>
                    <span class="info-value">{{ $order->payment_label }}</span>
                </div>
            </div>

            <div class="info-card">
                <h3>🚚 Địa chỉ giao hàng</h3>
                <div class="info-row">
                    <span class="info-value">{{ $order->address }}</span>
                </div>
                @if($order->note)
                <div class="info-row">
                    <span class="info-label">Ghi chú:</span>
                    <span class="info-value">{{ $order->note }}</span>
                </div>
                @endif
            </div>

            <div class="order-items">
                <h3 style="color: #2c3e50; margin-bottom: 20px; font-size: 20px;">📦 Chi tiết đơn hàng</h3>
                @foreach($orderItems as $item)
                <div class="item">
                    <div class="item-info">
                        <div class="item-title">{{ $item->book->title }}</div>
                        <div class="item-details">
                            👤 Tác giả: {{ $item->book->author ?? 'Đang cập nhật' }}<br>
                            📊 Số lượng: {{ $item->quantity }} cuốn
                        </div>
                    </div>
                    <div class="item-price">
                        <div class="price-main">{{ number_format($item->price, 0, ',', '.') }}đ</div>
                        <div class="price-sub">Tổng: {{ number_format($item->total_price, 0, ',', '.') }}đ</div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="total-section">
                <div class="total-row">
                    <span>Tổng sản phẩm ({{ $totalQuantity }} cuốn):</span>
                    <span>{{ number_format($order->price, 0, ',', '.') }}đ</span>
                </div>
                <div class="total-row">
                    <span>Phí vận chuyển:</span>
                    <span>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</span>
                </div>
                <div class="total-row total-main">
                    <span>💰 Tổng thanh toán:</span>
                    <span>{{ number_format($order->total_price, 0, ',', '.') }}đ</span>
                </div>
            </div>

            <div class="next-steps">
                <h3>🚀 Các bước tiếp theo</h3>
                <div class="step">
                    <div class="step-number">1</div>
                    <div>Đơn hàng sẽ được xử lý và đóng gói trong vòng 1-2 ngày làm việc</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div>Bạn sẽ nhận được thông báo khi đơn hàng được giao cho đơn vị vận chuyển</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div>Thời gian giao hàng dự kiến: 3-7 ngày làm việc</div>
                </div>
                @if($order->payment === 'cod')
                <div class="step">
                    <div class="step-number">💳</div>
                    <div>Bạn sẽ thanh toán khi nhận hàng (COD)</div>
                </div>
                @endif
            </div>

            <div style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border: 1px solid #f39c12; border-radius: 12px; padding: 20px; margin: 25px 0; text-align: center;">
                <div style="color: #856404; font-size: 16px; margin-bottom: 10px;">
                    <strong>📞 Cần hỗ trợ?</strong>
                </div>
                <div style="color: #856404;">
                    Liên hệ với chúng tôi và cung cấp mã đơn hàng <strong>{{ $order->order_code }}</strong> để được hỗ trợ nhanh chóng
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px; color: #7f8c8d;">
                <p>Trân trọng cảm ơn,</p>
                <p style="font-weight: 600; color: #2c3e50; margin-top: 10px;">💝 Đội ngũ SmartBook App</p>
            </div>
        </div>

        <div class="footer">
            <div class="footer-content">
                <div style="font-size: 18px; font-weight: 600; margin-bottom: 15px;">
                    📚 SmartBook App
                </div>
                <div class="contact-info">
                    <div>📧 support@smartbook.com</div>
                    <div>📞 Hotline: 1900-xxxx</div>
                    <div>🌐 www.smartbook.com</div>
                </div>
                <div class="social-links">
                    <a href="#" class="social-link">Facebook</a>
                    <a href="#" class="social-link">Instagram</a>
                    <a href="#" class="social-link">Zalo</a>
                </div>
                <div style="margin-top: 20px; opacity: 0.8; font-size: 14px;">
                    © {{ date('Y') }} SmartBook App. Tất cả quyền được bảo lưu.
                </div>
            </div>
        </div>
    </div>
</body>
</html>