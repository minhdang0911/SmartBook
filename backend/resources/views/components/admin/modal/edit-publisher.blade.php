@props(['publisher'])

<div class="modal fade" id="editPublisherModal{{ $publisher->id }}" tabindex="-1"
     aria-labelledby="editPublisherModalLabel{{ $publisher->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.publishers.update', $publisher->id) }}" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editPublisherModalLabel{{ $publisher->id }}">✏️ Sửa Nhà xuất bản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tên nhà xuất bản</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $publisher->name) }}" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">💾 Cập nhật</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">✖️ Hủy</button>
            </div>
        </form>
    </div>
</div>
