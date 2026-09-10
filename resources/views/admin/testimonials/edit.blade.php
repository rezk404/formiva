@extends('layouts.admin', [
    'title' => 'Quote from '.$testimonial->author_name.' — FORMIVA',
    'breadcrumbs' => [
        ['label' => 'About', 'url' => route('admin.about.overview')],
        ['label' => 'Testimonials', 'url' => route('admin.testimonials.index')],
        ['label' => $testimonial->author_name],
    ],
])

@section('content')

<x-admin.page-header
    kicker="About"
    :title="$testimonial->author_name"
    :description="$testimonial->author_role.' at '.$testimonial->company.'.'"
>
    <x-slot:actions>
        <x-admin.delete
            :action="route('admin.testimonials.destroy', $testimonial)"
            variant="danger"
            label="Delete quote"
            title="Delete this quote?"
            :confirm="'The quote attributed to '.$testimonial->author_name.' will be removed from the site. The record is soft-deleted, so it can be restored from the database.'"
        />
    </x-slot:actions>
</x-admin.page-header>

@include('admin.testimonials.form', [
    'action' => route('admin.testimonials.update', $testimonial),
    'method' => 'PUT',
    'submit' => 'Save quote',
])

@endsection
