@extends('layouts.admin', [
    'title' => 'New insight — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'Insights', 'url' => route('admin.insights.index')],
        ['label' => 'New'],
    ],
])

@section('content')

<x-admin.page-header
    kicker="Content"
    title="Write an insight"
    description="Start with the standfirst. If it does not earn the click on its own, the entry underneath is not ready yet."
/>

@include('admin.insights.form', [
    'action' => route('admin.insights.store'),
    'method' => 'POST',
    'submit' => 'Create entry',
])

@endsection
