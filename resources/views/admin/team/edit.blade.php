@extends('layouts.admin', [
    'title' => $member->name.' — Team — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'About', 'url' => route('admin.about.overview')],
        ['label' => 'Team', 'url' => route('admin.team.index')],
        ['label' => $member->name],
    ],
])

@section('content')

<x-admin.page-header
    kicker="About"
    :title="$member->name"
    :description="$member->role.' · at the studio since '.$member->since_year.'.'"
>
    <x-slot:actions>
        <x-admin.delete
            :action="route('admin.team.destroy', $member)"
            variant="danger"
            label="Remove person"
            title="Remove this person?"
            :confirm="$member->name.' will be removed from the studio page. The record is soft-deleted, so it can be restored from the database.'"
        />
    </x-slot:actions>
</x-admin.page-header>

@include('admin.team.form', [
    'action' => route('admin.team.update', $member),
    'method' => 'PUT',
    'submit' => 'Save person',
])

@endsection
