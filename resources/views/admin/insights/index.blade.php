@extends('layouts.admin', [
    'title' => 'Insights — FORMIVA',
    'breadcrumbs' => [['label' => 'Insights']],
])

@section('content')

<x-admin.page-header
    kicker="Content"
    title="Insights"
    description="The studio journal. Entries are published in reverse chronology, so the publish date is also the running order."
>
    <x-slot:actions>
        <x-admin.button href="{{ route('admin.insights.create') }}" icon="plus">Write an insight</x-admin.button>
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
    placeholder="Search insights"
>
    <x-admin.filter-select
        name="category"
        label="Category"
        any="Any category"
        :value="$filters['category']"
        :options="$categories->pluck('name', 'id')->all()"
    />
    <x-admin.filter-select
        name="author"
        label="Author"
        any="Anyone"
        :value="$filters['author']"
        :options="$authors->pluck('name', 'id')->all()"
    />
</x-admin.filters>

<div class="admin-panel">
    @if ($insights->total() > 0)
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <x-admin.sort column="title" :sort="$sort" :direction="$direction">Entry</x-admin.sort>
                        <th scope="col">Category</th>
                        <th scope="col">Author</th>
                        <x-admin.sort column="status" :sort="$sort" :direction="$direction">Status</x-admin.sort>
                        <x-admin.sort column="published" :sort="$sort" :direction="$direction" default="desc">Published</x-admin.sort>
                        <x-admin.sort column="updated" :sort="$sort" :direction="$direction" default="desc">Updated</x-admin.sort>
                        <th scope="col"><span class="admin-sr">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($insights as $insight)
                        <tr>
                            <td>
                                <a class="admin-table__primary" href="{{ route('admin.insights.edit', $insight) }}">
                                    {{ $insight->title }}
                                </a>
                                <span class="admin-table__secondary">{{ $insight->index_label }} · {{ $insight->reading_minutes }} min · /{{ $insight->slug }}</span>
                            </td>
                            <td data-label="Category">
                                @if ($insight->category)
                                    <x-admin.badge tone="neutral">{{ $insight->category->name }}</x-admin.badge>
                                @else
                                    <span class="admin-badge admin-badge--warning">None</span>
                                @endif
                            </td>
                            <td class="admin-table__num" data-label="Author">{{ $insight->author?->name ?? '—' }}</td>
                            <td data-label="Status"><x-admin.status :status="$insight->status" :at="$insight->published_at" /></td>
                            <td class="admin-table__num" data-label="Published">{{ $insight->published_at?->format('j M Y') ?? '—' }}</td>
                            <td class="admin-table__num" data-label="Updated">{{ $insight->updated_at?->format('j M Y') }}</td>
                            <td data-label="">
                                <div class="admin-table__actions">
                                    <x-admin.button href="{{ route('admin.insights.edit', $insight) }}" variant="ghost" size="sm">Edit</x-admin.button>
                                    <x-admin.delete
                                        :action="route('admin.insights.destroy', $insight)"
                                        :label="'Delete '.$insight->title"
                                        title="Delete this entry?"
                                        :confirm="'“'.$insight->title.'” will be removed from the journal. The record is soft-deleted and can be restored from the database.'"
                                    />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$insights" label="entries" />
    @elseif (count($active) > 0)
        <x-admin.empty
            icon="search"
            title="Nothing matches that"
            description="No entry answers to this search and filter combination."
        >
            <x-slot:actions>
                <x-admin.button href="{{ route('admin.insights.index') }}" variant="ghost">Clear filters</x-admin.button>
            </x-slot:actions>
        </x-admin.empty>
    @else
        <x-admin.empty
            icon="insights"
            title="The journal is empty"
            description="Insights are the studio thinking in public — a position, argued, with something at stake. The first one sets the tone for the rest."
        >
            <x-slot:actions>
                <x-admin.button href="{{ route('admin.insights.create') }}" icon="plus">Write the first entry</x-admin.button>
            </x-slot:actions>
        </x-admin.empty>
    @endif
</div>

@endsection
