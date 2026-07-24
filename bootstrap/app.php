<?php

use App\Exceptions\PremiumRequiredException;
use App\Http\Middleware\CaptureReferral;
use App\Http\Middleware\SecurityHeaders;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

if (! class_exists(Form::class) && class_exists(Schema::class)) {
    class_alias(Schema::class, Form::class);
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // Register the /broadcasting/auth route and load routes/channels.php so the
    // private-channel authorization (e.g. research-space.{id}) actually runs.
    // Enforcement still requires a real driver (BROADCAST_DRIVER=reverb — the env
    // key config/broadcasting.php reads — plus a running Reverb server).
    ->withBroadcasting(__DIR__.'/../routes/channels.php')
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SecurityHeaders::class);
        // Capture ?ref=CODE affiliate links into a cookie for guests (issue #1621).
        $middleware->web(append: CaptureReferral::class);
        $middleware->validateCsrfTokens(except: [
            'stripe/*',
        ]);
        $middleware->statefulApi();
        // api RateLimiter lives in RouteServiceProvider, which is never registered;
        // inline 60/min throttle avoids that dependency (mirrors its perMinute(60)).
        $middleware->throttleApi('60,1');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // A non-premium user who reaches a gated premium surface is redirected
        // to sign-up rather than shown a bare 403 (upstream #1630). Keyed on the
        // exception type, so tenant/team 403s (TeamMembers, TreePrivacy) fall
        // through untouched. Target mirrors PremiumDashboardPage::mount().
        $exceptions->render(function (PremiumRequiredException $e) {
            $user = auth()->user();

            if (! $user) {
                return new Response('Forbidden', 403);
            }

            $route = $user->hasExpiredTrial()
                ? 'filament.app.pages.trial-expired'
                : 'filament.app.pages.subscription';

            // Build the RedirectResponse directly: the redirect() helper resolves
            // Livewire's Redirector inside a Livewire request, which is not a
            // Symfony Response and breaks the panel's typed middleware.
            return new RedirectResponse(route($route, ['tenant' => $user->currentTeam]));
        });
    })->create();
