@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">🏢 Danh sách Nhà xuất bản</h1>

        {{-- Flash messages --}}
        @include('components.alert')

        {{-- Search Form --}}
        <form method="GET" action="{{ route('admin.publishers.index') }}" class="mb-3 d-flex" role="search">
            <input type="text" name="search" class="form-control me-2" placeholder="🔍 Tìm nhà xuất bản..."
                   value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-primary">Tìm</button>
        </form>

        {{-- Add new button --}}
        <x-admin.button.modal-button target="addPublisherModal" text="➕ Thêm mới" class="btn-success mb-3" />

        {{-- Table publishers --}}
        <x-admin.table :headers="['STT', 'Tên nhà xuất bản', 'Hành động']">
            @forelse ($publishers as $index => $publisher)
                <tr>
                    <td>{{ $publishers->firstItem() + $index }}</td>
                    <td>{{ $publisher->name }}</td>
                    <td>
                        <x-admin.button.modal-button
                            target="editPublisherModal{{ $publisher->id }}"
                            text="Sửa"
                            class="btn-warning btn-sm" />

                        <form action="{{ route('admin.publishers.destroy', $publisher) }}" method="POST" class="d-inline"
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
                        😕 Không tìm thấy nhà xuất bản nào
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
            {{ $publishers->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
        </div>

        {{-- Modal chỉnh sửa nhà xuất bản --}}
        @foreach ($publishers as $publisher)
            <x-admin.modal.edit-publisher :publisher="$publisher" />
        @endforeach

        {{-- Modal thêm nhà xuất bản --}}
        <x-admin.modal.add-publisher />
    </div>
@endsection
