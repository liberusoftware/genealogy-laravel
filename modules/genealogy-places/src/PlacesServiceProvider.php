<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places;

use Illuminate\Support\ServiceProvider;

final class PlacesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-places',
            'Genealogy Places',
            ['genealogy.places', 'genealogy.places.lifecycle'],
        ));
    }
}
