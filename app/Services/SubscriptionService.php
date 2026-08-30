<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use RuntimeException;

final class SubscriptionService
{
    public const INTERVALS = ['month', 'year'];

    public function enabled(): bool
    {
        return (bool) config('premium.enabled', false);
    }

    public function requiresCard(): bool
    {
        return (bool) config('premium.require_card', true);
    }

    public function trialDays(): int
    {
        return max(0, (int) config('premium.trial_days', 7));
    }

    /** @return array<string, mixed> */
    public function pricing(): array
    {
        return [
            'currency' => strtoupper((string) config('premium.currency', 'gbp')),
            'trial_days' => $this->trialDays(),
            'require_card' => $this->requiresCard(),
            'monthly' => ['amount' => (int) config('premium.prices.month', 249), 'display' => '£2.49'],
            'yearly' => ['amount' => (int) config('premium.prices.year', 2499), 'display' => '£24.99'],
        ];
    }

    public function startLocalTrial(User $user): void
    {
        if (! $this->enabled() || $this->requiresCard()) {
            throw new RuntimeException('A payment card is required for this premium trial.');
        }

        $user->forceFill([
            'is_premium' => true,
            'premium_started_at' => $user->premium_started_at ?? now(),
            'trial_ends_at' => $user->trial_ends_at ?? now()->addDays($this->trialDays()),
        ])->save();
    }

    public function createCheckout(User $user, string $interval = 'month'): mixed
    {
        if (! $this->enabled()) {
            throw new RuntimeException('Premium billing is disabled.');
        }

        $interval = in_array($interval, self::INTERVALS, true) ? $interval : 'month';
        $price = config('premium.stripe_prices.'.$interval);
        if (! is_string($price) || trim($price) === '') {
            throw new RuntimeException('Stripe price IDs are not configured for Premium.');
        }

        $builder = $user->newSubscription('premium', $price);
        if ($this->trialDays() > 0) {
            $builder->trialDays($this->trialDays());
        }

        return $builder->checkout([
            'success_url' => route('filament.app.pages.premium-dashboard'),
            'cancel_url' => route('filament.app.pages.subscription'),
        ]);
    }

    public function cancel(User $user): void
    {
        $subscription = $user->subscription('premium');
        if ($subscription !== null && ! $subscription->canceled()) {
            $subscription->cancel();
        }

        $user->forceFill(['premium_cancelled_at' => now()])->save();
    }

    public function resume(User $user): void
    {
        $subscription = $user->subscription('premium');
        $isResumable = $subscription?->onGracePeriod() ?? false;
        if ($isResumable) {
            $subscription->resume();
        }

        if ($subscription === null || $isResumable) {
            $user->forceFill(['premium_cancelled_at' => null, 'is_premium' => true])->save();
        }
    }
}
