<?php

declare(strict_types=1);

return [
    'paywall_enabled' => (bool) env('PREMIUM_ENABLED', false),
    'allowlist_route_patterns' => [
        'filament.app.pages.subscription',
        'filament.app.pages.trial-expired',
        'filament.app.pages.payment*',
        'genealogy.import-export.export',
        'affiliate.*',
        '*logout*',
    ],
];
