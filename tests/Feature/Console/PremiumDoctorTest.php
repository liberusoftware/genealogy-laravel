<?php

namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * premium:doctor (#1635, ticket 04) reports the funnel's config prerequisites
 * and exits non-zero on any blocking failure, so "is my .env correct??" has a
 * one-command answer.
 */
class PremiumDoctorTest extends TestCase
{
    use RefreshDatabase;

    private function withGoodConfig(): void
    {
        config([
            'premium.enabled' => false,
            'cashier.secret' => 'sk_test_example',
            'cashier.currency' => 'usd',
        ]);
    }

    public function test_passes_when_the_funnel_is_configured(): void
    {
        $this->withGoodConfig();

        // subscription_prices is migrated by RefreshDatabase; amounts default > 0.
        $this->artisan('premium:doctor')->assertExitCode(0);
    }

    public function test_fails_without_a_stripe_secret(): void
    {
        $this->withGoodConfig();
        config(['cashier.secret' => null]);

        $this->artisan('premium:doctor')
            ->expectsOutputToContain('STRIPE_SECRET')
            ->assertExitCode(1);
    }

    public function test_fails_when_premium_is_globally_enabled(): void
    {
        // premium.enabled true makes the subscription page redirect away, so the
        // funnel is unreachable — a blocking misconfiguration for a paywall.
        $this->withGoodConfig();
        config(['premium.enabled' => true]);

        $this->artisan('premium:doctor')->assertExitCode(1);
    }

    public function test_fails_when_a_premium_amount_is_zero(): void
    {
        $this->withGoodConfig();
        config(['subscription.premium.amounts.month' => 0]);

        $this->artisan('premium:doctor')->assertExitCode(1);
    }
}
