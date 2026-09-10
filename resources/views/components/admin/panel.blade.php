@props([
    'title' => null,
    'description' => null,
    'kicker' => null,
    'flush' => false,
])

<section {{ $attributes->class('admin-panel') }}>
    @if ($title || isset($actions))
        <header class="admin-panel__head">
            <div>
                @if ($kicker)<p class="admin-section-label">{{ $kicker }}</p>@endif
                @if ($title)<h2>{{ $title }}</h2>@endif
                @if ($description)<p>{{ $description }}</p>@endif
            </div>
            @isset($actions)
                <div class="admin-page-head__actions">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    <div class="admin-panel__body{{ $flush ? ' admin-panel__body--flush' : '' }}">{{ $slot }}</div>

    @isset($footer)
        <footer class="admin-panel__foot">{{ $footer }}</footer>
    @endisset
</section>
