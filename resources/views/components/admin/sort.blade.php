@props(['column', 'sort', 'direction', 'default' => 'asc'])

{{--
    A sortable column header.

    Clicking the current column flips direction; clicking another starts it at
    its own natural direction — dates newest first, names A–Z. aria-sort sits
    on the cell, where it belongs, so the state is announced rather than only
    drawn as an arrow.
--}}

@php
    $isActive = $sort === $column;
    $next = $isActive ? ($direction === 'asc' ? 'desc' : 'asc') : $default;
    $arrow = $isActive ? ($direction === 'asc' ? '↑' : '↓') : '';
@endphp

<th scope="col" @if ($isActive) aria-sort="{{ $direction === 'asc' ? 'ascending' : 'descending' }}" @endif>
    <a
        class="{{ $isActive ? 'is-active' : '' }}"
        data-async-nav
        href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => $next, 'page' => null]) }}"
    >
        {{ $slot }}
        @if ($arrow)<span aria-hidden="true">{{ $arrow }}</span>@endif
        <span class="admin-sr">, sort {{ $next === 'asc' ? 'ascending' : 'descending' }}</span>
    </a>
</th>
