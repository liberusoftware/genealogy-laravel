{{-- bare: this layout supplies its own landmarks, so the shell must not wrap
     them in a second <main>. See App\View\Components\AppLayout. --}}
{{-- $pageTitle / $pageDescription come from the child view's top-level @php block:
     @extends passes get_defined_vars() into this layout. They cannot reach the
     head any other way — <x-app-layout> is a component, so its data is isolated
     and @section never gets there. #1648. --}}
<x-app-layout bare :page-title="$pageTitle ?? null" :page-description="$pageDescription ?? null">
    {{-- Pages with a drenched hero pass fieldHero => true via @extends, so the
         bar joins their field. Everything else gets paper chrome. --}}
    @include('components.home-navbar', ['onField' => $fieldHero ?? false])

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    @include('components.footer')
</x-app-layout>
