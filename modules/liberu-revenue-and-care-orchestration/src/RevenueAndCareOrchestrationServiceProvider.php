<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration;

use Illuminate\Support\ServiceProvider;

final class RevenueAndCareOrchestrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'liberu-revenue-and-care-orchestration',
            'Liberu Revenue And Care Orchestration',
            ['liberu.revenue-and-care-orchestration', 'liberu.revenue-and-care-orchestration.lifecycle'],
        ));
    }
}
