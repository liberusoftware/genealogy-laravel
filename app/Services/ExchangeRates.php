<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;
use Throwable;

/**
 * Converted price estimates from the ECB's daily euro reference rates (#1636).
 *
 * Display only. The ECB publishes these "for information purposes only" and
 * strongly discourages transactional use, so nothing here may ever reach a
 * charge — Stripe bills `cashier.currency` and only that. Whatever this returns
 * is a label, and the surfaces that show it must cite the ECB as source and say
 * the figure was converted by us (the ECB's terms require both).
 */
class ExchangeRates
{
    private const FEED = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    /**
     * The feed's default namespace is on **ecb.int**, not ecb.europa.eu. Get this
     * wrong and every XPath matches nothing — silently, which is indistinguishable
     * from "the feed is down" and would make the estimate vanish for good.
     */
    private const NS = 'http://www.ecb.int/vocabulary/2002-08-01/eurofxref';

    private const CACHE_KEY = 'ecb.eurofxref.daily';

    /**
     * Convert a minor-unit amount out of $base into each configured display currency.
     *
     * Returns null whenever there is nothing honest to show — no rates, an
     * unquoted base, or no display currency left after filtering. Callers render
     * the base-currency price alone; that is the whole failure mode.
     *
     * The `date` is the feed's own rate date, which is routinely older than today:
     * the ECB publishes ~16:00 Europe/Berlin on working days and serves the last
     * business day's rates at HTTP 200 all weekend and through TARGET holidays (a
     * 4-day-old rate is normal around 1 May). Staleness is not failure — but it is
     * the reason the date has to be shown next to the figure.
     *
     * @return array{date: string, amounts: array<string, int>}|null
     */
    public function estimate(int $amount, string $base): ?array
    {
        $feed = $this->rates();

        if ($feed === null) {
            return null;
        }

        $rates = $feed['rates'];

        // EUR is the feed's own base, so it never appears as a row of its own.
        $rates['EUR'] = 1.0;

        $base = strtoupper($base);

        if (! isset($rates[$base])) {
            return null;
        }

        $amounts = [];

        foreach ($this->displayCurrencies() as $code) {
            if ($code === $base || ! isset($rates[$code])) {
                continue;
            }

            // Rates are units-per-EUR, so crossing out of a non-EUR base means
            // dividing by the base's rate and multiplying by the target's. The
            // inverse reads just as plausibly and throws nothing, which is why
            // ExchangeRatesTest pins the direction against known figures.
            //
            // Round once, here, on the final minor-unit amount — never the rate.
            $amounts[$code] = (int) round($amount * $rates[$code] / $rates[$base]);
        }

        if ($amounts === []) {
            return null;
        }

        return ['date' => $feed['date'], 'amounts' => $amounts];
    }

    /**
     * Configured display currencies, upper-cased. Empty disables the estimate.
     *
     * @return list<string>
     */
    private function displayCurrencies(): array
    {
        /** @var list<string> $configured */
        $configured = config('subscription.display_currencies', []);

        return array_map('strtoupper', $configured);
    }

    /**
     * @return array{date: string, rates: array<string, float>}|null
     */
    private function rates(): ?array
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached)) {
            // [] is the cached-failure sentinel; Cache::remember can't express it
            // because it treats null as a miss and would refetch on every request.
            return $cached === [] ? null : $cached;
        }

        $fetched = $this->fetch();

        // ponytail: a failure is cached for 15 minutes so a flapping feed can't be
        // hammered once per page view, while a success holds the full day the ECB
        // publishes on. Drop to a scheduled warm if first-hit latency ever shows up.
        Cache::put(
            self::CACHE_KEY,
            $fetched ?? [],
            $fetched === null ? now()->addMinutes(15) : now()->addDay(),
        );

        return $fetched;
    }

    /**
     * @return array{date: string, rates: array<string, float>}|null
     */
    private function fetch(): ?array
    {
        try {
            $response = Http::timeout(3)->get(self::FEED);
        } catch (Throwable) {
            // Connection refused, DNS failure, timeout — all the same to a caller
            // that only wanted a decorative number.
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return $this->parse($response->body());
    }

    /**
     * @return array{date: string, rates: array<string, float>}|null
     */
    private function parse(string $body): ?array
    {
        // A bad path on this host answers 404 with an HTML error page, so the body
        // is what decides whether we got rates, not the status line.
        $xml = @simplexml_load_string($body);

        if (! $xml instanceof SimpleXMLElement) {
            return null;
        }

        $xml->registerXPathNamespace('ecb', self::NS);
        $days = $xml->xpath('//ecb:Cube[@time]');

        if (empty($days)) {
            return null;
        }

        // The daily file has carried exactly one dated Cube every time it has been
        // looked at, but the ECB publishes no schema saying it must, and the 90-day
        // and full-history files use this identical shape with thousands. Taking the
        // newest costs a loop and removes the assumption.
        $latest = null;
        $latestDate = '';

        foreach ($days as $day) {
            // ->attributes() rather than $day['time']: once an element is reached
            // through the namespaced children()/xpath, array access looks the
            // attribute up in that namespace, and these attributes have none — so
            // it silently reads as empty. attributes() defaults to no namespace.
            $date = (string) $day->attributes()['time'];

            if ($latest === null || $date > $latestDate) {
                $latest = $day;
                $latestDate = $date;
            }
        }

        $rates = [];

        // The quoted currency set is not fixed — BGN left the file when Bulgaria
        // joined the euro — so read what is there rather than expecting a list.
        foreach ($latest->children(self::NS) as $row) {
            $attributes = $row->attributes();
            $code = strtoupper((string) $attributes['currency']);
            $rate = (float) $attributes['rate'];

            if ($code !== '' && $rate > 0.0) {
                $rates[$code] = $rate;
            }
        }

        if ($rates === []) {
            return null;
        }

        return ['date' => $latestDate, 'rates' => $rates];
    }
}
