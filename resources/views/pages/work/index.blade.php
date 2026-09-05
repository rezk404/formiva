@extends('layouts.app')

@section('content')
<section class="fv-work-archive" data-theme="light">
    <div class="fv-container">
        <a class="fv-detail-page__back" href="{{ route('home') }}">← Back to FORMIVA</a>
        <header class="fv-work-archive__head">
            <span class="fv-spec">Work / Selected systems</span>
            <h1>Built to be<br><em>used in the real world.</em></h1>
            <p>A focused collection of digital products, business systems, and brand experiences shaped around measurable use.</p>
        </header>

        <div class="fv-work-archive__list">
            @forelse ($projects as $project)
                <a class="fv-work-archive__project" href="{{ route('projects.show', $project['slug']) }}" data-cursor="view" data-cursor-label="view">
                    <span class="fv-spec">{{ $project['index'] }}</span>
                    <div class="fv-work-archive__content">
                        <div class="fv-work-archive__meta fv-spec"><span>{{ $project['category'] }}</span><span>{{ $project['year'] }}</span></div>
                        <h2>{{ $project['title'] }}</h2>
                        <p>{{ $project['statement'] }}</p>
                        <div><span class="fv-spec">{{ implode(' · ', array_slice($project['services'], 0, 3)) }}</span>@if ($project['result']['value'])<strong>{{ $project['result']['value'] }}</strong>@endif</div>
                    </div>
                    <div class="fv-work-archive__visual">
                        <x-visual.plate :seed="$project['coverImage']['seed']" :variant="$project['coverImage']['variant']" :ratio="$project['coverImage']['ratio']" :alt="$project['alt']" />
                        <span class="fv-work-archive__logo">{{ $project['clientLogo'] }}</span>
                    </div>
                </a>
            @empty
                <p class="fv-empty">No published work yet. <a class="fv-text-link" href="{{ route('contact') }}">Start a project</a> and it could be the first.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
