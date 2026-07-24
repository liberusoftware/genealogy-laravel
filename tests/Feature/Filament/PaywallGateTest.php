<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Paywall (#1635): the whole app panel is subscription-only. A non-subscriber is
 * redirected to the subscription page from every surface — including ones that
 * were free before (dashboard, people) — except the allowlist. A subscriber
 * passes. Revert-sensitive: before EnsureSubscribed these surfaces were
 * reachable by any authenticated user, so the assertRedirects fail on old code.
 */
class PaywallGateTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsNonSubscriber(): User
    {
        config(['premium.enabled' => false]);
        $user = User::factory()->withPersonalTeam()->create(['is_premium' => false, 'trial_ends_at' => null]);
        $this->actingAs($user);
        Filament::setTenant($user->currentTeam, isQuiet: true);

        return $user;
    }

    private function actingAsSubscriber(): User
    {
        config(['premium.enabled' => false]);
        $user = User::factory()->withPersonalTeam()->create(['stripe_id' => 'cus_sub']);
        $user->subscriptions()->create([
            'type' => 'premium',
            'stripe_id' => 'sub_sub',
            'stripe_status' => 'active',
            'stripe_price' => 'price_premium_monthly',
            'quantity' => 1,
        ]);
        $this->actingAs($user);
        Filament::setTenant($user->currentTeam, isQuiet: true);

        return $user;
    }

    public function test_non_subscriber_is_bounced_from_previously_free_surfaces(): void
    {
        $user = $this->actingAsNonSubscriber();
        $target = route('filament.app.pages.subscription', ['tenant' => $user->currentTeam]);

        // Dashboard and People were reachable by any authed user before the paywall.
        $this->get(route('filament.app.pages.dashboard', ['tenant' => $user->currentTeam]))->assertRedirect($target);
        $this->get(route('filament.app.resources.people.index', ['tenant' => $user->currentTeam]))->assertRedirect($target);
    }

    public function test_non_subscriber_can_reach_the_subscription_page_no_loop(): void
    {
        // The loop guard: the redirect target itself must not be gated.
        $user = $this->actingAsNonSubscriber();

        $this->get(route('filament.app.pages.subscription', ['tenant' => $user->currentTeam]))->assertOk();
    }

    public function test_subscriber_passes_the_gate(): void
    {
        $user = $this->actingAsSubscriber();

        $this->get(route('filament.app.pages.dashboard', ['tenant' => $user->currentTeam]))->assertOk();
    }

    public function test_the_paywall_is_opt_in_and_off_leaves_the_panel_open(): void
    {
        // #1638 made the gate a flag defaulting to false, so a fresh checkout of
        // this open-source project is not paywalled. Off, the surfaces #1635
        // closed are reachable again by any authenticated user.
        config(['subscription.paywall_enabled' => false]);
        $user = $this->actingAsNonSubscriber();

        $this->get(route('filament.app.pages.dashboard', ['tenant' => $user->currentTeam]))->assertOk();
        $this->get(route('filament.app.resources.people.index', ['tenant' => $user->currentTeam]))->assertOk();
    }

    public function test_the_flag_off_does_not_open_the_per_resource_premium_gates(): void
    {
        // The paywall and the premium tier are separate concerns: turning the
        // paywall off opens the panel, not the DNA tools that were premium-only
        // long before #1635 closed everything.
        config(['subscription.paywall_enabled' => false]);
        $user = $this->actingAsNonSubscriber();

        $this->get(route('filament.app.resources.dnas.index', ['tenant' => $user->currentTeam]))
            ->assertRedirect(route('filament.app.pages.subscription', ['tenant' => $user->currentTeam]));
    }
}
