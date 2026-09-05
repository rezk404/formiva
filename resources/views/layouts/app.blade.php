<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <title>{{ $title ?? $site['meta']['title'] }}</title>
    <meta name="description" content="{{ $description ?? $site['meta']['description'] }}">
    <meta name="keywords" content="{{ $site['meta']['keywords'] }}">
    <meta name="author" content="{{ $site['brand']['name'] }}">
    <meta name="theme-color" content="{{ $site['meta']['theme_color'] }}">
    <meta name="color-scheme" content="dark light">

    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph. The card artwork is drawn with the same slab system as
         the site itself, so a shared link already looks like the place it
         points at. --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $site['brand']['name'] }}">
    <meta property="og:title" content="{{ $title ?? $site['meta']['title'] }}">
    <meta property="og:description" content="{{ $description ?? $site['meta']['description'] }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ $site['meta']['locale'] }}">
    <meta property="og:image" content="{{ asset('og.svg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="FORMIVA — Ideas take form.">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? $site['meta']['title'] }}">
    <meta name="twitter:description" content="{{ $description ?? $site['meta']['description'] }}">
    <meta name="twitter:image" content="{{ asset('og.svg') }}">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="mask-icon" href="{{ asset('favicon.svg') }}" color="{{ $site['meta']['theme_color'] }}">

    {{-- Archivo carries a width axis, which the display sizes use to keep
         very large headlines from dissolving into a wall. IBM Plex Mono is
         the annotation voice: labels, indices, metadata. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&family=IBM+Plex+Mono:wght@400;500&display=swap"
    >

    {{-- Paint the page ink before any stylesheet resolves. Without this the
         first frame is a white flash under a dark hero. --}}
    <style>html{background-color:#0b0b0c}</style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ProfessionalService',
            'name' => $site['brand']['name'],
            'description' => $site['meta']['description'],
            'url' => url('/'),
            'email' => $site['contact']['email'],
            'telephone' => $site['contact']['phone'],
            'foundingDate' => $site['brand']['founded'],
            'slogan' => $site['brand']['tagline'],
        ];
    @endphp

    <script type="application/ld+json">
        @json($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    </script>
</head>

<body>
    <a class="skip" href="#main">Skip to content</a>

    <x-world />

    <x-navbar :site="$site" />
    <x-mobile-menu :site="$site" />

    <main class="shell" id="main">
        @yield('content')
    </main>

    <x-footer :site="$site" />


    {{-- One fixed grain layer over the whole page. The quietest element on
         the site, and the one doing most of the work to stop flat colour
         reading as screen-native. --}}
    <div class="grain" aria-hidden="true"></div>

    {{-- The world is enhancement. If script never runs, this ensures the
         canvas holder cannot sit as an empty black box over the hero. --}}
    <noscript>
        <style>
            .world { display: none; }
            .hero, .cta, .services { background-color: #0b0b0c; }
            .veil { display: none; }
            [data-reveal] { opacity: 1 !important; }
        </style>
    </noscript>
</body>
</html>
