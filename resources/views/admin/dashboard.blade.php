@extends('layouts.admin', ['title' => 'Dashboard — FORMIVA', 'header' => 'Dashboard'])

@section('content')
<div class="admin-page-intro"><p class="admin-kicker">Overview</p><h1>Good to see you, {{ auth()->user()->name }}.</h1><p>Here is the current shape of the FORMIVA workspace.</p></div>
<div class="admin-stat-grid">
    @foreach([
        ['label' => 'Projects', 'value' => $stats['projects'], 'detail' => $stats['publishedProjects'].' published'],
        ['label' => 'Inquiries', 'value' => $stats['inquiries'], 'detail' => $stats['newInquiries'].' new'],
        ['label' => 'Clients', 'value' => $stats['clients'], 'detail' => 'Across the studio'],
        ['label' => 'Insights', 'value' => $stats['insights'], 'detail' => 'Published and drafted'],
    ] as $stat)
        <section class="admin-stat"><span>{{ $stat['label'] }}</span><strong>{{ $stat['value'] }}</strong><small>{{ $stat['detail'] }}</small></section>
    @endforeach
</div>
<section class="admin-panel"><div><p class="admin-kicker">Next</p><h2>The foundation is ready.</h2><p>Content management areas will arrive here in the next phase. Your role controls what becomes available.</p></div><a class="admin-button admin-button-light" href="{{ route('admin.profile.edit') }}">Review profile</a></section>
@endsection
