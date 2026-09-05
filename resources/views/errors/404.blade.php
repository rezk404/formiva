@extends('layouts.app')

@section('content')
<section class="fv-detail-page fv-error-page" data-theme="light">
    <div class="fv-container">
        <span class="fv-spec">Error / 404</span>
        <h1>This page<br><em>has not been drawn.</em></h1>
        <p>The link may be old, or the work may not be published yet. These all still lead somewhere.</p>

        <div class="fv-error-page__links">
            <a class="fv-btn fv-btn--ink" href="{{ route('home') }}">Home <span>→</span></a>
            <a class="fv-text-link" href="{{ route('work.index') }}">Work <span>→</span></a>
            <a class="fv-text-link" href="{{ route('services.index') }}">Services <span>→</span></a>
            <a class="fv-text-link" href="{{ route('contact') }}">Start a project <span>→</span></a>
        </div>
    </div>
</section>
@endsection
