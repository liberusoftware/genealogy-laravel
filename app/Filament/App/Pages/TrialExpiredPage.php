<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

final class TrialExpiredPage extends Page
{
    protected static ?string $slug = 'trial-expired';

    protected static ?string $title = 'Premium access suspended';

    protected string $view = 'filament.app.pages.trial-expired';

    public function mount(): void
    {
        if (! (bool) config('premium.enabled', false) || auth()->user()->isPremium()) {
            $this->redirect(route('filament.app.pages.premium-dashboard'));
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
