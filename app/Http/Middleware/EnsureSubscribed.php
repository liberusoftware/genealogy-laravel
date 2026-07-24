<?php

namespace App\Http\Middleware;

use App\Exceptions\PremiumRequiredException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Paywall gate (upstream #1635, made opt-in by #1638): with
 * `subscription.paywall_enabled` on, the whole `app` panel is subscription-only.
 *
 * A non-subscriber is redirected to the subscription page from every panel
 * surface EXCEPT the ones they need in order to pay or leave. The redirect is
 * driven by PremiumRequiredException + the handler in bootstrap/app.php (reused
 * from #1630) — this middleware just extends the throw from the 8 premium
 * resources to the whole panel.
 *
 * Exempting the redirect target (the subscription page) is what prevents an
 * infinite loop: without it, a non-subscriber sent to /subscription would be
 * gated there too and bounced forever.
 */
class EnsureSubscribed
{
    /**
     * Routes a non-subscriber must still reach: the subscription/buy page (the
     * redirect target — exempting it is the loop guard), the trial-expired page
     * (the handler's other target), tenant creation, and logout.
     */
    private const ALLOWLIST = [
        'filament.app.pages.subscription',
        'filament.app.pages.trial-expired',
        'filament.app.tenant.registration',
        'filament.app.auth.logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Opt-in (#1638). Off, the panel is open and only the per-resource
        // premium gates apply; on, this is the whole paywall.
        if (! config('subscription.paywall_enabled')) {
            return $next($request);
        }

        if (! $request->routeIs(...self::ALLOWLIST)) {
            PremiumRequiredException::unlessPremium();
        }

        return $next($request);
    }
}
