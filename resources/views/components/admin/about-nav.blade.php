@props(['current', 'counts' => []])

{{--
    Local navigation for the About area.

    Five screens, one subject. Rendered on every one of them so the area
    always shows its own shape — a writer should never have to go back to the
    sidebar to remember that the team and the process are the same chapter.
--}}

@php
    $sections = [
        'overview' => ['label' => 'Overview', 'route' => 'admin.about.overview', 'count' => $counts['positions'] ?? null, 'unit' => 'positions'],
        'team' => ['label' => 'Team', 'route' => 'admin.team.index', 'count' => $counts['team'] ?? null, 'unit' => 'people'],
        'process' => ['label' => 'Process', 'route' => 'admin.process.index', 'count' => $counts['process'] ?? null, 'unit' => 'stages'],
        'testimonials' => ['label' => 'Testimonials', 'route' => 'admin.testimonials.index', 'count' => $counts['testimonials'] ?? null, 'unit' => 'quotes'],
        'stats' => ['label' => 'Stats', 'route' => 'admin.about.stats', 'count' => $counts['stats'] ?? null, 'unit' => 'numbers'],
    ];
@endphp

<nav class="admin-subnav" aria-label="About">
    @foreach ($sections as $key => $section)
        <a
            class="admin-subnav__link {{ $current === $key ? 'is-active' : '' }}"
            href="{{ route($section['route']) }}"
            @if ($current === $key) aria-current="page" @endif
        >
            <span>{{ $section['label'] }}</span>
            @if ($section['count'] !== null)
                <small>
                    {{ $section['count'] }}
                    <span class="admin-sr">{{ $section['unit'] }}</span>
                </small>
            @endif
        </a>
    @endforeach
</nav>
