<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration;

use Illuminate\Support\ServiceProvider;

final class PlatformOrchestrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'liberu-platform-orchestration',
            'Liberu Platform Orchestration',
            ['liberu.platform-orchestration', 'liberu.platform-orchestration.lifecycle'],
        ));
    }
}
