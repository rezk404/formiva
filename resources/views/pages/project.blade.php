@extends('layouts.app')

@section('content')
<article class="fv-detail-page" data-world="disperse" data-theme="light">
    <div class="fv-container">
        <a class="fv-detail-page__back" href="{{ route('work.index') }}">← Back to all work</a>
        <header class="fv-detail-page__hero">
            <div class="fv-detail-page__meta"><span>{{ $project['index'] }} / {{ $project['category'] }}</span><span>{{ $project['year'] }} · {{ $project['client'] }}</span></div>
            <h1>{{ $project['name'] }}</h1>
            <p>{{ $project['description'] }}</p>
            @if ($project['result']['value'])
                <div class="fv-detail-page__result"><strong>{{ $project['result']['value'] }}</strong><span>{{ $project['result']['label'] }}</span></div>
            @endif
        </header>

        <div class="fv-detail-page__cover">
            <x-visual.plate :seed="$project['plate']['seed']" :variant="$project['plate']['variant']" ratio="16/9" :alt="$project['alt']" />
        </div>

        <section class="fv-detail-page__story" aria-label="Project story">
            @if ($project['challenge'])<div><span>Challenge</span><p>{{ $project['challenge'] }}</p></div>@endif
            @if ($project['solution'])<div><span>Approach</span><p>{{ $project['solution'] }}</p></div>@endif
            @if ($project['outcome'])<div><span>Outcome</span><p>{{ $project['outcome'] }}</p></div>@endif
        </section>

        @if (count($project['gallery']))
            <div class="fv-detail-page__gallery">
                @foreach ($project['gallery'] as $image)
                    <x-visual.plate :seed="$image['seed']" :variant="$image['variant']" :ratio="$image['ratio']" decorative />
                @endforeach
            </div>
        @endif

        <footer class="fv-detail-page__footer">
            <div><span>Services</span><p>{{ $project['services'] ? implode(' · ', $project['services']) : '—' }}</p></div>
            <div><span>Technology</span><p>{{ $project['stack'] ? implode(' · ', $project['stack']) : '—' }}</p></div>
            <a class="fv-btn fv-btn--ink" href="{{ route('contact') }}">Start a project <span>→</span></a>
        </footer>
        {{-- With a single project there are no neighbours, and an empty nav
             landmark is worse than none. --}}
        @if ($neighbors['previous'] || $neighbors['next'])
            <nav class="fv-project-pagination" aria-label="Project navigation">
                @if ($neighbors['previous'])
                    <a href="{{ route('projects.show', $neighbors['previous']['slug']) }}"><span>← Previous project</span><strong>{{ $neighbors['previous']['title'] }}</strong></a>
                @endif
                @if ($neighbors['next'])
                    <a href="{{ route('projects.show', $neighbors['next']['slug']) }}"><span>Next project →</span><strong>{{ $neighbors['next']['title'] }}</strong></a>
                @endif
            </nav>
        @endif
    </div>
</article>
@endsection
