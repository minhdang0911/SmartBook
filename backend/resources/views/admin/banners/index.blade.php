@extends('layouts.app')

@section('title', 'Quản lý Banner')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2.5rem 1.5rem;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        margin-top: 0;
    }

    .page-header h1 {
        margin: 0;
        font-size: clamp(1.5rem, 5vw, 2rem);
        font-weight: 700;
    }

    .page-header p {
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
        font-size: clamp(0.875rem, 3vw, 1rem);
    }

    .banner-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    .banner-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: clamp(0.75rem, 2.5vw, 0.875rem);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
        font-size: clamp(0.75rem, 2.5vw, 0.875rem);
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: clamp(0.75rem, 2.5vw, 0.875rem);
        transition: border-color 0.3s ease;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .upload-area {
        border: 2px dashed #d9d9d9;
        border-radius: 12px;
        padding: 2rem 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fafafa;
    }

    .upload-area:hover {
        border-color: #667eea;
        background: #f0f4ff;
    }

    .banner-image {
        width: 100%;
        max-width: 100px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .banner-table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        width: 100%;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }

    th, td {
        padding: 1rem;
        text-align: left;
    }

    .action-btn-container {
        display: flex;
        flex-direction: row;
        gap: 0.5rem;
    }

    .action-btn {
        transition: all 0.3s ease;
        min-width: 60px;
    }

    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    @media (max-width: 768px) {
        .action-btn-container {
            flex-direction: column;
            align-items: stretch;
        }
    }

    @media (max-width: 576px) {
        .page-header h1 {
            font-size: clamp(1.25rem, 4vw, 1.5rem);
        }

        .page-header p {
            font-size: clamp(0.75rem, 3vw, 0.875rem);
        }

        th, td {
            padding: 0.75rem;
            font-size: clamp(0.7rem, 2.5vw, 0.8rem);
        }

        .banner-image {
            max-width: 60px;
            height: 36px;
        }
    }

    @media (max-width: 400px) {
        .btn-primary,
        .btn-secondary {
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
        }

        .modal-dialog {
            margin: 0.25rem;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Quản lý Banner</h1>
    <p>Quản lý các banner quảng cáo trên hệ thống SmartBook</p>
</div>

<div class="banner-container">
    <div class="banner-card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h3 style="margin: 0; color: #333;">Danh sách Banner</h3>
                <p style="margin: 4px 0 0 0; color: #666;">Tổng cộng: <strong id="total-banners">0</strong> banner</p>
            </div>
            <button class="btn-primary" data-bs-toggle="modal" data-bs-target="#bannerModal">➕ Thêm Banner Mới</button>
        </div>
    </div>

    <div class="banner-card banner-table-responsive">
        <div id="banner-table">Loading...</div>
    </div>
</div>

<div class="modal fade" id="bannerModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Thêm Banner Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="banner-form">
                    <input type="hidden" id="banner-id">

                    <div class="form-group">
                        <label class="form-label">📝 Tiêu đề</label>
                        <input type="text" id="title" class="form-control" placeholder="Nhập tiêu đề banner..." required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">📄 Mô tả</label>
                        <textarea id="description" rows="3" class="form-control" placeholder="Nhập mô tả chi tiết..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">🔗 Liên kết (URL)</label>
                        <input type="url" id="link" class="form-control" placeholder="https://example.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label">📚 ID Sách (tùy chọn)</label>
                        <input type="number" id="book_id" class="form-control" placeholder="Nhập ID sách...">
                    </div>

                    <div class="form-group">
                        <label class="form-label">🖼️ Hình ảnh Banner</label>
                        <div class="upload-area" onclick="document.getElementById('image-input').click()">
                            <div id="upload-content">
                                <div style="font-size: clamp(2rem, 5vw, 3rem); color: #999; margin-bottom: 16px;">📁</div>
                                <p style="margin: 0; color: #666; font-size: clamp(0.875rem, 2.5vw, 1rem);">Nhấp để chọn hoặc kéo thả hình ảnh</p>
                                <p style="margin: 8px 0 0 0; color: #999; font-size: clamp(0.625rem, 2vw, 0.75rem);">Hỗ trợ: JPG, PNG, GIF (tối đa 5MB)</p>
                            </div>
                            <div id="image-preview" style="display: none;">
                                <img id="preview-img" style="max-width: 200px; max-height: 120px; border-radius: 8px;">
                                <p style="margin: 8px 0 0 0; color: #666; font-size: clamp(0.75rem, 2.5vw, 0.875rem);">Nhấp để thay đổi hình ảnh</p>
                            </div>
                        </div>
                        <input type="file" id="image-input" accept="image/*" style="display: none;" onchange="previewImage(this)">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="saveBanner()">💾 Lưu Banner</button>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
    <script>
        // Định nghĩa API_BASE_URL (cần được cấu hình trong môi trường thực tế)

        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let banners = [];
        let editingBannerId = null;

        document.addEventListener('DOMContentLoaded', function () {
            loadBanners();
            setupUploadArea();
        });

        async function loadBanners() {
            document.getElementById('banner-table').innerHTML = '<div>Loading...</div>';
            try {
                console.log('Calling API:', `${API_BASE_URL}/banners`);
                const response = await axios.get(`${API_BASE_URL}/banners`);
                console.log('Response:', response.data);
                if (response.data.success) {
                    banners = response.data.data;
                    renderBannerTable();
                    updateTotalCount();
                } else {
                    showNotification('Lỗi khi tải danh sách banner', 'error');
                }
            } catch (error) {
                console.error('Error loading banners:', error);
                showNotification(error.response?.data?.message || 'Lỗi khi tải danh sách banner', 'error');
            }
        }

        function renderBannerTable() {
            console.log('Banners:', banners);
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
                                        <div style="width: 100%; max-width: 100px; height: 60px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999; font-size: clamp(0.625rem, 2vw, 0.75rem);">
                                            Không có ảnh
                                        </div>
                                    `}
                                </td>
                                <td style="padding: 16px;">
                                    <div style="font-weight: 600; color: #333; margin-bottom: 4px;">
                                        ${banner.title || '<em style="color: #999;">Chưa có tiêu đề</em>'}
                                    </div>
                                    <div style="color: #666; font-size: clamp(0.75rem, 2.5vw, 0.8125rem); line-height: 1.4;">
                                        ${banner.description || '<em style="color: #999;">Chưa có mô tả</em>'}
                                    </div>
                                </td>
                                <td style="padding: 16px;">
                                    ${banner.link ? `
                                        <a href="${banner.link}" target="_blank" style="color: #667eea; text-decoration: none; font-size: clamp(0.75rem, 2.5vw, 0.8125rem);">
                                            🔗 ${banner.link.length > 30 ? banner.link.substring(0, 30) + '...' : banner.link}
                                        </a>
                                    ` : `
                                        <span style="color: #999; font-style: italic; font-size: clamp(0.75rem, 2.5vw, 0.8125rem);">Không có liên kết</span>
                                    `}
                                </td>
                                <td style="padding: 16px;">
                                    ${banner.book_id ? `
                                        <span class="status-tag" style="background: #e6f7ff; color: #1890ff; border: 1px solid #91d5ff;">
                                            📚 Sách #${banner.book_id}
                                        </span>
                                    ` : `
                                        <span style="color: #999; font-style: italic; font-size: clamp(0.75rem, 2.5vw, 0.8125rem);">Không liên kết</span>
                                    `}
                                </td>
                                <td style="padding: 16px; text-align: center;">
                                    <div class="action-btn-container">
                                        <button class="action-btn" onclick="viewBanner(${banner.id})" style="background: #52c41a; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: clamp(0.625rem, 2vw, 0.75rem);" aria-label="Xem banner ${banner.id}">
                                            👁️ Xem
                                        </button>
                                        <button class="action-btn" onclick="editBanner(${banner.id})" style="background: #1890ff; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: clamp(0.625rem, 2vw, 0.75rem);" aria-label="Sửa banner ${banner.id}">
                                            ✏️ Sửa
                                        </button>
                                        <button class="action-btn" onclick="deleteBanner(${banner.id})" style="background: #ff4d4f; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: clamp(0.625rem, 2vw, 0.75rem);" aria-label="Xóa banner ${banner.id}">
                                            🗑️ Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            document.getElementById('banner-table').innerHTML = tableHtml;
        }

        function getImageUrl(imagePath) {
            if (imagePath.startsWith('http')) {
                return imagePath;
            }
            return `/storage/${imagePath}`;
        }

        function updateTotalCount() {
            document.getElementById('total-banners').textContent = banners.length;
        }

        function openCreateModal() {
            editingBannerId = null;
            document.getElementById('modalTitle').textContent = 'Thêm Banner Mới';
            document.getElementById('banner-form').reset();
            document.getElementById('image-preview').style.display = 'none';
            document.getElementById('upload-content').style.display = 'block';
            new bootstrap.Modal(document.getElementById('bannerModal')).show();
        }

        function editBanner(id) {
            const banner = banners.find(b => b.id === id);
            if (!banner) return;

            editingBannerId = id;
            document.getElementById('modalTitle').textContent = '✏️ Chỉnh sửa Banner';
            document.getElementById('banner-id').value = banner.id;
            document.getElementById('title').value = banner.title || '';
            document.getElementById('description').value = banner.description || '';
            document.getElementById('link').value = banner.link || '';
            document.getElementById('book_id').value = banner.book_id || '';

            if (banner.image) {
                document.getElementById('preview-img').src = getImageUrl(banner.image);
                document.getElementById('image-preview').style.display = 'block';
                document.getElementById('upload-content').style.display = 'none';
            } else {
                document.getElementById('image-preview').style.display = 'none';
                document.getElementById('upload-content').style.display = 'block';
            }

            new bootstrap.Modal(document.getElementById('bannerModal')).show();
        }

        function viewBanner(id) {
            const banner = banners.find(b => b.id === id);
            if (!banner) return;

            alert(`Banner #${banner.id}\nTiêu đề: ${banner.title || 'Không có'}\nMô tả: ${banner.description || 'Không có'}\nLiên kết: ${banner.link || 'Không có'}\nID Sách: ${banner.book_id || 'Không có'}`);
        }

        async function deleteBanner(id) {
            if (!confirm('Bạn có chắc chắn muốn xóa banner này?')) return;

            try {
                const response = await axios.delete(`${API_BASE_URL}/banners/${id}`);
                if (response.data.success) {
                    showNotification('Xóa banner thành công!', 'success');
                    loadBanners();
                } else {
                    showNotification('Lỗi khi xóa banner', 'error');
                }
            } catch (error) {
                console.error('Error deleting banner:', error);
                showNotification(error.response?.data?.message || 'Lỗi khi xóa banner', 'error');
            }
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (!file.type.startsWith('image/')) {
                    showNotification('Vui lòng chọn file hình ảnh!', 'error');
                    return;
                }
                if (file.size > maxSize) {
                    showNotification('File quá lớn! Tối đa 5MB.', 'error');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('image-preview').style.display = 'block';
                    document.getElementById('upload-content').style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        }

        async function saveBanner() {
            const title = document.getElementById('title').value;
            const link = document.getElementById('link').value;
            if (!title) {
                showNotification('Vui lòng nhập tiêu đề banner!', 'error');
                return;
            }
            if (link && !isValidUrl(link)) {
                showNotification('URL không hợp lệ!', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('title', title);
            formData.append('description', document.getElementById('description').value);
            formData.append('link', link);
            const book_id = document.getElementById('book_id').value;
            if (book_id) formData.append('book_id', book_id);
            const imageInput = document.getElementById('image-input');
            if (imageInput.files[0]) formData.append('image', imageInput.files[0]);

            try {
                let response;
                if (editingBannerId) {
                    formData.append('_method', 'PUT');
                    response = await axios.post(`${API_BASE_URL}/banners/${editingBannerId}`, formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                } else {
                    response = await axios.post(`${API_BASE_URL}/banners`, formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                }

                if (response.data.success) {
                    showNotification(editingBannerId ? 'Cập nhật banner thành công!' : 'Thêm banner thành công!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('bannerModal')).hide();
                    loadBanners();
                } else {
                    showNotification('Lỗi khi lưu banner', 'error');
                }
            } catch (error) {
                console.error('Error saving banner:', error);
                showNotification(error.response?.data?.message || 'Lỗi khi lưu banner', 'error');
            }
        }

        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.padding = '10px 20px';
            notification.style.borderRadius = '8px';
            notification.style.color = 'white';
            notification.style.zIndex = '1000';
            notification.style.maxWidth = '90%';
            notification.style.fontSize = 'clamp(0.8rem, 2.5vw, 0.9rem)';
            notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)';
            notification.textContent = message;

            if (type === 'success') {
                notification.style.background = '#52c41a';
            } else {
                notification.style.background = '#ff4d4f';
            }

            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.transition = 'opacity 0.5s ease';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }

        function setupUploadArea() {
            const uploadArea = document.querySelector('.upload-area');
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.style.borderColor = '#667eea';
                uploadArea.style.background = '#f0f4ff';
            });
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.style.borderColor = '#d9d9d9';
                uploadArea.style.background = '#fafafa';
            });
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.style.borderColor = '#d9d9d9';
                uploadArea.style.background = '#fafafa';
                const file = e.dataTransfer.files[0];
                if (file) {
                    document.getElementById('image-input').files = e.dataTransfer.files;
                    previewImage(document.getElementById('image-input'));
                }
            });
        }
    </script>
@endpush
