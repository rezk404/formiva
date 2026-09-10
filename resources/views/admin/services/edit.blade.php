@extends('layouts.admin', [
    'title' => $service->title.' — Services — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'Services', 'url' => route('admin.services.index')],
        ['label' => $service->title],
    ],
])

@section('content')

<x-admin.page-header kicker="Content" :title="$service->title" description="Edit the pillar, its narrative and the capabilities nested inside it.">
    <x-slot:actions>
        <x-admin.delete
            :action="route('admin.services.destroy', $service)"
            variant="danger"
            label="Delete service"
            title="Delete this service?"
            :confirm="'“'.$service->title.'” will be removed from the site. The record is soft-deleted, so it can be restored from the database.'"
        />
    </x-slot:actions>
</x-admin.page-header>

{{-- Above the editor and outside it: these are forms of their own. --}}
<x-admin.publish-bar
    :record="$service"
    :action="route('admin.services.publish', $service)"
    :transitions="$transitions"
    label="service"
/>

@include('admin.services.form', [
    'action' => route('admin.services.update', $service),
    'method' => 'PUT',
    'submit' => 'Save service',
])

@endsection
