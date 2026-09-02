@extends('layouts.app')

@section('content')
<section class="fv-work-archive" data-theme="dark">
    <div class="fv-container">
        <a class="fv-detail-page__back" href="{{ route('home') }}#work">← Back to FORMIVA</a>
        <header class="fv-work-archive__head">
            <span>Work / Selected systems</span>
            <h1>Built to be<br><em>used in the real world.</em></h1>
            <p>A focused collection of digital products, commerce platforms, and brand experiences shaped around measurable use.</p>
        </header>

        <div class="fv-work-archive__list">
            @foreach ($projects as $project)
                <a class="fv-work-archive__project fv-work-archive__project--{{ $loop->iteration % 2 === 0 ? 'reverse' : 'standard' }}" href="{{ route('projects.show', $project['slug']) }}" data-cursor="view" data-cursor-label="view">
                    <div class="fv-work-archive__visual">
                        <x-visual.plate :seed="$project['coverImage']['seed']" :variant="$project['coverImage']['variant']" :ratio="$project['coverImage']['ratio']" :alt="$project['alt']" :reveal="false" :parallax="false" />
                        <span class="fv-work-archive__logo">{{ $project['clientLogo'] }}</span>
                    </div>
                    <div class="fv-work-archive__content">
                        <div class="fv-work-archive__meta"><span>{{ $project['index'] }} / {{ $project['category'] }}</span><span>{{ $project['year'] }}</span></div>
                        <h2>{{ $project['title'] }}</h2>
                        <p>{{ $project['statement'] }}</p>
                        <div><span>{{ implode(' · ', array_slice($project['services'], 0, 3)) }}</span><strong>{{ $project['result']['value'] }} <small>{{ $project['result']['label'] }}</small></strong></div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endsection
