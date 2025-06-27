@props(['status'])

<span class="badge {{ $status ? 'bg-success' : 'bg-secondary' }}">
    {{ $status ? '🟢 Đang hoạt động' : '⚪ Chưa kích hoạt' }}
</span>
