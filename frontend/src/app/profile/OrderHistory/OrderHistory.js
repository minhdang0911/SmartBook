// components/Profile/OrderHistory/OrderHistory.js
import { Spin, Tabs } from 'antd';
import { useState } from 'react';
import { useOrderDetail, useOrders } from '../../hooks/useOrders';
import OrderCard from './OrderCard';
import OrderDetailModal from './OrderDetailModal';

const OrderHistory = ({ token, enabled }) => {
    const { orders, loading, cancelOrder } = useOrders(token, enabled);
    const [activeTab, setActiveTab] = useState('all');
    const [selectedOrderId, setSelectedOrderId] = useState(null);
    const [isModalVisible, setIsModalVisible] = useState(false);

    const { orderDetail } = useOrderDetail(selectedOrderId, token);

    const handleViewDetail = (orderId) => {
        setSelectedOrderId(orderId);
        setIsModalVisible(true);
    };

    const handleCloseModal = () => {
        setIsModalVisible(false);
        setSelectedOrderId(null);
    };

    const filteredOrders = activeTab === 'all' ? orders : orders.filter((order) => order.status === activeTab);

    const statusTabs = [
        { key: 'all', label: 'Tất cả' },
        { key: 'ready_to_pick', label: 'Chờ lấy hàng' },
        { key: 'picking', label: 'Đang lấy hàng' },
        { key: 'picked', label: 'Đã lấy hàng' },
        { key: 'delivering', label: 'Đang giao' },
        { key: 'delivered', label: 'Đã giao' },
        { key: 'cancelled', label: 'Đã hủy' },
    ];

    if (loading) {
        return (
            <div
                style={{
                    display: 'flex',
                    justifyContent: 'center',
                    alignItems: 'center',
                    minHeight: '400px',
                }}
            >
                <Spin size="large" />
                <div style={{ marginLeft: '16px', fontSize: '16px', color: '#666' }}>Đang tải lịch sử đơn hàng...</div>
            </div>
        );
    }

    return (
        <div
            style={{
                background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                minHeight: '100vh',
                padding: '40px 20px',
            }}
        >
            <div
                style={{
                    maxWidth: '1200px',
                    margin: '0 auto',
                    background: 'rgba(255, 255, 255, 0.98)',
                    borderRadius: '24px',
                    padding: '40px',
                    boxShadow: '0 20px 40px rgba(0, 0, 0, 0.15)',
                    backdropFilter: 'blur(10px)',
                }}
            >
 
                <div
                    style={{
                        textAlign: 'center',
                        marginBottom: '40px',
                        background: 'linear-gradient(135deg, #667eea, #764ba2)',
                        WebkitBackgroundClip: 'text',
                        WebkitTextFillColor: 'transparent',
                        backgroundClip: 'text',
                    }}
                >
                    <h1
                        style={{
                            fontSize: '2.5rem',
                            fontWeight: '700',
                            marginBottom: '8px',
                            letterSpacing: '-0.5px',
                            margin: '0 0 8px 0',
                        }}
                    >
                        📦 Lịch sử đơn hàng
                    </h1>
                    <p
                        style={{
                            fontSize: '1.1rem',
                            color: '#64748b',
                            fontWeight: '400',
                            margin: 0,
                        }}
                    >
                        Quản lý và theo dõi đơn hàng của bạn
                    </p>
                </div>

                <div style={{ marginBottom: '32px' }}>
                    <Tabs
                        activeKey={activeTab}
                        onChange={(key) => setActiveTab(key)}
                        items={statusTabs}
                        centered
                        tabBarStyle={{
                            background: 'rgba(102, 126, 234, 0.08)',
                            padding: '8px',
                            borderRadius: '16px',
                            border: 'none',
                            marginBottom: '0',
                        }}
                    />
                </div>

                {/* Loading State */}
                {loading && (
                    <div style={{ textAlign: 'center', padding: '60px 0' }}>
                        <Spin size="large" />
                        <div style={{ marginTop: '16px', color: '#666' }}>Đang tải đơn hàng...</div>
                    </div>
                )}

                {/* Orders Grid */}
                {!loading && filteredOrders.length > 0 && (
                    <div
                        style={{
                            display: 'grid',
                            gap: '24px',
                            gridTemplateColumns: 'repeat(auto-fill, minmax(400px, 1fr))',
                        }}
                    >
                        {filteredOrders.map((order) => (
                            <OrderCard
                                key={order.id}
                                order={order}
                                onViewDetail={handleViewDetail}
                                onCancelOrder={cancelOrder}
                            />
                        ))}
                    </div>
                )}

                {/* Empty State */}
                {!loading && filteredOrders.length === 0 && (
                    <div
                        style={{
                            textAlign: 'center',
                            padding: '60px 20px',
                            background: 'linear-gradient(135deg, #f8fafc, #e2e8f0)',
                            borderRadius: '20px',
                            border: '2px dashed #cbd5e1',
                        }}
                    >
                        <div style={{ fontSize: '4rem', marginBottom: '16px' }}>📦</div>
                        <h3
                            style={{
                                fontSize: '1.5rem',
                                color: '#64748b',
                                fontWeight: '600',
                                marginBottom: '8px',
                                margin: '0 0 8px 0',
                            }}
                        >
                            Không có đơn hàng nào
                        </h3>
                        <p style={{ color: '#94a3b8', fontSize: '1rem', margin: 0 }}>
                            {activeTab === 'all'
                                ? 'Bạn chưa có đơn hàng nào'
                                : 'Chưa có đơn hàng nào trong danh mục này'}
                        </p>
                    </div>
                )}

                {/* Order Detail Modal */}
                <OrderDetailModal
                    visible={isModalVisible}
                    onCancel={handleCloseModal}
                    order={orderDetail}
                    token={token}
                />
            </div>
        </div>
    );
};

export default OrderHistory;
