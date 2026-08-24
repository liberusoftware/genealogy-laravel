<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights;

use Illuminate\Support\ServiceProvider;

final class ExecutiveInsightsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'liberu-executive-insights',
            'Liberu Executive Insights',
            ['liberu.executive-insights', 'liberu.executive-insights.lifecycle'],
        ));
    }
}
