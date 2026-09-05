@extends('layouts.app')

@section('content')
<section class="fv-list-page" data-world="resolve" data-theme="light">
    <div class="fv-container">
        <a class="fv-detail-page__back" href="{{ route('home') }}">← Back to FORMIVA</a>
        <header class="fv-list-page__head"><span class="fv-spec">Signals / Journal</span><h1>Useful notes on<br><em>making digital things.</em></h1></header>
        <div class="fv-list-page__items">
            @forelse ($insights as $insight)
                <a href="{{ route('insights.show', $insight['slug']) }}" class="fv-list-page__item">
                    <span>{{ $insight['index'] }} / {{ $insight['category'] }}</span>
                    <div>
                        <h2>{{ $insight['title'] }}</h2>
                        <p>{{ $insight['dek'] }}</p>
                    </div>
                    <small>{{ $insight['date_label'] }} · {{ $insight['reading'] }}<b>Read →</b></small>
                </a>
            @empty
                <p class="fv-empty">No notes published yet.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
