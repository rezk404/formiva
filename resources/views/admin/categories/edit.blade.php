@php
    $usage = sprintf(
        'Used by %d project%s and %d insight%s. Renaming is safe. The public site addresses categories by name, so the slug is only an internal handle.',
        $category->projects_count,
        $category->projects_count === 1 ? '' : 's',
        $category->insights_count,
        $category->insights_count === 1 ? '' : 's',
    );
@endphp

@extends('layouts.admin', [
    'title' => $category->name.' — Categories — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'Categories', 'url' => route('admin.categories.index')],
        ['label' => $category->name],
    ],
])

@section('content')

<x-admin.page-header kicker="Configuration" :title="$category->name" :description="$usage">
    <x-slot:actions>
        <x-admin.delete
            :action="route('admin.categories.destroy', $category)"
            variant="danger"
            label="Delete category"
            title="Delete this category?"
            :confirm="'“'.$category->name.'” will be removed. Anything still using it keeps its content but loses the label.'"
        />
    </x-slot:actions>
</x-admin.page-header>

@include('admin.categories.form', [
    'action' => route('admin.categories.update', $category),
    'method' => 'PUT',
    'submit' => 'Save changes',
])

@endsection
