@props(['name', 'label' => null])

{{--
    One inline sprite rather than an icon package.

    The set is small and closed — every glyph below has a caller — and drawn
    on the same rectilinear grid as the public site's marks: straight strokes,
    square caps, no rounded corners, nothing pictorial. Inline SVG means the
    icons inherit colour and stroke from CSS and cost no request.

    Decorative by default. Pass :label when the icon is the only thing naming
    a control, and it becomes an image with an accessible name instead.
--}}

@php
    $paths = [
        'dashboard' => '<path d="M2 2h5v5H2zM9 2h5v9H9zM2 9h5v5H2z"/>',
        'services' => '<path d="M2 3h12M2 8h12M2 13h8"/>',
        'insights' => '<path d="M3 2h10v12H3zM5.5 5h5M5.5 8h5M5.5 11h3"/>',
        'testimonials' => '<path d="M2 3h12v8H6l-4 3z"/>',
        'team' => '<path d="M6 3.5a2 2 0 1 1 0 4 2 2 0 0 1 0-4zM2 14v-1.5C2 11 3.5 10 6 10s4 1 4 2.5V14M11 4.5a1.6 1.6 0 1 1 0 3.2M14 14v-1.4c0-1.1-.8-1.9-2.2-2.2"/>',
        'studio' => '<path d="M2 14V6l6-4 6 4v8zM6.5 14V9h3v5"/>',
        'stats' => '<path d="M2 14h12M4 14V8M7.3 14V4M10.7 14V10M14 14V6"/>',
        'process' => '<path d="M2 8h12M4 5.5v5M8 3.5v9M12 6.5v3"/>',
        'categories' => '<path d="M2 2h5v5H2zM9 2h5v5H9zM2 9h5v5H2zM9 9h5v5H9z"/>',
        'intake' => '<path d="M3 2h10v12H3zM6 5.5h4M6 8.5h4M6 11.5h2"/>',
        'settings' => '<path d="M2 4.5h12M2 11.5h12M6 2.5v4M10 9.5v4"/>',
        'profile' => '<path d="M8 3a2.4 2.4 0 1 1 0 4.8A2.4 2.4 0 0 1 8 3zM2.5 14v-1.6C2.5 10.5 5 9.5 8 9.5s5.5 1 5.5 2.9V14"/>',
        'users' => '<path d="M6 3.5a2 2 0 1 1 0 4 2 2 0 0 1 0-4zM2 14v-1.5C2 11 3.5 10 6 10s4 1 4 2.5V14M11 4.5a1.6 1.6 0 1 1 0 3.2M14 14v-1.4c0-1.1-.8-1.9-2.2-2.2"/>',
        'plus' => '<path d="M8 3v10M3 8h10"/>',
        'search' => '<path d="M7 2.5a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9zM10.5 10.5 14 14"/>',
        'arrow-right' => '<path d="M3 8h10M9 4l4 4-4 4"/>',
        'external' => '<path d="M9 2h5v5M14 2 7.5 8.5M12 9.5V14H2V4h4.5"/>',
        'check' => '<path d="M3 8.5 6.5 12 13 4.5"/>',
        'alert' => '<path d="M8 2 15 14H1zM8 6.5v3.5M8 11.8v.2"/>',
        'info' => '<path d="M8 2a6 6 0 1 1 0 12A6 6 0 0 1 8 2zM8 7v4M8 4.8v.2"/>',
        'trash' => '<path d="M3 4.5h10M6.5 4.5V2.5h3v2M4.5 4.5V14h7V4.5M6.8 7v4.5M9.2 7v4.5"/>',
        'up' => '<path d="M8 12.5V4M4 8l4-4 4 4"/>',
        'down' => '<path d="M8 3.5V12M4 8l4 4 4-4"/>',
        'close' => '<path d="M4 4l8 8M12 4l-8 8"/>',
        'calendar' => '<path d="M2.5 3.5h11V14h-11zM2.5 6.5h11M5.5 2v3M10.5 2v3"/>',
        'inbox' => '<path d="M2 9.5 4 3h8l2 6.5V14H2zM2 9.5h3.5l.8 1.6h3.4l.8-1.6H14"/>',
        'eye' => '<path d="M1.5 8S4 3.5 8 3.5 14.5 8 14.5 8 12 12.5 8 12.5 1.5 8 1.5 8zM8 6a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/>',
        'eye-off' => '<path d="M2.5 2.5l11 11M6.2 6.3A2 2 0 0 0 8 10a2 2 0 0 0 1.7-1M4.2 4.4C2.5 5.6 1.5 8 1.5 8s2.5 4.5 6.5 4.5c1 0 1.9-.3 2.7-.7M11.4 5A6.9 6.9 0 0 1 14.5 8"/>',
    ];
@endphp

<svg
    viewBox="0 0 16 16"
    fill="none"
    stroke="currentColor"
    stroke-width="1.4"
    stroke-linecap="square"
    stroke-linejoin="miter"
    @if ($label)
        role="img" aria-label="{{ $label }}"
    @else
        aria-hidden="true" focusable="false"
    @endif
    {{ $attributes }}
>{!! $paths[$name] ?? $paths['info'] !!}</svg>
