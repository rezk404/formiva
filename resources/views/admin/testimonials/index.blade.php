@extends('layouts.admin', [
    'title' => 'Testimonials — FORMIVA',
    'breadcrumbs' => [['label' => 'About', 'url' => route('admin.about.overview')], ['label' => 'Testimonials']],
])

@section('content')

<x-admin.page-header
    kicker="About"
    title="Testimonials"
    description="Proof, in the client's own words. The homepage shows the first published quote in this order, so what sits at the top is the studio's opening argument."
>
    <x-slot:actions>
        <x-admin.button href="{{ route('admin.testimonials.create') }}" icon="plus">Add a quote</x-admin.button>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.about-nav current="testimonials" :counts="$about" />

<x-admin.tabs
    param="state"
    :current="$filters['state'] ?: 'all'"
    :tabs="[
        'all' => ['label' => 'All', 'count' => $counts['all']],
        'published' => ['label' => 'Live', 'count' => $counts['published']],
        'hidden' => ['label' => 'Hidden', 'count' => $counts['hidden']],
    ]"
/>

<x-admin.filters
    :active="$active"
    :query="$filters['q']"
    :keep="['state' => $filters['state']]"
    placeholder="Search quotes, names, companies"
/>

<div class="admin-panel">
    @if ($testimonials->total() > 0)
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">Quote</th>
                        <x-admin.sort column="author" :sort="$sort" :direction="$direction">Attribution</x-admin.sort>
                        <th scope="col">Linked to</th>
                        <th scope="col">State</th>
                        <x-admin.sort column="order" :sort="$sort" :direction="$direction">Order</x-admin.sort>
                        <th scope="col"><span class="admin-sr">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($testimonials as $testimonial)
                        <tr>
                            <td>
                                <a class="admin-table__primary" href="{{ route('admin.testimonials.edit', $testimonial) }}">
                                    {{ Str::limit(strip_tags($testimonial->quote), 90) }}
                                </a>
                            </td>
                            <td data-label="Attribution">
                                <span class="admin-table__primary">{{ $testimonial->author_name }}</span>
                                <span class="admin-table__secondary">{{ $testimonial->author_role }} · {{ $testimonial->company }}</span>
                            </td>
                            <td class="admin-table__num" data-label="Linked to">
                                {{ $testimonial->project?->name ?? $testimonial->client?->name ?? '—' }}
                            </td>
                            <td data-label="State">
                                <x-admin.badge :tone="$testimonial->is_published ? 'positive' : 'neutral'">
                                    {{ $testimonial->is_published ? 'Live' : 'Hidden' }}
                                </x-admin.badge>
                            </td>
                            <td class="admin-table__num" data-label="Order">{{ $testimonial->position }}</td>
                            <td data-label="">
                                <div class="admin-table__actions">
                                    <x-admin.move :action="route('admin.testimonials.move', $testimonial)" :label="'quote from '.$testimonial->author_name" />

                                    <form method="POST" action="{{ route('admin.testimonials.toggle', $testimonial) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="admin-btn admin-btn--icon"
                                            aria-label="{{ $testimonial->is_published ? 'Hide' : 'Publish' }} quote from {{ $testimonial->author_name }}"
                                            title="{{ $testimonial->is_published ? 'Hide from the site' : 'Show on the site' }}"
                                        >
                                            <x-admin.icon :name="$testimonial->is_published ? 'eye-off' : 'eye'" />
                                        </button>
                                    </form>

                                    <x-admin.button href="{{ route('admin.testimonials.edit', $testimonial) }}" variant="ghost" size="sm">Edit</x-admin.button>

                                    <x-admin.delete
                                        :action="route('admin.testimonials.destroy', $testimonial)"
                                        :label="'Delete quote from '.$testimonial->author_name"
                                        title="Delete this quote?"
                                        :confirm="'The quote attributed to '.$testimonial->author_name.' will be removed from the site. The record is soft-deleted and can be restored from the database.'"
                                    />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$testimonials" label="quotes" />
    @elseif (count($active) > 0)
        <x-admin.empty
            icon="search"
            title="Nothing matches that"
            description="No quote answers to this search and filter combination."
        >
            <x-slot:actions>
                <x-admin.button href="{{ route('admin.testimonials.index') }}" variant="ghost">Clear filters</x-admin.button>
            </x-slot:actions>
        </x-admin.empty>
    @else
        <x-admin.empty
            icon="testimonials"
            title="No quotes yet"
            description="One specific sentence from a client does more than a page of claims. Add the first when a project ends well."
        >
            <x-slot:actions>
                <x-admin.button href="{{ route('admin.testimonials.create') }}" icon="plus">Add a quote</x-admin.button>
            </x-slot:actions>
        </x-admin.empty>
    @endif
</div>

@endsection
