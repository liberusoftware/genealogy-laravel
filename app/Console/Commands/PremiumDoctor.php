<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Laravel\Cashier\Cashier;
use Throwable;

/**
 * Answers Curtis's "is my .env correct??" (#1635): checks every prerequisite
 * the premium checkout funnel needs to reach a live Stripe Checkout session,
 * from the config/DB prerequisites documented in the funnel research
 * (.scratch/premium-funnel-1635/research-stripe-funnel-config.md). Reports
 * PASS / WARN / FAIL per item and exits non-zero on any hard failure.
 *
 * ponytail: a thin reporter over existing config/accessors — it reads, it never
 * mutates env or DB. The live Stripe call is opt-in (--live) so the default run
 * is deterministic and offline.
 */
class PremiumDoctor extends Command
{
    protected $signature = 'premium:doctor {--live : Also make a read-only Stripe API call to verify the key works}';

    protected $description = 'Check the premium subscription funnel is correctly configured';

    /** @var list<bool> hard-check results; false = a blocking failure */
    private array $hardResults = [];

    public function handle(): int
    {
        $this->info('Premium funnel configuration (#1635)');
        $this->newLine();

        // 0 — asked first because it decides whether any of the rest is load-bearing.
        // Warn-only: an open app is a valid posture, just rarely the intended one
        // for someone running this command (#1638).
        $this->warnCheck('paywall on (app panel requires a subscription)',
            (bool) config('subscription.paywall_enabled'),
            'Set SUBSCRIPTION_PAYWALL_ENABLED=true — off, the panel is open and registration skips checkout.');

        // 1
        $this->hard('premium.enabled is false (funnel reachable)',
            config('premium.enabled') === false,
            'Set PREMIUM_ENABLED=false — when true the subscription page redirects away.');

        // 2 — the one hard blocker
        $secret = config('cashier.secret');
        $hasSecret = is_string($secret) && $secret !== '';
        $this->hard('STRIPE_SECRET set',
            $hasSecret,
            'Set STRIPE_SECRET — required for every Stripe call (Cashier reads config(cashier.secret)).');
        if ($hasSecret) {
            $mode = str_contains((string) $secret, '_live_') ? 'live'
                : (str_contains((string) $secret, '_test_') ? 'test' : 'unknown');
            $this->line("      mode: <comment>{$mode}</comment> — confirm it matches this environment.");
        }

        // 3, 4 — warn-only for reaching Checkout
        $this->warnCheck('STRIPE_KEY set (card Elements)',
            filled(config('cashier.key')),
            'Set STRIPE_KEY — needed for card Elements surfaces, not the hosted redirect.');
        $this->warnCheck('STRIPE_WEBHOOK_SECRET set (post-payment sync)',
            filled(config('cashier.webhook.secret')),
            'Set STRIPE_WEBHOOK_SECRET — without it a paid subscription may not sync back to the app.');

        // 5
        $this->hard('cashier.currency set',
            filled(config('cashier.currency')),
            'Set CASHIER_CURRENCY — a blank currency breaks price formatting.');

        // 6
        $month = (int) config('subscription.premium.amounts.month');
        $year = (int) config('subscription.premium.amounts.year');
        $this->hard('premium amounts are > 0',
            $month > 0 && $year > 0,
            'Set SUBSCRIPTION_PREMIUM_MONTHLY_AMOUNT / _YEARLY_AMOUNT above 0 (minor units) — 0 creates a free price.');

        // 7
        $this->hard('subscription_prices table migrated',
            Schema::hasTable('subscription_prices'),
            'Run migrations — the funnel needs the subscription_prices table (rows auto-create, no seeding).');

        // 8 — opt-in live Stripe reachability
        if ($this->option('live')) {
            $this->hard('Stripe API reachable with this key',
                $this->stripeReachable(),
                'The configured STRIPE_SECRET was rejected or unreachable — check the key and its mode.');
        } else {
            $this->line('  <comment>-</comment> Stripe API reachability skipped (pass --live to verify the key).');
        }

        // 9 — request-scoped, not verifiable from the CLI
        $this->line('  <comment>i</comment> Each subscriber also needs a currentTeam for the checkout URLs; that is per-request and not checked here.');

        $this->newLine();
        $failures = count(array_filter($this->hardResults, fn (bool $ok): bool => ! $ok));
        if ($failures > 0) {
            $this->error("{$failures} blocking problem(s) — the funnel cannot reach Stripe Checkout until fixed.");

            return self::FAILURE;
        }

        $this->info('All blocking prerequisites pass.');

        return self::SUCCESS;
    }

    private function hard(string $label, bool $ok, string $fix): void
    {
        $this->hardResults[] = $ok;
        $ok
            ? $this->line("  <info>✓</info> {$label}")
            : $this->line("  <fg=red>✗</> {$label} — {$fix}");
    }

    private function warnCheck(string $label, bool $ok, string $fix): void
    {
        $ok
            ? $this->line("  <info>✓</info> {$label}")
            : $this->line("  <comment>⚠</comment> {$label} — {$fix}");
    }

    private function stripeReachable(): bool
    {
        try {
            // Read-only, no side effects (contrast resolveManagedPrice which creates).
            Cashier::stripe()->products->all(['limit' => 1]);

            return true;
        } catch (Throwable $e) {
            $this->line("      <fg=red>{$e->getMessage()}</>");

            return false;
        }
    }
}
