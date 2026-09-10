@props(['title', 'icon' => 'inbox', 'description' => null])

{{--
    An empty area is a designed state, not an accident. It says what would be
    here, and offers the one action that would put something in it.
--}}

<div {{ $attributes->class('admin-empty') }}>
    <span class="admin-empty__mark"><x-admin.icon :name="$icon" /></span>
    <h3>{{ $title }}</h3>
    @if ($description)<p>{{ $description }}</p>@endif
    @isset($actions)
        <div class="admin-page-head__actions">{{ $actions }}</div>
    @endisset
</div>
