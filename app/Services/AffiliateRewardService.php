<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AffiliateReward;
use App\Models\Referral;
use App\Models\User;
use App\Support\Affiliate;
use Illuminate\Support\Carbon;

/**
 * Turns a referred user's first successful premium payment into a qualified
 * referral, and settles any free months the referrer has now earned.
 *
 * "Free months owed" = qualified referrals minus referrals already consumed by
 * granted rewards (the ledger is the source of truth — no separate counter to
 * drift). Delivery is hybrid: Stripe balance credit for a subscriber, a 30-day
 * access extension otherwise.
 */
class AffiliateRewardService
{
    /**
     * Qualify the referred user's pending referral (idempotent — only a pending
     * row flips, so replays and renewals are no-ops), then settle rewards.
     */
    public function qualifyReferralFor(User $referred): void
    {
        if (! Affiliate::enabled()) {
            return;
        }

        $referral = Referral::query()
            ->where('referred_user_id', $referred->id)
            ->where('status', Referral::STATUS_PENDING)
            ->first();

        if ($referral === null) {
            return;
        }

        $referral->update([
            'status' => Referral::STATUS_QUALIFIED,
            'qualified_at' => now(),
        ]);

        if ($referral->referrer !== null) {
            $this->settleRewards($referral->referrer);
        }
    }

    /**
     * Grant one free month per N unconsumed qualified referrals, looping so a
     * backlog crossing several thresholds at once is fully settled.
     */
    public function settleRewards(User $referrer): void
    {
        $perMonth = Affiliate::referralsPerFreeMonth();

        while ($this->unconsumedQualified($referrer) >= $perMonth) {
            $reward = $this->deliver($referrer);
            $reward->referrals_consumed = $perMonth;
            $referrer->affiliateRewards()->save($reward);
        }
    }

    /**
     * Qualified referrals not yet paid out by a reward.
     */
    private function unconsumedQualified(User $referrer): int
    {
        $qualified = $referrer->referrals()
            ->where('status', Referral::STATUS_QUALIFIED)
            ->count();

        $consumed = (int) $referrer->affiliateRewards()->sum('referrals_consumed');

        return $qualified - $consumed;
    }

    /**
     * Build (unsaved) the reward row and perform its side effect. A Stripe
     * subscriber is credited one month's price (Stripe applies it to the next
     * invoice); everyone else has premium access extended 30 days.
     */
    private function deliver(User $referrer): AffiliateReward
    {
        if ($referrer->subscribed('premium')) {
            $amount = (int) config('subscription.premium.amounts.month');
            $this->creditReferrer($referrer, $amount);

            return new AffiliateReward([
                'delivery' => AffiliateReward::DELIVERY_STRIPE_CREDIT,
                'amount_cents' => $amount,
                'granted_at' => now(),
            ]);
        }

        // Non-subscriber: extend the local access window, keeping any unused
        // future time (stack, don't shrink) and ensuring isPremium() is true.
        $current = $referrer->trial_ends_at !== null ? Carbon::parse($referrer->trial_ends_at) : null;
        $from = ($current !== null && $current->isFuture()) ? $current : now();

        $referrer->forceFill([
            'trial_ends_at' => $from->copy()->addDays(30),
            'is_premium' => true,
        ])->save();

        return new AffiliateReward([
            'delivery' => AffiliateReward::DELIVERY_ACCESS_EXTENSION,
            'amount_cents' => null,
            'granted_at' => now(),
        ]);
    }

    /**
     * The single Stripe-touching call, isolated so tests can override it without
     * hitting the API. creditBalance records a customer balance credit that
     * Stripe auto-applies to the next invoice.
     */
    protected function creditReferrer(User $referrer, int $amountCents): void
    {
        $referrer->creditBalance($amountCents, 'Affiliate reward: 1 free month (#1621)');
    }
}
