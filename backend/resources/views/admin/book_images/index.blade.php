@extends('layouts.app')

@section('title', 'Quản lý Ảnh sách')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .container {
            max-width: 1200px;
            padding: 24px;
        }

        h4.mb-0 {
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            justify-content: center;
        }

        .table {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table thead {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        .table th, .table td {
            padding: 16px;
            vertical-align: middle;
            font-size: 0.9rem;
            color: #333;
        }

        .table tr:hover {
            background: #f0f4ff;
        }

        .btn-sm {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
            font-weight: 600;
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
            border: none;
        }

        .btn-danger {
            background: #dc3545;
            border: none;
        }

        img.preview-thumb {
            height: 50px;
            max-width: 100%;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        .modal img.preview-modal {
            max-height: 140px;
            width: auto;
            object-fit: contain;
            display: block;
            margin-top: 10px;
            border-radius: 8px;
        }

        .table tbody tr td[colspan='4'] {
            text-align: center;
            color: #666;
            font-size: 1rem;
            padding: 24px;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container.select2-container--open,
        .select2-dropdown {
            z-index: 9999 !important;
        }

        #multi-preview img {
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">📸 Danh sách ảnh phụ</h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-circle"></i> Thêm ảnh
        </button>
    </div>

    @include('components.alert')

    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>STT</th>
                    <th>Ảnh</th>
                    <th>Sách</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($images as $img)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><img src="{{ $img->image_url }}" class="preview-thumb" alt="Ảnh sách"></td>
                        <td>{{ $img->book->title ?? 'Không xác định' }}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal-{{ $img->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.book_images.destroy', $img->id) }}" class="d-inline" onsubmit="return confirm('Xoá ảnh này?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editModal-{{ $img->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.book_images.update', $img->id) }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">🛠️ Sửa ảnh sách</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label">Chọn sách</label>
                                        <select name="book_id" id="book-select-edit-{{ $img->id }}" class="form-select select-book" required>
                                            <option></option>
                                            @foreach ($books as $book)
                                                <option value="{{ $book->id }}" {{ $img->book_id == $book->id ? 'selected' : '' }}>{{ $book->title }}</option>
                                            @endforeach
                                        </select>

                                        <label class="form-label mt-3">Ảnh mới (nếu thay)</label>
                                        <input type="file" name="image_url" accept="image/*" class="form-control preview-input" data-preview-target="#preview-edit-{{ $img->id }}">
                                        <img src="{{ $img->image_url }}" class="preview-modal" id="preview-edit-{{ $img->id }}" alt="Ảnh sách">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="4">Không có ảnh nào 🥲</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $images->links() }}

    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.book_images.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">➕ Thêm ảnh sách</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Chọn sách</label>
                        <select name="book_id" id="book-select-create" class="form-select select-book" required>
                            <option></option>
                            @foreach ($books as $book)
                                <option value="{{ $book->id }}">{{ $book->title }}</option>
                            @endforeach
                        </select>

                        <label class="form-label mt-3">Chọn ảnh (có thể nhiều)</label>
                        <input type="file" name="images[]" multiple accept="image/*" class="form-control preview-input-multi" data-preview-container="#multi-preview">
                        <div class="row mt-3 gx-2 gy-2" id="multi-preview"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">📸 Thêm ảnh</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.preview-input').forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                const preview = document.querySelector(this.dataset.previewTarget);
                if (file && preview) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.src = e.target.result;
                        preview.classList.remove('d-none');
                        preview.classList.add('d-block');
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        document.querySelectorAll('.preview-input-multi').forEach(input => {
            input.addEventListener('change', function() {
                const container = document.querySelector(this.dataset.previewContainer);
                container.innerHTML = '';
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = e => {
                        const col = document.createElement('div');
                        col.className = 'col-auto';
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.classList.add('img-thumbnail');
                        img.style.height = '100px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '8px';
                        col.appendChild(img);
                        container.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                });
            });
        });

        $('#createModal').on('shown.bs.modal', function () {
            $('#book-select-create').select2({
                dropdownParent: $('#createModal'),
                width: '100%',
                placeholder: 'Chọn sách...',
                allowClear: true,
                minimumInputLength: 0,
                language: {
                    noResults: () => "Không tìm thấy sách",
                    searching: () => "Đang tìm kiếm..."
                }
            });
        }).on('hidden.bs.modal', function () {
            $('#book-select-create').select2('destroy');
        });

        @foreach ($images as $img)
        $('#editModal-{{ $img->id }}').on('shown.bs.modal', function () {
            $('#book-select-edit-{{ $img->id }}').select2({
                dropdownParent: $('#editModal-{{ $img->id }}'),
                width: '100%',
                placeholder: 'Chọn sách...',
                allowClear: true,
                minimumInputLength: 0,
                language: {
                    noResults: () => "Không tìm thấy sách",
                    searching: () => "Đang tìm kiếm..."
                }
            });
        }).on('hidden.bs.modal', function () {
            $('#book-select-edit-{{ $img->id }}').select2('destroy');
        });
        @endforeach
    });
</script>
@endpush
