<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flash Sale Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/antd/5.0.0/antd.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 600;
            color: #1a1a1a;
        }

        .create-btn {
            background: #1677ff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
        }

        .create-btn:hover {
            background: #0958d9;
        }

        .flash-sale-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .flash-sale-item {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #f0f0f0;
        }

        .sale-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .sale-info h3 {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .sale-dates {
            color: #666;
            font-size: 14px;
        }

        .sale-actions {
            display: flex;
            gap: 12px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
            margin-right: 12px;
        }

        .status-live {
            background: #f6ffed;
            color: #52c41a;
            border: 1px solid #b7eb8f;
        }

        .status-scheduled {
            background: #e6f4ff;
            color: #1677ff;
            border: 1px solid #91caff;
        }

        .status-inactive {
            background: #fafafa;
            color: #8c8c8c;
            border: 1px solid #d9d9d9;
        }

        .action-btn {
            background: none;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: #f5f5f5;
        }

        .edit-btn {
            color: #1677ff;
        }

        .duplicate-btn {
            color: #722ed1;
        }

        .delete-btn {
            color: #ff4d4f;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px;
            border-bottom: 1px solid #f0f0f0;
        }

        .modal-header h2 {
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-header .close-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
        }

        .modal-body {
            padding: 24px;
        }

        .form-row {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d9d9d9;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #1677ff;
        }

        .search-box {
            width: 100%;
            padding: 12px;
            border: 1px solid #d9d9d9;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .product-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid #f0f0f0;
            border-radius: 8px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .product-item:hover {
            background: #f5f5f5;
        }

        .product-image {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .product-info h4 {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .product-price {
            color: #666;
            font-size: 14px;
        }

        .discount-input {
            width: 100px;
            padding: 8px;
            border: 1px solid #d9d9d9;
            border-radius: 4px;
            text-align: center;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .products-table th,
        .products-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .products-table th {
            background: #fafafa;
            font-weight: 500;
            color: #666;
        }

        .discount-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .discount-20 {
            background: #f6ffed;
            color: #52c41a;
        }

        .discount-30 {
            background: #fff7e6;
            color: #fa8c16;
        }

        .discount-15 {
            background: #f6ffed;
            color: #52c41a;
        }

        .discount-25 {
            background: #f6ffed;
            color: #52c41a;
        }

        .sale-price {
            color: #52c41a;
            font-weight: 600;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 16px 24px;
            border-top: 1px solid #f0f0f0;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-default {
            background: #fafafa;
            color: #666;
        }

        .btn-primary {
            background: #1677ff;
            color: white;
        }

        .btn-primary:hover {
            background: #0958d9;
        }

        .add-product-btn {
            background: none;
            border: 1px dashed #d9d9d9;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            color: #666;
            width: 100%;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-product-btn:hover {
            border-color: #1677ff;
            color: #1677ff;
        }

        .hidden {
            display: none;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 22px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.toggle-slider {
            background-color: #1677ff;
        }

        input:checked+.toggle-slider:before {
            transform: translateX(22px);
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: #666;
        }

        .error {
            color: #ff4d4f;
            background: #fff2f0;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
        }

        .success {
            color: #52c41a;
            background: #f6ffed;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Manage Flash Sales</h1>
            <button class="create-btn" onclick="openCreateModal()">
                <i class="fas fa-plus"></i>
                Create Flash Sale
            </button>
        </div>

        <div class="flash-sale-list" id="flashSaleList">
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i>
                Loading flash sales...
            </div>
        </div>
    </div>

    <!-- Create/Edit Flash Sale Modal -->
    <div class="modal-overlay hidden" id="flashSaleModal">
        <div class="modal">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-bolt" style="color: #fa8c16;"></i>
                    <span id="modalTitle">Add Flash Sale</span>
                </h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="modalError" class="error hidden"></div>
                <div id="modalSuccess" class="success hidden"></div>

                <form id="flashSaleForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="eventName">Name</label>
                            <input type="text" id="eventName" name="event_name" required>
                        </div>
                        <div class="form-group">
                            <label for="eventStatus">Status</label>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span id="statusLabel">Inactive</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="eventStatus" name="status">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="startDate">Start Date</label>
                            <input type="datetime-local" id="startDate" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="endDate">End Date</label>
                            <input type="datetime-local" id="endDate" name="end_date" required>
                        </div>
                    </div>
                </form>

                <div class="products-section">
                    <h3 style="margin: 24px 0 16px 0;">Products</h3>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Original Price</th>
                                <th>Discount</th>
                                <th>Sale Price</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                        </tbody>
                    </table>
                    <button type="button" class="add-product-btn" onclick="openAddProductModal()">
                        <i class="fas fa-plus"></i>
                        Add Product
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn btn-default" onclick="saveDraft()">Save as Draft</button>
                <button type="button" class="btn btn-primary" onclick="activateFlashSale()">Activate</button>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal-overlay hidden" id="addProductModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Add Product to Flash Sale</h2>
                <button class="close-btn" onclick="closeAddProductModal()">&times;</button>
            </div>
            <div class="modal-body">
                <input type="text" class="search-box" placeholder="Search product" id="productSearch"
                    onkeyup="searchProducts()">
                <div id="productsList"></div>
            </div>
        </div>
    </div>

    <!-- Product Selection Modal -->
    <div class="modal-overlay hidden" id="productSelectionModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Configure Product</h2>
                <button class="close-btn" onclick="closeProductSelectionModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="selectedProductInfo"></div>
                <div class="form-group">
                    <label for="discountPercent">Discount (%)</label>
                    <input type="number" id="discountPercent" name="discount_percent" min="0" max="100" required>
                </div>
                <div class="form-group">
                    <label for="startDateProduct">Start Date (optional)</label>
                    <input type="datetime-local" id="startDateProduct" name="start_date">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="closeProductSelectionModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addProductToSale()">Add to Flash Sale</button>
            </div>
        </div>
    </div>
    <script>
        // Global variables
        let flashSales = [];
        let books = [];
        let currentEditingId = null;
        let currentSelectedProduct = null;
        let saleProducts = [];

        // API Base URL
        const API_BASE = 'http://localhost:8000/api';

        // Initialize
        document.addEventListener('DOMContentLoaded', function () {
            loadFlashSales();
            loadBooks();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Status toggle
            document.getElementById('eventStatus').addEventListener('change', function () {
                const label = document.getElementById('statusLabel');
                label.textContent = this.checked ? 'Active' : 'Inactive';
            });
        }

        // API Functions
        async function apiRequest(url, method = 'GET', data = null) {
            try {
                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                };

                if (data) {
                    options.body = JSON.stringify(data);
                }

                const response = await fetch(url, options);
                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'API request failed');
                }

                return result;
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        }

        async function loadFlashSales() {
            try {
                const response = await apiRequest(`${API_BASE}/events`);
                flashSales = response.data || response;
                renderFlashSales();
            } catch (error) {
                document.getElementById('flashSaleList').innerHTML = `
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i>
                Error loading flash sales: ${error.message}
            </div>
        `;
            }
        }

        async function loadBooks() {
            try {
                const response = await apiRequest(`${API_BASE}/buybooks?limit=100`);
                books = response.data || response;
            } catch (error) {
                console.error('Error loading books:', error);
            }
        }

        function renderFlashSales() {
            const container = document.getElementById('flashSaleList');

            if (flashSales.length === 0) {
                container.innerHTML = `
            <div class="flash-sale-item">
                <p style="text-align: center; color: #666;">No flash sales found. Create your first flash sale!</p>
            </div>
        `;
                return;
            }

            container.innerHTML = flashSales.map(sale => `
        <div class="flash-sale-item">
            <div class="sale-header">
                <div class="sale-info">
                    <h3>${sale.event_name}</h3>
                    <div class="sale-dates">
                        <span class="status-badge ${getStatusClass(sale.status)}">${getStatusText(sale.status)}</span>
                        ${formatDate(sale.start_date)} — ${formatDate(sale.end_date)}
                    </div>
                    ${sale.books && sale.books.length > 0 ? `
                        <div class="sale-products">
                            <strong>Products: ${sale.books.length}</strong>
                            <div class="products-preview">
                                ${sale.books.slice(0, 3).map(book => `
                                    <div class="product-preview">
                                        <span class="product-title">${book.title}</span>
                                        <span class="product-price">
                                            <span class="original-price">${formatPrice(book.price)}</span>
                                            <span class="discount-percent">${book.discount_percent}% OFF</span>
                                            <span class="sale-price">${formatPrice(calculateSalePrice(book.price, book.discount_percent))}</span>
                                        </span>
                                    </div>
                                `).join('')}
                                ${sale.books.length > 3 ? `<div class="more-products">+${sale.books.length - 3} more</div>` : ''}
                            </div>
                        </div>
                    ` : ''}
                </div>
                <div class="sale-actions">
                    <button class="action-btn edit-btn" onclick="editFlashSale(${sale.event_id})">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="action-btn duplicate-btn" onclick="duplicateFlashSale(${sale.event_id})">
                        <i class="fas fa-copy"></i>
                        Duplicate
                    </button>
                    <button class="action-btn delete-btn" onclick="deleteFlashSale(${sale.event_id})">
                        <i class="fas fa-trash"></i>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    `).join('');
        }

        function getStatusClass(status) {
            switch (status.toLowerCase()) {
                case 'active': return 'status-live';
                case 'scheduled': return 'status-scheduled';
                case 'inactive': return 'status-inactive';
                default: return 'status-inactive';
            }
        }

        function getStatusText(status) {
            switch (status.toLowerCase()) {
                case 'active': return 'Live';
                case 'scheduled': return 'Scheduled';
                case 'inactive': return 'Inactive';
                default: return 'Inactive';
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('vi-VN', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function formatPrice(price) {
            return parseFloat(price).toLocaleString('vi-VN') + '₫';
        }

        function calculateSalePrice(originalPrice, discountPercent) {
            const price = parseFloat(originalPrice);
            const discount = parseFloat(discountPercent);
            return price - (price * discount / 100);
        }

        // Modal Functions
        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Add Flash Sale';
            document.getElementById('flashSaleForm').reset();
            document.getElementById('productsTableBody').innerHTML = '';
            document.getElementById('statusLabel').textContent = 'Inactive';
            document.getElementById('eventStatus').checked = false;
            saleProducts = [];
            currentEditingId = null;
            document.getElementById('flashSaleModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('flashSaleModal').classList.add('hidden');
            hideMessages();
        }

        function openAddProductModal() {
            document.getElementById('addProductModal').classList.remove('hidden');
            renderProductsList();
        }

        function closeAddProductModal() {
            document.getElementById('addProductModal').classList.add('hidden');
        }

        function openProductSelectionModal(book) {
            currentSelectedProduct = book;

            document.getElementById('selectedProductInfo').innerHTML = `
        <div class="product-item">
            <div class="product-image">
                <i class="fas fa-book"></i>
            </div>
            <div class="product-info">
                <h4>${book.title}</h4>
                <div class="product-price">
                    <strong>Original Price: ${formatPrice(book.price)}</strong>
                </div>
            </div>
        </div>
    `;

            // Reset input
            document.getElementById('discountPercent').value = '';
            document.getElementById('startDateProduct').value = '';

            // Add event listener for discount calculation
            document.getElementById('discountPercent').addEventListener('input', updatePricePreview);

            document.getElementById('productSelectionModal').classList.remove('hidden');
        }

        function updatePricePreview() {
            const discount = parseFloat(document.getElementById('discountPercent').value) || 0;
            const originalPrice = parseFloat(currentSelectedProduct.price);
            const salePrice = calculateSalePrice(originalPrice, discount);

            const infoDiv = document.getElementById('selectedProductInfo');
            infoDiv.innerHTML = `
        <div class="product-item">
            <div class="product-image">
                <i class="fas fa-book"></i>
            </div>
            <div class="product-info">
                <h4>${currentSelectedProduct.title}</h4>
                <div class="product-price">
                    <div><strong>Original Price: ${formatPrice(originalPrice)}</strong></div>
                    ${discount > 0 ? `
                        <div style="color: #fa8c16; font-weight: bold;">
                            Discount: ${discount}%
                        </div>
                        <div style="color: #52c41a; font-weight: bold; font-size: 18px;">
                            Sale Price: ${formatPrice(salePrice)}
                        </div>
                        <div style="color: #999; font-size: 14px;">
                            Savings: ${formatPrice(originalPrice - salePrice)}
                        </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
        }

        function closeProductSelectionModal() {
            document.getElementById('productSelectionModal').classList.add('hidden');
            currentSelectedProduct = null;
        }

        function renderProductsList() {
            const container = document.getElementById('productsList');

            if (books.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #666;">No products available</p>';
                return;
            }

            container.innerHTML = books.map(book => `
        <div class="product-item" onclick="openProductSelectionModal(${JSON.stringify(book).replace(/"/g, '&quot;')})">
            <div class="product-image">
                <i class="fas fa-book"></i>
            </div>
            <div class="product-info">
                <h4>${book.title}</h4>
                <div class="product-price">Price: ${formatPrice(book.price)}</div>
            </div>
        </div>
    `).join('');
        }

        function searchProducts() {
            const searchTerm = document.getElementById('productSearch').value.toLowerCase();
            const filteredBooks = books.filter(book =>
                book.title.toLowerCase().includes(searchTerm)
            );

            const container = document.getElementById('productsList');
            container.innerHTML = filteredBooks.map(book => `
        <div class="product-item" onclick="openProductSelectionModal(${JSON.stringify(book).replace(/"/g, '&quot;')})">
            <div class="product-image">
                <i class="fas fa-book"></i>
            </div>
            <div class="product-info">
                <h4>${book.title}</h4>
                <div class="product-price">Price: ${formatPrice(book.price)}</div>
            </div>
        </div>
    `).join('');
        }

        function addProductToSale() {
            const discount = parseFloat(document.getElementById('discountPercent')?.value);
            const quantityLimit = parseInt(document.getElementById('quantityLimit')?.value) || 50;

            if (!discount || discount < 0 || discount > 100) {
                showMessage('Please enter a valid discount percentage (0-100)', 'error');
                return;
            }

            const salePrice = calculateSalePrice(currentSelectedProduct.price, discount);

            const productData = {
                ...currentSelectedProduct,
                discount_percent: discount,
                sale_price: salePrice.toFixed(2),
                quantity_limit: quantityLimit
            };

            // Check if product already exists
            const existingIndex = saleProducts.findIndex(p => p.id === currentSelectedProduct.id);
            if (existingIndex !== -1) {
                saleProducts[existingIndex] = productData;
            } else {
                saleProducts.push(productData);
            }

            renderProductsTable();
            closeProductSelectionModal();
            closeAddProductModal();
        }

        function renderProductsTable() {
            const tbody = document.getElementById('productsTableBody');

            tbody.innerHTML = saleProducts.map(product => `
        <tr>
            <td>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="product-image" style="width: 32px; height: 32px; font-size: 14px;">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <div style="font-weight: 500;">${product.title}</div>
                        ${product.quantity_limit ? `<div style="font-size: 12px; color: #666;">Limit: ${product.quantity_limit}</div>` : ''}
                    </div>
                </div>
            </td>
            <td>${formatPrice(product.price)}</td>
            <td>
                <span class="discount-badge">${product.discount_percent}%</span>
            </td>
            <td>
                <span class="sale-price" style="font-weight: bold; color: #52c41a;">
                    ${formatPrice(product.sale_price)}
                </span>
                <div style="font-size: 12px; color: #999;">
                    Save: ${formatPrice(product.price - product.sale_price)}
                </div>
            </td>
            <td>
                <button class="action-btn delete-btn" onclick="removeProduct(${product.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
        }

        function removeProduct(productId) {
            saleProducts = saleProducts.filter(p => p.id !== productId);
            renderProductsTable();
        }

        // Function to remove product from flash sale via API
        async function removeProductFromSale(eventId, bookId) {
            if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi flash sale?')) {
                return;
            }

            try {
                await apiRequest(`${API_BASE}/events/${eventId}/books/${bookId}`, 'DELETE');
                showMessage('Sản phẩm đã được xóa khỏi flash sale', 'success');

                // Reload the flash sale data to update the display
                if (currentEditingId) {
                    await loadFlashSaleForEdit(currentEditingId);
                }

                // Also reload the main list
                loadFlashSales();
            } catch (error) {
                showMessage('Lỗi khi xóa sản phẩm: ' + error.message, 'error');
            }
        }

        // Function to load flash sale data for editing with scrollable products list
        async function loadFlashSaleForEdit(eventId) {
            try {
                const response = await apiRequest(`${API_BASE}/events/${eventId}`);
                const sale = response.data || response;

                // Load products for this sale
                saleProducts = sale.books || [];
                renderEditProductsTable(eventId);

                // Update summary info
                updateEventSummary(sale);
            } catch (error) {
                console.error('Error loading flash sale products:', error);
            }
        }

        // Function to update event summary display
        function updateEventSummary(sale) {
            const summaryDiv = document.getElementById('eventSummary');
            if (summaryDiv) {
                summaryDiv.innerHTML = `
            <div class="event-summary">
                <h4>Event Summary</h4>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="label">Total Products:</span>
                        <span class="value">${sale.total_products || 0}</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Total Sold:</span>
                        <span class="value">${sale.total_sold || 0}</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Status:</span>
                        <span class="value status-badge ${getStatusClass(sale.status)}">${getStatusText(sale.status)}</span>
                    </div>
                </div>
            </div>
        `;
            }
        }

        // Updated render function for edit mode with scrollable list and delete buttons
        function renderEditProductsTable(eventId) {
            const tbody = document.getElementById('productsTableBody');

            if (saleProducts.length === 0) {
                tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; color: #666; padding: 20px;">
                    Chưa có sản phẩm nào trong flash sale này
                </td>
            </tr>
        `;
                return;
            }

            tbody.innerHTML = saleProducts.map(product => `
        <tr>
            <td>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="product-image" style="width: 32px; height: 32px; font-size: 14px;">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <div style="font-weight: 500; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${product.title}">
                            ${product.title}
                        </div>
                        <div style="font-size: 12px; color: #666;">
                            Limit: ${product.quantity_limit || 50} | Sold: ${product.sold_quantity || 0}
                        </div>
                    </div>
                </div>
            </td>
            <td style="font-weight: 500;">${formatPrice(product.price)}</td>
            <td>
                <span class="discount-badge">${product.discount_percent}%</span>
            </td>
            <td>
                <div style="font-weight: bold; color: #52c41a;">
                    ${formatPrice(product.sale_price || calculateSalePrice(product.price, product.discount_percent))}
                </div>
                <div style="font-size: 12px; color: #999;">
                    Save: ${formatPrice(product.price - (product.sale_price || calculateSalePrice(product.price, product.discount_percent)))}
                </div>
            </td>
            <td>
                <div class="quantity-info">
                    <div style="font-size: 12px; color: #666;">Available: ${(product.quantity_limit || 50) - (product.sold_quantity || 0)}</div>
                    <div style="font-size: 10px; color: #999;">of ${product.quantity_limit || 50}</div>
                </div>
            </td>
            <td>
                <button class="action-btn delete-btn" onclick="removeProductFromSale(${eventId}, ${product.books_id || product.id})" title="Xóa khỏi flash sale">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');

            // Add scrollable styling to the table container
            const tableContainer = document.querySelector('.products-table-container');
            if (tableContainer) {
                tableContainer.style.maxHeight = '400px';
                tableContainer.style.overflowY = 'auto';
                tableContainer.style.border = '1px solid #e0e0e0';
                tableContainer.style.borderRadius = '8px';
                tableContainer.style.marginTop = '10px';
            }
        }

        // Flash Sale Actions
        async function editFlashSale(id) {
            try {
                const response = await apiRequest(`${API_BASE}/events/${id}`);
                const sale = response.data || response;

                currentEditingId = id;
                document.getElementById('modalTitle').textContent = 'Edit Flash Sale';
                document.getElementById('eventName').value = sale.event_name;
                document.getElementById('startDate').value = formatDateForInput(sale.start_date);
                document.getElementById('endDate').value = formatDateForInput(sale.end_date);

                const isActive = sale.status.toLowerCase() === 'active';
                document.getElementById('eventStatus').checked = isActive;
                document.getElementById('statusLabel').textContent = isActive ? 'Active' : 'Inactive';

                // Load products for this sale with the new scrollable table
                await loadFlashSaleForEdit(id);

                document.getElementById('flashSaleModal').classList.remove('hidden');

                // Show loading message while fetching products
                showMessage('Đang tải danh sách sản phẩm...', 'info');
                setTimeout(() => hideMessages(), 2000);

            } catch (error) {
                showMessage('Error loading flash sale details: ' + error.message, 'error');
            }
        }

        async function duplicateFlashSale(id) {
            try {
                const response = await apiRequest(`${API_BASE}/events/${id}`);
                const sale = response.data || response;

                currentEditingId = null;
                document.getElementById('modalTitle').textContent = 'Duplicate Flash Sale';
                document.getElementById('eventName').value = sale.event_name + ' - Copy';
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';
                document.getElementById('eventStatus').checked = false;
                document.getElementById('statusLabel').textContent = 'Inactive';

                // Load products from the original sale
                saleProducts = sale.books || [];
                renderProductsTable();

                document.getElementById('flashSaleModal').classList.remove('hidden');
            } catch (error) {
                showMessage('Error duplicating flash sale: ' + error.message, 'error');
            }
        }

        async function deleteFlashSale(id) {
            if (!confirm('Are you sure you want to delete this flash sale?')) {
                return;
            }

            try {
                await apiRequest(`${API_BASE}/events/${id}`, 'DELETE');
                showMessage('Flash sale deleted successfully', 'success');
                loadFlashSales();
            } catch (error) {
                showMessage('Error deleting flash sale: ' + error.message, 'error');
            }
        }

        async function saveDraft() {
            const formData = getFormData();
            formData.status = 'inactive';

            try {
                let eventId = currentEditingId;

                if (currentEditingId) {
                    await apiRequest(`${API_BASE}/events/${currentEditingId}`, 'PUT', formData);
                    showMessage('Flash sale updated as draft', 'success');
                } else {
                    const response = await apiRequest(`${API_BASE}/events`, 'POST', formData);
                    eventId = response.data?.event_id || response.event_id;
                    currentEditingId = eventId;
                    showMessage('Flash sale saved as draft', 'success');
                }

                // Save products using the correct API endpoint
                if (eventId && saleProducts.length > 0) {
                    await saveProducts(eventId);
                }

                setTimeout(() => {
                    closeModal();
                    loadFlashSales();
                }, 1500);
            } catch (error) {
                showMessage('Error saving draft: ' + error.message, 'error');
            }
        }

        async function activateFlashSale() {
            const formData = getFormData();
            formData.status = 'active';

            try {
                let eventId = currentEditingId;

                if (currentEditingId) {
                    await apiRequest(`${API_BASE}/events/${currentEditingId}`, 'PUT', formData);
                    showMessage('Flash sale updated and activated', 'success');
                } else {
                    const response = await apiRequest(`${API_BASE}/events`, 'POST', formData);
                    eventId = response.data?.event_id || response.event_id;
                    currentEditingId = eventId;
                    showMessage('Flash sale created and activated', 'success');
                }

                // Save products using the correct API endpoint
                if (eventId && saleProducts.length > 0) {
                    await saveProducts(eventId);
                }

                setTimeout(() => {
                    closeModal();
                    loadFlashSales();
                }, 1500);
            } catch (error) {
                showMessage('Error activating flash sale: ' + error.message, 'error');
            }
        }

        async function saveProducts(eventId) {
            if (!eventId || saleProducts.length === 0) {
                return;
            }

            try {
                // Add each product to the flash sale using the correct API endpoint
                for (const product of saleProducts) {
                    const productData = {
                        books_id: product.id,
                        discount_percent: product.discount_percent,
                        quantity_limit: product.quantity_limit || 50
                    };

                    await apiRequest(`${API_BASE}/events/${eventId}/books`, 'POST', productData);
                }
            } catch (error) {
                console.error('Error saving products:', error);
                throw error;
            }
        }

        function getFormData() {
            return {
                event_name: document.getElementById('eventName').value,
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value,
                status: document.getElementById('eventStatus').checked ? 'active' : 'inactive'
            };
        }

        function formatDateForInput(dateString) {
            const date = new Date(dateString);
            return date.toISOString().slice(0, 16);
        }

        function showMessage(message, type) {
            const errorDiv = document.getElementById('modalError');
            const successDiv = document.getElementById('modalSuccess');

            hideMessages();

            if (type === 'error') {
                errorDiv.textContent = message;
                errorDiv.classList.remove('hidden');
            } else if (type === 'info') {
                successDiv.textContent = message;
                successDiv.classList.remove('hidden');
                successDiv.style.backgroundColor = '#1890ff';
            } else {
                successDiv.textContent = message;
                successDiv.classList.remove('hidden');
                successDiv.style.backgroundColor = '#52c41a';
            }
        }

        function hideMessages() {
            document.getElementById('modalError').classList.add('hidden');
            document.getElementById('modalSuccess').classList.add('hidden');
        }

        // Form validation
        document.getElementById('eventName').addEventListener('input', function () {
            if (this.value.length > 100) {
                this.value = this.value.slice(0, 100);
            }
        });

        document.getElementById('startDate').addEventListener('change', function () {
            const endDate = document.getElementById('endDate');
            if (this.value && endDate.value && new Date(this.value) >= new Date(endDate.value)) {
                endDate.value = '';
                showMessage('End date must be after start date', 'error');
            }
        });

        document.getElementById('endDate').addEventListener('change', function () {
            const startDate = document.getElementById('startDate');
            if (this.value && startDate.value && new Date(this.value) <= new Date(startDate.value)) {
                this.value = '';
                showMessage('End date must be after start date', 'error');
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (!document.getElementById('flashSaleModal').classList.contains('hidden')) {
                    closeModal();
                }
                if (!document.getElementById('addProductModal').classList.contains('hidden')) {
                    closeAddProductModal();
                }
                if (!document.getElementById('productSelectionModal').classList.contains('hidden')) {
                    closeProductSelectionModal();
                }
            }
        });

        // Auto-refresh every 30 seconds
        setInterval(loadFlashSales, 30000);
    </script>
</body>

</html>