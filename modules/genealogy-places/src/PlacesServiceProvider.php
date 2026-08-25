<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Policies\TeamOwnedPolicy;
use Liberu\Genealogy\Places\Models\Place;
use Liberu\Genealogy\Places\Models\PlaceName;

final class PlacesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        Gate::policy(Place::class, TeamOwnedPolicy::class);
        Gate::policy(PlaceName::class, TeamOwnedPolicy::class);
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-places',
            'Genealogy Places',
            ['genealogy.places', 'genealogy.places.hierarchy', 'genealogy.places.names', 'genealogy.places.coordinates', 'genealogy.places.jurisdictions', 'genealogy.places.maps', 'genealogy.places.lifecycle'],
        ));
    }
}
