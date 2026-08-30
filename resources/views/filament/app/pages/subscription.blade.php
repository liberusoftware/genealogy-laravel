<x-filament-panels::page>
    <div class="space-y-6">
        <p>Premium includes the full genealogy workspace with a {{ $this->getPricingData()['trial_days'] }}-day trial.</p>
        @if ($this->getPricingData()['require_card'])
            <p>Your card is collected securely by Stripe and is charged when the trial ends unless you cancel.</p>
        @else
            <p>No card is required for the trial. Add a payment method before the trial ends to continue Premium.</p>
        @endif
        <div class="grid gap-4 md:grid-cols-2">
            <x-filament::section heading="Monthly">
                <p class="text-2xl font-semibold">£2.49 / month</p>
                <x-filament::button wire:click="subscribe" wire:loading.attr="disabled">Start monthly trial</x-filament::button>
            </x-filament::section>
            <x-filament::section heading="Yearly">
                <p class="text-2xl font-semibold">£24.99 / year</p>
                <x-filament::button wire:click="subscribeYearly" wire:loading.attr="disabled">Start yearly trial</x-filament::button>
            </x-filament::section>
        </div>
        @unless ($this->getPricingData()['require_card'])
            <x-filament::button color="gray" wire:click="startTrial" wire:loading.attr="disabled">Start trial without a card</x-filament::button>
        @endunless
    </div>
</x-filament-panels::page>
