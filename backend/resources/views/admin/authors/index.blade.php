@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">📚 Danh sách Tác giả</h1>

        {{-- Flash messages & validation --}}
        @include('components.alert')

        {{-- Search Form --}}
        <form method="GET" action="{{ route('admin.authors.index') }}" class="mb-3 d-flex" role="search">
            <input type="text" name="search" class="form-control me-2" placeholder="🔍 Tìm tác giả..."
                   value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-primary">Tìm</button>
        </form>

        {{-- Add New Button --}}
        <x-admin.button.modal-button target="addAuthorModal" text="➕ Thêm mới" class="btn-success mb-3" />

        {{-- Table Authors --}}
        <x-admin.table :headers="['STT', 'Tên tác giả', 'Hành động']">
            @forelse ($authors as $index => $author)
                <tr>
                    <td>{{ $authors->firstItem() + $index }}</td>
                    <td>{{ $author->name }}</td>
                    <td>
                        <x-admin.button.modal-button
                            target="editAuthorModal{{ $author->id }}"
                            text="Sửa"
                            class="btn-warning btn-sm" />

                        <form action="{{ route('admin.authors.destroy', $author) }}" method="POST" class="d-inline"
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
                        😕 Không tìm thấy tác giả nào
                        @if(request('search'))
                            với từ khóa <strong>"{{ request('search') }}"</strong>.
                        @endif
                        <p class="text-muted small mt-1">Hãy thử tìm với tên khác hoặc kiểm tra lại chính tả nha!</p>
                    </td>
                </tr>
            @endforelse
        </x-admin.table>

        {{-- Pagination --}}
        <div class="mt-4 text-center">
            {{ $authors->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
        </div>

        {{-- Edit Modals --}}
        @foreach ($authors as $author)
            <x-admin.modal.edit-author :author="$author" />
        @endforeach

        {{-- Add Modal --}}
        <x-admin.modal.add-author />
    </div>
@endsection
