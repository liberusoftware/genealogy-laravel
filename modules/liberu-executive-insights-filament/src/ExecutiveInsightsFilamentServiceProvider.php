<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class ExecutiveInsightsFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class ExecutiveInsightsFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'liberu-executive-insights-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
