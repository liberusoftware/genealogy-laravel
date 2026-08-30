<x-filament-panels::page>
    <div class="space-y-6">
        <p>Your Premium access is active.</p>
        @if (auth()->user()->onPremiumTrial())
            <p>You have {{ auth()->user()->trialDaysRemaining() }} trial days remaining.</p>
        @endif
        @if (auth()->user()->premium_cancelled_at)
            <p>Your subscription is cancelled and access will remain available until the current trial or billing period ends.</p>
            <x-filament::button wire:click="resume">Resume subscription</x-filament::button>
        @else
            <x-filament::button color="danger" wire:click="cancel">Cancel subscription</x-filament::button>
        @endif
    </div>
</x-filament-panels::page>
