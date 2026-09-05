@props(['site'])
<header class="fv-nav" data-nav>
    <a class="fv-nav__brand" href="{{ route('home') }}" aria-label="FORMIVA home">
        <x-mark />
        <span>{{ $site['brand']['name'] }}</span>
    </a>
    <nav class="fv-nav__links" aria-label="Primary navigation">
        <a href="{{ route('work.index') }}" data-index="01" class="{{ request()->routeIs('work.index', 'projects.show') ? 'is-current' : '' }}" @if (request()->routeIs('work.index', 'projects.show')) aria-current="page" @endif>Work</a>
        <a href="{{ route('services.index') }}" data-index="02" class="{{ request()->routeIs('services.index') ? 'is-current' : '' }}" @if (request()->routeIs('services.index')) aria-current="page" @endif>Services</a>
        <a href="{{ route('studio') }}" data-index="03" class="{{ request()->routeIs('studio') ? 'is-current' : '' }}" @if (request()->routeIs('studio')) aria-current="page" @endif>Studio</a>
        <a href="{{ route('insights.index') }}" data-index="04" class="{{ request()->routeIs('insights.index', 'insights.show') ? 'is-current' : '' }}" @if (request()->routeIs('insights.index', 'insights.show')) aria-current="page" @endif>Insights</a>
    </nav>
    <a class="fv-nav__cta" href="{{ route('contact') }}">Start a project</a>
    <button class="fv-nav__menu" type="button" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu">Menu</button>
</header>
