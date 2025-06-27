@props([])

<div class="modal fade" id="addPublisherModal" tabindex="-1" aria-labelledby="addPublisherModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.publishers.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addPublisherModalLabel">➕ Thêm Nhà xuất bản mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Tên nhà xuất bản</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">💾 Lưu</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">✖️ Hủy</button>
            </div>
        </form>
    </div>
</div>
