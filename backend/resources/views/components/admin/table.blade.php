@props(['headers'])

<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            @foreach ($headers as $header)
                <th>{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        {{ $slot }}
    </tbody>
</table>
