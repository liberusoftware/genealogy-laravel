@php
    $settings = app(\App\Settings\GeneralSettings::class);

    // #1648: shared links previewed blank, with the seeded brand name. Both
    // strings come from settings so a renamed deployment previews as itself;
    // $pageTitle / $pageDescription are the per-page overrides.
    $metaTitle = $pageTitle ?? $settings->site_name;
    $metaDescription = $pageDescription ?? $settings->site_description;
    $metaImage = $ogImage();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="application-name" content="{{ $settings->site_name }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="_token" content="{!! csrf_token() !!}" />
    <title>{{ $metaTitle }}</title>

    {{-- Link previews. og:image must be absolute or the card renders blank —
         AppLayout::ogImage() owns that, and the size hints let a scraper lay the
         card out before it has fetched the file. twitter:site is deliberately
         absent: GeneralSettings holds a profile URL, not an @handle. --}}
    <meta name="description" content="{{ $metaDescription }}">
    <meta property="og:site_name" content="{{ $settings->site_name }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $settings->site_name }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaImage }}">

    {{-- Figtree. Was configured in tailwind.config.js and fetched only on the
         auth layout, so it never actually rendered. See docs/DESIGN.md §3. --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    {{-- Four weights, no more: 400 body, 500 label, 600 headline/title, 800 display.
         800-against-400 is the weight contrast the brand surface needs; 700 is unused. --}}
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,800&display=swap" rel="stylesheet" />

    @vite('resources/css/app.css')
    @stack('styles')
</head>
<body class="flex min-h-full flex-col bg-paper font-sans text-body text-ink antialiased">
    <a href="#main"
       class="sr-only rounded-md bg-ink px-4 py-2 text-label text-paper focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:z-[var(--z-tooltip)]">
        Skip to content
    </a>

    @if ($bare ?? false)
        {{-- The layout owns its own landmarks (header / main / footer). --}}
        {{ $slot ?? '' }}
    @else
        <main id="main" class="flex flex-grow flex-col">
            {{ $slot ?? '' }}
        </main>
    @endif

    @vite('resources/js/app.js')
    @stack('scripts')
</body>
</html>
