@extends('layouts.admin', [
    'title' => 'New stage — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'About', 'url' => route('admin.about.overview')],
        ['label' => 'Process', 'url' => route('admin.process.index')],
        ['label' => 'New stage'],
    ],
])

@section('content')

<x-admin.page-header
    kicker="About"
    title="Add a stage"
    description="A stage earns its place if a client can tell when it has finished. If the output is a document about the work rather than the work, it is not a stage yet."
/>

@include('admin.process.form', [
    'action' => route('admin.process.store'),
    'method' => 'POST',
    'submit' => 'Add stage',
])

@endsection
