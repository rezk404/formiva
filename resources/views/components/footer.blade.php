@props(['site'])

<footer class="fv-footer">
    <div class="fv-container fv-footer__grid">
        <div>
            <div class="fv-footer__logo"><x-mark /><span>{{ $site['brand']['name'] }}</span></div>
            <p>{{ $site['brand']['tagline'] }}</p>
        </div>
        <div><span class="fv-footer__label fv-spec">Explore</span><a href="{{ route('work.index') }}">Work</a><a href="{{ route('services.index') }}">Services</a><a href="{{ route('studio') }}">Studio</a><a href="{{ route('insights.index') }}">Insights</a></div>
        <div><span class="fv-footer__label fv-spec">Contact</span><a href="{{ route('contact') }}">Start a project</a><a href="mailto:{{ $site['contact']['email'] }}">{{ $site['contact']['email'] }}</a></div>
        <div><span class="fv-footer__label fv-spec">Follow</span>@foreach ($site['social'] as $social)<a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">{{ $social['label'] }}</a>@endforeach</div>
    </div>
    <div class="fv-container fv-footer__bottom fv-spec"><span>© {{ date('Y') }} {{ $site['legal']['entity'] }}</span><span>{{ $site['contact']['city'] }}, {{ $site['contact']['country'] }}</span></div>
</footer>
