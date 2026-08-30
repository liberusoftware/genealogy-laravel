<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePremiumAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('premium.enabled', false) || ! auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if ($user->isPremium() || $this->isAllowed($request)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $user->isPremiumSuspended()
                    ? 'Your account is suspended. GEDCOM export and billing remain available.'
                    : 'An active Premium subscription is required.',
            ], Response::HTTP_PAYMENT_REQUIRED);
        }

        return redirect()->route(
            $user->isPremiumSuspended()
                ? 'filament.app.pages.trial-expired'
                : 'filament.app.pages.subscription',
        );
    }

    private function isAllowed(Request $request): bool
    {
        foreach ((array) config('subscription.allowlist_route_patterns', []) as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return $request->is('api/v1/genealogy/import-export/export')
            || $request->is('affiliate', 'affiliate/*');
    }
}
