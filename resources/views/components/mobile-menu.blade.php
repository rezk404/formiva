@props(['site'])
<div class="fv-mobile" id="mobile-menu" aria-hidden="true">
    <div class="fv-mobile__inner">
        <div class="fv-mobile__head"><span>FORMIVA</span><button type="button" data-menu-toggle aria-label="Close menu">×</button></div>
        <nav>
            <a href="{{ route('home') }}#services">Services <span>01</span></a>
            <a href="{{ route('work.index') }}">Work <span>02</span></a>
            <a href="{{ route('home') }}#about">About <span>03</span></a>
            <a href="{{ route('home') }}#process">Process <span>04</span></a>
            <a href="{{ route('home') }}#insights">Insights <span>05</span></a>
            <a href="{{ route('home') }}#contact">Start a project <span>06</span></a>
        </nav>
    </div>
</div>
