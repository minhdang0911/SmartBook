@props(['author'])

<div class="modal fade" id="editAuthorModal{{ $author->id }}" tabindex="-1"
     aria-labelledby="editAuthorModalLabel{{ $author->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.authors.update', $author->id) }}" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editAuthorModalLabel{{ $author->id }}">✏️ Sửa Tác giả</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tên tác giả</label>
                    <input type="text" name="name" class="form-control" value="{{ e($author->name) }}" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">💾 Cập nhật</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">✖️ Hủy</button>
            </div>
        </form>
    </div>
</div>
