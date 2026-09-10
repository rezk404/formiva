@extends('layouts.admin', [
    'title' => $stage->title.' — Process — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'About', 'url' => route('admin.about.overview')],
        ['label' => 'Process', 'url' => route('admin.process.index')],
        ['label' => $stage->title],
    ],
])

@section('content')

<x-admin.page-header kicker="About" :title="$stage->title" :description="$stage->window.' · '.$stage->span_start.'% to '.$stage->span_end.'% of the engagement.'">
    <x-slot:actions>
        <x-admin.delete
            :action="route('admin.process.destroy', $stage)"
            variant="danger"
            label="Remove stage"
            title="Remove this stage?"
            :confirm="'“'.$stage->title.'” will be removed from the sequence and the public page redrawn without it.'"
        />
    </x-slot:actions>
</x-admin.page-header>

@include('admin.process.form', [
    'action' => route('admin.process.update', $stage),
    'method' => 'PUT',
    'submit' => 'Save stage',
])

@endsection
