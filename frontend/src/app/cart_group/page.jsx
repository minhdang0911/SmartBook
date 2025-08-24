'use client';
import {
    ClockCircleOutlined,
    CopyOutlined,
    DeleteOutlined,
    LoadingOutlined,
    LockOutlined,
    LogoutOutlined,
    MinusOutlined,
    PlusOutlined,
    ShareAltOutlined,
    ShoppingCartOutlined,
    UserAddOutlined,
    UserOutlined,
} from '@ant-design/icons';
import {
    Alert,
    Button,
    Card,
    Col,
    Divider,
    Empty,
    Image,
    Input,
    InputNumber,
    message,
    Modal,
    Row,
    Space,
    Spin,
    Tag,
    Typography,
} from 'antd';
import { useRouter } from 'next/navigation';
import { useEffect, useState } from 'react';
import { toast } from 'react-toastify';
import '../cart/Cart.css'; // Reuse the same CSS file

const { Title, Text, Paragraph } = Typography;
const { confirm } = Modal;

const CartGroup = () => {
    const router = useRouter();
    const [groupData, setGroupData] = useState(null);
    const [joinUrl, setJoinUrl] = useState('');
    const [timeRemaining, setTimeRemaining] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [currentUserId, setCurrentUserId] = useState(null);
    const [actionLoading, setActionLoading] = useState({
        lock: false,
        leave: false,
        kick: {}, // { [userId]: boolean }
        quantityUpdate: {}, // { [itemId]: boolean }
        deleteItem: {}, // { [itemId]: boolean }
    });
    const [showLockConfirm, setShowLockConfirm] = useState(false);
    const [deleteConfirm, setDeleteConfirm] = useState({
        open: false,
        itemId: null,
        itemTitle: '',
    });

    // helpers
    const getAuthHeaders = () => {
        const authToken =
            localStorage.getItem('auth_token') || localStorage.getItem('access_token') || localStorage.getItem('token');

        const headers = {
            'Content-Type': 'application/json',
            Accept: 'application/json',
        };
        if (authToken) headers.Authorization = `Bearer ${authToken}`;
        return headers;
    };

    const getGroupToken = () => localStorage.getItem('group_cart_token');

    // try to detect current user id (tùy backend của m — ưu tiên localStorage để nhanh)
    const loadCurrentUserId = async () => {
        // 1) thử lấy từ localStorage
        const localId =
            localStorage.getItem('user_id') || localStorage.getItem('uid') || localStorage.getItem('auth_user_id');

        if (localId) {
            setCurrentUserId(String(localId));
            return;
        }

        // 2) fallback gọi /api/me (nếu backend có)
        try {
            const res = await fetch('/api/me', {
                method: 'GET',
                headers: getAuthHeaders(),
                credentials: 'include',
            });
            if (res.ok) {
                const me = await res.json();
                if (me?.id || me?.user?.id) {
                    setCurrentUserId(String(me.id ?? me.user.id));
                }
            }
        } catch (_) {
            // bỏ qua, không có cũng không sao
        }
    };

    // Fetch group data from API using token from localStorage
    const fetchGroupData = async () => {
        try {
            setLoading(true);
            setError(null);

            // Get token from localStorage
            const groupToken = getGroupToken();

            if (!groupToken) {
                throw new Error('Không tìm thấy token giỏ hàng nhóm. Vui lòng tạo giỏ hàng nhóm mới.');
            }

            const response = await fetch(`http://localhost:8000/api/group-orders/${groupToken}`, {
                method: 'GET',
                headers: getAuthHeaders(),
                credentials: 'include',
            });

            if (!response.ok) {
                let errorMessage = 'Không thể tải thông tin giỏ hàng nhóm';

                if (response.status === 404) {
                    errorMessage = 'Giỏ hàng nhóm không tồn tại hoặc đã hết hạn';
                } else if (response.status === 401) {
                    errorMessage = 'Bạn không có quyền truy cập giỏ hàng nhóm này';
                } else {
                    try {
                        const errorData = await response.json();
                        errorMessage = errorData.message || errorMessage;
                    } catch {
                        const errorText = await response.text();
                        errorMessage = errorText || errorMessage;
                    }
                }
                throw new Error(`${errorMessage} (${response.status})`);
            }

            const data = await response.json();

            // Calculate total items from by_member
            let totalItems = 0;
            const membersWithItems = [];

            if (data.by_member) {
                Object.keys(data.by_member).forEach((memberId) => {
                    const memberData = data.by_member[memberId];
                    if (memberData.items) {
                        totalItems += memberData.items.reduce((sum, item) => sum + item.qty, 0);
                    }

                    // Find member info and add items data
                    const memberInfo = data.members?.find((m) => String(m.id) === String(memberId));
                    if (memberInfo) {
                        membersWithItems.push({
                            ...memberInfo,
                            subtotal: memberData.subtotal ?? 0,
                            items: memberData.items ?? [],
                            itemCount: memberData.items ? memberData.items.reduce((sum, item) => sum + item.qty, 0) : 0,
                        });
                    }
                });
            }

            // Transform API response to match component structure
            const transformedData = {
                id: getGroupToken(),
                join_token: getGroupToken(),
                owner_user_id: data.members?.find((m) => m.role === 'owner')?.user_id || null,
                allow_guest: true,
                shipping_rule: data.shipping_rule || 'equal',
                expires_at: data.expires_at,
                created_at: data.created_at ?? new Date().toISOString(),
                status: data.status,
                members: data.members || [],
                membersWithItems,
                by_member: data.by_member || {},
                total_items: totalItems,
                total_amount: data.total || 0,
            };

            setGroupData(transformedData);
            setJoinUrl(data.join_url);
        } catch (error) {
            console.error('Error fetching group data:', error);
            setError(error.message);

            if (error.message.includes('token')) {
                toast.error(error.message);
                setTimeout(() => {
                    router.push('/cart');
                }, 3000);
            } else if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
                toast.error('Không thể kết nối đến server. Vui lòng kiểm tra kết nối mạng.');
            } else {
                toast.error(error.message || 'Có lỗi xảy ra khi tải giỏ hàng nhóm');
            }
        } finally {
            setLoading(false);
        }
    };

    // Calculate time remaining
    const updateTimeRemaining = () => {
        if (!groupData?.expires_at) return;

        const now = new Date();
        const expiry = new Date(groupData.expires_at);
        const diff = expiry.getTime() - now.getTime();

        if (diff > 0) {
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            setTimeRemaining(`${hours}h ${minutes}m`);
        } else {
            setTimeRemaining('Đã hết hạn');
        }
    };

    // Format price
    const formatPrice = (price) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(price ?? 0);
    };

    // NEW: Update item quantity
    const handleUpdateQuantity = async (itemId, quantityChange) => {
        try {
            setActionLoading((s) => ({ ...s, quantityUpdate: { ...s.quantityUpdate, [itemId]: true } }));
            const token = groupData?.join_token || getGroupToken();

            const response = await fetch(`http://localhost:8000/api/group-orders/${token}/items/${itemId}/quantity`, {
                method: 'PATCH',
                headers: getAuthHeaders(),
                credentials: 'include',
                body: JSON.stringify({
                    quantity: quantityChange,
                }),
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(errorText || 'Không thể cập nhật số lượng');
            }

            const result = await response.json();

            if (result.success) {
                message.success('Đã cập nhật số lượng thành công');
                await fetchGroupData(); // Refresh data
            } else {
                throw new Error(result.message || 'Cập nhật thất bại');
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            toast.error(error.message || 'Có lỗi xảy ra khi cập nhật số lượng');
        } finally {
            setActionLoading((s) => ({ ...s, quantityUpdate: { ...s.quantityUpdate, [itemId]: false } }));
        }
    };

    // NEW: Delete item
    const handleDeleteConfirmed = async (itemId, itemTitle) => {
        if (!itemId) return;

        try {
            setActionLoading((s) => ({
                ...s,
                deleteItem: { ...s.deleteItem, [itemId]: true },
            }));

            const token = groupData?.join_token || getGroupToken();

            const response = await fetch(`http://localhost:8000/api/group-orders/${token}/items/${itemId}`, {
                method: 'DELETE',
                headers: getAuthHeaders(),
                credentials: 'include',
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(errorText || 'Không thể xóa sản phẩm');
            }

            message.success(`Đã xóa sản phẩm "${itemTitle}" thành công`);
            await fetchGroupData();
        } catch (error) {
            console.error('Error deleting item:', error);
            toast.error(error.message || 'Có lỗi xảy ra khi xóa sản phẩm');
        } finally {
            setActionLoading((s) => ({
                ...s,
                deleteItem: { ...s.deleteItem, [itemId]: false },
            }));
        }
    };

    // ACTIONS (existing)
    const handleLockGroup = async () => {
        try {
            setActionLoading((s) => ({ ...s, lock: true }));
            const token = groupData?.join_token || getGroupToken();

            const res = await fetch(`http://localhost:8000/api/group-orders/${token}/lock`, {
                method: 'POST',
                headers: getAuthHeaders(),
                credentials: 'include',
            });

            if (!res.ok) {
                const t = await res.text();
                throw new Error(t || 'Không thể lock nhóm');
            }

            toast.success('Đã khóa nhóm (lock) thành công');
            await fetchGroupData();
        } catch (e) {
            toast.error(e.message);
        } finally {
            setActionLoading((s) => ({ ...s, lock: false }));
            setShowLockConfirm(false); // đóng modal
        }
    };

    const handleLeaveGroup = async () => {
        try {
            setActionLoading((s) => ({ ...s, leave: true }));
            const token = groupData?.join_token || getGroupToken();

            const res = await fetch(`http://localhost:8000/api/group-orders/${token}/users`, {
                method: 'DELETE',
                headers: getAuthHeaders(),
                credentials: 'include',
            });

            if (!res.ok) {
                const t = await res.text();
                throw new Error(t || 'Không thể thoát nhóm');
            }

            toast.success('Bạn đã rời nhóm');
            router.push('/cart');
        } catch (e) {
            toast.error(e.message);
        } finally {
            setActionLoading((s) => ({ ...s, leave: false }));
        }
    };

    const handleKickMember = async (userId) => {
        confirm({
            title: 'Kick thành viên này?',
            icon: <DeleteOutlined />,
            content: `Bạn sẽ xóa thành viên ID ${userId} khỏi nhóm.`,
            okText: 'Kick',
            okButtonProps: { danger: true },
            cancelText: 'Hủy',
            onOk: async () => {
                try {
                    setActionLoading((s) => ({ ...s, kick: { ...s.kick, [userId]: true } }));
                    const token = groupData?.join_token || getGroupToken();
                    const res = await fetch(`http://localhost:8000/api/group-orders/${token}/users/${userId}`, {
                        method: 'DELETE',
                        headers: getAuthHeaders(),
                        credentials: 'include',
                    });
                    if (!res.ok) {
                        const t = await res.text();
                        throw new Error(t || 'Không thể kick thành viên');
                    }
                    toast.success(`Đã kick thành viên ${userId}`);
                    await fetchGroupData();
                } catch (e) {
                    toast.error(e.message);
                } finally {
                    setActionLoading((s) => ({ ...s, kick: { ...s.kick, [userId]: false } }));
                }
            },
        });
    };

    const handleCopyJoinUrl = () => {
        if (joinUrl) {
            navigator.clipboard
                .writeText(joinUrl)
                .then(() => {
                    message.success('Đã copy đường link!');
                })
                .catch(() => {
                    message.error('Không thể copy đường link');
                });
        }
    };

    const handleShareGroup = () => {
        if (navigator.share && joinUrl) {
            navigator
                .share({
                    title: 'Tham gia giỏ hàng nhóm',
                    text: 'Tham gia cùng tôi mua hàng với giá tốt hơn!',
                    url: joinUrl,
                })
                .catch(() => {
                    handleCopyJoinUrl();
                });
        } else {
            handleCopyJoinUrl();
        }
    };

    const handleBackToCart = () => {
        router.push('/cart');
    };

    const handleAddProducts = () => {
        router.push('/buybooks');
    };

    const handleRefresh = () => {
        fetchGroupData();
    };

    // Load group data on component mount
    useEffect(() => {
        loadCurrentUserId();
        fetchGroupData();
    }, []);

    // Update time remaining periodically
    useEffect(() => {
        if (groupData?.expires_at) {
            updateTimeRemaining();
            const interval = setInterval(updateTimeRemaining, 60000);
            return () => clearInterval(interval);
        }
    }, [groupData?.expires_at]);

    // Loading state
    if (loading) {
        return (
            <div className="cart-loading" style={{ textAlign: 'center', padding: '100px 0' }}>
                <Spin size="large" indicator={<LoadingOutlined style={{ fontSize: 48 }} spin />} />
                <div style={{ marginTop: '16px' }}>
                    <Text>Đang tải thông tin giỏ hàng nhóm...</Text>
                </div>
            </div>
        );
    }

    // Error state
    if (error) {
        return (
            <div className="cart-container">
                <div className="cart-header">
                    <Title level={2}>
                        <UserAddOutlined /> Giỏ hàng nhóm
                    </Title>
                </div>
                <div style={{ textAlign: 'center', padding: '50px 0' }}>
                    <Alert
                        message="Lỗi tải dữ liệu"
                        description={error}
                        type="error"
                        showIcon
                        style={{ marginBottom: '20px' }}
                        action={
                            <Space>
                                <Button size="small" onClick={handleRefresh}>
                                    Thử lại
                                </Button>
                                <Button size="small" onClick={handleBackToCart}>
                                    Quay lại giỏ hàng
                                </Button>
                            </Space>
                        }
                    />
                </div>
            </div>
        );
    }

    // No data state
    if (!groupData) {
        return (
            <div className="cart-container">
                <div className="cart-header">
                    <Title level={2}>
                        <UserAddOutlined /> Giỏ hàng nhóm
                    </Title>
                </div>
                <div style={{ textAlign: 'center', padding: '50px 0' }}>
                    <Empty description="Không tìm thấy thông tin giỏ hàng nhóm" image={Empty.PRESENTED_IMAGE_SIMPLE}>
                        <Space>
                            <Button onClick={handleRefresh}>Thử lại</Button>
                            <Button type="primary" onClick={handleBackToCart}>
                                Quay lại giỏ hàng
                            </Button>
                        </Space>
                    </Empty>
                </div>
            </div>
        );
    }

    const isOwner = currentUserId && String(groupData.owner_user_id) === String(currentUserId);

    return (
        <div className="cart-container">
            <div className="cart-header">
                <Title level={2}>
                    <UserAddOutlined /> Giỏ hàng nhóm #{groupData.join_token}
                </Title>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
                    <Text type="secondary">Mời bạn bè cùng mua hàng để được giá tốt hơn!</Text>
                    <Button size="small" onClick={handleRefresh} loading={loading}>
                        Làm mới
                    </Button>
                    {/* Khóa nhóm */}
                    <Button
                        size="small"
                        icon={<LockOutlined />}
                        onClick={() => setShowLockConfirm(true)}
                        loading={actionLoading.lock}
                        disabled={groupData.status !== 'open' || !isOwner}
                    >
                        Khóa nhóm
                    </Button>

                    {/* Chủ nhóm có thể thoát (out) luôn qua API chung */}
                    {isOwner && (
                        <Button
                            size="small"
                            danger
                            icon={<LogoutOutlined />}
                            onClick={handleLeaveGroup}
                            loading={actionLoading.leave}
                        >
                            Chủ nhóm thoát
                        </Button>
                    )}
                </div>
            </div>

            <Row gutter={24}>
                <Col xs={24} lg={16}>
                    {/* Group Info Card */}
                    <Card
                        className="cart-items-card"
                        title={
                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                <UserAddOutlined />
                                <span>Thông tin nhóm</span>
                                <Tag color={groupData.status === 'open' ? 'green' : 'red'}>
                                    {groupData.status === 'open' ? 'Đang mở' : 'Đã đóng'}
                                </Tag>
                            </div>
                        }
                        style={{ marginBottom: '16px' }}
                    >
                        <Row gutter={16}>
                            <Col xs={24} sm={12}>
                                <div style={{ marginBottom: '12px' }}>
                                    <Text strong>Thành viên:</Text>
                                    <Tag color="blue" style={{ marginLeft: '8px' }}>
                                        <UserOutlined /> {groupData.members.length} người
                                    </Tag>
                                </div>
                                <div style={{ marginBottom: '12px' }}>
                                    <Text strong>Thời gian còn lại:</Text>
                                    <Tag
                                        color={timeRemaining.includes('hết hạn') ? 'red' : 'green'}
                                        style={{ marginLeft: '8px' }}
                                    >
                                        <ClockCircleOutlined /> {timeRemaining}
                                    </Tag>
                                </div>
                            </Col>
                            <Col xs={24} sm={12}>
                                <div style={{ marginBottom: '12px' }}>
                                    <Text strong>Cho phép khách:</Text>
                                    <Tag
                                        color={groupData.allow_guest ? 'green' : 'orange'}
                                        style={{ marginLeft: '8px' }}
                                    >
                                        {groupData.allow_guest ? 'Có' : 'Không'}
                                    </Tag>
                                </div>
                                <div style={{ marginBottom: '12px' }}>
                                    <Text strong>Vận chuyển:</Text>
                                    <Tag color="purple" style={{ marginLeft: '8px' }}>
                                        {groupData.shipping_rule === 'equal' ? 'Chia đều' : 'Tùy chọn'}
                                    </Tag>
                                </div>
                            </Col>
                        </Row>

                        <Divider />

                        <div>
                            <Text strong>Chia sẻ nhóm:</Text>
                            <div style={{ marginTop: '8px' }}>
                                <Input.Group compact>
                                    <Input
                                        value={joinUrl}
                                        readOnly
                                        style={{
                                            width: 'calc(100% - 160px)',
                                            backgroundColor: '#f6ffed',
                                        }}
                                    />
                                    <Button
                                        type="primary"
                                        icon={<CopyOutlined />}
                                        onClick={handleCopyJoinUrl}
                                        style={{ width: '80px' }}
                                    >
                                        Copy
                                    </Button>
                                    <Button
                                        icon={<ShareAltOutlined />}
                                        onClick={handleShareGroup}
                                        style={{ width: '80px' }}
                                    >
                                        Chia sẻ
                                    </Button>
                                </Input.Group>
                            </div>
                        </div>
                    </Card>

                    {/* Members List Card with Items */}
                    <Card
                        className="cart-items-card"
                        title="Danh sách thành viên và sản phẩm"
                        style={{ marginBottom: '16px' }}
                    >
                        <div className="cart-items-list">
                            {groupData.membersWithItems.map((member) => {
                                const isMemberOwner = member.role === 'owner';
                                const isSelf = currentUserId && String(member.user_id) === String(currentUserId);

                                return (
                                    <div
                                        key={member.id}
                                        className="cart-item"
                                        style={{
                                            padding: '16px',
                                            marginBottom: '16px',
                                            border: '1px solid #f0f0f0',
                                            borderRadius: '8px',
                                        }}
                                    >
                                        <div
                                            style={{
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'space-between',
                                                marginBottom: '12px',
                                            }}
                                        >
                                            <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                                                <UserOutlined style={{ fontSize: '24px', color: '#1890ff' }} />
                                                <div>
                                                    <Text strong>{member.name}</Text>
                                                    {isMemberOwner && (
                                                        <Tag color="gold" style={{ marginLeft: '8px' }}>
                                                            Chủ nhóm
                                                        </Tag>
                                                    )}
                                                    <Tag color="blue" style={{ marginLeft: '4px' }}>
                                                        {member.role}
                                                    </Tag>
                                                    <br />
                                                    <Text type="secondary" style={{ fontSize: '12px' }}>
                                                        ID: {member.user_id}
                                                    </Text>
                                                </div>
                                            </div>

                                            <div style={{ textAlign: 'right' }}>
                                                <Text strong>{member.itemCount} sản phẩm</Text>
                                                <br />
                                                <Text style={{ color: '#52c41a' }}>{formatPrice(member.subtotal)}</Text>
                                            </div>
                                        </div>

                                        {/* ACTION: Kick / Leave */}
                                        <div
                                            style={{
                                                display: 'flex',
                                                gap: 8,
                                                marginBottom: member.items?.length ? 12 : 0,
                                            }}
                                        >
                                            {isSelf && (
                                                <Button
                                                    danger
                                                    icon={<LogoutOutlined />}
                                                    onClick={handleLeaveGroup}
                                                    loading={actionLoading.leave}
                                                >
                                                    Thoát nhóm
                                                </Button>
                                            )}
                                            {isOwner && !isMemberOwner && !isSelf && (
                                                <Button
                                                    danger
                                                    icon={<DeleteOutlined />}
                                                    onClick={() => handleKickMember(member.user_id)}
                                                    loading={!!actionLoading.kick[member.user_id]}
                                                >
                                                    Kick
                                                </Button>
                                            )}
                                        </div>

                                        {/* Member's Items + ENHANCED with quantity controls */}
                                        {member.items && member.items.length > 0 && (
                                            <div style={{ paddingLeft: '36px', marginTop: '12px' }}>
                                                <Text
                                                    strong
                                                    style={{ fontSize: '14px', marginBottom: '8px', display: 'block' }}
                                                >
                                                    Sản phẩm:
                                                </Text>
                                                {member.items.map((item) => (
                                                    <div
                                                        key={item.id}
                                                        style={{
                                                            display: 'flex',
                                                            justifyContent: 'space-between',
                                                            alignItems: 'flex-start',
                                                            padding: '12px',
                                                            backgroundColor: '#fafafa',
                                                            borderRadius: '8px',
                                                            marginBottom: '12px',
                                                            gap: 16,
                                                            border: '1px solid #f0f0f0',
                                                        }}
                                                    >
                                                        <div
                                                            style={{
                                                                display: 'flex',
                                                                alignItems: 'flex-start',
                                                                gap: 16,
                                                                flex: 1,
                                                            }}
                                                        >
                                                            {/* Book Cover Image */}
                                                            {item.cover_image ? (
                                                                <div style={{ flexShrink: 0 }}>
                                                                    <Image
                                                                        src={item.cover_image}
                                                                        alt={item.title || 'Book cover'}
                                                                        style={{
                                                                            width: 60,
                                                                            height: 80,
                                                                            objectFit: 'cover',
                                                                            borderRadius: 6,
                                                                            border: '2px solid #e8e8e8',
                                                                            boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
                                                                        }}
                                                                        preview={{
                                                                            mask: 'Xem ảnh',
                                                                        }}
                                                                        fallback="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=="
                                                                    />
                                                                </div>
                                                            ) : (
                                                                <div
                                                                    style={{
                                                                        width: 60,
                                                                        height: 80,
                                                                        backgroundColor: '#f5f5f5',
                                                                        border: '2px dashed #d9d9d9',
                                                                        borderRadius: 6,
                                                                        display: 'flex',
                                                                        alignItems: 'center',
                                                                        justifyContent: 'center',
                                                                        flexShrink: 0,
                                                                    }}
                                                                >
                                                                    <Text type="secondary" style={{ fontSize: '12px' }}>
                                                                        No Image
                                                                    </Text>
                                                                </div>
                                                            )}

                                                            {/* Book Details */}
                                                            <div style={{ flex: 1, minWidth: 0 }}>
                                                                <div style={{ marginBottom: '4px' }}>
                                                                    <Text
                                                                        strong
                                                                        style={{
                                                                            fontSize: '14px',
                                                                            display: 'block',
                                                                            wordBreak: 'break-word',
                                                                            lineHeight: '1.4',
                                                                        }}
                                                                    >
                                                                        {item.title || 'Untitled Book'}
                                                                    </Text>
                                                                </div>

                                                                <div style={{ marginBottom: '8px' }}>
                                                                    <Text type="secondary" style={{ fontSize: '12px' }}>
                                                                        Đơn giá:{' '}
                                                                        <Text style={{ color: '#1890ff' }}>
                                                                            {formatPrice(parseFloat(item.price) || 0)}
                                                                        </Text>
                                                                    </Text>
                                                                </div>

                                                                {/* QUANTITY CONTROLS - Only show for current user's items */}

                                                                <div
                                                                    style={{
                                                                        display: 'flex',
                                                                        alignItems: 'center',
                                                                        gap: '8px',
                                                                        marginBottom: '8px',
                                                                    }}
                                                                >
                                                                    <Text type="secondary" style={{ fontSize: '12px' }}>
                                                                        Số lượng1:
                                                                    </Text>
                                                                    <Button
                                                                        size="small"
                                                                        icon={<MinusOutlined />}
                                                                        onClick={() =>
                                                                            handleUpdateQuantity(item.id, -1)
                                                                        }
                                                                        loading={actionLoading.quantityUpdate[item.id]}
                                                                        disabled={item.qty <= 1}
                                                                    />
                                                                    <InputNumber
                                                                        size="small"
                                                                        min={1}
                                                                        max={999}
                                                                        value={item.qty}
                                                                        style={{ width: '60px' }}
                                                                        readOnly
                                                                    />
                                                                    <Button
                                                                        size="small"
                                                                        icon={<PlusOutlined />}
                                                                        onClick={() => handleUpdateQuantity(item.id, 1)}
                                                                        loading={actionLoading.quantityUpdate[item.id]}
                                                                    />
                                                                        <Button
                                                                            size="small"
                                                                            danger
                                                                            icon={<DeleteOutlined />}
                                                                            onClick={() =>
                                                                                handleDeleteConfirmed(item.id, item.title)
                                                                            }
                                                                            loading={actionLoading.deleteItem[item.id]}
                                                                            title="Xóa sản phẩm1"
                                                                        />
                                                                </div>

                                                                {/* Show quantity for non-editable items */}
                                                                {(!isSelf || groupData.status !== 'open') && (
                                                                    <div style={{ marginBottom: '4px' }}>
                                                                        <Text
                                                                            type="secondary"
                                                                            style={{ fontSize: '12px' }}
                                                                        >
                                                                            Số lượng: <Text strong>{item.qty}</Text>
                                                                        </Text>
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>

                                                        {/* Total Price */}
                                                        <div style={{ textAlign: 'right', flexShrink: 0 }}>
                                                            <Text
                                                                strong
                                                                style={{
                                                                    color: '#52c41a',
                                                                    fontSize: '15px',
                                                                    display: 'block',
                                                                }}
                                                            >
                                                                {formatPrice((parseFloat(item.price) || 0) * item.qty)}
                                                            </Text>
                                                            <Text type="secondary" style={{ fontSize: '11px' }}>
                                                                Thành tiền
                                                            </Text>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </Card>
                </Col>

                <Col xs={24} lg={8}>
                    {/* Summary Card */}
                    <Card className="cart-summary-card" title="Tổng kết nhóm">
                        <div className="cart-summary">
                            {groupData.status === 'open' ? (
                                <Alert
                                    message="Thông báo"
                                    description={
                                        groupData.total_amount > 0
                                            ? `Tổng giá trị đơn hàng nhóm: ${formatPrice(groupData.total_amount)}`
                                            : 'Giỏ hàng nhóm hiện chưa có sản phẩm nào. Thêm sản phẩm hoặc mời thêm bạn bè để bắt đầu mua hàng cùng nhau!'
                                    }
                                    type={groupData.total_amount > 0 ? 'success' : 'info'}
                                    showIcon
                                    style={{ marginBottom: '16px' }}
                                />
                            ) : (
                                <Alert
                                    message="Giỏ hàng nhóm đã đóng"
                                    description="Giỏ hàng nhóm này đã đóng hoặc hết hạn. Không thể thêm sản phẩm mới."
                                    type="warning"
                                    showIcon
                                    style={{ marginBottom: '16px' }}
                                />
                            )}

                            <div className="cart-summary-section">
                                <Title level={4}>Thông tin thanh toán</Title>

                                <div className="cart-summary-row">
                                    <Text>Tổng thành viên:</Text>
                                    <Text>{groupData.members.length} người</Text>
                                </div>

                                <div className="cart-summary-row">
                                    <Text>Tổng sản phẩm:</Text>
                                    <Text>{groupData.total_items} sản phẩm</Text>
                                </div>

                                <div className="cart-summary-row">
                                    <Text>Trạng thái:</Text>
                                    <Tag color={groupData.status === 'open' ? 'green' : 'red'}>
                                        {groupData.status === 'open' ? 'Đang mở' : 'Đã đóng'}
                                    </Tag>
                                </div>

                                <Divider />

                                <div className="cart-summary-row cart-total">
                                    <Text strong>Tổng số tiền:</Text>
                                    <Text strong className="cart-total-price">
                                        {formatPrice(groupData.total_amount)}
                                    </Text>
                                </div>

                                <Button
                                    type="primary"
                                    size="large"
                                    block
                                    className="cart-checkout-btn"
                                    disabled={groupData.total_amount === 0 || groupData.status !== 'open'}
                                    style={{ marginTop: '16px' }}
                                >
                                    Thanh toán ({groupData.total_items} sản phẩm)
                                </Button>

                                {groupData.status === 'open' && (
                                    <div style={{ marginTop: '16px', textAlign: 'center' }}>
                                        <Space direction="vertical" style={{ width: '100%' }}>
                                            <Button block onClick={handleAddProducts} icon={<ShoppingCartOutlined />}>
                                                Thêm sản phẩm
                                            </Button>
                                            <Button block onClick={handleShareGroup} icon={<UserAddOutlined />}>
                                                Mời thêm bạn bè
                                            </Button>
                                            {/* Nút thoát nhóm nhanh cho bất kỳ ai */}
                                            {!isOwner && (
                                                <Button
                                                    block
                                                    danger
                                                    icon={<LogoutOutlined />}
                                                    onClick={handleLeaveGroup}
                                                    loading={actionLoading.leave}
                                                >
                                                    Thoát nhóm
                                                </Button>
                                            )}
                                        </Space>
                                    </div>
                                )}
                            </div>
                        </div>
                    </Card>

                    {/* Group Statistics Card */}
                    <Card title="Thống kê nhóm" style={{ marginTop: '16px' }}>
                        <Space direction="vertical" style={{ width: '100%' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                <Text>Thời gian tạo:</Text>
                                <Text>{new Date(groupData.created_at).toLocaleString('vi-VN')}</Text>
                            </div>
                            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                <Text>Hết hạn:</Text>
                                <Text>{new Date(groupData.expires_at).toLocaleString('vi-VN')}</Text>
                            </div>
                            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                <Text>Mã tham gia:</Text>
                                <Text code>{groupData.join_token}</Text>
                            </div>
                        </Space>
                    </Card>
                </Col>
            </Row>

            <Modal
                title="Khóa nhóm & dọn item?"
                open={showLockConfirm}
                onOk={handleLockGroup}
                confirmLoading={actionLoading.lock}
                onCancel={() => setShowLockConfirm(false)}
                okText="Khóa ngay"
                cancelText="Hủy"
            >
                <p>Thao tác này sẽ gọi API lock phòng và xóa item theo luật của phòng. Bạn chắc chưa?</p>
            </Modal>

            <Modal
                title="Xóa sản phẩm?"
                open={deleteConfirm.open}
                onOk={() => handleDeleteConfirmed()}
                onCancel={() => setDeleteConfirm({ open: false, itemId: null, itemTitle: '' })}
                okText="Xóa"
                okButtonProps={{ danger: true }}
                cancelText="Hủy"
                confirmLoading={deleteConfirm.itemId && actionLoading.deleteItem?.[deleteConfirm.itemId]}
            >
                <p>
                    Bạn có chắc muốn xóa <b>{deleteConfirm.itemTitle}</b> khỏi giỏ hàng nhóm?
                </p>
            </Modal>
        </div>
    );
};

export default CartGroup;
