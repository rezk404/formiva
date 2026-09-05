@props([
    'seed' => 0,
    'variant' => 'ink',
    'ratio' => '4/3',
    'alt' => '',
    'caption' => null,
    'framed' => false,
    'decorative' => false,
])

@php
    $plate = \App\Support\Plate::compose((int) $seed, $variant, $ratio);
@endphp

{{--
    A plate is drawn, never photographed.

    Every composition is the studio's slab system flattened to two dimensions
    and seeded from the content file, so a project's artwork is stable across
    deploys, weighs a couple of kilobytes, and is sharp at any size.

    Note there is no per-plate noise filter. A dozen feTurbulence passes over
    large areas is real GPU cost for texture the single fixed grain layer
    already supplies.

    When real photography replaces these, only this file changes.
--}}

<figure
    {{ $attributes->merge([
        'class' => 'plate' . ($framed ? ' plate--framed' : ''),
    ]) }}
    data-ratio="{{ $ratio }}"
>
    <span class="plate__inner">
        <svg
            viewBox="0 0 {{ $plate['width'] }} {{ $plate['height'] }}"
            preserveAspectRatio="xMidYMid slice"
            xmlns="http://www.w3.org/2000/svg"
            @if ($decorative || $alt === '')
                aria-hidden="true" focusable="false"
            @else
                role="img" aria-label="{{ $alt }}"
            @endif
        >
            <defs>
                <linearGradient id="{{ $plate['uid'] }}-depth" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="{{ $plate['field'] }}" stop-opacity="0" />
                    <stop offset="100%" stop-color="{{ $plate['field'] }}" stop-opacity="0.55" />
                </linearGradient>
            </defs>

            <rect width="{{ $plate['width'] }}" height="{{ $plate['height'] }}" fill="{{ $plate['field'] }}" />

            @foreach ($plate['slabs'] as $slab)
                <rect
                    x="{{ $slab['x'] }}"
                    y="{{ $slab['y'] }}"
                    width="{{ $slab['w'] }}"
                    height="{{ $slab['h'] }}"
                    rx="{{ $slab['r'] }}"
                    fill="{{ $slab['fill'] }}"
                    opacity="{{ $slab['opacity'] }}"
                />
            @endforeach

            {{-- Measurement ticks: the architectural detail that says the
                 image was drawn, and the only element running against the
                 horizontal grain of the stack. --}}
            @foreach ($plate['ticks'] as $tick)
                <rect
                    x="{{ $tick['x'] }}"
                    y="{{ $tick['y'] }}"
                    width="{{ $tick['w'] }}"
                    height="1.5"
                    fill="{{ $plate['rule'] }}"
                    opacity="0.5"
                />
            @endforeach

            <rect
                width="{{ $plate['width'] }}"
                height="{{ $plate['height'] }}"
                fill="url(#{{ $plate['uid'] }}-depth)"
            />
        </svg>
    </span>

    @if ($caption)
        <figcaption class="plate__caption mono">{{ $caption }}</figcaption>
    @endif
</figure>
