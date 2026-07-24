<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use App\Services\AffiliateRewardService;
use App\Support\Affiliate;
use Laravel\Cashier\Events\WebhookReceived;

/**
 * On a successful Stripe invoice, qualify the paying user's pending referral (if
 * any) and settle the referrer's earned free months. The pending->qualified flip
 * is idempotent, so renewals and webhook replays are no-ops.
 */
class QualifyReferralOnPayment
{
    public function __construct(private readonly AffiliateRewardService $rewards) {}

    public function handle(WebhookReceived $event): void
    {
        if (! Affiliate::enabled()) {
            return;
        }

        if (($event->payload['type'] ?? null) !== 'invoice.payment_succeeded') {
            return;
        }

        $customer = $event->payload['data']['object']['customer'] ?? null;

        if (! is_string($customer)) {
            return;
        }

        $user = User::query()->where('stripe_id', $customer)->first();

        if ($user === null) {
            return;
        }

        $this->rewards->qualifyReferralFor($user);
    }
}
