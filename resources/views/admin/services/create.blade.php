@extends('layouts.admin', [
    'title' => 'New service — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'Services', 'url' => route('admin.services.index')],
        ['label' => 'New'],
    ],
])

@section('content')

<x-admin.page-header
    kicker="Content"
    title="New service"
    description="A pillar of what the studio sells. Write the narrative first — the capabilities under it are easier to name once the argument exists."
/>

@include('admin.services.form', [
    'action' => route('admin.services.store'),
    'method' => 'POST',
    'submit' => 'Create service',
])

@endsection
