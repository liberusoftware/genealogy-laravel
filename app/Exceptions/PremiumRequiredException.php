<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown by a premium Filament surface's canAccess() when the current user may
 * not use premium features. A single handler in bootstrap/app.php renders it as
 * a redirect to the sign-up path (upstream #1630) instead of a bare 403 — so a
 * non-premium user who reaches a gated URL is sent to buy, not stonewalled.
 *
 * It is a *distinct* exception on purpose: plain HttpException 403s (tenant/team
 * boundaries like TeamMembers / TreePrivacy) must keep 403ing, so the handler
 * keys on this type, not on the 403 status. See the payment-gate map decision.
 *
 * canAccess() stays a bool for premium users (returns true) — the throw only
 * replaces the false branch, and nav never reaches it because
 * shouldRegisterNavigation() already hides the item from non-premium users.
 */
class PremiumRequiredException extends Exception
{
    /**
     * Throw unless the current user may use premium features. Mirrors the
     * canonical gate: the global switch, or the user's own premium status.
     */
    public static function unlessPremium(): void
    {
        if (config('premium.enabled')) {
            return;
        }

        if (auth()->user()?->isPremium() ?? false) {
            return;
        }

        throw new self;
    }
}
