<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SubscriptionService $subscriptionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriptionService = new SubscriptionService;
    }

    public function test_create_premium_subscription_without_payment_method_is_rejected(): void
    {
        // The app is subscription-only (#1635): the no-card trial is removed as
        // code, so this path always requires a payment method — even with
        // require_card off, config can no longer re-enable a free grant.
        config()->set('subscription.premium.require_card', false);

        $user = User::factory()->create();

        $this->expectException(\RuntimeException::class);

        try {
            $this->subscriptionService->createPremiumSubscription($user);
        } finally {
            $this->assertFalse((bool) $user->fresh()->is_premium, 'no premium granted without a payment method');
        }
    }

    public function test_get_pricing_info_returns_premium_info(): void
    {
        $pricingInfo = $this->subscriptionService->getPricingInfo();

        $this->assertArrayHasKey('premium', $pricingInfo);
        $this->assertArrayHasKey('name', $pricingInfo['premium']);
        $this->assertArrayHasKey('features', $pricingInfo['premium']);
        $this->assertEquals('Premium', $pricingInfo['premium']['name']);
        $this->assertIsArray($pricingInfo['premium']['features']);
    }

    public function test_pricing_info_derives_per_month_and_yearly_savings(): void
    {
        // $2.99/month vs $29.99/year: $2.50 a month, saving $5.89 (16%) against
        // twelve monthly charges. Derived from the charged amounts, never hardcoded.
        config([
            'subscription.premium.amounts.month' => 299,
            'subscription.premium.amounts.year' => 2999,
        ]);

        $intervals = $this->subscriptionService->getPricingInfo()['premium']['intervals'];

        $this->assertSame('$2.99', $intervals['month']['per_month']);
        $this->assertSame('$2.50', $intervals['year']['per_month']);
        $this->assertSame('$5.89', $intervals['year']['savings']);
        $this->assertSame(16, $intervals['year']['savings_percent']);
        $this->assertArrayNotHasKey('savings', $intervals['month']);
    }

    public function test_pricing_info_omits_savings_when_yearly_saves_nothing(): void
    {
        config([
            'subscription.premium.amounts.month' => 299,
            'subscription.premium.amounts.year' => 3588,
        ]);

        $intervals = $this->subscriptionService->getPricingInfo()['premium']['intervals'];

        $this->assertArrayNotHasKey('savings', $intervals['year']);
    }

    public function test_check_dna_upload_limit_for_non_premium_user(): void
    {
        $user = User::factory()->create(['is_premium' => false]);
        $result = $this->subscriptionService->checkDnaUploadLimit($user);

        $this->assertArrayHasKey('can_upload', $result);
        $this->assertArrayHasKey('limit', $result);
    }

    public function test_get_premium_features_status(): void
    {
        $user = User::factory()->create(['is_premium' => false]);
        $status = $this->subscriptionService->getPremiumFeaturesStatus($user);

        $this->assertIsArray($status);
    }

    public function test_downgrade_to_free_clears_trial(): void
    {
        $user = User::factory()->create([
            'is_premium' => true,
            'trial_ends_at' => now()->addDays(5),
        ]);

        $this->subscriptionService->downgradeToFree($user);

        $user = $user->fresh();
        $this->assertFalse($user->is_premium);
        $this->assertNull($user->trial_ends_at);
    }

    public function test_has_expired_trial_returns_true_when_trial_passed(): void
    {
        // Disable the global premium bypass for this test
        config(['premium.enabled' => false]);

        $user = User::factory()->create([
            'is_premium' => true,
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->assertTrue($user->hasExpiredTrial());
        $this->assertFalse($user->isPremium());
    }

    public function test_has_expired_trial_returns_false_when_trial_active(): void
    {
        config(['premium.enabled' => false]);

        $user = User::factory()->create([
            'is_premium' => true,
            'trial_ends_at' => now()->addDays(10),
        ]);

        $this->assertFalse($user->hasExpiredTrial());
        $this->assertTrue($user->isPremium());
    }
}
