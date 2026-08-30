<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Services\SubscriptionService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Throwable;

final class SubscriptionPage extends Page
{
    protected static ?string $slug = 'subscription';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Premium';

    protected static ?string $title = 'Premium subscription';

    protected string $view = 'filament.app.pages.subscription';

    public string $interval = 'month';

    /** @return array<string, mixed> */
    public function getPricingData(): array
    {
        return app(SubscriptionService::class)->pricing();
    }

    public function mount(SubscriptionService $subscriptions): void
    {
        if ($subscriptions->enabled() && auth()->user()->isPremium()) {
            $this->redirect(route('filament.app.pages.premium-dashboard'));
        }
    }

    public function subscribe(SubscriptionService $subscriptions): void
    {
        try {
            $checkout = $subscriptions->createCheckout(auth()->user(), $this->interval);
            $this->redirect($checkout->redirect()->getTargetUrl());
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->title('Unable to start checkout')->body($exception->getMessage())->danger()->send();
        }
    }

    public function subscribeYearly(SubscriptionService $subscriptions): void
    {
        $this->interval = 'year';
        $this->subscribe($subscriptions);
    }

    public function startTrial(SubscriptionService $subscriptions): void
    {
        try {
            $subscriptions->startLocalTrial(auth()->user());
            $this->redirect(route('filament.app.pages.premium-dashboard'));
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->title('Unable to start trial')->body($exception->getMessage())->danger()->send();
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) config('premium.enabled', false);
    }
}
