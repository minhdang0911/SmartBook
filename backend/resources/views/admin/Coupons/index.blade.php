
@extends('layouts.app')

@section('content')



    <!DOCTYPE html>
    <html lang="vi">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quản lý Coupon</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            .loader {
                border: 4px solid #f3f3f3;
                border-top: 4px solid #3498db;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                animation: spin 2s linear infinite;
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }

            .autocomplete-dropdown {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid #e5e7eb;
                border-top: none;
                max-height: 200px;
                overflow-y: auto;
                z-index: 1000;
            }

            .autocomplete-item {
                padding: 8px 12px;
                cursor: pointer;
                border-bottom: 1px solid #f3f4f6;
            }

            .autocomplete-item:hover {
                background-color: #f9fafb;
            }

            .selected-item {
                background-color: #dbeafe;
                color: #1e40af;
                padding: 4px 8px;
                border-radius: 4px;
                margin: 2px;
                display: inline-flex;
                align-items: center;
                font-size: 12px;
            }

            .scope-field {
                transition: all 0.3s ease;
            }

            .scope-field.hidden {
                display: none;
            }
        </style>
    </head>

    <body class="bg-gray-100">
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">Quản lý Coupon</h1>
                    <button id="addCouponBtn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Thêm Coupon
                    </button>
                </div>
            </div>

            <!-- Coupon Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tên</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mô tả</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Phạm vi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Giảm giá</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Trạng thái</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thời gian</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="couponTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Loading -->
                            <tr id="loadingRow">
                                <td colspan="8" class="px-6 py-4 text-center">
                                    <div class="loader mx-auto"></div>
                                    <p class="mt-2 text-gray-500">Đang tải...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div id="couponModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-screen overflow-y-auto">
                    <div class="p-6">
                        <!-- Modal Header -->
                        <div class="flex justify-between items-center mb-6">
                            <h2 id="modalTitle" class="text-xl font-bold text-gray-800">Thêm Coupon</h2>
                            <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Form -->
                        <form id="couponForm" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Tên coupon -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tên coupon</label>
                                    <input type="text" id="name" name="name" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Phạm vi áp dụng -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phạm vi áp dụng</label>
                                    <select id="scope" name="scope" onchange="toggleScopeFields()"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="order">Tất cả đơn hàng</option>
                                        <option value="product">Sản phẩm cụ thể</option>
                                        <option value="category">Danh mục cụ thể</option>
                                    </select>
                                </div>

                                <!-- Loại giảm giá -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Loại giảm giá</label>
                                    <select id="discount_type" name="discount_type"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="percent">Phần trăm (%)</option>
                                        <option value="fixed">Số tiền cố định</option>
                                    </select>
                                </div>

                                <!-- Giá trị giảm -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Giá trị giảm</label>
                                    <input type="number" id="discount_value" name="discount_value" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Giá trị đơn hàng tối thiểu -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Giá trị đơn hàng tối
                                        thiểu</label>
                                    <input type="number" id="min_order_value" name="min_order_value"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Giới hạn sử dụng -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Giới hạn sử dụng</label>
                                    <input type="number" id="usage_limit" name="usage_limit"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Ngày bắt đầu -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ngày bắt đầu</label>
                                    <input type="datetime-local" id="start_date" name="start_date" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Ngày kết thúc -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ngày kết thúc</label>
                                    <input type="datetime-local" id="end_date" name="end_date" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <!-- Mô tả -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả</label>
                                <textarea id="description" name="description" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>

                            <!-- Sách áp dụng -->
                            <div id="bookScopeField" class="scope-field hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sách áp dụng</label>
                                <div class="relative">
                                    <input type="text" id="bookSearch" placeholder="Tìm kiếm sách..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <div id="bookDropdown" class="autocomplete-dropdown hidden"></div>
                                </div>
                                <div id="selectedBooks" class="mt-2 flex flex-wrap gap-1"></div>
                            </div>

                            <!-- Danh mục áp dụng -->
                            <div id="categoryScopeField" class="scope-field hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Danh mục áp dụng</label>
                                <div class="relative">
                                    <input type="text" id="categorySearch" placeholder="Tìm kiếm danh mục..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <div id="categoryDropdown" class="autocomplete-dropdown hidden"></div>
                                </div>
                                <div id="selectedCategories" class="mt-2 flex flex-wrap gap-1"></div>
                            </div>

                            <!-- Trạng thái -->
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" id="is_active" name="is_active" checked
                                        class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="text-sm font-medium text-gray-700">Kích hoạt</span>
                                </label>
                            </div>

                            <!-- Submit buttons -->
                            <div class="flex justify-end space-x-3 pt-6">
                                <button type="button" id="cancelBtn"
                                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                    Hủy
                                </button>
                                <button type="submit" id="submitBtn"
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                    Lưu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Global variables
            let coupons = [];
            let books = [];
            let categories = [];
            let selectedBooks = [];
            let selectedCategories = [];
            let editingCouponId = null;

            // API endpoints
            const API_BASE = 'http://localhost:8000/api';
            const API_ENDPOINTS = {
                coupons: `${API_BASE}/coupons/get`,
                books: `${API_BASE}/buybooks`,
                categories: `${API_BASE}/categories`,
                createCoupon: `${API_BASE}/coupons`,
                updateCoupon: `${API_BASE}/coupons`,
                deleteCoupon: `${API_BASE}/coupons`
            };

            // Initialize
            document.addEventListener('DOMContentLoaded', function () {
                loadData();
                setupEventListeners();
            });

            // Setup event listeners
            function setupEventListeners() {
                // Modal controls
                document.getElementById('addCouponBtn').addEventListener('click', () => openModal());
                document.getElementById('closeModal').addEventListener('click', closeModal);
                document.getElementById('cancelBtn').addEventListener('click', closeModal);

                // Form submission
                document.getElementById('couponForm').addEventListener('submit', handleSubmit);

                // Autocomplete
                setupAutocomplete();

                // Close modal when clicking outside
                document.getElementById('couponModal').addEventListener('click', function (e) {
                    if (e.target === this) closeModal();
                });
            }

            // Toggle scope fields based on selected scope
            function toggleScopeFields() {
                const scope = document.getElementById('scope').value;
                const bookField = document.getElementById('bookScopeField');
                const categoryField = document.getElementById('categoryScopeField');

                // Hide all scope fields first
                bookField.classList.add('hidden');
                categoryField.classList.add('hidden');

                // Show relevant field based on scope
                switch (scope) {
                    case 'product':
                        bookField.classList.remove('hidden');
                        break;
                    case 'category':
                        categoryField.classList.remove('hidden');
                        break;
                    case 'order':
                        // No additional fields needed for order scope
                        break;
                }
            }

            // Load all data
            async function loadData() {
                try {
                    const [couponsData, booksData, categoriesData] = await Promise.all([
                        fetch(API_ENDPOINTS.coupons).then(r => r.json()),
                        fetch(API_ENDPOINTS.books).then(r => r.json()),
                        fetch(API_ENDPOINTS.categories).then(r => r.json())
                    ]);

                    coupons = couponsData;
                    books = booksData.data || booksData;
                    categories = categoriesData.data || categoriesData;

                    renderCoupons();
                } catch (error) {
                    console.error('Error loading data:', error);
                    showError('Không thể tải dữ liệu');
                }
            }

            // Get scope display text
            function getScopeText(scope) {
                switch (scope) {
                    case 'order':
                        return 'Tất cả đơn hàng';
                    case 'product':
                        return 'Sản phẩm cụ thể';
                    case 'category':
                        return 'Danh mục cụ thể';
                    default:
                        return scope;
                }
            }

            // Render coupons table
            function renderCoupons() {
                const tbody = document.getElementById('couponTableBody');

                if (coupons.length === 0) {
                    tbody.innerHTML = `
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        Không có dữ liệu
                                    </td>
                                </tr>
                            `;
                    return;
                }

                tbody.innerHTML = coupons.map(coupon => `
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ${coupon.id}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${coupon.name}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                    ${coupon.description || '-'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        ${getScopeText(coupon.scope)}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${coupon.discount_type === 'percent' ? coupon.discount_value + '%' : formatCurrency(coupon.discount_value)}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${coupon.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                        ${coupon.is_active ? 'Hoạt động' : 'Không hoạt động'}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>${formatDate(coupon.start_date)}</div>
                                    <div class="text-gray-500">${formatDate(coupon.end_date)}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editCoupon(${coupon.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="deleteCoupon(${coupon.id})" class="text-red-600 hover:text-red-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        `).join('');
            }

            // Setup autocomplete
            function setupAutocomplete() {
                const bookSearchInput = document.getElementById('bookSearch');
                const categorySearchInput = document.getElementById('categorySearch');
                const bookDropdown = document.getElementById('bookDropdown');
                const categoryDropdown = document.getElementById('categoryDropdown');

                // Book autocomplete
                bookSearchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase();

                    if (query.length === 0) {
                        bookDropdown.classList.add('hidden');
                        return;
                    }

                    const filteredBooks = books.filter(book => {
                        const lowerTitle = book.title.toLowerCase();
                        return lowerTitle.split(' ').some(word => word.startsWith(query)) &&
                            !selectedBooks.some(selected => selected.id === book.id);
                    });

                    if (filteredBooks.length > 0) {
                        bookDropdown.innerHTML = filteredBooks.map(book => `
                            <div class="autocomplete-item" onclick="selectBook(${book.id}, '${book.title.replace(/'/g, "\\'")}')">
                                ${book.title}
                            </div>
                        `).join('');
                        bookDropdown.classList.remove('hidden');
                    } else {
                        bookDropdown.classList.add('hidden');
                    }
                });

                // Category autocomplete
                categorySearchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase();
                    if (query.length < 1) {
                        categoryDropdown.classList.add('hidden');
                        return;
                    }

                    const filteredCategories = categories.filter(category =>
                        category.name.toLowerCase().includes(query) &&
                        !selectedCategories.some(selected => selected.id === category.id)
                    );

                    if (filteredCategories.length > 0) {
                        categoryDropdown.innerHTML = filteredCategories.map(category => `
                                    <div class="autocomplete-item" onclick="selectCategory(${category.id}, '${category.name.replace(/'/g, "\\'")}')">
                                        ${category.name}
                                    </div>
                                `).join('');
                        categoryDropdown.classList.remove('hidden');
                    } else {
                        categoryDropdown.classList.add('hidden');
                    }
                });

                // Hide dropdowns when clicking outside
                document.addEventListener('click', function (e) {
                    if (!bookSearchInput.contains(e.target) && !bookDropdown.contains(e.target)) {
                        bookDropdown.classList.add('hidden');
                    }
                    if (!categorySearchInput.contains(e.target) && !categoryDropdown.contains(e.target)) {
                        categoryDropdown.classList.add('hidden');
                    }
                });
            }

            // Select book from autocomplete
            function selectBook(id, title) {
                const book = { id, title };
                selectedBooks.push(book);
                renderSelectedBooks();
                document.getElementById('bookSearch').value = '';
                document.getElementById('bookDropdown').classList.add('hidden');
            }

            // Select category from autocomplete
            function selectCategory(id, name) {
                const category = { id, name };
                selectedCategories.push(category);
                renderSelectedCategories();
                document.getElementById('categorySearch').value = '';
                document.getElementById('categoryDropdown').classList.add('hidden');
            }

            // Remove selected book
            function removeBook(id) {
                selectedBooks = selectedBooks.filter(book => book.id !== id);
                renderSelectedBooks();
            }

            // Remove selected category
            function removeCategory(id) {
                selectedCategories = selectedCategories.filter(category => category.id !== id);
                renderSelectedCategories();
            }

            // Render selected books
            function renderSelectedBooks() {
                const container = document.getElementById('selectedBooks');
                container.innerHTML = selectedBooks.map(book => `
                            <span class="selected-item">
                                ${book.title}
                                <button onclick="removeBook(${book.id})" class="ml-1 text-red-500 hover:text-red-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </span>
                        `).join('');
            }

            // Render selected categories
            function renderSelectedCategories() {
                const container = document.getElementById('selectedCategories');
                container.innerHTML = selectedCategories.map(category => `
                            <span class="selected-item">
                                ${category.name}
                                <button onclick="removeCategory(${category.id})" class="ml-1 text-red-500 hover:text-red-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </span>
                        `).join('');
            }

            // Open modal
            function openModal(coupon = null) {
                const modal = document.getElementById('couponModal');
                const modalTitle = document.getElementById('modalTitle');
                const form = document.getElementById('couponForm');

                if (coupon) {
                    modalTitle.textContent = 'Sửa Coupon';
                    editingCouponId = coupon.id;
                    populateForm(coupon);
                } else {
                    modalTitle.textContent = 'Thêm Coupon';
                    editingCouponId = null;
                    form.reset();
                    selectedBooks = [];
                    selectedCategories = [];
                    renderSelectedBooks();
                    renderSelectedCategories();
                    document.getElementById('is_active').checked = true;
                    document.getElementById('scope').value = 'order'; // Default to order scope
                    toggleScopeFields();
                }

                modal.classList.remove('hidden');
            }

            // Close modal
            function closeModal() {
                document.getElementById('couponModal').classList.add('hidden');
                document.getElementById('bookDropdown').classList.add('hidden');
                document.getElementById('categoryDropdown').classList.add('hidden');
            }

            // Populate form with coupon data
            function populateForm(coupon) {
                document.getElementById('name').value = coupon.name;
                document.getElementById('description').value = coupon.description || '';
                document.getElementById('discount_type').value = coupon.discount_type;
                document.getElementById('discount_value').value = coupon.discount_value;
                document.getElementById('scope').value = coupon.scope;
                document.getElementById('min_order_value').value = coupon.min_order_value || '';
                document.getElementById('usage_limit').value = coupon.usage_limit || '';
                document.getElementById('is_active').checked = coupon.is_active;
                document.getElementById('start_date').value = formatDateForInput(coupon.start_date);
                document.getElementById('end_date').value = formatDateForInput(coupon.end_date);

                // Set selected books and categories
                selectedBooks = coupon.books || [];
                selectedCategories = coupon.categories || [];
                renderSelectedBooks();
                renderSelectedCategories();

                // Toggle scope fields after setting the scope value
                toggleScopeFields();
            }

            // Handle form submission
            async function handleSubmit(e) {
                e.preventDefault();

                const formData = new FormData(e.target);
                const scope = formData.get('scope');
                const data = {
                    name: formData.get('name'),
                    description: formData.get('description'),
                    discount_type: formData.get('discount_type'),
                    discount_value: parseFloat(formData.get('discount_value')),
                    scope: formData.get('scope'),
                    min_order_value: parseFloat(formData.get('min_order_value')) || 0,
                    usage_limit: parseInt(formData.get('usage_limit')) || null,
                    is_active: formData.get('is_active') === 'on',
                    start_date: formData.get('start_date'),
                    end_date: formData.get('end_date'),
                    book_ids: selectedBooks.map(book => book.id),
                    category_ids: selectedCategories.map(category => category.id)
                };

                try {
                    const url = editingCouponId ?
                        `${API_ENDPOINTS.updateCoupon}/${editingCouponId}` :
                        API_ENDPOINTS.createCoupon;

                    const method = editingCouponId ? 'PUT' : 'POST';

                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            // Add authorization header if needed
                            // 'Authorization': 'Bearer ' + token
                        },
                        body: JSON.stringify(data)
                    });

                    if (response.ok) {
                        closeModal();
                        loadData(); // Reload data
                        showSuccess(editingCouponId ? 'Cập nhật coupon thành công' : 'Thêm coupon thành công');
                    } else {
                        throw new Error('API request failed');
                    }
                } catch (error) {
                    console.error('Error saving coupon:', error);
                    showError('Có lỗi xảy ra khi lưu coupon');
                }
            }

            // Edit coupon
            function editCoupon(id) {
                const coupon = coupons.find(c => c.id === id);
                if (coupon) {
                    openModal(coupon);
                }
            }

            // Delete coupon
            async function deleteCoupon(id) {
                if (!confirm('Bạn có chắc chắn muốn xóa coupon này?')) {
                    return;
                }

                try {
                    const response = await fetch(`${API_ENDPOINTS.deleteCoupon}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            // Add authorization header if needed
                            // 'Authorization': 'Bearer ' + token
                        }
                    });

                    if (response.ok) {
                        loadData(); // Reload data
                        showSuccess('Xóa coupon thành công');
                    } else {
                        throw new Error('API request failed');
                    }
                } catch (error) {
                    console.error('Error deleting coupon:', error);
                    showError('Có lỗi xảy ra khi xóa coupon');
                }
            }

            // Utility functions
            function formatCurrency(amount) {
                return new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(amount);
            }

            function formatDate(dateString) {
                return new Date(dateString).toLocaleDateString('vi-VN');
            }

            function formatDateForInput(dateString) {
                const date = new Date(dateString);
                return date.toISOString().slice(0, 16);
            }

            function showSuccess(message) {
                // Create and show success notification
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                notification.textContent = message;
                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }

            function showError(message) {
                // Create and show error notification
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                notification.textContent = message;
                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.remove();
                }, 5000);
            }
        </script>
    </body>

    </html>


@endsection