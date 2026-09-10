@props(['title', 'kicker' => null, 'description' => null])

<header class="admin-page-head">
    <div class="admin-page-head__text">
        @if ($kicker)<p class="admin-kicker">{{ $kicker }}</p>@endif
        <h1>{{ $title }}</h1>
        @if ($description)<p>{{ $description }}</p>@endif
    </div>

    @isset($actions)
        <div class="admin-page-head__actions">{{ $actions }}</div>
    @endisset
</header>
