<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\ExchangeRates;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRatesTest extends TestCase
{
    private const FEED = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config()->set('subscription.display_currencies', ['GBP', 'EUR']);
    }

    /**
     * The one test that matters. Rates are quoted as units-per-EUR, so converting
     * out of USD means dividing by USD's rate and multiplying by the target's.
     * Both inversions produce plausible-looking money and neither throws, so the
     * assertion pins real figures from the 2026-07-24 feed rather than a shape.
     */
    public function test_converts_out_of_usd_via_the_euro_cross_rate(): void
    {
        Http::fake([self::FEED => Http::response($this->feed())]);

        $estimate = (new ExchangeRates)->estimate(2999, 'usd');

        // 2999 * 0.85388 / 1.1377 = 2250.84
        $this->assertSame(2251, $estimate['amounts']['GBP']);
        // 2999 * 1 / 1.1377 = 2636.02
        $this->assertSame(2636, $estimate['amounts']['EUR']);

        // Inverting the cross-rate gives 2999 * 1.1377 / 0.85388 = 3995.8, and
        // multiplying by USD's rate instead gives 3412 — both look like money.
        $this->assertNotSame(3996, $estimate['amounts']['GBP']);
        $this->assertNotSame(3412, $estimate['amounts']['EUR']);
    }

    public function test_rounds_the_amount_once_at_the_end(): void
    {
        Http::fake([self::FEED => Http::response($this->feed())]);

        // 299 * 0.85388 / 1.1377 = 224.41 -> 224, not 299 * round(0.75) = 224.25
        // and not a rate rounded to 0.8 (which would give 239).
        $this->assertSame(224, (new ExchangeRates)->estimate(299, 'usd')['amounts']['GBP']);
    }

    public function test_exposes_the_feeds_own_rate_date_not_todays(): void
    {
        // A Saturday fetch serves Friday's rates at HTTP 200. The surfaces have to
        // print this date, so it must survive rather than be replaced with now().
        Http::fake([self::FEED => Http::response($this->feed(date: '2026-05-29'))]);

        $this->assertSame('2026-05-29', (new ExchangeRates)->estimate(2999, 'usd')['date']);
    }

    public function test_omits_the_base_currency_from_its_own_estimate(): void
    {
        config()->set('subscription.display_currencies', ['GBP', 'USD']);
        Http::fake([self::FEED => Http::response($this->feed())]);

        $this->assertSame(['GBP'], array_keys((new ExchangeRates)->estimate(2999, 'usd')['amounts']));
    }

    public function test_skips_a_display_currency_the_feed_does_not_quote(): void
    {
        // The quoted set is not fixed — BGN vanished when Bulgaria joined the euro.
        config()->set('subscription.display_currencies', ['GBP', 'BGN']);
        Http::fake([self::FEED => Http::response($this->feed())]);

        $this->assertSame(['GBP'], array_keys((new ExchangeRates)->estimate(2999, 'usd')['amounts']));
    }

    public function test_returns_null_when_the_base_currency_is_not_quoted(): void
    {
        Http::fake([self::FEED => Http::response($this->feed())]);

        $this->assertNull((new ExchangeRates)->estimate(2999, 'zwl'));
    }

    public function test_takes_the_newest_dated_cube_when_the_feed_carries_several(): void
    {
        // The daily file has only ever carried one, but the ECB publishes no schema
        // promising that, and the history files use this identical shape.
        Http::fake([self::FEED => Http::response($this->multiDayFeed())]);

        $this->assertSame('2026-07-24', (new ExchangeRates)->estimate(2999, 'usd')['date']);
    }

    public function test_returns_null_when_the_feed_errors(): void
    {
        Http::fake([self::FEED => Http::response('', 503)]);

        $this->assertNull((new ExchangeRates)->estimate(2999, 'usd'));
    }

    public function test_returns_null_when_the_body_is_html_not_xml(): void
    {
        // A bad path on this host answers 404 with an HTML error page, so a status
        // check alone would happily parse a web page as rates.
        Http::fake([self::FEED => Http::response('<html><body>Not found</body></html>', 404)]);

        $this->assertNull((new ExchangeRates)->estimate(2999, 'usd'));
    }

    public function test_returns_null_when_no_display_currencies_are_configured(): void
    {
        config()->set('subscription.display_currencies', []);
        Http::fake([self::FEED => Http::response($this->feed())]);

        $this->assertNull((new ExchangeRates)->estimate(2999, 'usd'));
    }

    public function test_fetches_once_a_day_not_once_a_call(): void
    {
        Http::fake([self::FEED => Http::response($this->feed())]);

        $rates = new ExchangeRates;
        $rates->estimate(2999, 'usd');
        $rates->estimate(299, 'usd');

        Http::assertSentCount(1);
    }

    public function test_does_not_refetch_after_a_failure(): void
    {
        // Cache::remember treats a null return as a miss, which would put a live
        // HTTP call on every page view for as long as the feed stayed down.
        Http::fake([self::FEED => Http::response('', 503)]);

        $rates = new ExchangeRates;
        $rates->estimate(2999, 'usd');
        $rates->estimate(2999, 'usd');

        Http::assertSentCount(1);
    }

    private function feed(string $date = '2026-07-24'): string
    {
        return <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01" xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref">
            <gesmes:subject>Reference rates</gesmes:subject>
            <Cube>
                <Cube time='{$date}'>
                    <Cube currency='USD' rate='1.1377'/>
                    <Cube currency='GBP' rate='0.85388'/>
                    <Cube currency='JPY' rate='186.38'/>
                </Cube>
            </Cube>
        </gesmes:Envelope>
        XML;
    }

    private function multiDayFeed(): string
    {
        return <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01" xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref">
            <Cube>
                <Cube time='2026-07-23'>
                    <Cube currency='USD' rate='1.2000'/>
                    <Cube currency='GBP' rate='0.90000'/>
                </Cube>
                <Cube time='2026-07-24'>
                    <Cube currency='USD' rate='1.1377'/>
                    <Cube currency='GBP' rate='0.85388'/>
                </Cube>
            </Cube>
        </gesmes:Envelope>
        XML;
    }
}
