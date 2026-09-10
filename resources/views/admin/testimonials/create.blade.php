@extends('layouts.admin', [
    'title' => 'New testimonial — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'About', 'url' => route('admin.about.overview')],
        ['label' => 'Testimonials', 'url' => route('admin.testimonials.index')],
        ['label' => 'New'],
    ],
])

@section('content')

<x-admin.page-header
    kicker="About"
    title="Add a quote"
    description="Keep the sentence that says something only this client could say."
/>

@include('admin.testimonials.form', [
    'action' => route('admin.testimonials.store'),
    'method' => 'POST',
    'submit' => 'Add quote',
])

@endsection
