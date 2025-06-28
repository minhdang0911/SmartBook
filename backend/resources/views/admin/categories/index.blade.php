@extends('layouts.app')

@section('title', 'Danh sách Danh mục')

@push('styles')
    <style>
        /* Container */
        .container {
            max-width: 1200px;
            padding: 24px;
        }

        /* Header */
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 32px 24px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Search Form */
        .search-form {
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 500px;
            margin-bottom: 24px;
        }

        .search-form .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 0.95rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .search-form .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .search-form .btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .search-form .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        /* Add New Button */
        .btn-success {
            background: linear-gradient(135deg, #28a745, #34c759);
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }

        /* Table */
        .table {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table thead {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        .table th {
            font-weight: 600;
            color: #333;
            padding: 16px;
            font-size: 0.95rem;
        }

        .table td {
            padding: 16px;
            vertical-align: middle;
            font-size: 0.9rem;
            color: #333;
        }

        .table tr {
            transition: background 0.3s ease;
        }

        .table tr:hover {
            background: #f0f4ff;
        }

        /* Action Buttons */
        .btn-sm {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-warning {
            background: #ffc107;
            border: none;
            color: #333;
        }

        .btn-warning:hover {
            background: #ffca2c;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
        }

        .btn-danger {
            background: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background: #e4606d;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        /* Empty State */
        .empty-state {
            padding: 24px;
            text-align: center;
            color: #666;
            font-size: 1rem;
        }

        .empty-state strong {
            color: #333;
        }

        .empty-state .text-muted {
            font-size: 0.85rem;
            margin-top: 8px;
        }

        /* Pagination */
        .pagination {
            justify-content: center;
            margin-top: 24px;
        }

        .pagination .page-link {
            border-radius: 6px;
            margin: 0 4px;
            color: #667eea;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background: #f0f4ff;
            color: #764ba2;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-color: #667eea;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                padding: 24px;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .search-form {
                flex-direction: column;
                align-items: stretch;
                max-width: 100%;
            }

            .search-form .form-control,
            .search-form .btn {
                width: 100%;
            }

            .table-responsive {
                overflow-x: auto;
            }

            .table th,
            .table td {
                font-size: 0.85rem;
                padding: 12px;
            }

            .btn-sm {
                padding: 5px 10px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .page-header {
                padding: 16px;
            }

            .page-header h1 {
                font-size: 1.2rem;
            }

            .table th,
            .table td {
                font-size: 0.8rem;
                padding: 10px;
            }

            .btn-success {
                padding: 10px 16px;
                font-size: 0.9rem;
            }

            .empty-state {
                font-size: 0.9rem;
            }
        }

        .search-form {
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 100%;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .search-form .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 0.95rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            flex: 1;
            min-width: 200px;
        }

        .search-form .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .search-form .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .search-form .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .search-form .btn-success {
            background: linear-gradient(135deg, #28a745, #34c759);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .search-form .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="page-header">
            <h1><i class="bi bi-tags"></i> Danh sách Danh mục</h1>
        </div>

        {{-- Flash messages --}}
        @include('components.alert')

        {{-- Search Form and Add Button for Category --}}
        <form method="GET" action="{{ route('admin.categories.index') }}"
            class="search-form d-flex align-items-center gap-2 flex-wrap" role="search">
            <input type="text" name="search" class="form-control" placeholder="🔍 Tìm danh mục..."
                value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary">Tìm</button>
            <x-admin.button.modal-button target="addCategoryModal" text="➕ Thêm mới" class="btn-success ms-auto" />
        </form>



        {{-- Table categories --}}
        <div class="table-responsive">
            <x-admin.table :headers="['STT', 'Tên danh mục', 'Hành động']">
                @forelse ($categories as $index => $category)
                    <tr>
                        <td>{{ $categories->firstItem() + $index }}</td>
                        <td>{{ $category->name }}</td>
                        <td>
                            <x-admin.button.modal-button target="editCategoryModal{{ $category->id }}" text="Sửa"
                                class="btn-warning btn-sm" />

                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty-state">
                            😕 Không tìm thấy danh mục nào
                            @if (request('search'))
                                với từ khóa <strong>"{{ request('search') }}"</strong>.
                            @endif
                            <p class="text-muted">Hãy thử tên khác hoặc kiểm tra lại chính tả!</p>
                        </td>
                    </tr>
                @endforelse
            </x-admin.table>
        </div>

        {{-- Pagination --}}
        <div class="pagination">
            {{ $categories->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
        </div>

        {{-- Edit modals --}}
        @foreach ($categories as $category)
            <x-admin.modal.edit-category :category="$category" />
        @endforeach

        {{-- Add modal --}}
        <x-admin.modal.add-category />
    </div>
@endsection
