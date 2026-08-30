@php
    $premiumEnabled = (bool) config('premium.enabled', false);
    $githubUrl = 'https://github.com/liberusoftware/genealogy-laravel';
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Liberu Genealogy helps families preserve people, places, records, relationships, and stories in one connected family history.">
    <title>Liberu Genealogy — preserve the connections</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('themes/genealogy/resources/css/app.css')
    @endif
</head>
<body class="genealogy-theme">
    <header class="genealogy-wrap">
        <nav class="genealogy-nav" aria-label="Main navigation">
            <a class="genealogy-mark" href="{{ url('/') }}" aria-label="Liberu Genealogy home">
                <span class="genealogy-mark__icon" aria-hidden="true"><span>✦</span></span>
                <span>Liberu Genealogy</span>
            </a>
            <div class="genealogy-nav__links">
                <a href="#features">Explore</a>
                <a href="#plans">{{ $premiumEnabled ? 'Plans' : 'Open source' }}</a>
                <a href="{{ $githubUrl }}" target="_blank" rel="noopener">GitHub</a>
                @auth
                    <a class="genealogy-button" href="{{ url('/app') }}">Open workspace</a>
                @else
                    @if (Route::has('login')) <a href="{{ route('login') }}">Sign in</a> @endif
                    @if (Route::has('register')) <a class="genealogy-button" href="{{ route('register') }}">Get started</a> @endif
                @endauth
            </div>
        </nav>
    </header>

    <main>
        <section class="genealogy-wrap genealogy-hero">
            <div>
                <span class="genealogy-eyebrow">Family history, thoughtfully connected</span>
                <h1>Keep the people and stories together.</h1>
                <p>Build a living family history from the records you already have. Connect people, relationships, places, evidence, timelines, and memories in a workspace made for careful research.</p>
                <div class="genealogy-actions">
                    @if (Route::has('register')) <a class="genealogy-button" href="{{ route('register') }}">Begin your family history</a> @endif
                    <a class="genealogy-button genealogy-button--quiet" href="{{ $githubUrl }}" target="_blank" rel="noopener">View the project on GitHub</a>
                </div>
            </div>
            <div class="genealogy-hero__art" aria-label="Illustration of connected generations in a family tree" role="img">
                <div class="genealogy-tree-card">
                    <div class="genealogy-node genealogy-node--a">Your story<small>begins here</small></div>
                    <div class="genealogy-node genealogy-node--b">Ada Lovelace<small>1815 — 1852</small></div>
                    <div class="genealogy-node genealogy-node--c">Charles Babbage<small>1791 — 1871</small></div>
                    <div class="genealogy-node genealogy-node--d">A connected family<small>one branch at a time</small></div>
                </div>
            </div>
        </section>

        <section id="features" class="genealogy-section genealogy-section--soft">
            <div class="genealogy-wrap">
                <span class="genealogy-eyebrow">A calmer research workbench</span>
                <h2>From scattered records to a story you can follow.</h2>
                <p class="genealogy-section__intro">Good genealogy is a conversation between sources, people, and places. Liberu keeps those connections visible while leaving room for uncertainty and discovery.</p>
                <div class="genealogy-feature-grid">
                    <article class="genealogy-feature"><span class="genealogy-feature__number">01</span><h3>Build connected trees</h3><p>Record people, families, partnerships, children, events, and relationships without losing the thread.</p></article>
                    <article class="genealogy-feature"><span class="genealogy-feature__number">02</span><h3>Keep evidence close</h3><p>Organise sources, citations, media, places, and research tasks around the people they support.</p></article>
                    <article class="genealogy-feature"><span class="genealogy-feature__number">03</span><h3>Share with care</h3><p>Collaborate in privacy-aware family spaces and bring existing work with GEDCOM and Gramps XML import.</p></article>
                </div>
            </div>
        </section>

        <section id="plans" class="genealogy-section">
            <div class="genealogy-wrap">
                <span class="genealogy-eyebrow">Clear terms, no surprises</span>
                @if ($premiumEnabled)
                    <h2>Premium starts with seven days to explore.</h2>
                    <p class="genealogy-section__intro">This hosted deployment offers a seven-day Premium trial. A payment card is required by default and is charged only after the trial unless you cancel.</p>
                    <div class="genealogy-plan-grid">
                        <article class="genealogy-plan"><span class="genealogy-badge">Monthly</span><div class="genealogy-plan__price">£2.49 <small>/ month</small></div><p>Seven-day Premium trial, then £2.49 monthly.</p></article>
                        <article class="genealogy-plan genealogy-plan--featured"><span class="genealogy-badge">Best value</span><div class="genealogy-plan__price">£24.99 <small>/ year</small></div><p>Seven-day Premium trial, then £24.99 yearly.</p></article>
                        <article class="genealogy-plan"><span class="genealogy-badge">Your data</span><div class="genealogy-plan__price">Always yours</div><p>Cancel during the trial and keep access through the remaining trial period.</p></article>
                    </div>
                @else
                    <h2>Open-source genealogy for everyone.</h2>
                    <p class="genealogy-section__intro">This installation is open and free to use because Premium billing is disabled. Explore the code, run it yourself, and help shape a better way to preserve family history.</p>
                    <div class="genealogy-notice">This is the open-source mode. If a hosted deployment enables Premium, it will clearly show its seven-day trial and paid plan terms instead.</div>
                @endif
            </div>
        </section>
    </main>

    <footer class="genealogy-footer">
        <div class="genealogy-wrap genealogy-footer__inner">
            <span>© {{ now()->year }} Liberu Genealogy</span>
            <a href="{{ $githubUrl }}" target="_blank" rel="noopener">Source code on GitHub</a>
        </div>
    </footer>
</body>
</html>
