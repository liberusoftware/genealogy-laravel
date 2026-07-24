<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Support\Affiliate;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Where a user runs their affiliate activity: their link, referrals, rewards and
 * progress to the next free month. User-scoped (not team-scoped) — shows the
 * authenticated user's data regardless of active team. Hidden + inaccessible
 * when the program is dormant.
 */
class ReferAndEarn extends Page
{
    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    #[\Override]
    protected static ?string $navigationLabel = 'Refer & Earn';

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = '👤 Account & Settings';

    #[\Override]
    protected static ?int $navigationSort = 3;

    #[\Override]
    protected string $view = 'filament.app.pages.refer-and-earn';

    #[\Override]
    protected static ?string $title = 'Refer & Earn';

    #[\Override]
    protected static ?string $slug = 'refer-and-earn';

    #[\Override]
    public static function canAccess(): bool
    {
        return Affiliate::enabled() && Auth::check();
    }

    #[\Override]
    public static function shouldRegisterNavigation(): bool
    {
        return Affiliate::enabled() && Auth::check();
    }

    public function getReferralLink(): string
    {
        return Auth::user()->referralLink();
    }

    /** @return array{needed:int,toward:int,free_months:int,qualified:int,pending:int} */
    public function getProgress(): array
    {
        return Auth::user()->affiliateProgress();
    }

    public function getReferrals(): Collection
    {
        return Auth::user()->referrals()->with('referredUser')->latest()->get();
    }

    public function getRewards(): Collection
    {
        return Auth::user()->affiliateRewards()->latest('granted_at')->get();
    }
}
