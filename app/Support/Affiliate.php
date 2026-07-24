<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Single source of truth for whether the affiliate program is live and how many
 * referrals buy a free month. Everything downstream (middleware, registration
 * hook, webhook listener, Filament page/widget) gates on Affiliate::enabled().
 */
class Affiliate
{
    /**
     * The program runs only when its own switch is on AND premium is a paid
     * product. When premium.enabled is true everyone is premium, so a free-month
     * reward has nothing to give — the whole program goes dormant.
     */
    public static function enabled(): bool
    {
        return (bool) config('affiliate.enabled') && ! (bool) config('premium.enabled');
    }

    /**
     * Qualified referrals required per free month, floored to 1 so the reward
     * maths can never divide by zero.
     */
    public static function referralsPerFreeMonth(): int
    {
        return max(1, (int) config('affiliate.referrals_per_free_month'));
    }
}
