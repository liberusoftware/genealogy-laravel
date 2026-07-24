<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Affiliate;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stashes a valid ?ref=CODE affiliate code in a 30-day cookie for a guest, so it
 * survives until they register (bound there by CreateNewUser). No-op when the
 * program is dormant, the visitor is already authenticated, or the code is bogus.
 */
class CaptureReferral
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Affiliate::enabled() && ! $request->user()) {
            $code = $request->query('ref');

            if (is_string($code) && $code !== ''
                && User::query()->where('referral_code', $code)->exists()) {
                Cookie::queue('referral', $code, 60 * 24 * 30);
            }
        }

        return $next($request);
    }
}
