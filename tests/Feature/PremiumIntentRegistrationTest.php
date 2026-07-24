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
 * The guest-to-paid funnel (upstream #1635, ticket 01): a signup that comes in
 * through the premium CTA must land on the subscription checkout page, not the
 * app dashboard. Intent rides as `?plan=premium` on the register link, is
 * stashed in the session by the /register route, and is consumed by
 * RegisterResponse after the account is created.
 *
 * The two halves are tested at their seams: the GET route (intent capture) via
 * HTTP, and RegisterResponse (intent consumption) directly — Fortify's POST
 * /register route is not registered under the test env (Filament owns auth),
 * so the account-creation step can't be driven over HTTP here.
 */
class PremiumIntentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }
    }

    public function test_register_link_with_premium_plan_stashes_intent(): void
    {
        $this->get('/register?plan=premium')
            ->assertRedirect('/app/register')
            ->assertSessionHas('premium_intent', true);
    }

    public function test_register_link_without_plan_does_not_stash_intent(): void
    {
        $this->get('/register')
            ->assertRedirect('/app/register')
            ->assertSessionMissing('premium_intent');
    }

    public function test_register_response_sends_premium_intent_to_checkout(): void
    {
        $response = $this->responseFor(premiumIntent: true);

        $this->assertStringContainsString('/subscription', $response->getTargetUrl());
    }

    public function test_register_response_without_intent_lands_on_app(): void
    {
        // Revert guard: without the flag, registration must NOT divert to
        // checkout — the pre-existing landing is preserved.
        $response = $this->responseFor(premiumIntent: false);

        $this->assertStringNotContainsString('/subscription', $response->getTargetUrl());
    }

    private function responseFor(bool $premiumIntent): RedirectResponse
    {
        $user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($user);
        Filament::setTenant($user->currentTeam, isQuiet: true);

        $request = Request::create('/register', 'POST');
        $request->setLaravelSession(app('session.store'));

        if ($premiumIntent) {
            $request->session()->put('premium_intent', true);
        }

        return (new RegisterResponse)->toResponse($request);
    }
}
