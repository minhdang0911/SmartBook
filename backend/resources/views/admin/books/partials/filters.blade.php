<form action="{{ route('admin.books.index') }}" method="GET" class="search-form d-flex flex-wrap gap-3 align-items-end mb-4">
    <div>
        <label class="form-label">🔍 Từ khoá</label>
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Tên sách..." value="{{ request('search') }}">
    </div>

    <div>
        <label class="form-label">✍️ Tác giả</label>
        <select name="author_id" class="form-control form-control-sm">
            <option value="">-- Tất cả --</option>
            @foreach ($authors as $author)
                <option value="{{ $author->id }}" {{ request('author_id') == $author->id ? 'selected' : '' }}>{{ $author->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="form-label">📂 Danh mục</label>
        <select name="category_id" class="form-control form-control-sm">
            <option value="">-- Tất cả --</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="form-label">🏢 NXB</label>
        <select name="publisher_id" class="form-control form-control-sm">
            <option value="">-- Tất cả --</option>
            @foreach ($publishers as $publisher)
                <option value="{{ $publisher->id }}" {{ request('publisher_id') == $publisher->id ? 'selected' : '' }}>{{ $publisher->name }}</option>
            @endforeach
        </select>
    </div>
<div>
        <label class="form-label">📚 Loại sách</label>
        <select name="is_physical" class="form-control form-control-sm">
            <option value="">-- Tất cả --</option>
            <option value="1" {{ request('is_physical') == '1' ? 'selected' : '' }}>Sách giấy</option>
            <option value="0" {{ request('is_physical') == '0' ? 'selected' : '' }}>Ebook</option>
        </select>
    </div>
    <div>
        <label class="form-label">💰 Giá từ</label>
        <input type="number" name="price_min" class="form-control form-control-sm" value="{{ request('price_min') }}">
    </div>

    <div>
        <label class="form-label">💸 Giá đến</label>
        <input type="number" name="price_max" class="form-control form-control-sm" value="{{ request('price_max') }}">
    </div>

    <div>
        <label class="form-label">📦 Tồn kho</label>
        <select name="stock_status" class="form-control form-control-sm">
            <option value="">-- Tất cả --</option>
            <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>Còn hàng</option>
            <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Hết hàng</option>
        </select>
    </div>

    

    <div>
        <label class="form-label">↕️ Sắp xếp</label>
        <select name="sort_by" class="form-control form-control-sm">
            <option value="latest" {{ request('sort_by') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
            <option value="views" {{ request('sort_by') == 'views' ? 'selected' : '' }}>Lượt xem nhiều</option>
            <option value="likes" {{ request('sort_by') == 'likes' ? 'selected' : '' }}>Lượt thích nhiều</option>
            <option value="rating" {{ request('sort_by') == 'rating' ? 'selected' : '' }}>Đánh giá cao</option>
            <option value="price_asc" {{ request('sort_by') == 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
            <option value="price_desc" {{ request('sort_by') == 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
        </select>
    </div>

    {{-- Nút lọc và thêm --}}
    <div class="d-flex gap-2 align-items-end flex-wrap">
        <div>
            <label class="form-label d-block">⚙️</label>
            <button type="submit" class="btn btn-primary btn-sm me-1">🔍 Lọc</button>
            <a href="{{ route('admin.books.index') }}" class="btn btn-outline-secondary btn-sm">🔄 Reset</a>
        </div>

        <div>
                            <a href="{{ route('admin.books.create') }}" class="btn btn-success btn-sm ms-auto">➕ Thêm sách</a>

        </div>
    </div>
</form>

@push('styles')
<style>
    .search-form {
        background-color: #f8f9fa;
        padding: 16px 20px;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    }

    .search-form .form-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 4px;
    }

    .search-form .form-control-sm {
        min-width: 160px;
        max-width: 220px;
    }

    .search-form .btn-sm {
        font-size: 0.875rem;
        padding: 6px 12px;
        border-radius: 6px;
    }

    @media (max-width: 768px) {
        .search-form {
            flex-direction: column;
            align-items: stretch;
        }

        .search-form > div {
            width: 100%;
        }

        .search-form .form-control-sm {
            width: 100%;
        }

        .search-form .btn-sm {
            width: 100%;
        }
    }
    .btn-success.btn-sm {
    background-color: #28a745;
    color: white;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 0.85rem;
    min-width: 100px;
}
.btn-success.btn-sm:hover {
    background-color: #218838;
}

</style>
@endpush
