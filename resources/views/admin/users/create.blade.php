@extends('layouts.admin', [
    'title' => 'New account — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'Users', 'url' => route('admin.users.index')],
        ['label' => 'New'],
    ],
])

@section('content')

<x-admin.page-header
    kicker="System"
    title="New account"
    description="Start people as editors. Admin adds site settings and account management, and there is rarely a reason for more than two."
/>

@include('admin.users.form', [
    'action' => route('admin.users.store'),
    'method' => 'POST',
    'submit' => 'Create account',
])

@endsection
