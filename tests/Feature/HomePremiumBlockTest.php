<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Home premium block, 3 states (#1635, ticket 02). The public home page (/) must
 * show the right call for each visitor: a guest is invited to get started, a
 * signed-in non-subscriber is asked to subscribe (with the price), and a
 * subscriber sees no price and no "subscribe" prompt — only Manage. Revert guard
 * for the contradictory "upgrade to a plan you already hold" bug Curtis reported.
 */
class HomePremiumBlockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['premium.enabled' => false]);
    }

    public function test_guest_sees_get_started(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Get started')
            ->assertSee('Billed at checkout')
            ->assertDontSee('Subscribe')
            ->assertDontSee('Manage subscription');
    }

    public function test_non_subscriber_sees_subscribe_and_price(): void
    {
        $this->actingAs(User::factory()->withPersonalTeam()->create(['is_premium' => false, 'trial_ends_at' => null]));

        $this->get('/')
            ->assertOk()
            ->assertSee('Subscribe')
            ->assertSee('Billed at checkout')
            ->assertDontSee('Manage subscription');
    }

    public function test_subscriber_sees_manage_and_no_price_or_upgrade(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['stripe_id' => 'cus_x']);
        $user->subscriptions()->create([
            'type' => 'premium',
            'stripe_id' => 'sub_x',
            'stripe_status' => 'active',
            'stripe_price' => 'price_premium_monthly',
            'quantity' => 1,
        ]);
        $this->actingAs($user);

        $this->get('/')
            ->assertOk()
            ->assertSee('Manage subscription')
            ->assertDontSee('Subscribe')
            ->assertDontSee('Billed at checkout');
    }
}
