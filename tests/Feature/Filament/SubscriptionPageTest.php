<?php

namespace Tests\Feature\Filament;

use App\Filament\App\Pages\SubscriptionPage;
use App\Models\User;
use App\Services\SubscriptionService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Livewire\Livewire;
use Tests\TestCase;

class SubscriptionPageTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($user);
        Filament::setTenant($user->currentTeam, isQuiet: true);

        return $user;
    }

    public function test_page_offers_subscribe_and_never_a_trial_button(): void
    {
        // The app is subscription-only (#1635): the no-card trial button is
        // gone as code, so even a deployment that flips these flags back on
        // never renders it. Revert guard for the removal.
        config()->set('subscription.premium.require_card', false);
        config()->set('subscription.premium.trial_days', 14);
        $this->actingUser();

        Livewire::test(SubscriptionPage::class)
            ->assertSee('Subscribe')
            ->assertDontSee('Start Free Trial');
    }

    public function test_redirect_to_checkout_uses_service_for_selected_interval(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        // Mirror Cashier's Checkout: the Stripe URL is exposed via redirect()
        // and magic __get, NOT a declared property. A fake with a declared
        // `public $url` (as this test used to have) let the old
        // property_exists() guard pass while the guard was broken against the
        // real object — keep this faithful so the test guards the fix.
        $mockCheckout = new class
        {
            public function redirect(): RedirectResponse
            {
                return new RedirectResponse('https://stripe-example');
            }

            public function __get(string $key): ?string
            {
                return $key === 'url' ? 'https://stripe-example' : null;
            }
        };

        $pricingInfo = [
            'premium' => [
                'name' => 'Premium',
                'trial_days' => 14,
                'require_card' => true,
                'intervals' => [
                    'month' => ['interval' => 'month', 'amount' => 299, 'price' => '$2.99'],
                    'year' => ['interval' => 'year', 'amount' => 2999, 'price' => '$29.99'],
                ],
                'features' => [],
            ],
        ];

        $mockService = \Mockery::mock(SubscriptionService::class);
        $mockService->allows('getPricingInfo')->andReturn($pricingInfo);
        $mockService->allows('requiresCard')->andReturnTrue();
        $mockService->allows('trialDays')->andReturn(14);
        $mockService->allows('checkDnaUploadLimit')->andReturn(['can_upload' => false, 'remaining' => 0, 'limit' => 1]);
        $mockService->shouldReceive('createCheckoutRedirect')
            ->once()
            ->with(\Mockery::type(User::class), 'year')
            ->andReturn($mockCheckout);

        $this->app->instance(SubscriptionService::class, $mockService);

        Livewire::actingAs($user)
            ->test(SubscriptionPage::class)
            ->set('interval', 'year')
            ->call('redirectToCheckout')
            ->assertRedirect('https://stripe-example');
    }

    public function test_redirect_to_checkout_surfaces_notification_when_checkout_throws(): void
    {
        // A missing prerequisite (invalid STRIPE_SECRET, un-migrated
        // subscription_prices, no team) throws inside createCheckoutRedirect.
        // The page must surface a notification, not an uncaught 500.
        $user = User::factory()->withPersonalTeam()->create();

        $mockService = \Mockery::mock(SubscriptionService::class);
        $mockService->allows('getPricingInfo')->andReturn([
            'premium' => [
                'name' => 'Premium', 'trial_days' => 14, 'require_card' => true,
                'intervals' => [
                    'month' => ['interval' => 'month', 'amount' => 299, 'price' => '$2.99'],
                    'year' => ['interval' => 'year', 'amount' => 2999, 'price' => '$29.99'],
                ],
                'features' => [],
            ],
        ]);
        $mockService->allows('requiresCard')->andReturnTrue();
        $mockService->allows('trialDays')->andReturn(14);
        $mockService->allows('checkDnaUploadLimit')->andReturn(['can_upload' => false, 'remaining' => 0, 'limit' => 1]);
        $mockService->shouldReceive('createCheckoutRedirect')
            ->once()
            ->andThrow(new \RuntimeException('Stripe misconfigured'));

        $this->app->instance(SubscriptionService::class, $mockService);

        Livewire::actingAs($user)
            ->test(SubscriptionPage::class)
            ->call('redirectToCheckout')
            ->assertNoRedirect()
            ->assertNotified('Subscription Error');
    }
}
