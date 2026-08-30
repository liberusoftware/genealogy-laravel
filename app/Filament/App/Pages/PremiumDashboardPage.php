<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Services\SubscriptionService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Throwable;

final class PremiumDashboardPage extends Page
{
    protected static ?string $slug = 'premium-dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Premium status';

    protected static ?string $title = 'Premium status';

    protected string $view = 'filament.app.pages.premium-dashboard';

    public function mount(): void
    {
        if (! auth()->user()->isPremium()) {
            $this->redirect(route(
                auth()->user()->isPremiumSuspended()
                    ? 'filament.app.pages.trial-expired'
                    : 'filament.app.pages.subscription',
            ));
        }
    }

    public function cancel(SubscriptionService $subscriptions): void
    {
        try {
            $subscriptions->cancel(auth()->user());
            Notification::make()->title('Subscription cancelled')->body('You can continue using Premium until your trial or billing period ends.')->success()->send();
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->title('Unable to cancel subscription')->body($exception->getMessage())->danger()->send();
        }
    }

    public function resume(SubscriptionService $subscriptions): void
    {
        try {
            $subscriptions->resume(auth()->user());
            Notification::make()->title('Subscription resumed')->success()->send();
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->title('Unable to resume subscription')->body($exception->getMessage())->danger()->send();
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) config('premium.enabled', false);
    }
}
