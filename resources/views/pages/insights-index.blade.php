@extends('layouts.app')

@section('content')
<section class="fv-list-page" data-world="resolve" data-theme="dark">
    <div class="fv-container">
        <a class="fv-detail-page__back" href="{{ route('home') }}#insights">← Back to FORMIVA</a>
        <header class="fv-list-page__head"><span>Signals / Journal</span><h1>Useful notes on<br><em>making digital things.</em></h1></header>
        <div class="fv-list-page__items">
            @foreach ($insights as $insight)
                <a href="{{ route('insights.show', $insight['slug']) }}" class="fv-list-page__item">
                    <span>{{ $insight['index'] }} / {{ $insight['category'] }}</span>
                    <h2>{{ $insight['title'] }}</h2>
                    <p>{{ $insight['dek'] }}</p>
                    <small>{{ $insight['date_label'] }} · {{ $insight['reading'] }} <b>Read →</b></small>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endsection
