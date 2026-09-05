@extends('layouts.app')

@section('content')
<section class="fv-services-page" data-theme="light">
    <div class="fv-container">
        <a class="fv-detail-page__back" href="{{ route('home') }}">← Back to FORMIVA</a>

        <header class="fv-services-page__head">
            <span class="fv-spec">What we do / Full breakdown</span>
            <h1>Three ways in.<br><em>One studio.</em></h1>
            <p>Digital products, the business systems that run behind them, and the experience layer that makes both feel considered. Most engagements draw from more than one.</p>
        </header>

        @foreach ($services as $pillar)
            <article class="fv-pillar" id="{{ $pillar['slug'] }}" data-pillar="{{ $pillar['slug'] }}">
                <div class="fv-pillar__head">
                    <div>
                        <span class="fv-pillar__index">{{ $pillar['index'] }} / {{ strtoupper($pillar['slug']) }}</span>
                        <h2>{{ $pillar['title'] }}</h2>
                    </div>
                    <div>
                        <p class="fv-pillar__lede">{{ $pillar['lede'] }}</p>
                        <p class="fv-pillar__why">{{ $pillar['why'] }}</p>
                        <p class="fv-pillar__outcome">{{ $pillar['outcome'] }}</p>
                    </div>
                </div>

                <div class="fv-pillar__items">
                    @foreach ($pillar['items'] as $item)
                        <div class="fv-pillar__item">
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ $item['summary'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="fv-pillar__foot">
                    <span>{{ $pillar['note'] }}</span>
                    <span>Starts with a conversation, not a proposal</span>
                </div>
            </article>
        @endforeach

        <div class="fv-services-page__cta">
            <p>Not sure which of these fits? Start the project intake — it is built to help figure that out together.</p>
            <a class="fv-btn fv-btn--ink" href="{{ route('contact') }}">Start a project <span>→</span></a>
        </div>
    </div>
</section>
@endsection
