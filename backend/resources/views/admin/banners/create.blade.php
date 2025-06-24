@extends('layouts.app')

@section('content')
<div id="root"></div>

<!-- Ant Design CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/antd/4.24.15/antd.min.css" rel="stylesheet">

<!-- React & ReactDOM -->
<script crossorigin src="https://cdnjs.cloudflare.com/ajax/libs/react/17.0.2/umd/react.production.min.js"></script>
<script crossorigin src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/17.0.2/umd/react-dom.production.min.js"></script>

<!-- Babel Standalone -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/6.26.0/babel.min.js"></script>

<!-- Ant Design JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/antd/4.24.15/antd.min.js"></script>

<style>
body {
    background-color: #f5f5f5;
}
.banner-form-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
}
.preview-image {
    max-width: 200px;
    max-height: 150px;
    border-radius: 6px;
    margin-top: 12px;
}
</style>

<script type="text/babel">
const { useState, useEffect } = React;
const { 
    Form, 
    Input, 
    Button, 
    Card, 
    Upload, 
    Select, 
    message, 
    Space,
    Typography,
    Divider,
    Row,
    Col
} = antd;

const { TextArea } = Input;
const { Title } = Typography;
const { Option } = Select;

function BannerFormComponent() {
    const [form] = Form.useForm();
    const [loading, setLoading] = useState(false);
    const [books, setBooks] = useState([]);
    const [fileList, setFileList] = useState([]);
    const [previewImage, setPreviewImage] = useState('');

    // Lấy danh sách sách khi component mount
    useEffect(() => {
        fetchBooks();
    }, []);

    const fetchBooks = async () => {
        try {
            const response = await fetch('{{ url("/api/books/ids") }}');
            const result = await response.json();
            
            if (result.status === 'success') {
                setBooks(result.data);
                message.success('Đã tải danh sách sách');
            } else {
                message.error('Không thể tải danh sách sách');
            }
        } catch (error) {
            console.error('Error fetching books:', error);
            message.error('Có lỗi khi tải danh sách sách');
        }
    };

    const handleSubmit = async (values) => {
        if (fileList.length === 0) {
            message.error('Vui lòng chọn hình ảnh');
            return;
        }

        setLoading(true);
        
        try {
            const formData = new FormData();
            formData.append('image', fileList[0].originFileObj);
            formData.append('title', values.title);
            formData.append('description', values.description || '');
            formData.append('link', values.link || '');
            formData.append('book_id', values.book_id || '');

            const response = await fetch('{{ url("/api/banners") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success || response.ok) {
                message.success('Tạo banner thành công!');
                form.resetFields();
                setFileList([]);
                setPreviewImage('');
                
                // Chuyển hướng sau 2 giây
                setTimeout(() => {
                    window.location.href = '{{ route("admin.banners.index") }}';
                }, 2000);
            } else {
                message.error(result.message || 'Có lỗi xảy ra khi tạo banner');
            }
        } catch (error) {
            console.error('Error:', error);
            message.error('Có lỗi xảy ra khi gửi dữ liệu');
        } finally {
            setLoading(false);
        }
    };

    const handleImageChange = ({ fileList: newFileList }) => {
        setFileList(newFileList);
        
        if (newFileList.length > 0) {
            const file = newFileList[0].originFileObj;
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    setPreviewImage(e.target.result);
                };
                reader.readAsDataURL(file);
            }
        } else {
            setPreviewImage('');
        }
    };

    const uploadProps = {
        fileList,
        onChange: handleImageChange,
        beforeUpload: () => false, // Prevent auto upload
        accept: 'image/*',
        maxCount: 1,
        listType: 'picture'
    };

    const uploadButton = (
        <div>
            <div>📷 Chọn hình ảnh</div>
        </div>
    );

    return (
        <div className="banner-form-container">
            <Card 
                title={
                    <div>
                        <Title level={2} >
                            🎨 Thêm Banner Mới
                        </Title>
                    </div>
                }
             
            >
                <Form
                    form={form}
                    layout="vertical"
                    onFinish={handleSubmit}
                    size="large"
                >
                    <Row gutter={24}>
                        <Col span={24}>
                            <Form.Item
                                label={<span>📷 Hình ảnh Banner</span>}
                                required
                            >
                                <Upload {...uploadProps}>
                                    <Button 
                                        size="large"
                                       
                                    >
                                        {uploadButton}
                                    </Button>
                                </Upload>
                                {previewImage && (
                                    <div >
                                        <img
                                            src={previewImage}
                                            alt="Preview"
                                            className="preview-image"
                                        />
                                    </div>
                                )}
                            </Form.Item>
                        </Col>

                        <Col span={24}>
                            <Form.Item
                                name="title"
                                label={<span>📝 Tiêu đề</span>}
                                rules={[{ required: true, message: 'Vui lòng nhập tiêu đề!' }]}
                            >
                                <Input
                                    placeholder="Nhập tiêu đề cho banner..."
                                />
                            </Form.Item>
                        </Col>

                        <Col span={24}>
                            <Form.Item
                                name="description"
                                label={<span>📄 Mô tả</span>}
                            >
                                <TextArea
                                    rows={4}
                                    placeholder="Nhập mô tả cho banner..."
                                    showCount
                                    maxLength={500}
                                />
                            </Form.Item>
                        </Col>

                        <Col span={12}>
                            <Form.Item
                                name="link"
                                label={<span>🔗 Liên kết</span>}
                            >
                                <Input
                                    placeholder="https://example.com"
                                />
                            </Form.Item>
                        </Col>

                        <Col span={12}>
                            <Form.Item
                                name="book_id"
                                label={<span>📚 Sách liên quan</span>}
                            >
                                <Select
                                    placeholder="Chọn sách (tùy chọn)"
                                    allowClear
                                    showSearch
                                    optionFilterProp="children"
                                    filterOption={(input, option) =>
                                        option.children.toLowerCase().includes(input.toLowerCase())
                                    }
                                >
                                    {books.map(book => (
                                        <Option key={book.id} value={book.id}>
                                            {book.id} - {book.title.trim()}
                                        </Option>
                                    ))}
                                </Select>
                            </Form.Item>
                        </Col>
                    </Row>

                    <Divider />

                    <Form.Item>
                        <Space size="large">
                            <Button
                                type="primary"
                                htmlType="submit"
                                loading={loading}
                                size="large"
                                
                            >
                                {loading ? 'Đang lưu...' : '💾 Lưu Banner'}
                            </Button>
                            
                            <Button
                                size="large"
                                onClick={() => window.location.href = '{{ route("admin.banners.index") }}'}
                            >
                                ⬅️ Quay lại
                            </Button>
                        </Space>
                    </Form.Item>
                </Form>
            </Card>
        </div>
    );
}

// Render component
ReactDOM.render(<BannerFormComponent />, document.getElementById('root'));
</script>
@endsection