@php
    /*
     | The workspace map.
     |
     | Four groups in the order a studio actually works: what is happening,
     | what goes on the site, who the studio is, and the machinery underneath.
     | Each entry declares the policy that gates it, so a link is rendered
     | exactly when the route behind it would answer — an editor never sees a
     | door that would 403.
     */
    $sections = [
        'Overview' => [
            ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'ability' => null],
        ],
        'Content' => [
            ['route' => 'admin.services.index', 'match' => 'admin.services.*', 'label' => 'Services', 'icon' => 'services', 'ability' => ['viewAny', \App\Models\Service::class]],
            ['route' => 'admin.insights.index', 'match' => 'admin.insights.*', 'label' => 'Insights', 'icon' => 'insights', 'ability' => ['viewAny', \App\Models\Insight::class]],
        ],
        'Work' => [
            ['route' => 'admin.projects.index', 'match' => 'admin.projects.*', 'label' => 'Projects', 'icon' => 'projects', 'ability' => ['viewAny', \App\Models\Project::class]],
        ],
        /*
         | About is one entry with five screens, not five entries. The story,
         | the people, the process, the proof and the numbers all answer the
         | same question, and splitting them across the sidebar made a writer
         | choose a database table before they could choose a sentence.
         */
        'About' => [
            ['route' => 'admin.about.overview', 'match' => 'admin.about.overview*', 'label' => 'Overview', 'icon' => 'studio', 'ability' => ['viewAny', \App\Models\StudioPosition::class]],
            ['route' => 'admin.team.index', 'match' => 'admin.team.*', 'label' => 'Team', 'icon' => 'team', 'ability' => ['viewAny', \App\Models\TeamMember::class]],
            ['route' => 'admin.process.index', 'match' => 'admin.process.*', 'label' => 'Process', 'icon' => 'process', 'ability' => ['viewAny', \App\Models\ProcessStage::class]],
            ['route' => 'admin.testimonials.index', 'match' => 'admin.testimonials.*', 'label' => 'Testimonials', 'icon' => 'testimonials', 'ability' => ['viewAny', \App\Models\Testimonial::class]],
            ['route' => 'admin.about.stats', 'match' => 'admin.about.stats*', 'label' => 'Stats', 'icon' => 'stats', 'ability' => ['viewAny', \App\Models\StudioStat::class]],
        ],
        'Configuration' => [
            ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'label' => 'Categories', 'icon' => 'categories', 'ability' => ['viewAny', \App\Models\Category::class]],
            ['route' => 'admin.intake.index', 'match' => 'admin.intake.*', 'label' => 'Intake options', 'icon' => 'intake', 'ability' => ['viewAny', \App\Models\IntakeOption::class]],
            ['route' => 'admin.settings.edit', 'match' => 'admin.settings.*', 'label' => 'Site settings', 'icon' => 'settings', 'ability' => ['viewAny', \App\Models\Setting::class]],
        ],
        'System' => [
            ['route' => 'admin.profile.edit', 'match' => 'admin.profile.*', 'label' => 'Profile', 'icon' => 'profile', 'ability' => null],
            ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'label' => 'Users', 'icon' => 'users', 'ability' => ['viewAny', \App\Models\User::class]],
        ],
    ];

    $visible = [];

    foreach ($sections as $group => $links) {
        $allowed = array_values(array_filter(
            $links,
            fn (array $link): bool => $link['ability'] === null
                || \Illuminate\Support\Facades\Gate::allows($link['ability'][0], $link['ability'][1]),
        ));

        if ($allowed !== []) {
            $visible[$group] = $allowed;
        }
    }

    $crumbs = $breadcrumbs ?? [];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Workspace — FORMIVA' }}</title>

    {{-- The same two faces as the public site: Archivo for voice, IBM Plex
         Mono for annotation. The workspace is the studio at work, not a
         different company. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap"
    >

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="admin-body" data-admin-app>
    <a class="admin-skip" href="#workspace">Skip to content</a>

    <div class="admin-shell">
        <aside class="admin-sidebar" data-sidebar>
            {{-- Brand and disclosure sit on the same row so that on a phone
                 the panel opens directly under the control that opened it,
                 rather than above a button stranded in the bar below. --}}
            <div class="admin-sidebar__bar">
                <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                    <span class="admin-brand__mark" aria-hidden="true">F</span>
                    <span>
                        <span class="admin-brand__name">FORMIVA</span>
                        <span class="admin-brand__sub">Workspace</span>
                    </span>
                </a>

                <button
                    type="button"
                    class="admin-nav-toggle"
                    data-nav-toggle
                    aria-expanded="false"
                    aria-controls="workspace-nav"
                >
                    <span class="admin-nav-toggle__lines" aria-hidden="true"></span>
                    Menu
                </button>
            </div>

            <div class="admin-sidebar__panel" id="workspace-nav">
                <nav class="admin-nav" aria-label="Workspace">
                    @foreach ($visible as $group => $links)
                        <div class="admin-nav__group">
                            <h2 class="admin-nav__label">{{ $group }}</h2>
                            @foreach ($links as $link)
                                @php
                                    // Matched against a declared pattern rather than one derived
                                    // from the route name: "admin.about.*" would light Overview
                                    // and Stats at the same time, and two active items in a
                                    // sidebar is worse than none.
                                    $isActive = request()->routeIs($link['match']);
                                @endphp
                                <a
                                    class="admin-nav__link {{ $isActive ? 'is-active' : '' }}"
                                    href="{{ route($link['route']) }}"
                                    @if ($isActive) aria-current="page" @endif
                                >
                                    <x-admin.icon :name="$link['icon']" />
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </nav>

                <div class="admin-sidebar__foot">
                    <a href="{{ url('/') }}" target="_blank" rel="noopener">
                        <x-admin.icon name="external" /> View the public site
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="admin-btn admin-btn--quiet admin-btn--sm">Sign out</button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <nav class="admin-topbar__crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}">Workspace</a>
                    @foreach ($crumbs as $crumb)
                        <span class="admin-topbar__sep" aria-hidden="true">/</span>
                        @if (! empty($crumb['url']) && ! $loop->last)
                            <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                        @else
                            <span aria-current="page">{{ $crumb['label'] }}</span>
                        @endif
                    @endforeach
                </nav>

                <div class="admin-topbar__tools">
                    <button type="button" class="admin-command-trigger" data-command-open aria-haspopup="dialog">
                        <x-admin.icon name="search" />
                        <span>Search workspace</span>
                        <kbd>Ctrl K</kbd>
                    </button>
                    <div class="admin-topbar__user">
                    <span class="admin-topbar__identity">
                        <b>{{ auth()->user()->name }}</b>
                        <span>{{ auth()->user()->role->label() }}</span>
                    </span>
                    </div>
                </div>
            </header>

            <main class="admin-content" id="workspace">
                @if ($errors->any())
                    <x-admin.alert tone="error" title="That could not be saved.">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-admin.alert>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <div class="admin-toast-stack" data-toast-stack aria-live="polite" aria-atomic="false">
        @if (session('status'))
            <x-admin.toast tone="success" :message="session('status')" />
        @endif
        @if ($errors->any())
            <x-admin.toast tone="error" message="Please check the highlighted fields." />
        @endif
    </div>

    <dialog class="admin-command" data-command-dialog aria-labelledby="command-title">
        <div class="admin-command__head">
            <label id="command-title" class="admin-sr" for="command-input">Search workspace</label>
            <x-admin.icon name="search" />
            <input id="command-input" type="search" placeholder="Search workspace..." autocomplete="off" data-command-input>
            <kbd>ESC</kbd>
        </div>
        <div class="admin-command__results" data-command-results>
            <p class="admin-command__label">Navigate</p>
            @foreach ($visible as $group => $links)
                @foreach ($links as $link)
                    <a class="admin-command__item" href="{{ route($link['route']) }}" data-command-item data-command-search="{{ $link['label'].' '.$group }}">
                        <x-admin.icon :name="$link['icon']" />
                        <span>{{ $link['label'] }}</span>
                        <small>{{ $group }}</small>
                    </a>
                @endforeach
            @endforeach
            <p class="admin-command__label">Create</p>
            @foreach ([
                ['route' => 'admin.projects.create', 'label' => 'New project', 'icon' => 'projects'],
                ['route' => 'admin.services.create', 'label' => 'New service', 'icon' => 'services'],
                ['route' => 'admin.insights.create', 'label' => 'New insight', 'icon' => 'insights'],
            ] as $command)
                @if (Route::has($command['route']))
                    <a class="admin-command__item" href="{{ route($command['route']) }}" data-command-item data-command-search="{{ $command['label'] }}">
                        <x-admin.icon :name="$command['icon']" />
                        <span>{{ $command['label'] }}</span>
                        <small>Create</small>
                    </a>
                @endif
            @endforeach
            <p class="admin-command__empty" data-command-empty hidden>No matching command.</p>
        </div>
    </dialog>

    {{-- One dialog for the whole workspace. Each destructive button fills it
         with its own question before it opens. --}}
    <dialog class="admin-dialog" data-confirm-dialog aria-labelledby="confirm-title">
        <form method="dialog">
            <div class="admin-dialog__body">
                <h2 id="confirm-title" data-confirm-title>Are you sure?</h2>
                <p data-confirm-body></p>
            </div>
            <div class="admin-dialog__actions">
                <button type="submit" value="cancel" class="admin-btn admin-btn--ghost">Cancel</button>
                <button type="submit" value="accept" class="admin-btn admin-btn--danger" data-confirm-accept>Delete</button>
            </div>
        </form>
    </dialog>
</body>
</html>
