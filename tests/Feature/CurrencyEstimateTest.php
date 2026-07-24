<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The converted price estimate on the public price surfaces (#1636).
 *
 * The assertions that matter most are the negative ones: with no rates, every
 * page must render exactly what it rendered before this feature existed.
 */
class CurrencyEstimateTest extends TestCase
{
    use RefreshDatabase;

    private const FEED = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config()->set('cashier.currency', 'usd');
        config()->set('subscription.premium.amounts.month', 299);
    }

    public function test_home_carries_every_currency_and_the_switcher(): void
    {
        $this->withRates();

        $response = $this->get('/');

        $response->assertOk();
        // 299 * 0.85388 / 1.1377 = 224.41
        $response->assertSee('data-gbp="£2.24"', escape: false);
        $response->assertSee('data-eur="€2.63"', escape: false);
        $response->assertSee('data-currency-option="GBP"', escape: false);
    }

    public function test_pricing_page_carries_every_currency_and_the_switcher(): void
    {
        $this->withRates();

        $response = $this->get('/subscription');

        $response->assertOk();
        $response->assertSee('data-gbp="£2.24"', escape: false);
        $response->assertSee('data-currency-option="EUR"', escape: false);
    }

    public function test_the_charge_currency_is_what_renders_without_javascript(): void
    {
        $this->withRates();

        // The estimate is a text swap done client-side, so the served HTML must
        // still say what Stripe will charge — not a figure we can't promise.
        $this->get('/subscription')->assertSee('>$2.99</span>', escape: false);
    }

    public function test_attribution_names_the_ecb_and_dates_the_rate(): void
    {
        // Not decoration: the ECB's terms require citation as source, that a
        // modified figure say so, and that the rate be dated.
        $this->withRates(date: '2026-05-29');

        $response = $this->get('/subscription');

        $response->assertSee('European', escape: false);
        $response->assertSee('Central Bank', escape: false);
        $response->assertSee('2026-05-29', escape: false);
        $response->assertSee('cross-rate calculated by us', escape: false);
    }

    public function test_no_rates_leaves_the_pages_exactly_as_they_were(): void
    {
        config()->set('subscription.display_currencies', ['GBP', 'EUR']);
        Http::fake([self::FEED => Http::response('', 503)]);

        $response = $this->get('/subscription');

        $response->assertOk();
        $response->assertSee('$2.99');
        $response->assertDontSee('data-currency-option', escape: false);
        $response->assertDontSee('data-gbp', escape: false);
        $response->assertDontSee('European Central Bank', escape: false);
    }

    public function test_the_feature_off_never_reaches_for_the_feed(): void
    {
        // phpunit.xml leaves the currency list empty, so the default posture of the
        // whole suite is "no outbound request from rendering a page".
        config()->set('subscription.display_currencies', []);
        Http::fake();

        $this->get('/subscription')->assertOk();

        Http::assertNothingSent();
    }

    public function test_the_hero_strapline_keeps_the_charge_currency(): void
    {
        // Deliberate: the strapline has no room for the attribution the ECB
        // requires, and an unattributed converted figure is the one thing we may
        // not show. Only the card below it switches.
        $this->withRates();

        $this->get('/')->assertSee('$2.99 per month · Cancel anytime', escape: false);
    }

    private function withRates(string $date = '2026-07-24'): void
    {
        config()->set('subscription.display_currencies', ['GBP', 'EUR']);

        Http::fake([self::FEED => Http::response(<<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01" xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref">
            <Cube>
                <Cube time='{$date}'>
                    <Cube currency='USD' rate='1.1377'/>
                    <Cube currency='GBP' rate='0.85388'/>
                </Cube>
            </Cube>
        </gesmes:Envelope>
        XML)]);
    }
}
