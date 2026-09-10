@extends('layouts.admin', [
    'title' => $project->name.' — Projects — FORMIVA',
    'breadcrumbs' => [['label' => 'Projects', 'url' => route('admin.projects.index')], ['label' => $project->name]],
])

@section('content')
<x-admin.page-header kicker="Work" :title="$project->name" description="The public project page is assembled from this record through ContentRepository.">
    <x-slot:actions>
        <x-admin.button href="{{ route('admin.projects.preview', $project) }}" variant="ghost" icon="eye">Preview</x-admin.button>
        <x-admin.delete :action="route('admin.projects.destroy', $project)" label="Delete project" variant="danger" :confirm="'“'.$project->name.'” will be removed from Work and the public site.'" />
    </x-slot:actions>
</x-admin.page-header>

<x-admin.publish-bar :record="$project" :action="route('admin.projects.publish', $project)" :transitions="$transitions" label="project" />
@include('admin.projects.form', ['action' => route('admin.projects.update', $project), 'method' => 'PUT', 'submit' => 'Save project'])
@endsection
