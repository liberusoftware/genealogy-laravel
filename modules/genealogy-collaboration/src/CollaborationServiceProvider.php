<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration;

use Illuminate\Support\ServiceProvider;

final class CollaborationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-collaboration',
            'Genealogy Collaboration',
            ['genealogy.collaboration', 'genealogy.collaboration.lifecycle'],
        ));
    }
}
