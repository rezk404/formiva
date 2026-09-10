@props(['tone' => 'info', 'title' => null])

@php
    $icon = match ($tone) {
        'success' => 'check',
        'error' => 'alert',
        default => 'info',
    };
@endphp

<div
    {{ $attributes->class(['admin-alert', 'admin-alert--'.$tone]) }}
    role="{{ $tone === 'error' ? 'alert' : 'status' }}"
>
    <x-admin.icon :name="$icon" />
    <div>
        @if ($title)<b>{{ $title }}</b>@endif
        {{ $slot }}
    </div>
</div>
