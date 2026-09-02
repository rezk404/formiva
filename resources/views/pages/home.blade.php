@extends('layouts.app')

@section('content')
    {{-- FORMIVA is intentionally presented as one clear story: what we do, what we made,
         how we work, and why someone should trust us. The motion and 3D enhance the story
         rather than replacing it. --}}

    <section class="fv-hero" id="hero" data-world="monolith" data-world-visible data-theme="dark">
        <div class="fv-container fv-hero__inner">
            <div class="fv-hero__copy">
                <div class="fv-kicker"><span class="fv-kicker__dot"></span> Creative technology studio</div>
                <h1 class="fv-display">We build digital<br><em>experiences that move ideas forward.</em></h1>
                <p class="fv-lede">FORMIVA is a creative technology studio building digital products, web experiences, and interactive systems for ambitious companies.</p>
                <div class="fv-actions">
                    <a class="fv-btn fv-btn--light" href="#work">See our work <span>↗</span></a>
                    <a class="fv-btn fv-btn--ghost" href="{{ route('contact') }}">Start a project <span>→</span></a>
                </div>
                <div class="fv-hero__meta">
                    <span>Strategy · Design · Engineering</span>
                    <span>Working worldwide</span>
                </div>
            </div>

            <div class="fv-hero__visual" aria-label="Interactive FORMIVA visual">
                <div class="fv-hero__frame">
                    <div class="fv-hero__frame-top">
                        <span>FORM / 01</span>
                        <span>LIVE SYSTEM</span>
                    </div>
                    <div class="fv-hero__orb fv-hero__orb--outer"></div>
                    <div class="fv-hero__orb fv-hero__orb--mid"></div>
                    <div class="fv-hero__orb fv-hero__orb--inner">
                        <span class="fv-hero__orb-letter">F</span>
                    </div>
                    <div class="fv-hero__beam fv-hero__beam--one"></div>
                    <div class="fv-hero__beam fv-hero__beam--two"></div>
                    <div class="fv-hero__visual-label fv-hero__visual-label--a">idea</div>
                    <div class="fv-hero__visual-label fv-hero__visual-label--b">form</div>
                    <div class="fv-hero__visual-label fv-hero__visual-label--c">motion</div>
                    <div class="fv-hero__frame-bottom"><span>SCROLL TO TRANSFORM</span><span>●</span></div>
                </div>
            </div>
        </div>
    </section>

    <section class="fv-intro" id="manifesto" data-world="breathe" data-theme="light">
        <div class="fv-container">
            <div class="fv-section-label"><span>01</span><span>POINT OF VIEW</span></div>
            <div class="fv-intro__grid">
                <p class="fv-intro__lead">We make digital things people <em>feel</em> before they understand how they work.</p>
                <div class="fv-intro__copy">
                    <p>Good design gets out of the way. Great design changes the way a person moves through a brand.</p>
                    <p>That is why every FORMIVA project starts with the idea, not the interface — then turns that idea into a system people can actually use.</p>
                    <a class="fv-text-link" href="#services">Explore what we do <span>→</span></a>
                </div>
            </div>
        </div>
    </section>

    <section class="fv-trust" id="trust" data-world="breathe" data-theme="light">
        <div class="fv-container">
            <div class="fv-trust__top"><span>SELECTED PARTNERS</span><span>2023 — 2026</span></div>
            <div class="fv-trust__logos">
                @foreach ($clients['names'] as $client)
                    <span>{{ $client['name'] }}</span>
                @endforeach
            </div>
        </div>
    </section>

    <section class="fv-services" id="services" data-services data-world="fan" data-theme="dark" data-world-visible>
        <div class="fv-container">
            <div class="fv-section-label fv-section-label--dark"><span>02</span><span>WHAT WE DO</span></div>
            <div class="fv-services__head">
                <h2 class="fv-h2">One studio.<br><em>Six capabilities.</em></h2>
                <p>From the first idea to the final deployment, we keep strategy, design and engineering in the same room.</p>
            </div>

            <div class="fv-services__grid">
                @foreach ($services as $index => $service)
                    <article class="fv-service" data-service="{{ $service['form'] ?? 'modular' }}">
                        <div class="fv-service__top"><span>{{ sprintf('%02d', $index + 1) }}</span><span>↗</span></div>
                        <h3>{{ $service['title'] }}</h3>
                        <p>{{ strip_tags($service['lede']) }}</p>
                        <div class="fv-service__caps">
                            @foreach (array_slice($service['capabilities'], 0, 3) as $cap)
                                <span>{{ strip_tags($cap) }}</span>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="fv-work" id="work" data-world="disperse" data-theme="dark">
        <div class="fv-container">
            <div class="fv-section-label fv-section-label--dark"><span>03</span><span>SELECTED WORK</span></div>
            <div class="fv-work__intro">
                <h2 class="fv-h2">Work that has<br><em>a reason to exist.</em></h2>
                <a class="fv-text-link fv-text-link--light" href="{{ route('work.index') }}">View all work <span>→</span></a>
            </div>

            <div class="fv-work__grid">
                @foreach ($projects as $index => $project)
                    <a class="fv-project fv-project--{{ $index === 0 ? 'large' : ($index === 1 ? 'wide' : 'small') }}" href="{{ route('projects.show', $project['slug']) }}" data-cursor="view" data-cursor-label="view" aria-label="View {{ $project['name'] }} project">
                        <div class="fv-project__media">
                            <x-visual.plate
                                :seed="$project['plate']['seed']"
                                :variant="$project['plate']['variant']"
                                :ratio="$project['plate']['ratio']"
                                :reveal="false"
                                :parallax="false"
                                decorative
                            />
                            <span class="fv-project__cursor">VIEW</span>
                        </div>
                        <div class="fv-project__info">
                            <div>
                                <span class="fv-project__name">{{ $project['name'] }}</span>
                                <span class="fv-project__category">{{ $project['category'] }}</span>
                                <span class="fv-project__statement">{{ $project['statement'] }}</span>
                            </div>
                            <div class="fv-project__result"><span>{{ $project['result']['value'] }}</span><small>{{ $project['result']['label'] }}</small></div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="fv-case" id="case-study" data-world="disperse" data-theme="light">
        <div class="fv-container">
            <div class="fv-case__hero">
                <div>
                    <div class="fv-section-label"><span>04</span><span>FEATURED CASE STUDY</span></div>
                    <div class="fv-case__eyebrow">{{ $caseStudy['eyebrow'] }} · {{ $caseStudy['client'] }}</div>
                    <h2 class="fv-h2 fv-h2--case">{{ $caseStudy['title'] }}</h2>
                    <p>{{ $caseStudy['summary'] }}</p>
                </div>
                <div class="fv-case__facts">
                    <span><b>19 weeks</b> delivery</span>
                    <span><b>38%</b> revenue / session</span>
                    <span><b>2,140</b> pieces catalogued</span>
                </div>
            </div>

            <div class="fv-case__story">
                @foreach ($caseStudy['beats'] as $beat)
                    <article class="fv-case-step">
                        <div class="fv-case-step__number">{{ $beat['index'] }}</div>
                        <div class="fv-case-step__content">
                            <span>{{ $beat['label'] }}</span>
                            <h3>{{ $beat['heading'] }}</h3>
                            <p>{{ $beat['body'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="fv-studio" id="about" data-world="stair" data-theme="dark">
        <div class="fv-container">
            <div class="fv-section-label fv-section-label--dark"><span>05</span><span>THE STUDIO</span></div>
            <div class="fv-studio__grid">
                <h2 class="fv-h2">Built by people who care about the <em>whole thing.</em></h2>
                <div class="fv-studio__copy">
                    @foreach (array_slice($studio['story'], 0, 2) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                    <a class="fv-text-link fv-text-link--light" href="#contact">Meet the studio <span>→</span></a>
                </div>
            </div>
            <div class="fv-stats">
                @foreach ($studio['stats'] as $stat)
                    <div class="fv-stat">
                        <span class="fv-stat__value">{{ $stat['value'] }}{{ strip_tags($stat['suffix']) }}</span>
                        <span class="fv-stat__label">{{ strip_tags($stat['label']) }}</span>
                        <span class="fv-stat__note">{{ strip_tags($stat['note']) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="fv-process" id="process" data-world="stair" data-theme="light">
        <div class="fv-container">
            <div class="fv-section-label"><span>06</span><span>HOW WE WORK</span></div>
            <div class="fv-process__head">
                <h2 class="fv-h2">Less theatre.<br><em>More making.</em></h2>
                <p>We move from question to working system quickly — with design and engineering evolving together.</p>
            </div>
            <div class="fv-process__track">
                @foreach ($process['stages'] as $stage)
                    <div class="fv-process-step">
                        <div class="fv-process-step__line"></div>
                        <span>{{ $stage['index'] }}</span>
                        <h3>{{ $stage['title'] }}</h3>
                        <p>{{ $stage['body'] }}</p>
                        <small>{{ $stage['output'] }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="fv-proof" id="proof" data-world="stair" data-theme="dark">
        <div class="fv-container">
            <div class="fv-section-label fv-section-label--dark"><span>07</span><span>PEOPLE + PROOF</span></div>
            <div class="fv-proof__quote">
                <span class="fv-proof__mark">“</span>
                <blockquote>{{ strip_tags($testimonials[0]['quote']) }}</blockquote>
                <div class="fv-proof__person"><strong>{{ $testimonials[0]['name'] }}</strong><span>{{ $testimonials[0]['role'] }} · {{ $testimonials[0]['company'] }}</span></div>
            </div>
        </div>
    </section>

    <section class="fv-insights" id="insights" data-world="resolve" data-theme="light">
        <div class="fv-container">
            <div class="fv-section-label"><span>08</span><span>SIGNALS</span></div>
            <div class="fv-insights__head">
                <h2 class="fv-h2">Notes on design,<br><em>technology &amp; motion.</em></h2>
                <a class="fv-text-link" href="{{ route('insights.index') }}">Read the journal <span>→</span></a>
            </div>
            <div class="fv-insights__grid">
                @foreach (array_slice($insights, 0, 3) as $insight)
                    <a class="fv-insight" href="{{ route('insights.show', $insight['slug']) }}" data-cursor="open" data-cursor-label="read" aria-label="Read {{ $insight['title'] }}">
                        <div class="fv-insight__media">
                            <x-visual.plate
                                :seed="$insight['plate']['seed']"
                                :variant="$insight['plate']['variant']"
                                :ratio="$insight['plate']['ratio']"
                                :reveal="false"
                                :parallax="false"
                                decorative
                            />
                        </div>
                        <div class="fv-insight__meta"><span>{{ $insight['category'] }}</span><span>{{ $insight['reading'] }}</span></div>
                        <h3>{{ $insight['title'] }}</h3>
                        <p>{{ $insight['dek'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="fv-cta" id="contact" data-world="resolve" data-world-visible data-theme="dark">
        <div class="fv-container fv-cta__inner">
            <div class="fv-section-label fv-section-label--dark"><span>09</span><span>START A PROJECT</span></div>
            <div class="fv-cta__title-wrap">
                <h2 class="fv-display">Have an idea<br><em>worth building?</em></h2>
                <p>Tell us what you are trying to make. We will help you find the right shape for it.</p>
                <div class="fv-actions">
                    <a class="fv-btn fv-btn--light" href="{{ route('contact') }}">Start a project <span>→</span></a>
                </div>
            </div>
            <div class="fv-cta__meta"><span>FORMIVA</span><span>Creative technology studio</span><span>{{ $site['contact']['timezone'] }}</span></div>
        </div>
    </section>

    <footer class="fv-footer" hidden aria-hidden="true">
        <div class="fv-container fv-footer__grid">
            <div>
                <div class="fv-footer__logo"><x-mark /><span>{{ $site['brand']['name'] }}</span></div>
                <p>{{ $site['brand']['tagline'] }}<br>Digital products, experiences and systems.</p>
            </div>
            <div>
                <span class="fv-footer__label">Explore</span>
                <a href="#services">Services</a>
                <a href="#work">Work</a>
                <a href="#about">About</a>
            </div>
            <div>
                <span class="fv-footer__label">Contact</span>
                <a href="mailto:{{ $site['contact']['email'] }}">{{ $site['contact']['email'] }}</a>
                <a href="mailto:{{ $site['contact']['new_business'] }}">New business</a>
            </div>
            <div>
                <span class="fv-footer__label">Follow</span>
                @foreach ($site['social'] as $social)
                    <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">{{ $social['label'] }}</a>
                @endforeach
            </div>
        </div>
        <div class="fv-container fv-footer__bottom"><span>© {{ date('Y') }} FORMIVA</span><span>Built with intention.</span></div>
    </footer>
@endsection
