<x-filament-panels::page>
    <div class="space-y-4">
        <h2 class="text-xl font-semibold">Your Premium access is suspended</h2>
        <p>Your genealogy data remains safe. GEDCOM export, affiliate pages, and billing remain available while you choose a plan.</p>
        <x-filament::button tag="a" href="{{ route('filament.app.pages.subscription') }}">Choose a Premium plan</x-filament::button>
    </div>
</x-filament-panels::page>
