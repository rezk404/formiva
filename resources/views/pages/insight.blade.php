@extends('layouts.app')

@section('content')
<article class="fv-detail-page fv-detail-page--reading" data-world="resolve" data-theme="dark">
    <div class="fv-container">
        <a class="fv-detail-page__back" href="{{ route('home') }}#insights">← Back to insights</a>
        <header class="fv-detail-page__hero">
            <div class="fv-detail-page__meta"><span>{{ $insight['category'] }}</span><span>{{ $insight['date_label'] }} · {{ $insight['reading'] }}</span></div>
            <h1>{{ $insight['title'] }}</h1>
            <p>{{ $insight['dek'] }}</p>
        </header>
        <div class="fv-detail-page__cover">
            <x-visual.plate :seed="$insight['plate']['seed']" :variant="$insight['plate']['variant']" ratio="16/9" :alt="$insight['alt']" :reveal="false" :parallax="false" />
        </div>
        <section class="fv-detail-page__article">
            <p>{{ $insight['body'] }}</p>
            <p>FORMIVA’s journal is a working record of the decisions behind better digital products and experiences. More entries are coming as the studio grows.</p>
        </section>
        <footer class="fv-detail-page__footer"><a class="fv-btn fv-btn--light" href="{{ route('contact') }}">Talk to FORMIVA <span>→</span></a></footer>
    </div>
</article>
@endsection
