@extends('layouts.admin', [
    'title' => $insight->title.' — Insights — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'Insights', 'url' => route('admin.insights.index')],
        ['label' => $insight->title],
    ],
])

@section('content')

<x-admin.page-header kicker="Content" :title="$insight->title" description="Edit the entry, its classification and how it is published.">
    <x-slot:actions>
        <x-admin.delete
            :action="route('admin.insights.destroy', $insight)"
            variant="danger"
            label="Delete entry"
            title="Delete this entry?"
            :confirm="'“'.$insight->title.'” will be removed from the journal. The record is soft-deleted, so it can be restored from the database.'"
        />
    </x-slot:actions>
</x-admin.page-header>

<x-admin.publish-bar
    :record="$insight"
    :action="route('admin.insights.publish', $insight)"
    :transitions="$transitions"
    label="entry"
/>

@include('admin.insights.form', [
    'action' => route('admin.insights.update', $insight),
    'method' => 'PUT',
    'submit' => 'Save entry',
])

@endsection
