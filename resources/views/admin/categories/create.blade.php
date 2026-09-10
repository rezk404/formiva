@extends('layouts.admin', [
    'title' => 'New category — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'Categories', 'url' => route('admin.categories.index')],
        ['label' => 'New'],
    ],
])

@section('content')

<x-admin.page-header
    kicker="Configuration"
    title="New category"
    description="Name it the way a visitor would describe the work, not the way the studio files it."
/>

@include('admin.categories.form', [
    'action' => route('admin.categories.store'),
    'method' => 'POST',
    'submit' => 'Create category',
])

@endsection
