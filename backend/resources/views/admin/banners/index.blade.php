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
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
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

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            border: 2px solid #e0e0e0;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .form-check-label {
            font-size: clamp(0.75rem, 2.5vw, 0.875rem);
            color: #333;
            cursor: pointer;
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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .banner-table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-active {
            background: #f6ffed;
            color: #52c41a;
            border: 1px solid #b7eb8f;
        }

        .status-inactive {
            background: #fff1f0;
            color: #ff4d4f;
            border: 1px solid #ffa39e;
        }

        .priority-badge {
            background: #e6f7ff;
            color: #1890ff;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-block;
            border: 1px solid #91d5ff;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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

            th,
            td {
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

             .book-select-container {
            position: relative;
        }
        
        .book-select-input {
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 38px;
        }
        
        .book-select-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .book-select-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1050;
            background: white;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.175);
            max-height: 250px;
            overflow: hidden;
            margin-top: 0.125rem;
            display: none;
        }
        
        .book-select-dropdown.show {
            display: block;
        }
        
        .book-select-search {
            padding: 0.5rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .book-select-options {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .book-select-option {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .book-select-option:hover {
            background-color: #e3f2fd;
        }
        
        .book-select-option.selected {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .book-select-placeholder {
            color: #6c757d;
        }
        
        .book-select-clear {
            background: none;
            border: none;
            color: #6c757d;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0;
            margin-left: 0.5rem;
        }
        
        .book-select-clear:hover {
            color: #495057;
        }
        
        .book-select-arrow {
            margin-left: 0.5rem;
            transition: transform 0.2s;
        }
        
        .book-select-arrow.rotated {
            transform: rotate(180deg);
        }
        
        .book-select-selected-info {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .book-select-selected-id {
            font-weight: 600;
            color: #0d6efd;
        }
        
        .book-select-loading {
            padding: 0.5rem 0.75rem;
            color: #6c757d;
            text-align: center;
        }
        
        .book-select-empty {
            padding: 0.5rem 0.75rem;
            color: #6c757d;
            text-align: center;
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
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm Banner Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="banner-form">
                        <input type="hidden" id="banner-id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">📝 Tiêu đề</label>
                                    <input type="text" id="title" class="form-control" placeholder="Nhập tiêu đề banner..."
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">🔗 Liên kết (URL)</label>
                                    <input type="url" id="link" class="form-control" placeholder="https://example.com">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">📄 Mô tả</label>
                            <textarea id="description" rows="3" class="form-control"
                                placeholder="Nhập mô tả chi tiết..."></textarea>
                        </div>

                        <div class="row">
                            <div class="container mt-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label">📚 ID Sách (tùy chọn)</label>
                                            <div class="book-select-container">
                                                <!-- Main Select Input -->
                                                <div class="form-control book-select-input" id="bookSelectInput">
                                                    <span class="book-select-placeholder" id="bookSelectPlaceholder">Chọn
                                                        sách...</span>
                                                    <div class="d-flex align-items-center">
                                                        <button type="button" class="book-select-clear" id="bookSelectClear"
                                                            style="display: none;">×</button>
                                                        <svg class="book-select-arrow" id="bookSelectArrow" width="16"
                                                            height="16" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    </div>
                                                </div>

                                                <!-- Dropdown Menu -->
                                                <div class="book-select-dropdown" id="bookSelectDropdown">
                                                    <!-- Search Input -->
                                                    <div class="book-select-search">
                                                        <input type="text" class="form-control form-control-sm"
                                                            id="bookSearchInput" placeholder="Tìm kiếm sách...">
                                                    </div>

                                                    <!-- Options List -->
                                                    <div class="book-select-options" id="bookSelectOptions">
                                                        <div class="book-select-loading">Đang tải...</div>
                                                    </div>
                                                </div>

                                                <!-- Hidden input for form submission -->
                                                <input type="hidden" name="book_id" id="bookIdInput" value="">
                                            </div>

                                            <!-- Display selected book ID -->
                                            <div class="book-select-selected-info" id="bookSelectedInfo"
                                                style="display: none;">
                                                Đã chọn ID: <span class="book-select-selected-id"
                                                    id="bookSelectedId"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">🎯 Độ ưu tiên</label>
                                    <input type="number" id="priority" class="form-control" placeholder="0" min="0"
                                        max="999" value="0">
                                    <small class="text-muted">Số càng cao càng ưu tiên hiển thị</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">🔄 Trạng thái</label>
                                    <div class="form-check">
                                        <input type="checkbox" id="status" class="form-check-input" checked>
                                        <label class="form-check-label" for="status">Hiển thị banner</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">🖼️ Hình ảnh Banner</label>
                            <div class="upload-area" onclick="document.getElementById('image-input').click()">
                                <div id="upload-content">
                                    <div style="font-size: clamp(2rem, 5vw, 3rem); color: #999; margin-bottom: 16px;">📁
                                    </div>
                                    <p style="margin: 0; color: #666; font-size: clamp(0.875rem, 2.5vw, 1rem);">Nhấp để chọn
                                        hoặc kéo thả hình ảnh</p>
                                    <p style="margin: 8px 0 0 0; color: #999; font-size: clamp(0.625rem, 2vw, 0.75rem);">Hỗ
                                        trợ: JPG, PNG, GIF (tối đa 5MB)</p>
                                </div>
                                <div id="image-preview" style="display: none;">
                                    <img id="preview-img" style="max-width: 200px; max-height: 120px; border-radius: 8px;">
                                    <p style="margin: 8px 0 0 0; color: #666; font-size: clamp(0.75rem, 2.5vw, 0.875rem);">
                                        Nhấp để thay đổi hình ảnh</p>
                                </div>
                            </div>
                            <input type="file" id="image-input" accept="image/*" style="display: none;"
                                onchange="previewImage(this)">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
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
                                <th style="padding: 16px; text-align: center; font-weight: 600; color: #333;">🎯 Ưu tiên</th>
                                <th style="padding: 16px; text-align: center; font-weight: 600; color: #333;">🔄 Trạng thái</th>
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
                                            <span class="status-tag" style="background: #e6f7ff; color: #1890ff; border: 1px solid #91d5ff; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">
                                                📚 Sách #${banner.book_id}
                                            </span>
                                        ` : `
                                            <span style="color: #999; font-style: italic; font-size: clamp(0.75rem, 2.5vw, 0.8125rem);">Không liên kết</span>
                                        `}
                                    </td>
                                    <td style="padding: 16px; text-align: center;">
                                        <span class="priority-badge">
                                            🎯 ${banner.priority || 0}
                                        </span>
                                    </td>
                                    <td style="padding: 16px; text-align: center;">
                                        <span class="status-badge ${banner.status ? 'status-active' : 'status-inactive'}">
                                            ${banner.status ? '✅ Hiển thị' : '❌ Ẩn'}
                                        </span>
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
            document.getElementById('priority').value = 0;
            document.getElementById('status').checked = true;
            document.getElementById('image-preview').style.display = 'none';
            document.getElementById('upload-content').style.display = 'block';
            new bootstrap.Modal(document.getElementById('bannerModal')).show();
        }

        function editBanner(id) {
            const banner = banners.find(b => b.id === id);
            

            editingBannerId = id;
            document.getElementById('modalTitle').textContent = '✏️ Chỉnh sửa Banner';
            document.getElementById('banner-id').value = banner.id;
            document.getElementById('title').value = banner.title || '';
            document.getElementById('description').value = banner.description || '';
            document.getElementById('link').value = banner.link || '';
            // document.getElementById('book_id').value = banner.book_id || '';
            document.getElementById('priority').value = banner.priority || 0;
            document.getElementById('status').checked = banner.status !== false;

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

            alert(`Banner #${banner.id}\nTiêu đề: ${banner.title || 'Không có'}\nMô tả: ${banner.description || 'Không có'}\nLiên kết: ${banner.link || 'Không có'}\nID Sách: ${banner.book_id || 'Không có'}\nĐộ ưu tiên: ${banner.priority || 0}\nTrạng thái: ${banner.status ? 'Hiển thị' : 'Ẩn'}`);
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
            formData.append('priority', document.getElementById('priority').value || 0);
            formData.append('status', document.getElementById('status').checked ? 1 : 0);

            // const book_id = document.getElementById('book_id').value;
            // if (book_id) formData.append('book_id', book_id);

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

        class BookSelect {
            constructor() {
                this.books = [];
                this.selectedBook = null;
                this.isOpen = false;
                this.isLoading = false;
                
                this.initElements();
                this.bindEvents();
                this.fetchBooks();
            }
            
            initElements() {
                this.selectInput = document.getElementById('bookSelectInput');
                this.placeholder = document.getElementById('bookSelectPlaceholder');
                this.clearBtn = document.getElementById('bookSelectClear');
                this.arrow = document.getElementById('bookSelectArrow');
                this.dropdown = document.getElementById('bookSelectDropdown');
                this.searchInput = document.getElementById('bookSearchInput');
                this.optionsContainer = document.getElementById('bookSelectOptions');
                this.hiddenInput = document.getElementById('bookIdInput');
                this.selectedInfo = document.getElementById('bookSelectedInfo');
                this.selectedIdSpan = document.getElementById('bookSelectedId');
            }
            
            bindEvents() {
                // Toggle dropdown
                this.selectInput.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.toggleDropdown();
                });
                
                // Clear selection
                this.clearBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.clearSelection();
                });
                
                // Search functionality
                this.searchInput.addEventListener('input', (e) => {
                    this.renderOptions(e.target.value);
                });
                
                // Prevent dropdown close when clicking inside
                this.dropdown.addEventListener('click', (e) => {
                    e.stopPropagation();
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', () => {
                    this.closeDropdown();
                });
            }
            
            async fetchBooks() {
                this.isLoading = true;
                this.renderOptions();
                
                try {
                    const response = await fetch('http://localhost:8000/api/books/ids');
                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        this.books = result.data.map(book => ({
                            value: book.id,
                            label: `${book.id} - ${book.title.trim()}`
                        }));
                    }
                } catch (error) {
                    console.error('Error fetching books:', error);
                    this.books = [];
                } finally {
                    this.isLoading = false;
                    this.renderOptions();
                }
            }
            
            toggleDropdown() {
                if (this.isOpen) {
                    this.closeDropdown();
                } else {
                    this.openDropdown();
                }
            }
            
            openDropdown() {
                this.isOpen = true;
                this.dropdown.classList.add('show');
                this.arrow.classList.add('rotated');
                this.searchInput.value = '';
                this.renderOptions();
                setTimeout(() => this.searchInput.focus(), 100);
            }
            
            closeDropdown() {
                this.isOpen = false;
                this.dropdown.classList.remove('show');
                this.arrow.classList.remove('rotated');
            }
            
            renderOptions(searchTerm = '') {
                if (this.isLoading) {
                    this.optionsContainer.innerHTML = '<div class="book-select-loading">Đang tải...</div>';
                    return;
                }
                
                const filteredBooks = this.books.filter(book =>
                    book.label.toLowerCase().includes(searchTerm.toLowerCase())
                );
                
                if (filteredBooks.length === 0) {
                    const emptyMessage = searchTerm ? 'Không tìm thấy sách nào' : 'Không có dữ liệu';
                    this.optionsContainer.innerHTML = `<div class="book-select-empty">${emptyMessage}</div>`;
                    return;
                }
                
                this.optionsContainer.innerHTML = filteredBooks.map(book => `
                    <div class="book-select-option ${this.selectedBook && this.selectedBook.value === book.value ? 'selected' : ''}" 
                         data-value="${book.value}" data-label="${book.label}">
                        <span>${book.label}</span>
                        ${this.selectedBook && this.selectedBook.value === book.value ? 
                            '<svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>' : 
                            ''
                        }
                    </div>
                `).join('');
                
                // Bind click events to options
                this.optionsContainer.querySelectorAll('.book-select-option').forEach(option => {
                    option.addEventListener('click', () => {
                        this.selectBook({
                            value: parseInt(option.dataset.value),
                            label: option.dataset.label
                        });
                    });
                });
            }
            
            selectBook(book) {
                this.selectedBook = book;
                this.placeholder.textContent = book.label;
                this.placeholder.classList.remove('book-select-placeholder');
                this.clearBtn.style.display = 'block';
                this.hiddenInput.value = book.value;
                this.selectedIdSpan.textContent = book.value;
                this.selectedInfo.style.display = 'block';
                this.closeDropdown();
            }
            
            clearSelection() {
                this.selectedBook = null;
                this.placeholder.textContent = 'Chọn sách...';
                this.placeholder.classList.add('book-select-placeholder');
                this.clearBtn.style.display = 'none';
                this.hiddenInput.value = '';
                this.selectedInfo.style.display = 'none';
                this.renderOptions();
            }
        }
        
        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new BookSelect();
        });
    </script>
@endpush