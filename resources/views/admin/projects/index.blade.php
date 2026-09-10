@extends('layouts.admin', [
    'title' => 'Projects — FORMIVA',
    'breadcrumbs' => [['label' => 'Projects']],
])

@section('content')

<x-admin.page-header
    kicker="Work"
    title="Projects"
    description="The studio's body of work. Featured projects appear on the homepage; published projects appear on /work."
>
    <x-slot:actions>
        <x-admin.button href="{{ route('admin.projects.create') }}" icon="plus">New project</x-admin.button>
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
    placeholder="Search projects"
>
    <x-admin.filter-select name="stage" label="Stage" any="Any stage" :value="$filters['stage']" :options="collect(\App\Enums\ProjectStage::cases())->mapWithKeys(fn ($stage) => [$stage->value => str_replace('_', ' ', ucfirst($stage->value))])->all()" />
    <x-admin.filter-select name="category" label="Category" any="Any category" :value="$filters['category']" :options="$categories->pluck('name', 'id')->all()" />
    <x-admin.filter-select name="client" label="Client" any="Any client" :value="$filters['client']" :options="$clients->pluck('name', 'id')->all()" />
    <x-admin.filter-select name="featured" label="Homepage" any="All projects" :value="$filters['featured']" :options="['yes' => 'Featured', 'no' => 'Not featured']" />
</x-admin.filters>

<div class="admin-panel">
    @if ($projects->total() > 0)
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <x-admin.sort column="name" :sort="$sort" :direction="$direction">Project</x-admin.sort>
                        <th scope="col">Client / category</th>
                        <x-admin.sort column="status" :sort="$sort" :direction="$direction">Status</x-admin.sort>
                        <th scope="col">Stage</th>
                        <x-admin.sort column="year" :sort="$sort" :direction="$direction">Year</x-admin.sort>
                        <x-admin.sort column="updated" :sort="$sort" :direction="$direction" default="desc">Updated</x-admin.sort>
                        <th scope="col"><span class="admin-sr">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr>
                            <td>
                                <a class="admin-table__primary" href="{{ route('admin.projects.edit', $project) }}">{{ $project->name }}</a>
                                <span class="admin-table__secondary">{{ $project->index_label }} · /{{ $project->slug }}</span>
                            </td>
                            <td data-label="Client / category">
                                <span class="admin-table__primary">{{ $project->client?->name ?? 'Unassigned' }}</span>
                                <span class="admin-table__secondary">{{ $project->category?->name ?? 'Uncategorised' }}</span>
                            </td>
                            <td data-label="Status">
                                <x-admin.status :status="$project->status" :at="$project->published_at" />
                                @if ($project->is_featured)
                                    <span class="admin-badge admin-badge--positive admin-badge--plain">Homepage</span>
                                @endif
                            </td>
                            <td data-label="Stage">{{ str_replace('_', ' ', ucfirst($project->stage->value)) }}</td>
                            <td class="admin-table__num" data-label="Year">{{ $project->year }}</td>
                            <td class="admin-table__num" data-label="Updated">{{ $project->updated_at?->format('j M Y') }}</td>
                            <td data-label="">
                                <div class="admin-table__actions">
                                    <x-admin.move :action="route('admin.projects.move', $project)" :label="$project->name" />
                                    <x-admin.button href="{{ route('admin.projects.preview', $project) }}" variant="ghost" size="sm" icon="eye">Preview</x-admin.button>
                                    <x-admin.button href="{{ route('admin.projects.edit', $project) }}" variant="ghost" size="sm">Edit</x-admin.button>
                                    <form method="POST" action="{{ route('admin.projects.featured', $project) }}">
                                        @csrf
                                        <button type="submit" class="admin-btn admin-btn--icon" aria-label="{{ $project->is_featured ? 'Remove from homepage' : 'Feature on homepage' }}" title="{{ $project->is_featured ? 'Remove from homepage' : 'Feature on homepage' }}"><x-admin.icon name="{{ $project->is_featured ? 'eye-off' : 'eye' }}" /></button>
                                    </form>
                                    <x-admin.delete :action="route('admin.projects.destroy', $project)" :label="'Delete '.$project->name" :confirm="'“'.$project->name.'” will be removed from Work and the public site.'" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-admin.pagination :paginator="$projects" label="projects" />
    @elseif (count($active) > 0)
        <x-admin.empty icon="search" title="Nothing matches that" description="No project answers to this search and filter combination.">
            <x-slot:actions><x-admin.button href="{{ route('admin.projects.index') }}" variant="ghost">Clear filters</x-admin.button></x-slot:actions>
        </x-admin.empty>
    @else
        <x-admin.empty icon="projects" title="No projects yet" description="The Work section is empty. Add the first project to give the studio's public work a shape.">
            <x-slot:actions><x-admin.button href="{{ route('admin.projects.create') }}" icon="plus">New project</x-admin.button></x-slot:actions>
        </x-admin.empty>
    @endif
</div>

@endsection
