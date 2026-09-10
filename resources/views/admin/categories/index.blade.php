@extends('layouts.admin', [
    'title' => 'Categories — FORMIVA',
    'breadcrumbs' => [['label' => 'Categories']],
])

@section('content')

<x-admin.page-header
    kicker="Configuration"
    title="Categories"
    description="The labels that group work on /work and entries on /insights. A category is only a name and an order — what it does is make a set of things findable as a set."
>
    <x-slot:actions>
        <x-admin.button href="{{ route('admin.categories.create', ['type' => $filters['type'] ?: null]) }}" icon="plus">
            New category
        </x-admin.button>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.filters :active="$active" :query="$filters['q']" placeholder="Search categories">
    <x-admin.filter-select
        name="type"
        label="Used for"
        any="Both"
        :value="$filters['type']"
        :options="collect($types)->mapWithKeys(fn ($type) => [$type->value => $type->label()])->all()"
    />
</x-admin.filters>

<div class="admin-panel">
    @if ($categories->total() > 0)
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <x-admin.sort column="name" :sort="$sort" :direction="$direction">Name</x-admin.sort>
                        <th scope="col">Used for</th>
                        <th scope="col">Slug</th>
                        <th scope="col">Attached</th>
                        <x-admin.sort column="order" :sort="$sort" :direction="$direction">Order</x-admin.sort>
                        <x-admin.sort column="updated" :sort="$sort" :direction="$direction" default="desc">Updated</x-admin.sort>
                        <th scope="col"><span class="admin-sr">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td>
                                <a class="admin-table__primary" href="{{ route('admin.categories.edit', $category) }}">
                                    {{ $category->name }}
                                </a>
                            </td>
                            <td data-label="Used for">
                                <x-admin.badge tone="neutral">{{ $category->type->label() }}</x-admin.badge>
                            </td>
                            <td class="admin-table__num" data-label="Slug">/{{ $category->slug }}</td>
                            <td class="admin-table__num" data-label="Attached">
                                @if ($category->isInUse())
                                    {{ $category->projects_count }} project{{ $category->projects_count === 1 ? '' : 's' }},
                                    {{ $category->insights_count }} insight{{ $category->insights_count === 1 ? '' : 's' }}
                                @else
                                    <span class="admin-badge admin-badge--muted admin-badge--plain">Unused</span>
                                @endif
                            </td>
                            <td class="admin-table__num" data-label="Order">{{ $category->position }}</td>
                            <td class="admin-table__num" data-label="Updated">{{ $category->updated_at?->format('j M Y') }}</td>
                            <td data-label="">
                                <div class="admin-table__actions">
                                    <x-admin.move
                                        :action="route('admin.categories.move', $category)"
                                        :label="$category->name"
                                    />
                                    <x-admin.button
                                        href="{{ route('admin.categories.edit', $category) }}"
                                        variant="ghost"
                                        size="sm"
                                    >Edit</x-admin.button>
                                    <x-admin.delete
                                        :action="route('admin.categories.destroy', $category)"
                                        :label="'Delete '.$category->name"
                                        title="Delete this category?"
                                        :confirm="'“'.$category->name.'” will be removed. Anything still using it keeps its content but loses the label.'"
                                    />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$categories" label="categories" />
    @elseif (count($active) > 0)
        <x-admin.empty
            icon="search"
            title="Nothing matches that"
            description="No category answers to this search and filter combination."
        >
            <x-slot:actions>
                <x-admin.button href="{{ route('admin.categories.index') }}" variant="ghost">Clear filters</x-admin.button>
            </x-slot:actions>
        </x-admin.empty>
    @else
        <x-admin.empty
            icon="categories"
            title="No categories yet"
            description="Categories group projects and journal entries. Add one for each family of work the studio wants to be found by."
        >
            <x-slot:actions>
                <x-admin.button href="{{ route('admin.categories.create') }}" icon="plus">New category</x-admin.button>
            </x-slot:actions>
        </x-admin.empty>
    @endif
</div>

@endsection
