@props(['tabs' => [], 'current' => '', 'param' => 'status'])

{{--
    Status shortcuts above a list. A row of links, not a control group — each
    one is a URL you can send to someone.
--}}

<nav class="admin-tabs" aria-label="Filter by state">
    @foreach ($tabs as $value => $tab)
        <a
            class="admin-tab {{ (string) $current === (string) $value ? 'is-active' : '' }}"
            href="{{ request()->fullUrlWithQuery([$param => $value === 'all' ? null : $value, 'page' => null]) }}"
            @if ((string) $current === (string) $value) aria-current="page" @endif
        >
            {{ $tab['label'] }}
            <small>{{ $tab['count'] }}</small>
        </a>
    @endforeach
</nav>
