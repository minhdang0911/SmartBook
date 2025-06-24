'use client'
import React, { useState } from 'react';
import { 
  Form, 
  Input, 
  Select, 
  Button, 
  Card, 
  Radio, 
  Divider, 
  Typography, 
  Space,
  Row,
  Col,
  Image,
  Alert
} from 'antd';
import { 
  ShoppingCartOutlined, 
  BellOutlined, 
  UserOutlined,
  CreditCardOutlined,
  QrcodeOutlined,
  BankOutlined,
  WalletOutlined,
  DownloadOutlined
} from '@ant-design/icons';
import './CheckoutPage.css';
import './responsive.css';

const { Title, Text } = Typography;
const { Option } = Select;

const CheckoutPage = () => {
  const [form] = Form.useForm();
  const [paymentMethod, setPaymentMethod] = useState('card');

  const orderSummary = {
    products: [
      {
        id: 1,
        name: 'Cùng nhỏ nối dài và bí ẩn thật',
        author: 'Tiểu hoa - Họa 2 Lưu Hoàng',
        quantity: 8,
        price: 149000,
        image: '/api/placeholder/80/100'
      }
    ],
    subtotal: 1192000,
    shipping: 0,
    voucher: 0,
    total: 1192000
  };

  const handleSubmit = (values) => {
    console.log('Form submitted:', values);
  };

  return (
    <div className="checkout-container">
      {/* Header */}
      <header className="checkout-header">
        <div className="header-content">
          <div className="logo">
            <span className="logo-waka">WAKA</span>
            <span className="logo-shop">SHOP</span>
            <span className="divider">|</span>
            <span className="page-title">Thanh toán</span>
          </div>
          <div className="header-actions">
            <ShoppingCartOutlined className="header-icon" />
            <BellOutlined className="header-icon" />
            <div className="user-avatar">
              <UserOutlined />
            </div>
          </div>
        </div>
      </header>

      <div className="checkout-content">
        <Row gutter={24}>
          {/* Left Column - Checkout Form */}
          <Col xs={24} lg={16}>
            <div className="checkout-form-section">
              <Title level={3} className="section-title">Xác nhận thanh toán</Title>
              
              {/* Delivery Address */}
              <Card className="form-card">
                <Title level={4} className="card-title">Địa chỉ nhận hàng</Title>
                <Form form={form} layout="vertical" onFinish={handleSubmit}>
                  <Row gutter={16}>
                    <Col xs={24} md={8}>
                      <Form.Item label="Họ và tên" name="fullName" rules={[{ required: true }]}>
                        <Input placeholder="Nhập họ và tên" />
                      </Form.Item>
                    </Col>
                    <Col xs={24} md={8}>
                      <Form.Item label="Số điện thoại" name="phone" rules={[{ required: true }]}>
                        <Input placeholder="Nhập số điện thoại" />
                      </Form.Item>
                    </Col>
                    <Col xs={24} md={8}>
                      <Form.Item label="Email" name="email">
                        <Input placeholder="Nhập email" />
                      </Form.Item>
                    </Col>
                  </Row>
                  
                  <Row gutter={16}>
                    <Col xs={24} md={8}>
                      <Form.Item label="Tỉnh/Thành Phố" name="province" rules={[{ required: true }]}>
                        <Select placeholder="Chọn tỉnh/thành phố">
                          <Option value="hcmc">Hồ Chí Minh</Option>
                          <Option value="hanoi">Hà Nội</Option>
                          <Option value="danang">Đà Nẵng</Option>
                        </Select>
                      </Form.Item>
                    </Col>
                    <Col xs={24} md={8}>
                      <Form.Item label="Quận/Huyện" name="district" rules={[{ required: true }]}>
                        <Select placeholder="Chọn quận/huyện">
                          <Option value="district1">Quận 1</Option>
                          <Option value="district2">Quận 2</Option>
                          <Option value="district3">Quận 3</Option>
                        </Select>
                      </Form.Item>
                    </Col>
                    <Col xs={24} md={8}>
                      <Form.Item label="Phường/Xã/Thị Trấn" name="ward" rules={[{ required: true }]}>
                        <Select placeholder="Chọn phường/xã">
                          <Option value="ward1">Phường 1</Option>
                          <Option value="ward2">Phường 2</Option>
                          <Option value="ward3">Phường 3</Option>
                        </Select>
                      </Form.Item>
                    </Col>
                       <Col xs={24} md={8}>
                      <Form.Item label="Quận/Huyện" name="district" rules={[{ required: true }]}>
                        <Select placeholder="Chọn số nhà">
                          <Option value="district1">Quận 1</Option>
                          <Option value="district2">Quận 2</Option>
                          <Option value="district3">Quận 3</Option>
                        </Select>
                      </Form.Item>
                    </Col>
                    <Col xs={24} md={8}>
                      <Form.Item label="Phường/Xã/Thị Trấn" name="ward" rules={[{ required: true }]}>
                        <Select placeholder="Chọn phường/xã">
                          <Option value="ward1">Phường 1</Option>
                          <Option value="ward2">Phường 2</Option>
                          <Option value="ward3">Phường 3</Option>
                        </Select>
                      </Form.Item>
                      
                    </Col>
                  </Row>
                  
                  <Form.Item label="Địa chỉ chi tiết" name="address" rules={[{ required: true }]}>
                    <Input placeholder="Nhập địa chỉ chi tiết" />
                  </Form.Item>
                  
                  <Form.Item label="Loại Địa Chỉ" name="addressType">
                    <Select placeholder="Chọn loại địa chỉ" defaultValue="home">
                      <Option value="home">Nhà riêng</Option>
                      <Option value="office">Văn phòng</Option>
                      <Option value="other">Khác</Option>
                    </Select>
                  </Form.Item>
                  
                  <Form.Item label="Ghi chú" name="note">
                    <Input.TextArea rows={3} placeholder="Nhập ghi chú (không bắt buộc)" />
                  </Form.Item>
                  
                  <Button type="primary" className="save-info-btn">
                    Lưu thông tin
                  </Button>
                </Form>
              </Card>

              {/* Products */}
              <Card className="form-card">
                <Title level={4} className="card-title">
                  <DownloadOutlined /> Sản phẩm
                </Title>
                <div className="product-section">
                  <Text className="section-subtitle">Ebooks</Text>
                  {orderSummary.products.map(product => (
                    <div key={product.id} className="product-item">
                      <Image 
                        src={product.image} 
                        alt={product.name}
                        width={60}
                        height={80}
                        className="product-image"
                      />
                      <div className="product-details">
                        <Text strong className="product-name">{product.name}</Text>
                        <Text className="product-author">{product.author}</Text>
                        <Text className="product-quantity">Số lượng: {product.quantity}</Text>
                      </div>
                      <div className="product-price">
                        <Text strong>{product.price.toLocaleString()}đ</Text>
                      </div>
                    </div>
                  ))}
                </div>
                
                <div className="shipping-info">
                  <Row>
                    <Col span={12}>
                      <Text>Đơn vị vận chuyển</Text>
                      <br />
                      <Text>Phí vận chuyển</Text>
                    </Col>
                    <Col span={12} className="text-right">
                      <Text>Chưa xác định</Text>
                      <br />
                      <Text>Chưa xác định</Text>
                    </Col>
                  </Row>
                  <div className="voucher-info">
                    <Text>Vận chuyển từ <Text className="highlight">Quan Đảo Tồ Lăng Hà Nội</Text> đến <Text className="highlight">Địa điểm chưa xác định</Text></Text>
                  </div>
                </div>
                
                <div className="total-section">
                  <Row justify="space-between" align="middle">
                    <Col>
                      <Text strong className="total-label">Tổng số tiền</Text>
                    </Col>
                    <Col>
                      <Text strong className="total-amount">{orderSummary.total.toLocaleString()}đ</Text>
                    </Col>
                  </Row>
                </div>
              </Card>

              {/* Payment Methods */}
              <Card className="form-card">
                <Title level={4} className="card-title">Chọn phương thức thanh toán</Title>
                <Radio.Group 
                  value={paymentMethod} 
                  onChange={(e) => setPaymentMethod(e.target.value)}
                  className="payment-methods"
                >
                  <div className="payment-option selected">
                    <Radio value="card" className="payment-radio">
                      <div className="payment-content">
                        <CreditCardOutlined className="payment-icon" />
                        <div>
                          <Text strong>Thanh toán khi nhận hàng</Text>
                          <br />
                          <Text className="payment-desc">Thanh toán khi nhận hàng</Text>
                        </div>
                      </div>
                    </Radio>
                  </div>
                  
                  <div className="payment-option">
                    <Radio value="qr" className="payment-radio">
                      <div className="payment-content">
                        <QrcodeOutlined className="payment-icon" />
                        <div>
                          <Text strong>Quét QR CODE</Text>
                        </div>
                      </div>
                    </Radio>
                  </div>
                  
                  <div className="payment-option">
                    <Radio value="atm" className="payment-radio">
                      <div className="payment-content">
                        <BankOutlined className="payment-icon" />
                        <div>
                          <Text strong>Thẻ ATM có Internet Banking</Text>
                          <br />
                          <Text className="payment-desc">Thẻ ngân hàng nội địa</Text>
                        </div>
                      </div>
                    </Radio>
                  </div>
                  
                  <div className="payment-option">
                    <Radio value="international" className="payment-radio">
                      <div className="payment-content">
                        <CreditCardOutlined className="payment-icon" />
                        <div>
                          <Text strong>Thẻ quốc tế Visa/Master/JBC</Text>
                        </div>
                      </div>
                    </Radio>
                  </div>
                  
                  <div className="payment-option">
                    <Radio value="wallet" className="payment-radio">
                      <div className="payment-content">
                        <WalletOutlined className="payment-icon" />
                        <div>
                          <Text strong>Ví điện tử</Text>
                          <br />
                          <Text className="payment-desc">MoMo, ZaloPay</Text>
                        </div>
                      </div>
                    </Radio>
                  </div>
                </Radio.Group>
                
                <div className="invoice-option">
                  <Text>Xuất hóa đơn điện tử</Text>
                </div>
              </Card>
            </div>
          </Col>

          {/* Right Column - Order Summary */}
          <Col xs={24} lg={8}>
            <div className="order-summary-section">
              {/* Promotion Banner */}
              <Alert
                message="Giảm 30% phí vận chuyển, điện tử tham gia phần"
                type="error"
                showIcon
                className="promo-banner"
              />
              
              {/* Order Summary */}
              <Card className="summary-card">
                <Title level={4} className="card-title">Thông tin thanh toán</Title>
                
                <div className="summary-row">
                  <Text>Số sản phẩm</Text>
                  <Text>{orderSummary.products.reduce((sum, p) => sum + p.quantity, 0)} sản phẩm</Text>
                </div>
                
                <div className="summary-row">
                  <Text>Tổng tiền hàng</Text>
                  <Text>{orderSummary.subtotal.toLocaleString()}đ</Text>
                </div>
                
                <div className="summary-row">
                  <Text>Voucher của Waka</Text>
                  <Text>{orderSummary.voucher}đ</Text>
                </div>
                
                <div className="summary-row">
                  <Text>Giảm giá vận chuyển</Text>
                  <Text>{orderSummary.shipping}đ</Text>
                </div>
                
                <div className="summary-row">
                  <Text>Phí vận chuyển</Text>
                  <Text className="highlight">Chưa xác định</Text>
                </div>
                
                <Divider />
                
                <div className="summary-row total-row">
                  <Text strong>Tổng cộng</Text>
                  <Text strong className="total-price">{orderSummary.total.toLocaleString()}đ</Text>
                </div>
                
                <Button type="primary" size="large" className="checkout-btn" block>
                  Mua hàng
                </Button>
              </Card>
            </div>
          </Col>
        </Row>
      </div>

      {/* Footer */}
      <footer className="checkout-footer">
        <div className="footer-content">
          <div className="footer-section">
            <div className="footer-logo">
              <span className="logo-waka">WAKA</span>
            </div>
            <Text className="footer-desc">
              Công ty Cổ phần Sách điện tử Waka
            </Text>
            <div className="contact-info">
              <Text>📞 0877736269</Text>
              <Text>✉️ Support@waka.vn</Text>
            </div>
          </div>
          
          <div className="footer-section">
            <Title level={5} className="footer-title">Về chúng tôi</Title>
            <div className="footer-links">
              <Text>Giới thiệu</Text>
              <Text>Cơ cấu tổ chức</Text>
              <Text>Liên hệ hoạt động</Text>
            </div>
          </div>
          
          <div className="footer-section">
            <Title level={5} className="footer-title">Thông tin hỗ trợ</Title>
            <div className="footer-links">
              <Text>Thẻ thanh toán ứng dụng dịch vụ</Text>
              <Text>Quyền lợi</Text>
              <Text>Quy định riêng tư</Text>
              <Text>Câu hỏi thường gặp</Text>
            </div>
          </div>
          
          <div className="footer-section">
            <Title level={5} className="footer-title">Tải ứng dụng</Title>
            <div className="app-downloads">
              <div className="qr-code">📱</div>
              <div className="download-buttons">
                <div className="download-btn">App Store</div>
                <div className="download-btn">Google Play</div>
              </div>
            </div>
          </div>
        </div>
        
        <div className="footer-bottom">
          <Text className="copyright">
            Công ty Cổ phần Sách điện tử Waka - Tầng 5, tòa nhà phố Hòa Bình, số 106, đường Hoàng Quốc Việt, phường Nghĩa Đô, Quận Cầu Giấy, thành phố Hà Nội, Việt Nam.
          </Text>
        </div>
      </footer>
    </div>
  );
};

export default CheckoutPage;