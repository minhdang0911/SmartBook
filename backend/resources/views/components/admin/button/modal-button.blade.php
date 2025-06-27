@props(['target', 'text', 'class' => 'btn-primary'])
<button type="button" class="btn {{ $class }}" data-bs-toggle="modal" data-bs-target="#{{ $target }}">
    {{ $text }}
</button>
