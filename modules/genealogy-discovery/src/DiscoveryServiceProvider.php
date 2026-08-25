<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery;

use Illuminate\Support\ServiceProvider;

final class DiscoveryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-discovery',
            'Genealogy Discovery',
            ['genealogy.discovery', 'genealogy.discovery.lifecycle'],
        ));
    }
}
