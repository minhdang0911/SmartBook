@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">📂 Danh sách Danh mục</h1>

        {{-- Flash messages --}}
        @include('components.alert')

        {{-- Search Form --}}
        <form method="GET" action="{{ route('admin.categories.index') }}" class="mb-3 d-flex" role="search">
            <input type="text" name="search" class="form-control me-2" placeholder="🔍 Tìm danh mục..."
                   value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-primary">Tìm</button>
        </form>

        {{-- Add new button --}}
        <x-admin.button.modal-button target="addCategoryModal" text="➕ Thêm mới" class="btn-success mb-3" />

        {{-- Table categories --}}
        <x-admin.table :headers="['STT', 'Tên danh mục', 'Hành động']">
            @forelse ($categories as $index => $category)
                <tr>
                    <td>{{ $categories->firstItem() + $index }}</td>
                    <td>{{ $category->name }}</td>
                    <td>
                        <x-admin.button.modal-button
                            target="editCategoryModal{{ $category->id }}"
                            text="Sửa"
                            class="btn-warning btn-sm" />

                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Bạn chắc chắn muốn xóa?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">
                        😕 Không tìm thấy danh mục nào
                        @if(request('search'))
                            với từ khóa <strong>"{{ request('search') }}"</strong>.
                        @endif
                        <p class="text-muted small mt-1">Hãy thử tên khác hoặc kiểm tra lại chính tả nha!</p>
                    </td>
                </tr>
            @endforelse
        </x-admin.table>

        {{-- Pagination --}}
        <div class="mt-4 text-center">
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
