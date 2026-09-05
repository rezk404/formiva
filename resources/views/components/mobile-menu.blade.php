@props(['site'])
<div class="fv-mobile" id="mobile-menu" role="dialog" aria-modal="true" aria-label="Site menu" aria-hidden="true">
    <div class="fv-mobile__inner">
        <div class="fv-mobile__head"><span>FORMIVA</span><button type="button" data-menu-toggle aria-label="Close menu">×</button></div>
        <nav aria-label="Mobile navigation">
            <a href="{{ route('work.index') }}">Work <span>01</span></a>
            <a href="{{ route('services.index') }}">Services <span>02</span></a>
            <a href="{{ route('studio') }}">Studio <span>03</span></a>
            <a href="{{ route('insights.index') }}">Insights <span>04</span></a>
            <a href="{{ route('contact') }}">Start a project <span>05</span></a>
        </nav>
    </div>
</div>
