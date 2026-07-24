<?php

namespace Tests\Feature\Filament;

use App\Filament\App\Pages\SubscriptionPage;
use App\Models\User;
use App\Services\SubscriptionService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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

    public function test_page_advertises_no_free_plan(): void
    {
        // The app is subscription-only (#1635): the page used to sell a
        // "Standard · Free forever · $0" column and a free-trial FAQ.
        config(['premium.enabled' => false]);
        $this->actingUser();

        Livewire::test(SubscriptionPage::class)
            ->assertDontSee('Free forever')
            ->assertDontSee('$0')
            ->assertDontSee('What happens during the free trial?');
    }

    public function test_billing_interval_rows_show_savings_and_drive_checkout(): void
    {
        config([
            'premium.enabled' => false,
            'subscription.premium.amounts.month' => 299,
            'subscription.premium.amounts.year' => 2999,
        ]);
        $this->actingUser();

        Livewire::test(SubscriptionPage::class)
            // Monthly is the default; yearly advertises what it saves. Every
            // money figure is wrapped by <x-price> so it can be switched to
            // another currency (#1636), which is why these assert across the
            // closing tag rather than on contiguous text.
            ->assertSee('$5.89</span>', escape: false)
            ->assertSee('$29.99</span>', escape: false)
            ->assertSee('billed once a year')
            // The charge line tracks the selected interval.
            ->assertSee('today, then every month')
            ->set('interval', 'year')
            ->assertSee('today, then every year');
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
                    'month' => ['interval' => 'month', 'amount' => 299, 'price' => '$2.99', 'per_month' => '$2.99', 'price_amounts' => ['USD' => '$2.99'], 'per_month_amounts' => ['USD' => '$2.99']],
                    'year' => ['interval' => 'year', 'amount' => 2999, 'price' => '$29.99', 'per_month' => '$2.50', 'savings' => '$5.89', 'savings_percent' => 16, 'price_amounts' => ['USD' => '$29.99'], 'per_month_amounts' => ['USD' => '$2.50'], 'savings_amounts' => ['USD' => '$5.89']],
                ],
                'features' => [],
                'estimate_date' => null,
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
                    'month' => ['interval' => 'month', 'amount' => 299, 'price' => '$2.99', 'per_month' => '$2.99', 'price_amounts' => ['USD' => '$2.99'], 'per_month_amounts' => ['USD' => '$2.99']],
                    'year' => ['interval' => 'year', 'amount' => 2999, 'price' => '$29.99', 'per_month' => '$2.50', 'savings' => '$5.89', 'savings_percent' => 16, 'price_amounts' => ['USD' => '$29.99'], 'per_month_amounts' => ['USD' => '$2.50'], 'savings_amounts' => ['USD' => '$5.89']],
                ],
                'features' => [],
                'estimate_date' => null,
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

    public function test_every_figure_on_the_page_converts_with_the_switcher(): void
    {
        // Not just the headline (#1636 ticket 02): a page framed in GBP that still
        // shows "$2.50 per month" in the same row reads as a bug, so per_month and
        // the savings pill carry their converted forms too.
        config([
            'premium.enabled' => false,
            'cashier.currency' => 'usd',
            'subscription.premium.amounts.month' => 299,
            'subscription.premium.amounts.year' => 2999,
            'subscription.display_currencies' => ['GBP'],
        ]);
        Cache::flush();
        Http::fake([
            'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml' => Http::response(
                '<?xml version="1.0" encoding="UTF-8"?>'.
                '<gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01" xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref">'.
                "<Cube><Cube time='2026-07-24'>".
                "<Cube currency='USD' rate='1.1377'/><Cube currency='GBP' rate='0.85388'/>".
                '</Cube></Cube></gesmes:Envelope>'
            ),
        ]);
        $this->actingUser();

        Livewire::test(SubscriptionPage::class)
            // headline monthly: 299 * 0.85388 / 1.1377 = 224.41
            ->assertSee('data-gbp="£2.24"', escape: false)
            // per-month equivalent of the yearly plan: 2999/12 = 250 -> 187.63
            ->assertSee('data-gbp="£1.88"', escape: false)
            // the savings pill: 3588 - 2999 = 589 -> 442.06
            ->assertSee('data-gbp="£4.42"', escape: false)
            ->assertSee('data-currency-option="GBP"', escape: false)
            ->assertSee('Central Bank');
    }
}
