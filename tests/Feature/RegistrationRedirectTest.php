<?php

namespace Tests\Feature;

use App\Http\Responses\Auth\RegisterResponse;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Laravel\Fortify\Features;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Tests\TestCase;

/**
 * Subscription-only registration (#1635, ticket 07): every new account is sent
 * to checkout after registering — there is no free tier, so registration leads
 * straight to the subscription page. A user who already has access (e.g. an
 * affiliate comp) goes to the app instead.
 *
 * RegisterResponse is tested directly: Fortify's POST /register route is not
 * registered under the test env (Filament owns auth).
 */
class RegistrationRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        config(['premium.enabled' => false]);
    }

    public function test_a_new_account_is_sent_to_checkout(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['is_premium' => false, 'trial_ends_at' => null]);

        $this->assertStringContainsString('/subscription', $this->responseFor($user)->getTargetUrl());
    }

    public function test_a_user_with_access_is_sent_to_the_app(): void
    {
        // e.g. an affiliate-comped account: already premium, so no forced checkout.
        $user = User::factory()->withPersonalTeam()->create(['stripe_id' => 'cus_x']);
        $user->subscriptions()->create([
            'type' => 'premium',
            'stripe_id' => 'sub_x',
            'stripe_status' => 'active',
            'stripe_price' => 'price_premium_monthly',
            'quantity' => 1,
        ]);

        $this->assertStringNotContainsString('/subscription', $this->responseFor($user)->getTargetUrl());
    }

    private function responseFor(User $user): RedirectResponse
    {
        $this->actingAs($user);
        Filament::setTenant($user->currentTeam, isQuiet: true);

        $request = Request::create('/register', 'POST');
        $request->setLaravelSession(app('session.store'));

        return (new RegisterResponse)->toResponse($request);
    }
}
