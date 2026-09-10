@props(['active' => [], 'action' => null, 'placeholder' => 'Search', 'query' => '', 'keep' => []])

{{--
    Search and filters as one GET form.

    Server-rendered, bookmarkable, and shareable: the state of a list lives in
    the query string, which is also what keeps pagination and sorting agreeing
    with each other. "Clear" is a link to the bare URL rather than a reset
    button, so it survives a back-button too.
--}}

<form method="GET" action="{{ $action ?? request()->url() }}" class="admin-filters" role="search" data-async-filter>
    <button type="button" class="admin-filter-toggle" data-filter-toggle aria-expanded="false">
        <span>Filters</span>
        @if (count($active) > 0)<b>{{ count($active) }}</b>@endif
    </button>
    {{-- Filters chosen elsewhere on the screen — the state tabs above — ride
         along so searching does not silently discard them. --}}
    @foreach ($keep as $name => $value)
        @if ($value !== null && $value !== '')
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="admin-filters__field admin-filters__field--grow">
        <label for="filter-q">{{ $placeholder }}</label>
        <input
            id="filter-q"
            type="search"
            name="q"
            value="{{ $query }}"
            class="admin-input"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
        >
    </div>

    {{ $slot }}

    <div class="admin-filters__actions">
        <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm">
            <x-admin.icon name="search" /> Apply
        </button>

        @if (count($active) > 0)
            <a class="admin-btn admin-btn--quiet admin-btn--sm" href="{{ $action ?? request()->url() }}">
                <x-admin.icon name="close" /> Clear
            </a>
        @endif
    </div>
</form>
