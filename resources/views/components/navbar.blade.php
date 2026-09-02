@props(['site'])
<header class="fv-nav" data-nav>
    <a class="fv-nav__brand" href="#hero" aria-label="FORMIVA home">
        <x-mark />
        <span>{{ $site['brand']['name'] }}</span>
    </a>
    <nav class="fv-nav__links" aria-label="Primary navigation">
        <a href="{{ route('home') }}#services">Services</a>
        <a href="{{ route('work.index') }}">Work</a>
        <a href="{{ route('home') }}#about">About</a>
        <a href="{{ route('home') }}#process">Process</a>
        <a href="{{ route('home') }}#insights">Insights</a>
    </nav>
    <a class="fv-nav__cta" href="#contact">Start a project <span>↗</span></a>
    <button class="fv-nav__menu" type="button" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu">Menu <span>+</span></button>
</header>
