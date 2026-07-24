<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Listeners\QualifyReferralOnPayment;
use App\Models\AffiliateReward;
use App\Models\Referral;
use App\Models\User;
use App\Services\AffiliateRewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Events\WebhookReceived;
use Tests\TestCase;

class AffiliateRewardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['affiliate.enabled' => true, 'premium.enabled' => false]);
    }

    /** A service whose only Stripe call is recorded instead of sent. */
    private function fakeCreditService(): AffiliateRewardService
    {
        return new class extends AffiliateRewardService
        {
            /** @var array<int,array{user:int,amount:int}> */
            public array $credited = [];

            protected function creditReferrer(User $referrer, int $amountCents): void
            {
                $this->credited[] = ['user' => $referrer->id, 'amount' => $amountCents];
            }
        };
    }

    private function pendingReferral(User $referrer, User $referred): Referral
    {
        return Referral::create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referred->id,
            'status' => Referral::STATUS_PENDING,
        ]);
    }

    private function seedQualified(User $referrer, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            Referral::create([
                'referrer_id' => $referrer->id,
                'referred_user_id' => User::factory()->create()->id,
                'status' => Referral::STATUS_QUALIFIED,
                'qualified_at' => now(),
            ]);
        }
    }

    public function test_dormant_program_does_not_qualify(): void
    {
        config(['premium.enabled' => true]); // everyone premium => dormant
        $referral = $this->pendingReferral(User::factory()->create(), $referred = User::factory()->create());

        $this->fakeCreditService()->qualifyReferralFor($referred);

        $this->assertSame(Referral::STATUS_PENDING, $referral->fresh()->status);
    }

    public function test_qualify_flips_pending_and_is_idempotent(): void
    {
        config(['affiliate.referrals_per_free_month' => 5]);
        $referrer = User::factory()->create();
        $referred = User::factory()->create();
        $this->pendingReferral($referrer, $referred);

        $service = $this->fakeCreditService();
        $service->qualifyReferralFor($referred);
        $service->qualifyReferralFor($referred); // replay / renewal

        $this->assertDatabaseCount('referrals', 1);
        $this->assertSame(Referral::STATUS_QUALIFIED, Referral::first()->status);
        $this->assertNotNull(Referral::first()->qualified_at);
        $this->assertDatabaseCount('affiliate_rewards', 0); // 1 qualified < 5
    }

    public function test_threshold_grants_one_free_month_via_access_extension(): void
    {
        config(['affiliate.referrals_per_free_month' => 2]);
        $referrer = User::factory()->create(['trial_ends_at' => null, 'is_premium' => false]);
        $this->seedQualified($referrer, 1); // one already qualified

        $referred = User::factory()->create();
        $this->pendingReferral($referrer, $referred);
        $this->fakeCreditService()->qualifyReferralFor($referred); // now 2 unconsumed

        $this->assertDatabaseCount('affiliate_rewards', 1);
        $reward = AffiliateReward::first();
        $this->assertSame(AffiliateReward::DELIVERY_ACCESS_EXTENSION, $reward->delivery);
        $this->assertSame(2, $reward->referrals_consumed);
        $this->assertNull($reward->amount_cents);

        $referrer->refresh();
        $this->assertTrue($referrer->trial_ends_at?->greaterThan(now()->addDays(29)));
        $this->assertTrue($referrer->isPremium());
    }

    public function test_backlog_crossing_two_thresholds_grants_two(): void
    {
        config(['affiliate.referrals_per_free_month' => 2]);
        $referrer = User::factory()->create(['trial_ends_at' => null, 'is_premium' => false]);
        $this->seedQualified($referrer, 4);

        $this->fakeCreditService()->settleRewards($referrer);

        $this->assertDatabaseCount('affiliate_rewards', 2);
        $this->assertSame(4, (int) $referrer->affiliateRewards()->sum('referrals_consumed'));
        // Two 30-day extensions stack from now.
        $this->assertTrue($referrer->fresh()->trial_ends_at?->greaterThan(now()->addDays(59)));
    }

    public function test_subscriber_referrer_gets_stripe_credit(): void
    {
        config(['affiliate.referrals_per_free_month' => 1]);
        $referrer = User::factory()->create(['trial_ends_at' => null]);
        $referrer->subscriptions()->create([
            'type' => 'premium',
            'stripe_id' => 'sub_test123',
            'stripe_status' => 'active',
            'stripe_price' => 'price_test',
            'quantity' => 1,
        ]);
        $this->assertTrue($referrer->fresh()->subscribed('premium'));

        $service = $this->fakeCreditService();
        $this->seedQualified($referrer, 1);
        $service->settleRewards($referrer);

        $month = (int) config('subscription.premium.amounts.month');
        $this->assertSame([['user' => $referrer->id, 'amount' => $month]], $service->credited);

        $reward = AffiliateReward::first();
        $this->assertSame(AffiliateReward::DELIVERY_STRIPE_CREDIT, $reward->delivery);
        $this->assertSame($month, $reward->amount_cents);
        $this->assertSame(1, $reward->referrals_consumed);
        $this->assertNull($referrer->fresh()->trial_ends_at); // access window untouched
    }

    public function test_listener_qualifies_on_invoice_paid_webhook(): void
    {
        config(['affiliate.referrals_per_free_month' => 5]);
        $referrer = User::factory()->create();
        $referred = User::factory()->create();
        $referred->forceFill(['stripe_id' => 'cus_ref'])->save();
        $referral = $this->pendingReferral($referrer, $referred);

        $payload = ['type' => 'invoice.payment_succeeded', 'data' => ['object' => ['customer' => 'cus_ref']]];
        (new QualifyReferralOnPayment(app(AffiliateRewardService::class)))->handle(new WebhookReceived($payload));

        $this->assertSame(Referral::STATUS_QUALIFIED, $referral->fresh()->status);
    }
}
