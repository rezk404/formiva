@props(['site'])

<footer class="fv-footer">
    <div class="fv-container fv-footer__grid">
        <div><div class="fv-footer__logo"><x-mark /><span>{{ $site['brand']['name'] }}</span></div><p>{{ $site['brand']['tagline'] }}<br>Digital products, experiences and systems.</p></div>
        <div><span class="fv-footer__label">Explore</span><a href="{{ route('home') }}#services">Services</a><a href="{{ route('work.index') }}">Work</a><a href="{{ route('home') }}#about">About</a></div>
        <div><span class="fv-footer__label">Contact</span><a href="{{ route('contact') }}">Start a project</a><a href="mailto:{{ $site['contact']['email'] }}">{{ $site['contact']['email'] }}</a></div>
        <div><span class="fv-footer__label">Follow</span>@foreach ($site['social'] as $social)<a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">{{ $social['label'] }}</a>@endforeach</div>
    </div>
    <div class="fv-container fv-footer__bottom"><span>© {{ date('Y') }} FORMIVA</span><span>Built with intention.</span></div>
</footer>
