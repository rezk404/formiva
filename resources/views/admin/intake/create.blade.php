@extends('layouts.admin', [
    'title' => 'New intake option — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'Intake options', 'url' => route('admin.intake.index')],
        ['label' => 'New'],
    ],
])

@section('content')

<x-admin.page-header
    kicker="Configuration"
    title="New intake option"
    description="Give the visitor a way to describe the work that matches how they would describe it themselves."
/>

@include('admin.intake.form', [
    'action' => route('admin.intake.store'),
    'method' => 'POST',
    'submit' => 'Add option',
])

@endsection
