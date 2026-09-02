@extends('layouts.app')

@section('content')
<article class="fv-detail-page" data-world="disperse" data-theme="dark">
    <div class="fv-container">
        <a class="fv-detail-page__back" href="{{ route('work.index') }}">← Back to all work</a>
        <header class="fv-detail-page__hero">
            <div class="fv-detail-page__meta"><span>{{ $project['index'] }} / {{ $project['category'] }}</span><span>{{ $project['year'] }} · {{ $project['client'] }}</span></div>
            <h1>{{ $project['name'] }}</h1>
            <p>{{ $project['description'] }}</p>
            <div class="fv-detail-page__result"><strong>{{ $project['result']['value'] }}</strong><span>{{ $project['result']['label'] }}</span></div>
        </header>

        <div class="fv-detail-page__cover">
            <x-visual.plate :seed="$project['plate']['seed']" :variant="$project['plate']['variant']" ratio="16/9" :alt="$project['alt']" :reveal="false" :parallax="false" />
        </div>

        <section class="fv-detail-page__story" aria-label="Project story">
            <div><span>Challenge</span><p>{{ $project['challenge'] }}</p></div>
            <div><span>Approach</span><p>{{ $project['solution'] }}</p></div>
            <div><span>Outcome</span><p>{{ $project['outcome'] }}</p></div>
        </section>

        <div class="fv-detail-page__gallery">
            @foreach ($project['gallery'] as $image)
                <x-visual.plate :seed="$image['seed']" :variant="$image['variant']" :ratio="$image['ratio']" :reveal="false" :parallax="false" decorative />
            @endforeach
        </div>

        <footer class="fv-detail-page__footer">
            <div><span>Services</span><p>{{ implode(' · ', $project['services']) }}</p></div>
            <div><span>Technology</span><p>{{ implode(' · ', $project['stack']) }}</p></div>
            <a class="fv-btn fv-btn--light" href="{{ route('contact') }}">Start a project <span>→</span></a>
        </footer>
        <nav class="fv-project-pagination" aria-label="Project navigation">
            @if ($neighbors['previous'])
                <a href="{{ route('projects.show', $neighbors['previous']['slug']) }}"><span>← Previous project</span><strong>{{ $neighbors['previous']['title'] }}</strong></a>
            @endif
            @if ($neighbors['next'])
                <a href="{{ route('projects.show', $neighbors['next']['slug']) }}"><span>Next project →</span><strong>{{ $neighbors['next']['title'] }}</strong></a>
            @endif
        </nav>
    </div>
</article>
@endsection
