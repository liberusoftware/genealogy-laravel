<x-filament-panels::page>
    @php($progress = $this->getProgress())

    <div class="space-y-6">
        {{-- Referral link --}}
        <x-filament::section>
            <x-slot name="heading">Your referral link</x-slot>
            <x-slot name="description">Share this link. When someone you refer buys Premium, it counts toward a free month.</x-slot>

            <div x-data="{ copied: false }" class="flex items-center gap-2">
                <input
                    type="text"
                    readonly
                    value="{{ $this->getReferralLink() }}"
                    class="flex-1 rounded-lg border-gray-300 bg-gray-50 text-sm dark:border-gray-700 dark:bg-gray-900"
                    x-on:focus="$el.select()"
                />
                <x-filament::button
                    x-on:click="navigator.clipboard.writeText('{{ $this->getReferralLink() }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    icon="heroicon-o-clipboard"
                >
                    <span x-show="! copied">Copy</span>
                    <span x-show="copied" x-cloak>Copied!</span>
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Progress --}}
        <x-filament::section>
            <x-slot name="heading">Progress to your next free month</x-slot>

            <div class="space-y-3">
                <div class="flex items-baseline justify-between">
                    <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        {{ $progress['toward'] }} / {{ $progress['needed'] }}
                    </span>
                    <span class="text-sm text-gray-500">
                        {{ $progress['free_months'] }} free {{ \Illuminate\Support\Str::plural('month', $progress['free_months']) }} earned
                    </span>
                </div>

                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div
                        class="h-full rounded-full bg-primary-500 transition-all"
                        style="width: {{ $progress['needed'] > 0 ? min(100, round($progress['toward'] / $progress['needed'] * 100)) : 0 }}%"
                    ></div>
                </div>

                <p class="text-sm text-gray-500">
                    {{ $progress['qualified'] }} qualified &middot; {{ $progress['pending'] }} pending
                </p>
            </div>
        </x-filament::section>

        {{-- Referrals --}}
        <x-filament::section>
            <x-slot name="heading">Your referrals</x-slot>

            @if ($this->getReferrals()->isEmpty())
                <p class="text-sm text-gray-500">No referrals yet. Share your link to get started.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="text-left text-gray-500">
                        <tr>
                            <th class="pb-2">Person</th>
                            <th class="pb-2">Joined</th>
                            <th class="pb-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($this->getReferrals() as $referral)
                            <tr>
                                <td class="py-2">{{ $referral->referredUser?->name ?? 'Unknown' }}</td>
                                <td class="py-2">{{ $referral->created_at?->toFormattedDateString() }}</td>
                                <td class="py-2">
                                    <x-filament::badge :color="$referral->status === \App\Models\Referral::STATUS_QUALIFIED ? 'success' : 'gray'">
                                        {{ ucfirst($referral->status) }}
                                    </x-filament::badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-filament::section>

        {{-- Rewards --}}
        @if ($this->getRewards()->isNotEmpty())
            <x-filament::section>
                <x-slot name="heading">Free months earned</x-slot>

                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    @foreach ($this->getRewards() as $reward)
                        <li class="flex justify-between py-2">
                            <span>1 free month &middot; {{ $reward->granted_at?->toFormattedDateString() }}</span>
                            <span class="text-gray-500">
                                {{ $reward->delivery === \App\Models\AffiliateReward::DELIVERY_STRIPE_CREDIT ? 'Applied as billing credit' : 'Added to your access' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
