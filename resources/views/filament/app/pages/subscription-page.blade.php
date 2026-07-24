<x-filament-panels::page>
    @php
        $pricing = $this->getPricingData()['premium'];
        $selected = $pricing['intervals'][$this->interval] ?? $pricing['intervals']['month'];
    @endphp
    <div class="space-y-8">
        <!-- Premium Features Overview -->
        <div class="bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg p-8 text-white">
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <div class="bg-white/20 rounded-full p-3">
                        @svg('heroicon-o-star', 'h-8 w-8')
                    </div>
                </div>
                <h1 class="text-3xl font-bold mb-2">Upgrade to Premium</h1>
                <p class="text-lg opacity-90 mb-6">Unlock powerful genealogy tools and unlimited features</p>

            </div>
        </div>

        {{-- One plan. There is no free tier (#1635) — the old "Standard · Free
             forever · $0" column advertised a plan that no longer exists. --}}
        <div class="mx-auto w-full max-w-xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Premium</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose how you'd like to be billed.</p>

                    {{-- Every figure below converts with this (#1636). Renders
                         nothing when there are no rates. --}}
                    <x-currency-switcher
                        :amounts="$selected['price_amounts']"
                        :date="$pricing['estimate_date']"
                        class="mt-4"
                    />
                </div>

                {{-- Billing interval. Rows rather than a segmented toggle so each
                     option can state its own per-month equivalent, what it saves
                     and how often it bills — the facts the choice turns on. --}}
                <div class="space-y-2" role="radiogroup" aria-label="Billing interval">
                    @foreach ($pricing['intervals'] as $key => $option)
                        @php $isSelected = $this->interval === $key; @endphp
                        <button
                            type="button"
                            role="radio"
                            aria-checked="{{ $isSelected ? 'true' : 'false' }}"
                            wire:click="$set('interval', '{{ $key }}')"
                            @class([
                                'flex w-full items-center gap-3 rounded-lg border p-4 text-left transition',
                                'border-primary-500 bg-primary-50 dark:bg-primary-500/10' => $isSelected,
                                'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' => ! $isSelected,
                            ])
                        >
                            <span @class([
                                'relative h-4 w-4 shrink-0 rounded-full border-2',
                                'border-primary-600 bg-primary-600' => $isSelected,
                                'border-gray-300 dark:border-gray-600' => ! $isSelected,
                            ])>
                                @if ($isSelected)
                                    <span class="absolute inset-[3px] rounded-full bg-white"></span>
                                @endif
                            </span>

                            <span class="min-w-0">
                                <span class="flex flex-wrap items-center gap-2 font-semibold text-gray-900 dark:text-white">
                                    {{ $key === 'year' ? 'Yearly' : 'Monthly' }}
                                    @isset($option['savings'])
                                        <span class="rounded-full bg-success-100 px-2 py-0.5 text-xs font-bold uppercase tracking-wide text-success-700 dark:bg-success-500/20 dark:text-success-400">
                                            Save <x-price :amounts="$option['savings_amounts']" />
                                        </span>
                                    @endisset
                                </span>
                                <span class="mt-0.5 block text-sm text-gray-500 dark:text-gray-400">
                                    @if ($key === 'year')
                                        <x-price :amounts="$option['price_amounts']" /> billed once a year
                                    @else
                                        Billed every month
                                    @endif
                                </span>
                            </span>

                            <span class="ml-auto shrink-0 text-right tabular-nums">
                                <span class="block font-semibold text-gray-900 dark:text-white"><x-price :amounts="$option['per_month_amounts']" /></span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">per month</span>
                            </span>
                        </button>
                    @endforeach
                </div>

                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    You'll be charged <x-price :amounts="$selected['price_amounts']" /> today, then every {{ $selected['interval'] }}. Cancel anytime.
                </p>

                <ul class="mt-6 space-y-3">
                    @foreach($this->getPricingData()['premium']['features'] as $feature)
                        <li class="flex items-center">
                            @svg('heroicon-o-check', 'h-5 w-5 text-green-500 mr-3')
                            <span class="text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-6">
                    <x-filament::button
                        color="primary"
                        size="lg"
                        class="w-full mb-2"
                        wire:click="redirectToCheckout"
                        wire:target="redirectToCheckout"
                        wire:loading.attr="disabled"
                        aria-label="Subscribe with card"
                    >
                        <span class="inline-flex items-center justify-center">
                            <svg wire:loading wire:target="redirectToCheckout" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="redirectToCheckout">Subscribe {{ ucfirst($selected['interval']) }}ly · <x-price :amounts="$selected['price_amounts']" /></span>
                            <span wire:loading wire:target="redirectToCheckout">Redirecting…</span>
                        </span>
                    </x-filament::button>
                </div>
            </div>
        </div>

        <!-- Current Usage -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Current Usage</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $this->getDnaLimitData()['remaining'] }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        DNA uploads remaining
                    </div>
                    <div class="text-xs text-gray-400 mt-1">
                        Limit: {{ $this->getDnaLimitData()['limit'] }}
                    </div>
                </div>

                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ auth()->user()->dna_uploads_count }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        DNA kits uploaded
                    </div>
                </div>

                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        None
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Current plan
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Frequently Asked Questions</h3>

            <div class="space-y-4">
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white">Can I switch between monthly and yearly?</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Yes. Once you're subscribed you can switch billing interval from the billing portal, and we'll prorate the difference.
                    </p>
                </div>

                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white">Can I cancel anytime?</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Yes, you can cancel your subscription at any time. You'll continue to have access until the end of your billing period.
                    </p>
                </div>

                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white">What payment methods do you accept?</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        We accept all major credit cards through Stripe's secure payment processing.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
