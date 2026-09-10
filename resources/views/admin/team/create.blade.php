@extends('layouts.admin', [
    'title' => 'Add a person — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'About', 'url' => route('admin.about.overview')],
        ['label' => 'Team', 'url' => route('admin.team.index')],
        ['label' => 'New'],
    ],
])

@section('content')

<x-admin.page-header
    kicker="About"
    title="Add a person"
    description="A short bio about what someone actually does beats a long one about what they care about."
/>

@include('admin.team.form', [
    'action' => route('admin.team.store'),
    'method' => 'POST',
    'submit' => 'Add to the studio',
])

@endsection
