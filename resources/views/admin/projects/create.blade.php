@extends('layouts.admin', [
    'title' => 'New project — FORMIVA',
    'breadcrumbs' => [['label' => 'Projects', 'url' => route('admin.projects.index')], ['label' => 'New']],
])

@section('content')
<x-admin.page-header kicker="Work" title="New project" description="Give the work a clear identity first. Story, delivery and publishing follow from there." />
@include('admin.projects.form', ['action' => route('admin.projects.store'), 'method' => 'POST', 'submit' => 'Create project'])
@endsection
