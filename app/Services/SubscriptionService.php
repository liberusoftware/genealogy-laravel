<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Cashier;
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
        $price = $this->stripePriceId($interval);

        $builder = $user->newSubscription('premium', $price);
        if ($this->trialDays() > 0) {
            $builder->trialDays($this->trialDays());
        }

        return $builder->checkout([
            'success_url' => route('filament.app.pages.premium-dashboard'),
            'cancel_url' => route('filament.app.pages.subscription'),
        ]);
    }

    /**
     * Return the configured Price ID or provision an application-specific Stripe Price.
     *
     * @throws \Throwable
     */
    public function stripePriceId(string $interval): string
    {
        $configured = config('premium.stripe_prices.'.$interval);
        if (is_string($configured) && trim($configured) !== '') {
            return trim($configured);
        }

        if (! in_array($interval, self::INTERVALS, true)) {
            throw new RuntimeException('Unsupported Premium billing interval.');
        }

        $applicationKey = $this->stripeApplicationKey();
        // Serialize both intervals together so simultaneous first requests share one Product.
        $lock = Cache::lock('premium-stripe-prices:'.$applicationKey, 60);

        return $lock->block(30, function () use ($applicationKey, $interval): string {
            $existing = DB::table('premium_stripe_prices')
                ->where('application_key', $applicationKey)
                ->where('interval', $interval)
                ->value('stripe_price_id');

            if (is_string($existing) && $existing !== '') {
                return $existing;
            }

            $amount = (int) config('premium.prices.'.$interval);
            $currency = strtolower((string) config('premium.currency', 'gbp'));
            if ($amount < 1 || strlen($currency) !== 3) {
                throw new RuntimeException('Premium Stripe pricing configuration is invalid.');
            }

            $metadata = [
                'liberu_application' => $applicationKey,
                'liberu_feature' => 'premium',
                'liberu_interval' => $interval,
            ];
            $stripe = Cashier::stripe();
            $productId = DB::table('premium_stripe_prices')
                ->where('application_key', $applicationKey)
                ->value('stripe_product_id');
            if (! is_string($productId) || $productId === '') {
                $product = $stripe->products->create([
                    'name' => $this->stripeProductName(),
                    'metadata' => [
                        'liberu_application' => $applicationKey,
                        'liberu_feature' => 'premium',
                    ],
                ]);
                $productId = $product->id;
            }
            $price = $stripe->prices->create([
                'currency' => $currency,
                'unit_amount' => $amount,
                'recurring' => ['interval' => $interval],
                'product' => $productId,
                'metadata' => $metadata,
            ]);

            DB::table('premium_stripe_prices')->insert([
                'application_key' => $applicationKey,
                'interval' => $interval,
                'stripe_product_id' => $productId,
                'stripe_price_id' => $price->id,
                'amount' => $amount,
                'currency' => $currency,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $price->id;
        });
    }

    public function stripeApplicationKey(): string
    {
        $configured = config('premium.stripe_application_key');
        if (is_string($configured) && trim($configured) !== '') {
            return substr(trim($configured), 0, 128);
        }

        return substr(hash('sha256', config('app.name').'|'.config('app.url')), 0, 32);
    }

    private function stripeProductName(): string
    {
        $configured = config('premium.stripe_product_name');

        return is_string($configured) && trim($configured) !== ''
            ? trim($configured)
            : (string) config('app.name', 'Application').' Premium';
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
