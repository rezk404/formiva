@extends('layouts.admin', [
    'title' => 'Team — FORMIVA',
    'breadcrumbs' => [['label' => 'About', 'url' => route('admin.about.overview')], ['label' => 'Team']],
])

@section('content')

<x-admin.page-header
    kicker="About"
    title="Team"
    description="The people on /studio, in the order they appear there. Portraits are drawn plates rather than photographs, so a new person looks right before anyone has taken a picture."
>
    <x-slot:actions>
        <x-admin.button href="{{ route('admin.team.create') }}" icon="plus">Add a person</x-admin.button>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.about-nav current="team" :counts="$about" />

<x-admin.tabs
    param="state"
    :current="$filters['state'] ?: 'all'"
    :tabs="[
        'all' => ['label' => 'All', 'count' => $counts['all']],
        'published' => ['label' => 'Listed', 'count' => $counts['published']],
        'hidden' => ['label' => 'Hidden', 'count' => $counts['hidden']],
    ]"
/>

<x-admin.filters
    :active="$active"
    :query="$filters['q']"
    :keep="['state' => $filters['state']]"
    placeholder="Search people"
/>

<div class="admin-panel">
    @if ($members->total() > 0)
        <div class="admin-people">
            @foreach ($members as $member)
                <article class="admin-person">
                    <div class="admin-person__portrait">
                        {{-- A photograph if one is attached and reachable;
                             otherwise the same stacked-band idea the public
                             plates use, so the grid never shows a broken frame. --}}
                        @if ($member->photo?->url())
                            <img src="{{ $member->photo->url() }}" alt="{{ $member->alt ?? $member->photo->alt ?? $member->name }}">
                        @else
                            <span
                                class="admin-person__plate"
                                data-initials="{{ Str::of($member->name)->explode(' ')->take(2)->map(fn ($part) => Str::substr($part, 0, 1))->implode('') }}"
                                aria-hidden="true"
                            ></span>
                        @endif
                    </div>

                    <div>
                        <a class="admin-person__name" href="{{ route('admin.team.edit', $member) }}">{{ $member->name }}</a>
                        <p class="admin-person__role">{{ $member->role }}</p>
                    </div>

                    <div class="admin-person__foot">
                        <x-admin.badge :tone="$member->is_published ? 'positive' : 'neutral'">
                            {{ $member->is_published ? 'Listed' : 'Hidden' }}
                        </x-admin.badge>

                        <div class="admin-person__tools">
                            <x-admin.move :action="route('admin.team.move', $member)" :label="$member->name" />

                            <form method="POST" action="{{ route('admin.team.toggle', $member) }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="admin-btn admin-btn--icon"
                                    aria-label="{{ $member->is_published ? 'Hide' : 'Show' }} {{ $member->name }}"
                                    title="{{ $member->is_published ? 'Hide from /studio' : 'Show on /studio' }}"
                                >
                                    <x-admin.icon :name="$member->is_published ? 'eye-off' : 'eye'" />
                                </button>
                            </form>

                            <x-admin.delete
                                :action="route('admin.team.destroy', $member)"
                                :label="'Remove '.$member->name"
                                title="Remove this person?"
                                :confirm="$member->name.' will be removed from the studio page. The record is soft-deleted and can be restored from the database.'"
                            />
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <x-admin.pagination :paginator="$members" label="people" />
    @elseif (count($active) > 0)
        <x-admin.empty
            icon="search"
            title="Nothing matches that"
            description="No one answers to this search and filter combination."
        >
            <x-slot:actions>
                <x-admin.button href="{{ route('admin.team.index') }}" variant="ghost">Clear filters</x-admin.button>
            </x-slot:actions>
        </x-admin.empty>
    @else
        <x-admin.empty
            icon="team"
            title="No one added yet"
            description="The studio page introduces the people behind the work. Bios read best written without pronouns — short, and about what someone actually does."
        >
            <x-slot:actions>
                <x-admin.button href="{{ route('admin.team.create') }}" icon="plus">Add the first person</x-admin.button>
            </x-slot:actions>
        </x-admin.empty>
    @endif
</div>

@endsection
