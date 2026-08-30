<?php

declare(strict_types=1);

return [
    // Open-source installs are fully open by default. SaaS deployments opt in.
    'enabled' => (bool) env('PREMIUM_ENABLED', false),
    'trial_days' => (int) env('PREMIUM_TRIAL_DAYS', 7),
    'require_card' => (bool) env('PREMIUM_REQUIRE_CARD', true),
    'prices' => [
        'month' => (int) env('PREMIUM_MONTHLY_AMOUNT', 249),
        'year' => (int) env('PREMIUM_YEARLY_AMOUNT', 2499),
    ],
    'stripe_prices' => [
        'month' => env('PREMIUM_STRIPE_MONTHLY_PRICE_ID'),
        'year' => env('PREMIUM_STRIPE_YEARLY_PRICE_ID'),
    ],
    'currency' => strtolower((string) env('PREMIUM_CURRENCY', 'gbp')),
];
