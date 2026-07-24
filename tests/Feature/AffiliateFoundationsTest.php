<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AffiliateReward;
use App\Models\Referral;
use App\Models\User;
use App\Support\Affiliate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateFoundationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_gate_is_on_only_when_affiliate_on_and_premium_paid(): void
    {
        config(['affiliate.enabled' => true, 'premium.enabled' => false]);
        $this->assertTrue(Affiliate::enabled());

        // Everyone premium => nothing to reward => dormant.
        config(['affiliate.enabled' => true, 'premium.enabled' => true]);
        $this->assertFalse(Affiliate::enabled());

        // Switched off explicitly.
        config(['affiliate.enabled' => false, 'premium.enabled' => false]);
        $this->assertFalse(Affiliate::enabled());
    }

    public function test_referrals_per_free_month_reads_config_and_floors_to_one(): void
    {
        config(['affiliate.referrals_per_free_month' => 5]);
        $this->assertSame(5, Affiliate::referralsPerFreeMonth());

        config(['affiliate.referrals_per_free_month' => 0]);
        $this->assertSame(1, Affiliate::referralsPerFreeMonth());
    }

    public function test_referral_code_is_generated_stable_and_unique(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->referral_code);
        $code = $user->referralCode();

        $this->assertNotEmpty($code);
        $this->assertSame($code, $user->fresh()->referralCode(), 'code must persist and be stable');

        $other = User::factory()->create();
        $this->assertNotSame($code, $other->referralCode());
    }

    public function test_relations_resolve(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral = Referral::create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referred->id,
            'status' => Referral::STATUS_PENDING,
        ]);

        $reward = AffiliateReward::create([
            'user_id' => $referrer->id,
            'referrals_consumed' => 5,
            'delivery' => AffiliateReward::DELIVERY_ACCESS_EXTENSION,
            'granted_at' => now(),
        ]);

        $this->assertTrue($referrer->referrals->contains($referral));
        $this->assertTrue($referrer->affiliateRewards->contains($reward));
        $this->assertSame($referral->id, $referred->referredBy?->id);
        $this->assertSame($referrer->id, $referral->referrer->id);
        $this->assertSame($referred->id, $referral->referredUser->id);
    }
}
