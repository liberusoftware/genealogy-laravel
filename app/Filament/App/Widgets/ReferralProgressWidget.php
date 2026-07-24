<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Filament\App\Pages\ReferAndEarn;
use App\Support\Affiliate;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

/**
 * Dashboard nudge toward the user's next free month. User-scoped; renders only
 * while the affiliate program is live.
 */
class ReferralProgressWidget extends BaseWidget
{
    public static function canView(): bool
    {
        return Affiliate::enabled() && Auth::check();
    }

    #[\Override]
    protected function getStats(): array
    {
        $progress = Auth::user()->affiliateProgress();

        return [
            Stat::make('Next free month', "{$progress['toward']} / {$progress['needed']}")
                ->description('Referrals toward your next free month')
                ->descriptionIcon('heroicon-m-gift')
                ->color('primary')
                ->url(ReferAndEarn::getUrl()),

            Stat::make('Free months earned', $progress['free_months'])
                ->description('From people you referred')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('success'),
        ];
    }
}
