<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quản lý Banner</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Ant Design CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/antd/5.12.8/reset.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/antd/5.12.8/antd.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5; /* Đổi background để không xung đột với navbar */
            min-height: 100vh;
        }

        /* Header Styles */
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 24px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-top: 0; /* Đảm bảo không có margin top */
        }

        .page-header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
        }

        .page-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }

        /* Container */
        .banner-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        /* Card Styles */
        .banner-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 24px;
        }

        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .ant-btn {
            background: #f5f5f5;
            border: 1px solid #d9d9d9;
            color: #333;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Upload Area */
        .upload-area {
            border: 2px dashed #d9d9d9;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .upload-area:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        /* Modal Styles */
        .modal-header {
            padding: 24px 24px 0 24px;
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 24px;
        }

        .ant-modal-body {
            padding: 0 24px 24px 24px;
            max-height: 60vh;
            overflow-y: auto;
        }

        .ant-modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #f0f0f0;
            text-align: right;
            background: #fafafa;
        }

        /* Table Styles */
        .banner-image {
            width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .status-tag {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
    </style>
</head>

<body>
    {{-- Navbar - Thêm navbar từ layout chính --}}
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
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.categories.index') }}">Danh mục</a></li>
                    <li class="nav-item"><a class="nav-link active" href="{{ route('admin.banners.index') }}">Banner</a></li>
                </ul>

                {{-- Dropdown người dùng --}}
                <div class="ms-auto text-white d-flex align-items-center">
                    <div class="dropdown">
                        <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            👤 <span id="user-name">Loading...</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#">Hồ sơ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" id="logout-btn">Đăng xuất</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header trên đầu trang -->
  

    <div class="banner-container">
        <!-- Action Bar -->
        <div class="banner-card" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; color: #333;">Danh sách Banner</h3>
                    <p style="margin: 4px 0 0 0; color: #666;">Tổng cộng: <strong id="total-banners">0</strong> banner
                    </p>
                </div>
                <button class="btn-primary" onclick="openCreateModal()">
                    ➕ Thêm Banner Mới
                </button>
            </div>
        </div>

        <!-- Banner Table -->
        <div class="banner-card">
            <div id="banner-table"></div>
        </div>
    </div>

    <!-- Modal -->
    <div id="banner-modal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div
        style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px;">
        <div
            style="width: 600px; max-width: 90vw; position: relative; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 12px 48px rgba(0,0,0,0.2); max-height: 90vh; display: flex; flex-direction: column;">
            <!-- Header -->
            <div
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 24px;">
                <h3 id="modal-title" style="margin: 0; font-size: 20px;">Thêm Banner Mới</h3>
            </div>

            <!-- Body -->
            <div style="padding: 24px; overflow-y: auto;">
                <form id="banner-form">
                    <input type="hidden" id="banner-id">

                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 600; color: #333; margin-bottom: 8px; display: block;">📝 Tiêu đề</label>
                        <input type="text" id="title"
                            style="width: 100%; padding: 10px 12px; border: 2px solid #e8e8e8; border-radius: 8px; font-size: 14px;"
                            placeholder="Nhập tiêu đề banner...">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 600; color: #333; margin-bottom: 8px; display: block;">📄 Mô tả</label>
                        <textarea id="description" rows="3"
                            style="width: 100%; padding: 10px 12px; border: 2px solid #e8e8e8; border-radius: 8px; font-size: 14px;"
                            placeholder="Nhập mô tả chi tiết..."></textarea>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 600; color: #333; margin-bottom: 8px; display: block;">🔗 Liên kết (URL)</label>
                        <input type="url" id="link"
                            style="width: 100%; padding: 10px 12px; border: 2px solid #e8e8e8; border-radius: 8px; font-size: 14px;"
                            placeholder="https://example.com">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 600; color: #333; margin-bottom: 8px; display: block;">📚 ID Sách (tùy chọn)</label>
                        <input type="number" id="book_id"
                            style="width: 100%; padding: 10px 12px; border: 2px solid #e8e8e8; border-radius: 8px; font-size: 14px;"
                            placeholder="Nhập ID sách...">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 600; color: #333; margin-bottom: 8px; display: block;">🖼️ Hình ảnh Banner</label>
                        <div onclick="document.getElementById('image-input').click()"
                            style="border: 2px dashed #d9d9d9; border-radius: 8px; padding: 40px; text-align: center; background: #fafafa; cursor: pointer;">
                            <div id="upload-content">
                                <div style="font-size: 48px; color: #999; margin-bottom: 16px;">📁</div>
                                <p style="margin: 0; color: #666; font-size: 16px;">Nhấp để chọn hoặc kéo thả hình ảnh</p>
                                <p style="margin: 8px 0 0 0; color: #999; font-size: 12px;">Hỗ trợ: JPG, PNG, GIF (tối đa 5MB)</p>
                            </div>
                            <div id="image-preview" style="display: none;">
                                <img id="preview-img"
                                    style="max-width: 200px; max-height: 120px; border-radius: 8px;">
                                <p style="margin: 8px 0 0 0; color: #666;">Nhấp để thay đổi hình ảnh</p>
                            </div>
                        </div>
                        <input type="file" id="image-input" accept="image/*" style="display: none;"
                            onchange="previewImage(this)">
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div
                style="padding: 16px 24px; border-top: 1px solid #f0f0f0; text-align: right; background: #fafafa;">
                <button type="button" onclick="closeModal()"
                    style="padding: 8px 16px; border-radius: 6px; border: 1px solid #d9d9d9; background: white; color: #333; cursor: pointer; margin-right: 8px;">
                    Hủy
                </button>
                <button type="button" onclick="saveBanner()"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 10px 24px; border-radius: 8px; color: white; font-weight: 600; cursor: pointer;">
                    💾 Lưu Banner
                </button>
            </div>
        </div>
    </div>
</div>


    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>

    {{-- Script gọi API /api/me cho navbar --}}
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
                window.location.href = '/login';
            });
        });
    </script>

    <script>
        // Setup CSRF token for axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let banners = [];
        let editingBannerId = null;

        // Load banners on page load
        document.addEventListener('DOMContentLoaded', function () {
            loadBanners();
        });

        // Load banners from API
        async function loadBanners() {
            try {
                const response = await axios.get('/api/banners');
                if (response.data.success) {
                    banners = response.data.data;
                    renderBannerTable();
                    updateTotalCount();
                }
            } catch (error) {
                console.error('Error loading banners:', error);
                showNotification('Lỗi khi tải danh sách banner', 'error');
            }
        }

        // Render banner table
        function renderBannerTable() {
            const tableHtml = `
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #fafafa; border-bottom: 2px solid #e8e8e8;">
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #333;">#</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #333;">🖼️ Hình ảnh</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #333;">📝 Thông tin</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #333;">🔗 Liên kết</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #333;">📚 Sách</th>
                            <th style="padding: 16px; text-align: center; font-weight: 600; color: #333;">⚡ Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${banners.map(banner => `
                            <tr style="border-bottom: 1px solid #f0f0f0; transition: background 0.3s;" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='white'">
                                <td style="padding: 16px; font-weight: 600; color: #667eea;">#${banner.id}</td>
                                <td style="padding: 16px;">
                                    ${banner.image ? `
                                        <img src="${getImageUrl(banner.image)}" class="banner-image" alt="Banner ${banner.id}">
                                    ` : `
                                        <div style="width: 100px; height: 60px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 12px;">
                                            Không có ảnh
                                        </div>
                                    `}
                                </td>
                                <td style="padding: 16px;">
                                    <div style="font-weight: 600; color: #333; margin-bottom: 4px;">
                                        ${banner.title || '<em style="color: #999;">Chưa có tiêu đề</em>'}
                                    </div>
                                    <div style="color: #666; font-size: 13px; line-height: 1.4;">
                                        ${banner.description || '<em style="color: #999;">Chưa có mô tả</em>'}
                                    </div>
                                </td>
                                <td style="padding: 16px;">
                                    ${banner.link ? `
                                        <a href="${banner.link}" target="_blank" style="color: #667eea; text-decoration: none; font-size: 13px;">
                                            🔗 ${banner.link.length > 30 ? banner.link.substring(0, 30) + '...' : banner.link}
                                        </a>
                                    ` : `
                                        <span style="color: #999; font-style: italic; font-size: 13px;">Không có liên kết</span>
                                    `}
                                </td>
                                <td style="padding: 16px;">
                                    ${banner.book_id ? `
                                        <span class="status-tag" style="background: #e6f7ff; color: #1890ff; border: 1px solid #91d5ff;">
                                            📚 Sách #${banner.book_id}
                                        </span>
                                    ` : `
                                        <span style="color: #999; font-style: italic; font-size: 13px;">Không liên kết</span>
                                    `}
                                </td>
                                <td style="padding: 16px; text-align: center;">
                                    <button class="action-btn" onclick="viewBanner(${banner.id})" style="background: #52c41a; color: white; border: none; padding: 6px 12px; border-radius: 4px; margin: 0 2px; cursor: pointer; font-size: 12px;">
                                        👁️ Xem
                                    </button>
                                    <button class="action-btn" onclick="editBanner(${banner.id})" style="background: #1890ff; color: white; border: none; padding: 6px 12px; border-radius: 4px; margin: 0 2px; cursor: pointer; font-size: 12px;">
                                        ✏️ Sửa
                                    </button>
                                    <button class="action-btn" onclick="deleteBanner(${banner.id})" style="background: #ff4d4f; color: white; border: none; padding: 6px 12px; border-radius: 4px; margin: 0 2px; cursor: pointer; font-size: 12px;">
                                        🗑️ Xóa
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;

            document.getElementById('banner-table').innerHTML = tableHtml;
        }

        // Get correct image URL
        function getImageUrl(imagePath) {
            if (imagePath.startsWith('http')) {
                return imagePath;
            }
            return `/storage/${imagePath}`;
        }

        // Update total count
        function updateTotalCount() {
            document.getElementById('total-banners').textContent = banners.length;
        }

        // Open create modal
        function openCreateModal() {
        window.location.href=
        "http://localhost:8000/admin/banners/create"
        }

        // Edit banner
        function editBanner(id) {
            const banner = banners.find(b => b.id === id);
            if (!banner) return;

            editingBannerId = id;
            document.getElementById('modal-title').textContent = '✏️ Chỉnh sửa Banner';
            document.getElementById('banner-id').value = banner.id;
            document.getElementById('title').value = banner.title || '';
            document.getElementById('description').value = banner.description || '';
            document.getElementById('link').value = banner.link || '';
            document.getElementById('book_id').value = banner.book_id || '';
 
            // Show current image if exists
            if (banner.image) {
                document.getElementById('preview-img').src = getImageUrl(banner.image);
                document.getElementById('image-preview').style.display = 'block';
                document.getElementById('upload-content').style.display = 'none';
            } else {
                document.getElementById('image-preview').style.display = 'none';
                document.getElementById('upload-content').style.display = 'block';
            }

            document.getElementById('banner-modal').style.display = 'block';
        }

        // View banner
        function viewBanner(id) {
            const banner = banners.find(b => b.id === id);
            if (!banner) return;

            alert(`Banner #${banner.id}\nTiêu đề: ${banner.title || 'Không có'}\nMô tả: ${banner.description || 'Không có'}\nLiên kết: ${banner.link || 'Không có'}\nID Sách: ${banner.book_id || 'Không có'}`);
        }

        // Delete banner
        async function deleteBanner(id) {
            if (!confirm('Bạn có chắc chắn muốn xóa banner này?')) return;

            try {
                const response = await axios.delete(`/api/banners/${id}`);
                if (response.data.success) {
                    showNotification('Xóa banner thành công!', 'success');
                    loadBanners();
                }
            } catch (error) {
                console.error('Error deleting banner:', error);
                showNotification('Lỗi khi xóa banner', 'error');
            }
        }

        // Close modal
        function closeModal() {
            document.getElementById('banner-modal').style.display = 'none';
        }

        // Preview image
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('image-preview').style.display = 'block';
                    document.getElementById('upload-content').style.display = 'none';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Save banner
        async function saveBanner() {
            const formData = new FormData();

            const title = document.getElementById('title').value;
            const description = document.getElementById('description').value;
            const link = document.getElementById('link').value;
            const book_id = document.getElementById('book_id').value;
            const imageInput = document.getElementById('image-input');

            formData.append('title', title);
            formData.append('description', description);
            formData.append('link', link);
            if (book_id) formData.append('book_id', book_id);
            if (imageInput.files[0]) formData.append('image', imageInput.files[0]);

            try {
                let response;
                if (editingBannerId) {
                    // Update existing banner
                    formData.append('_method', 'PUT');
                    response = await axios.post(`/api/banners/${editingBannerId}`, formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                } else {
                    // Create new banner
                    response = await axios.post('/api/banners', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                }

                if (response.data.success) {
                    showNotification(editingBannerId ? 'Cập nhật banner thành công!' : 'Thêm banner thành công!', 'success');
                    closeModal();
                    loadBanners();
                }
            } catch (error) {
                console.error('Error saving banner:', error);
                showNotification('Lỗi khi lưu banner', 'error');
            }
        }

        // Show notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 16px 24px;
                border-radius: 8px;
                color: white;
                font-weight: 600;
                z-index: 9999;
                background: ${type === 'success' ? '#52c41a' : type === 'error' ? '#ff4d4f' : '#1890ff'};
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);

            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Close modal when clicking outside
        document.getElementById('banner-modal').addEventListener('click', function (e) {
            if (e.target === this || e.target.classList.contains('ant-modal-wrap')) {
                closeModal();
            }
        });

        // Prevent modal from closing when clicking inside modal content
        document.addEventListener('click', function (e) {
            const modal = document.getElementById('banner-modal');
            const modalContent = modal.querySelector('.ant-modal-content');

            if (modal.style.display === 'block' && modalContent && !modalContent.contains(e.target) && e.target !== modal) {
                // Only close if clicking outside modal content
                if (e.target === modal || e.target.classList.contains('ant-modal-wrap')) {
                    closeModal();
                }
            }
        });
    </script>
</body>

</html>