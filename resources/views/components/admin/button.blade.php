@props([
    'variant' => 'solid',
    'size' => null,
    'href' => null,
    'icon' => null,
    'type' => 'submit',
    'block' => false,
])

@php
    $classes = collect(['admin-btn'])
        ->when($variant !== 'solid', fn ($classes) => $classes->push('admin-btn--'.$variant))
        ->when($size, fn ($classes) => $classes->push('admin-btn--'.$size))
        ->when($block, fn ($classes) => $classes->push('admin-btn--block'))
        ->implode(' ');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<x-admin.icon :name="$icon" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($icon)<x-admin.icon :name="$icon" />@endif
        {{ $slot }}
    </button>
@endif
