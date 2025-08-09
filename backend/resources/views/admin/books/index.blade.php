@extends('layouts.app')

@section('title', 'Danh sách Sách')

@push('styles')
<style>
    /* Reset và base styles */
    * {
        box-sizing: border-box;
    }

    .page-container {
        background-color: #f5f5f5;
        min-height: 100vh;
        padding: 24px;
    }

    /* Header section */
    .page-header {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        color: white;
        padding: 24px 32px;
        border-radius: 8px;
        margin-bottom: 24px;
        border-left: 4px solid #000;
    }

    .page-header h1 {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        letter-spacing: -0.02em;
    }

    /* Content wrapper */
    .content-wrapper {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #d9d9d9;
        overflow: hidden;
    }

    /* Table styles */
    .table-container {
        overflow-x: auto;
    }

    .table {
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    .table thead th {
        background: #000 !important;
        color: white !important;
        font-weight: 500;
        font-size: 0.875rem;
        padding: 16px 12px;
        text-align: center;
        border: none;
        white-space: nowrap;
    }

    .table thead th:first-child {
        border-top-left-radius: 0;
    }

    .table thead th:last-child {
        border-top-right-radius: 0;
    }

    .table tbody td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.875rem;
        vertical-align: middle;
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #fafafa;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Book cover */
    .book-cover {
        width: 48px;
        height: 64px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #d9d9d9;
        display: block;
        margin: 0 auto;
    }

    /* Action buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    .btn-action {
        padding: 4px 12px;
        border-radius: 4px;
        border: 1px solid;
        font-size: 0.75rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-edit {
        background: white;
        color: #595959;
        border-color: #d9d9d9;
    }

    .btn-edit:hover {
        color: #000;
        border-color: #40a9ff;
        background: #f6ffed;
    }

    .btn-delete {
        background: white;
        color: #ff4d4f;
        border-color: #ffccc7;
    }

    .btn-delete:hover {
        background: #fff2f0;
        border-color: #ff4d4f;
    }

    /* Status badges */
    .status-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-physical {
        background: #f6ffed;
        color: #52c41a;
        border: 1px solid #b7eb8f;
    }

    .status-digital {
        background: #f0f0f0;
        color: #595959;
        border: 1px solid #d9d9d9;
    }

    /* Price styling */
    .price {
        font-weight: 600;
        color: #000;
    }

    .price-free {
        color: #52c41a;
        font-weight: 500;
    }

    /* Stock number */
    .stock-badge {
        background: #f5f5f5;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 500;
        font-size: 0.75rem;
    }

    /* Mobile cards */
    .mobile-cards {
        display: none;
    }

    .book-card {
        background: white;
        border: 1px solid #d9d9d9;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
    }

    .book-card-header {
        display: flex;
        gap: 12px;
        margin-bottom: 12px;
    }

    .book-card-info h6 {
        margin: 0 0 8px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #000;
    }

    .book-card-meta {
        font-size: 0.75rem;
        color: #8c8c8c;
        line-height: 1.5;
    }

    .book-card-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        padding-top: 12px;
        border-top: 1px solid #f0f0f0;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: #8c8c8c;
        background: white;
        border-radius: 8px;
        border: 1px dashed #d9d9d9;
    }

    /* Pagination */
    .pagination-wrapper {
        background: white;
        padding: 16px 24px;
        border-radius: 8px;
        border: 1px solid #d9d9d9;
        margin-top: 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }

    .pagination .page-link {
        color: #595959;
        border: 1px solid #d9d9d9;
        padding: 6px 12px;
        margin: 0 2px;
        border-radius: 4px;
        font-size: 0.875rem;
    }

    .pagination .page-link:hover {
        background: #f5f5f5;
        border-color: #40a9ff;
        color: #1890ff;
    }

    .pagination .page-item.active .page-link {
        background: #000;
        border-color: #000;
        color: white;
    }

    .pagination .page-item.disabled .page-link {
        color: #bfbfbf;
        background: #f5f5f5;
        border-color: #d9d9d9;
    }

    .pagination-info {
        font-size: 0.875rem;
        color: #8c8c8c;
    }

    /* PopConfirm Styles */
    .popconfirm-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: transparent;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }

    .popconfirm-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .popconfirm {
        position: absolute;
        background: white;
        border-radius: 8px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12), 0 3px 6px -4px rgba(0, 0, 0, 0.12), 0 9px 28px 8px rgba(0, 0, 0, 0.05);
        border: 1px solid #d9d9d9;
        padding: 16px;
        min-width: 280px;
        max-width: 360px;
        transform: scale(0.8) translateY(-10px);
        opacity: 0;
        transition: all 0.2s cubic-bezier(0.645, 0.045, 0.355, 1);
        z-index: 1001;
    }

    .popconfirm.active {
        transform: scale(1) translateY(0);
        opacity: 1;
    }

    .popconfirm::before {
        content: '';
        position: absolute;
        top: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-bottom: 8px solid white;
        filter: drop-shadow(0 -2px 4px rgba(0, 0, 0, 0.08));
    }

    .popconfirm-content {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
    }

    .popconfirm-icon {
        flex-shrink: 0;
        width: 16px;
        height: 16px;
        margin-top: 2px;
    }

    .popconfirm-icon-warning {
        color: #faad14;
    }

    .popconfirm-message {
        flex: 1;
        font-size: 14px;
        line-height: 1.5;
        color: #262626;
        font-weight: 400;
    }

    .popconfirm-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .popconfirm-btn {
        padding: 4px 15px;
        border-radius: 6px;
        border: 1px solid #d9d9d9;
        background: white;
        color: #595959;
        font-size: 14px;
        font-weight: 400;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.645, 0.045, 0.355, 1);
        outline: none;
        user-select: none;
        height: 32px;
        line-height: 1.5;
        display: inline-flex;
        align-items: center;
    }

    .popconfirm-btn:hover {
        color: #40a9ff;
        border-color: #40a9ff;
    }

    .popconfirm-btn:active {
        color: #096dd9;
        border-color: #096dd9;
    }

    .popconfirm-btn-primary {
        background: #ff4d4f;
        border-color: #ff4d4f;
        color: white;
    }

    .popconfirm-btn-primary:hover {
        background: #ff7875;
        border-color: #ff7875;
        color: white;
    }

    .popconfirm-btn-primary:active {
        background: #d9363e;
        border-color: #d9363e;
        color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-container {
            padding: 16px;
        }

        .page-header {
            padding: 20px 16px;
        }

        .table-container {
            display: none;
        }

        .mobile-cards {
            display: block;
        }

        .action-buttons {
            justify-content: center;
        }

        .popconfirm {
            min-width: 260px;
            margin: 0 16px;
        }
    }

    /* Loading state */
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    /* Utilities */
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .font-medium { font-weight: 500; }
    .font-semibold { font-weight: 600; }
    .text-gray-500 { color: #8c8c8c; }
    .text-gray-700 { color: #595959; }
    .text-gray-900 { color: #262626; }
</style>
@endpush

@section('content')
<div class="page-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Quản lý Danh sách Sách</h1>
    </div>

    @include('components.alert')
    @include('admin.books.partials.filters')

    <!-- Desktop Table -->
    <div class="content-wrapper">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">STT</th>
                        <th style="width: 80px;">Ảnh bìa</th>
                        <th>Tên sách</th>
                        <th style="width: 120px;">Tác giả</th>
                        <th style="width: 120px;">NXB</th>
                        <th style="width: 100px;">Danh mục</th>
                        <th style="width: 100px;">Loại sách</th>
                        <th style="width: 100px;">Giá bán</th>
                        <th style="width: 80px;">Tồn kho</th>
                        <th style="width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $index => $book)
                        <tr>
                            <td class="text-center font-medium">
                                {{ $books->firstItem() + $index }}
                            </td>
                            <td class="text-center">
                                <img src="{{ $book->cover_image ?? 'https://via.placeholder.com/48x64/f5f5f5/bfbfbf?text=N/A' }}"
                                     alt="Cover"
                                     class="book-cover"
                                     loading="lazy">
                            </td>
                            <td>
                                <div class="font-semibold text-gray-900">{{ $book->title }}</div>
                                @if($book->subtitle)
                                    <div class="text-gray-500" style="font-size: 0.75rem;">{{ Str::limit($book->subtitle, 50) }}</div>
                                @endif
                            </td>
                            <td class="text-center text-gray-700">
                                {{ $book->author->name ?? '—' }}
                            </td>
                            <td class="text-center text-gray-700">
                                {{ $book->publisher->name ?? '—' }}
                            </td>
                            <td class="text-center text-gray-700">
                                {{ $book->category->name ?? '—' }}
                            </td>
                            <td class="text-center">
                                <span class="status-badge {{ $book->is_physical ? 'status-physical' : 'status-digital' }}">
                                    {{ $book->is_physical ? 'Sách in' : 'Điện tử' }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if($book->is_physical)
                                    <span class="price">{{ number_format($book->price, 0, ',', '.') }}₫</span>
                                @else
                                    <span class="price-free">Miễn phí</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($book->is_physical)
                                    <span class="stock-badge">{{ $book->stock }}</span>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.books.edit', $book) }}" 
                                       class="btn-action btn-edit"
                                       title="Chỉnh sửa">
                                        Sửa
                                    </a>
                                    <form action="{{ route('admin.books.destroy', $book) }}" 
                                          method="POST" 
                                          style="display: inline;"
                                          class="delete-form">
                                        @csrf @method('DELETE')
                                        <button type="button" 
                                                class="btn-action btn-delete delete-btn"
                                                title="Xóa"
                                                data-book-title="{{ $book->title }}"
                                                data-book-id="{{ $book->id }}">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-gray-500" style="padding: 48px 0;">
                                Không có dữ liệu sách
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Cards -->
    <div class="mobile-cards">
        @foreach ($books as $book)
            <div class="book-card">
                <div class="book-card-header">
                    <img src="{{ $book->cover_image ?? 'https://via.placeholder.com/48x64/f5f5f5/bfbfbf?text=N/A' }}" 
                         alt="Cover" 
                         class="book-cover"
                         style="width: 40px; height: 56px;">
                    <div class="book-card-info flex-1">
                        <h6>{{ $book->title }}</h6>
                        <div class="book-card-meta">
                            <div><strong>Tác giả:</strong> {{ $book->author->name ?? 'Không rõ' }}</div>
                            <div><strong>NXB:</strong> {{ $book->publisher->name ?? 'Không rõ' }}</div>
                            <div><strong>Danh mục:</strong> {{ $book->category->name ?? 'Không rõ' }}</div>
                            <div><strong>Loại:</strong> 
                                <span class="status-badge {{ $book->is_physical ? 'status-physical' : 'status-digital' }}" style="margin-left: 4px;">
                                    {{ $book->is_physical ? 'Sách in' : 'Điện tử' }}
                                </span>
                            </div>
                            <div><strong>Giá:</strong> 
                                @if($book->is_physical)
                                    <span class="price">{{ number_format($book->price, 0, ',', '.') }}₫</span>
                                @else
                                    <span class="price-free">Miễn phí</span>
                                @endif
                            </div>
                            @if($book->is_physical)
                                <div><strong>Tồn kho:</strong> <span class="stock-badge">{{ $book->stock }}</span></div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="book-card-actions">
                    <a href="{{ route('admin.books.edit', $book) }}" class="btn-action btn-edit">
                        Chỉnh sửa
                    </a>
                    <form action="{{ route('admin.books.destroy', $book) }}" 
                          method="POST" 
                          style="display: inline;"
                          class="delete-form">
                        @csrf @method('DELETE')
                        <button type="button" 
                                class="btn-action btn-delete delete-btn"
                                data-book-title="{{ $book->title }}"
                                data-book-id="{{ $book->id }}">
                            Xóa
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Empty State -->
    @if ($books->isEmpty())
        <div class="empty-state">
            <div style="font-size: 1.125rem; font-weight: 500; margin-bottom: 8px;">
                Không tìm thấy sách nào
            </div>
            @if (request('search'))
                <div>Với từ khóa: <strong>"{{ request('search') }}"</strong></div>
                <div style="margin-top: 8px; font-size: 0.875rem;">Thử với từ khóa khác hoặc bỏ bộ lọc</div>
            @else
                <div>Chưa có sách nào trong hệ thống</div>
            @endif
        </div>
    @endif

    <!-- Pagination -->
    @if ($books->total() > 0)
        <div class="pagination-wrapper">
            {{ $books->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
            <div class="pagination-info">
                Hiển thị <strong>{{ $books->firstItem() }}</strong> - <strong>{{ $books->lastItem() }}</strong> 
                trong tổng số <strong>{{ number_format($books->total()) }}</strong> sách
            </div>
        </div>
    @endif
</div>

<!-- PopConfirm Component -->
<div id="popconfirm-overlay" class="popconfirm-overlay">
    <div id="popconfirm" class="popconfirm">
        <div class="popconfirm-content">
            <div class="popconfirm-icon popconfirm-icon-warning">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
            </div>
            <div class="popconfirm-message" id="popconfirm-message">
                Bạn có chắc chắn muốn xóa sách này?
            </div>
        </div>
        <div class="popconfirm-buttons">
            <button type="button" class="popconfirm-btn" id="popconfirm-cancel">
                Hủy
            </button>
            <button type="button" class="popconfirm-btn popconfirm-btn-primary" id="popconfirm-confirm">
                Xóa
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('popconfirm-overlay');
    const popconfirm = document.getElementById('popconfirm');
    const message = document.getElementById('popconfirm-message');
    const cancelBtn = document.getElementById('popconfirm-cancel');
    const confirmBtn = document.getElementById('popconfirm-confirm');
    let currentForm = null;
    let currentButton = null;

    // Show popconfirm
    function showPopconfirm(triggerElement, bookTitle) {
        currentForm = triggerElement.closest('.delete-form');
        currentButton = triggerElement;
        
        // Update message
        message.textContent = `Bạn có chắc chắn muốn xóa sách "${bookTitle}"?`;
        
        // Position popconfirm
        const rect = triggerElement.getBoundingClientRect();
        const popconfirmRect = popconfirm.getBoundingClientRect();
        
        // Calculate position
        let top = rect.top - popconfirmRect.height - 16;
        let left = rect.left + (rect.width / 2) - (popconfirmRect.width / 2);
        
        // Adjust if goes off screen
        if (top < 16) {
            top = rect.bottom + 16;
            // Move arrow to top
            popconfirm.style.setProperty('--arrow-position', 'top');
            popconfirm.classList.add('arrow-top');
        } else {
            popconfirm.classList.remove('arrow-top');
        }
        
        if (left < 16) {
            left = 16;
        } else if (left + popconfirmRect.width > window.innerWidth - 16) {
            left = window.innerWidth - popconfirmRect.width - 16;
        }
        
        popconfirm.style.left = left + 'px';
        popconfirm.style.top = top + 'px';
        
        // Show overlay and popconfirm
        overlay.classList.add('active');
        setTimeout(() => {
            popconfirm.classList.add('active');
        }, 10);
        
        // Focus on cancel button
        cancelBtn.focus();
    }
    
    // Hide popconfirm
    function hidePopconfirm() {
        popconfirm.classList.remove('active');
        setTimeout(() => {
            overlay.classList.remove('active');
            currentForm = null;
            currentButton = null;
        }, 200);
    }
    
    // Handle delete button clicks
    document.addEventListener('click', function(e) {
        if (e.target.matches('.delete-btn')) {
            e.preventDefault();
            e.stopPropagation();
            
            const bookTitle = e.target.getAttribute('data-book-title');
            showPopconfirm(e.target, bookTitle);
        }
    });
    
    // Handle cancel
    cancelBtn.addEventListener('click', function(e) {
        e.preventDefault();
        hidePopconfirm();
    });
    
    // Handle confirm
    confirmBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (currentForm) {
            // Add loading state
            confirmBtn.textContent = 'Đang xóa...';
            confirmBtn.disabled = true;
            
            // Submit the form
            currentForm.submit();
        }
    });
    
    // Handle overlay click
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            hidePopconfirm();
        }
    });
    
    // Handle escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('active')) {
            hidePopconfirm();
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (overlay.classList.contains('active') && currentButton) {
            // Reposition popconfirm
            const bookTitle = currentButton.getAttribute('data-book-title');
            hidePopconfirm();
            setTimeout(() => {
                showPopconfirm(currentButton, bookTitle);
            }, 100);
        }
    });
});
</script>

<style>
/* Arrow styles for different positions */
.popconfirm.arrow-top::before {
    top: auto;
    bottom: -8px;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-top: 8px solid white;
    border-bottom: none;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.08));
}
</style>
@endpush