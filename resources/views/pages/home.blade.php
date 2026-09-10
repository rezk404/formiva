@extends('layouts.app')

@section('content')
    {{-- Idea. Form. System. — the sequence the studio's own name argues for,
         and the sequence this page is built to walk a reader through. --}}

    <section class="fv-hero" id="hero" data-world="monolith" data-world-visible data-theme="dark">
        <div class="fv-container fv-hero__inner">
            <div class="fv-hero__copy">
                <div class="fv-hero__kicker"><span class="fv-mark" aria-hidden="true"></span> Digital products &amp; business systems</div>
                <h1 class="fv-hero__title fv-plate">Idea. Form.<br>System.<em>We build all three.</em></h1>
                <p class="fv-hero__lede">FORMIVA designs and ships digital products — sites, stores, applications — and implements the ERP, CRM and automation that let a growing business run them without falling over.</p>
                <div class="fv-hero__actions">
                    <a class="fv-btn fv-btn--light" href="#work">See the work <span>↗</span></a>
                    <a class="fv-btn fv-btn--ghost" href="{{ route('contact') }}">Start a project <span>→</span></a>
                </div>
            </div>
            <div class="fv-hero__foot">
                <dl>
                    <div><dt class="fv-spec">Discipline</dt><dd>Strategy · Design · Engineering · Systems</dd></div>
                    <div><dt class="fv-spec">Reach</dt><dd>Working across the region</dd></div>
                </dl>
                <div class="fv-hero__cue"><span class="fv-spec">Scroll</span><span class="fv-hero__cue-line"></span></div>
            </div>
        </div>
    </section>

    <section class="fv-intro" id="manifesto" data-world="breathe" data-theme="light">
        <div class="fv-container">
            <div class="fv-section-label"><span class="fv-mark" aria-hidden="true"></span><span class="fv-spec">Point of view</span></div>
            <div class="fv-intro__grid">
                <p class="fv-intro__lead">A storefront running on a <em>spreadsheet</em> is not a finished project.</p>
                <div class="fv-intro__notes">
                    <div class="fv-intro__note">
                        <span class="fv-spec">01 / The gap</span>
                        <p>Most studios stop at the interface. Most systems integrators never touch it. The handoff between the two is where projects usually go quiet.</p>
                    </div>
                    <div class="fv-intro__note">
                        <span class="fv-spec">02 / The answer</span>
                        <p>FORMIVA does both under one roof, because a product and the system that operates it are usually the same engagement wearing different clothes.</p>
                    </div>
                    <a class="fv-text-link" href="#services">What we build <span>→</span></a>
                </div>
            </div>
        </div>
    </section>

    <section class="fv-trust" id="trust" data-world="breathe" data-theme="light">
        <div class="fv-container">
            <div class="fv-section-label"><span class="fv-mark" aria-hidden="true"></span><span class="fv-spec">Selected partners / {{ $clients['period'] }}</span></div>
            <div class="fv-trust__list">
                @foreach ($clients['names'] as $index => $client)
                    <div class="fv-trust__row">
                        <span class="fv-spec">{{ sprintf('%02d', $index + 1) }}</span>
                        <strong>{{ $client['name'] }}</strong>
                        <em>{{ $client['sector'] }}</em>
                        <span class="fv-spec">FIG. {{ sprintf('%02d', $index + 1) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="fv-services" id="services" data-services data-world="fan" data-theme="dark" data-world-visible>
        <div class="fv-container">
            <div class="fv-section-label fv-section-label--dark"><span class="fv-mark" aria-hidden="true"></span><span class="fv-spec">What we do</span></div>
            <div class="fv-services__head">
                <h2 class="fv-h2">One studio.<br><em>Three ways in.</em></h2>
                <p>Digital products, the business systems behind them, and the experience layer that makes both feel considered.</p>
            </div>

            <div class="fv-services__list">
                @foreach ($services as $index => $pillar)
                    <article class="fv-service{{ $index === 0 ? ' is-active' : '' }}" data-service data-form="{{ $pillar['form'] }}" data-pillar="{{ $pillar['slug'] }}">
                        <button
                            class="fv-service__trigger"
                            type="button"
                            data-service-trigger
                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                            aria-controls="service-pane-{{ $pillar['slug'] }}"
                        >
                            <span class="fv-service__index fv-spec">{{ $pillar['index'] }}</span>
                            <span class="fv-service__title">{{ $pillar['title'] }}</span>
                            <span class="fv-service__lede">{{ $pillar['lede'] }}</span>
                            <span class="fv-service__toggle" aria-hidden="true"><span></span><span></span></span>
                        </button>
                        <div
                            class="fv-service__pane{{ $index === 0 ? ' is-current' : '' }}"
                            id="service-pane-{{ $pillar['slug'] }}"
                            data-service-pane
                            aria-hidden="{{ $index === 0 ? 'false' : 'true' }}"
                        >
                            <div class="fv-service__pane-inner">
                                <p class="fv-service__why">{{ $pillar['why'] }}</p>
                                <ul class="fv-service__items">
                                    @foreach ($pillar['items'] as $item)
                                        <li data-item-form="{{ $item['form'] }}">
                                            <span>{{ $item['title'] }}</span>
                                            <small>{{ $item['summary'] }}</small>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="fv-service__foot fv-spec">
                                    <span>{{ $pillar['outcome'] }}</span>
                                    <span>{{ $pillar['note'] }}</span>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <a class="fv-text-link fv-section-link" href="{{ route('services.index') }}">Full breakdown <span>→</span></a>
        </div>
    </section>

    <section class="fv-work" id="work" data-world="disperse" data-theme="light">
        <div class="fv-container">
            <div class="fv-work__head">
                <div>
                    <div class="fv-section-label"><span class="fv-mark" aria-hidden="true"></span><span class="fv-spec">Selected work</span></div>
                    <h2 class="fv-h2">Work that has<br><em>a reason to exist.</em></h2>
                </div>
                <a class="fv-text-link" href="{{ route('work.index') }}">View all <span>→</span></a>
            </div>

            <div class="fv-work__list">
                @forelse ($projects as $index => $project)
                    <a class="fv-project" href="{{ route('projects.show', $project['slug']) }}" data-cursor="view" data-cursor-label="view" aria-label="View {{ $project['name'] }} project">
                        <span class="fv-project__index fv-spec">{{ $project['index'] }}</span>
                        <div class="fv-project__body">
                            <div class="fv-project__meta fv-spec"><span>{{ $project['category'] }}</span><span>{{ $project['year'] }}</span></div>
                            <span class="fv-project__name">{{ $project['name'] }}</span>
                            <span class="fv-project__statement">{{ $project['statement'] }}</span>
                        </div>
                        <div class="fv-project__media">
                            <x-visual.plate
                                :seed="$project['plate']['seed']"
                                :variant="$project['plate']['variant']"
                                :ratio="$project['plate']['ratio']"
                                decorative
                            />
                        </div>
                        @if ($project['result']['value'])
                            <div class="fv-project__result"><span>{{ $project['result']['value'] }}</span><small>{{ $project['result']['label'] }}</small></div>
                        @endif
                    </a>
                @empty
                    <p class="fv-empty">Selected work is being published. <a class="fv-text-link" href="{{ route('contact') }}">Start a project</a> in the meantime.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- caseStudy() returns an empty array when nothing is published, which
         is a state the database source can reach and the files never could. --}}
    @if (! empty($caseStudy['title']))
    <section class="fv-case" id="case-study" data-world="disperse" data-theme="dark">
        <div class="fv-container">
            <div class="fv-case__head">
                <div>
                    <div class="fv-case__eyebrow fv-spec">{{ $caseStudy['eyebrow'] }} · {{ $caseStudy['client'] }} · {{ $caseStudy['duration'] }}</div>
                    <h2 class="fv-h2">{{ $caseStudy['title'] }}</h2>
                    <p>{{ $caseStudy['summary'] }}</p>
                </div>
                <div class="fv-case__metrics">
                    @foreach (array_slice($caseStudy['metrics'], 0, 3) as $metric)
                        <div><b>{{ $metric['value'] }}</b><span class="fv-spec">{{ $metric['label'] }}</span></div>
                    @endforeach
                </div>
            </div>

            <div class="fv-case__story">
                @foreach ($caseStudy['beats'] as $beat)
                    <article class="fv-case-step">
                        <div class="fv-case-step__number fv-spec">{{ $beat['index'] }}</div>
                        <div class="fv-case-step__content">
                            <span class="fv-spec">{{ $beat['label'] }}</span>
                            <h3>{{ $beat['heading'] }}</h3>
                            <p>{{ $beat['body'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section class="fv-studio" id="about" data-world="stair" data-theme="light">
        <div class="fv-container">
            <div class="fv-section-label"><span class="fv-mark" aria-hidden="true"></span><span class="fv-spec">The studio</span></div>
            <div class="fv-studio__grid">
                <h2 class="fv-h2">Built by people who care about the <em>whole thing.</em></h2>
                <div class="fv-studio__copy">
                    @foreach (array_slice($studio['story'], 0, 2) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                    <a class="fv-text-link fv-section-link" href="{{ route('studio') }}">Meet the studio <span>→</span></a>
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
            <div class="fv-process__head">
                <div>
                    <div class="fv-section-label"><span class="fv-mark" aria-hidden="true"></span><span class="fv-spec">How we work</span></div>
                    <h2 class="fv-h2">Less theatre.<br><em>More making.</em></h2>
                </div>
                <p>{{ $process['lede'] }}</p>
            </div>
            <div class="fv-process__track">
                @foreach ($process['stages'] as $stage)
                    <div class="fv-process-step">
                        <span class="fv-spec">{{ $stage['index'] }}</span>
                        <span class="fv-process-step__window fv-spec">{{ $stage['window'] }}</span>
                        <div class="fv-process-step__body"><h3>{{ $stage['title'] }}</h3><p>{{ $stage['body'] }}</p></div>
                        <span class="fv-process-step__output fv-spec">{{ $stage['output'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Every quote can now be unpublished from the CMS, so the chapter has
         to be able to not exist. A section with an empty blockquote in it
         reads worse than no section at all. --}}
    @if (count($testimonials) > 0)
        <section class="fv-proof" id="proof" data-world="stair" data-theme="dark">
            <div class="fv-container">
                <div class="fv-section-label fv-section-label--dark"><span class="fv-mark" aria-hidden="true"></span><span class="fv-spec">Proof</span></div>
                <div class="fv-proof__quote">
                    <blockquote>{{ strip_tags($testimonials[0]['quote']) }}</blockquote>
                    <div class="fv-proof__person"><strong>{{ $testimonials[0]['name'] }}</strong><span>{{ $testimonials[0]['role'] }} · {{ $testimonials[0]['company'] }}</span></div>
                </div>
            </div>
        </section>
    @endif

    <section class="fv-insights" id="insights" data-world="resolve" data-theme="light">
        <div class="fv-container">
            <div class="fv-insights__head">
                <div>
                    <div class="fv-section-label"><span class="fv-mark" aria-hidden="true"></span><span class="fv-spec">Signals</span></div>
                    <h2 class="fv-h2">Notes on products,<br><em>systems &amp; motion.</em></h2>
                </div>
                <a class="fv-text-link" href="{{ route('insights.index') }}">Read the journal <span>→</span></a>
            </div>
            <div class="fv-insights__list">
                @forelse (array_slice($insights, 0, 3) as $insight)
                    <a class="fv-insight" href="{{ route('insights.show', $insight['slug']) }}" data-cursor="open" data-cursor-label="read" aria-label="Read {{ $insight['title'] }}">
                        <span class="fv-insight__meta fv-spec"><span>{{ $insight['category'] }}</span><span>{{ $insight['date_label'] }}</span><span>{{ $insight['reading'] }}</span></span>
                        <div>
                            <h3>{{ $insight['title'] }}</h3>
                            <p>{{ $insight['dek'] }}</p>
                        </div>
                        <span class="fv-insight__cta fv-spec">Read →</span>
                    </a>
                @empty
                    <p class="fv-empty">The journal is being written.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="fv-cta" id="contact" data-world="resolve" data-world-visible data-theme="dark">
        <div class="fv-container fv-cta__inner">
            <div class="fv-section-label fv-section-label--dark"><span class="fv-mark" aria-hidden="true"></span><span class="fv-spec">Start a project</span></div>
            <div class="fv-cta__wrap">
                <h2 class="fv-cta__title fv-plate">Have an idea<br><em>worth building?</em></h2>
                <p>Tell us what you are trying to make or fix. We will help you find the right shape for it — product, system, or both.</p>
                <div class="fv-hero__actions">
                    <a class="fv-btn fv-btn--light" href="{{ route('contact') }}">Start a project <span>→</span></a>
                </div>
            </div>
            <div class="fv-cta__foot fv-spec"><span>FORMIVA</span><span>Digital products &amp; business systems</span><span>{{ $site['contact']['timezone'] }}</span></div>
        </div>
    </section>
@endsection
