@extends('layouts.admin', [
    'title' => 'Services — FORMIVA',
    'breadcrumbs' => [['label' => 'Services']],
])

@section('content')

<x-admin.page-header
    kicker="Content"
    title="Services"
    description="The capability index — three pillars, not a list of disciplines. Order here is the order a visitor meets them on /services and on the homepage."
>
    <x-slot:actions>
        <x-admin.button href="{{ route('admin.services.create') }}" icon="plus">New service</x-admin.button>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.tabs
    :current="$filters['status'] ?: 'all'"
    :tabs="[
        'all' => ['label' => 'All', 'count' => $counts['all']],
        'published' => ['label' => 'Published', 'count' => $counts['published']],
        'scheduled' => ['label' => 'Scheduled', 'count' => $counts['scheduled']],
        'draft' => ['label' => 'Draft', 'count' => $counts['draft']],
        'archived' => ['label' => 'Archived', 'count' => $counts['archived']],
    ]"
/>

<x-admin.filters
    :active="$active"
    :query="$filters['q']"
    :keep="['status' => $filters['status']]"
    placeholder="Search services"
/>

<div class="admin-panel">
    @if ($services->total() > 0)
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <x-admin.sort column="title" :sort="$sort" :direction="$direction">Service</x-admin.sort>
                        <x-admin.sort column="status" :sort="$sort" :direction="$direction">Status</x-admin.sort>
                        <th scope="col">Capabilities</th>
                        <x-admin.sort column="order" :sort="$sort" :direction="$direction">Position</x-admin.sort>
                        <x-admin.sort column="updated" :sort="$sort" :direction="$direction" default="desc">Updated</x-admin.sort>
                        <th scope="col"><span class="admin-sr">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($services as $service)
                        <tr>
                            <td>
                                <a class="admin-table__primary" href="{{ route('admin.services.edit', $service) }}">
                                    {{ $service->title }}
                                </a>
                                <span class="admin-table__secondary">{{ $service->index_label }} · /{{ $service->slug }}</span>
                            </td>
                            <td data-label="Status"><x-admin.status :status="$service->status" :at="$service->published_at" /></td>
                            <td class="admin-table__num" data-label="Capabilities">
                                {{ $service->items_count }}
                                @if ($service->items_count === 0)
                                    <span class="admin-badge admin-badge--warning admin-badge--plain">Empty</span>
                                @endif
                            </td>
                            <td class="admin-table__num" data-label="Position">{{ $service->position }}</td>
                            <td class="admin-table__num" data-label="Updated">{{ $service->updated_at?->format('j M Y') }}</td>
                            <td data-label="">
                                <div class="admin-table__actions">
                                    <x-admin.move :action="route('admin.services.move', $service)" :label="$service->title" />
                                    <x-admin.button href="{{ route('admin.services.edit', $service) }}" variant="ghost" size="sm">Edit</x-admin.button>
                                    <x-admin.delete
                                        :action="route('admin.services.destroy', $service)"
                                        :label="'Delete '.$service->title"
                                        title="Delete this service?"
                                        :confirm="'“'.$service->title.'” and its '.$service->items_count.' capability row(s) will be removed from the site. The record is soft-deleted and can be restored from the database.'"
                                    />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$services" label="services" />
    @elseif (count($active) > 0)
        <x-admin.empty
            icon="search"
            title="Nothing matches that"
            description="No service answers to this search and filter combination."
        >
            <x-slot:actions>
                <x-admin.button href="{{ route('admin.services.index') }}" variant="ghost">Clear filters</x-admin.button>
            </x-slot:actions>
        </x-admin.empty>
    @else
        <x-admin.empty
            icon="services"
            title="No services yet"
            description="A service is a pillar of what the studio sells, with its capabilities nested inside it. The public services page has nothing to show until there is one."
        >
            <x-slot:actions>
                <x-admin.button href="{{ route('admin.services.create') }}" icon="plus">New service</x-admin.button>
            </x-slot:actions>
        </x-admin.empty>
    @endif
</div>

@endsection
