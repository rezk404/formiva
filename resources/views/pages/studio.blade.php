@extends('layouts.app')

@section('content')
<section class="fv-studio-page" data-theme="light">
    <div class="fv-container">
        <a class="fv-detail-page__back" href="{{ route('home') }}">← Back to FORMIVA</a>

        <header class="fv-studio-page__head">
            <span class="fv-spec">{{ $studio['eyebrow'] }}</span>
            <h1>{{ $studio['headline'][0] }}<br><em>{{ $studio['headline'][1] }}</em></h1>
        </header>

        <div class="fv-studio-page__story">
            @foreach ($studio['story'] as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>

        <div class="fv-studio-page__positions">
            @foreach ($studio['positions'] as $position)
                {{-- h2, not h3: these sit between the page h1 and the "The
                     people" h2 below, and a level skipped here is a real
                     navigation problem for a screen reader. --}}
                <div class="fv-studio-page__position">
                    <span>{{ $position['index'] }}</span>
                    <h2>{{ $position['title'] }}</h2>
                    <p>{{ $position['body'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="fv-studio-page__stats">
            @foreach ($studio['stats'] as $stat)
                <div class="fv-stat">
                    <span class="fv-stat__value">{{ $stat['value'] }}{{ strip_tags($stat['suffix']) }}</span>
                    <span class="fv-stat__label">{{ strip_tags($stat['label']) }}</span>
                    <span class="fv-stat__note">{{ strip_tags($stat['note']) }}</span>
                </div>
            @endforeach
        </div>

        <div class="fv-studio-page__team">
            <div class="fv-studio-page__team-head">
                <h2>The people</h2>
                <span class="fv-spec">{{ count($team) }} of nineteen, by discipline</span>
            </div>
            <div class="fv-team-grid">
                @foreach ($team as $person)
                    <article class="fv-team-card">
                        <x-visual.plate
                            :seed="$person['plate']['seed']"
                            :variant="$person['plate']['variant']"
                            :ratio="$person['plate']['ratio']"
                            :alt="$person['alt']"
                        />
                        <span class="fv-team-card__name">{{ $person['name'] }}</span>
                        <span class="fv-team-card__role">{{ $person['role'] }}</span>
                        <p>{{ $person['bio'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>

        <footer class="fv-detail-page__footer">
            <div>
                <span>How we work</span>
                <p>{{ $process['lede'] }}</p>
            </div>
            <div>
                <span>Start here</span>
                <p>Tell us what you are trying to build or fix — product, system, or both.</p>
            </div>
            <a class="fv-btn fv-btn--ink" href="{{ route('contact') }}">Start a project <span>→</span></a>
        </footer>
    </div>
</section>
@endsection
