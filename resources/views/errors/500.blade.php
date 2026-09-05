@extends('layouts.app')

@section('content')
<section class="fv-detail-page fv-error-page" data-theme="light">
    <div class="fv-container">
        <span class="fv-spec">Error / 500</span>
        <h1>Something<br><em>broke on our side.</em></h1>
        <p>Not your doing. Try again in a moment — and if it keeps happening, tell us and we will fix it.</p>

        <div class="fv-error-page__links">
            <a class="fv-btn fv-btn--ink" href="{{ route('home') }}">Home <span>→</span></a>
            <a class="fv-text-link" href="mailto:{{ $site['contact']['email'] }}">{{ $site['contact']['email'] }}</a>
        </div>
    </div>
</section>
@endsection
