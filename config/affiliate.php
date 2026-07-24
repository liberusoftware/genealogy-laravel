<?php

declare(strict_types=1);

return [
    // Master switch for the referral/affiliate program. NOTE: the program is
    // ALSO dormant whenever premium.enabled is true — when everyone is premium a
    // free-month reward is meaningless. Both conditions are ANDed in the single
    // gate App\Support\Affiliate::enabled(); always check that, never this raw
    // flag, so nav, routes, qualification and grants stay in lockstep.
    'enabled' => env('AFFILIATE_ENABLED', true),

    // Number of qualified (paid) referrals that earn one free month. Repeatable
    // with no cap. Floored to 1 by Affiliate::referralsPerFreeMonth().
    'referrals_per_free_month' => (int) env('AFFILIATE_REFERRALS_PER_FREE_MONTH', 5),
];
