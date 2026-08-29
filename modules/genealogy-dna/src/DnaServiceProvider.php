<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\Dna\Listeners\ReconcilePersonMerge;
use Liberu\Genealogy\People\Events\PersonMerged;

final class DnaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        Event::listen(PersonMerged::class, ReconcilePersonMerge::class);
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-dna',
            'Genealogy Dna',
            ['genealogy.dna', 'genealogy.dna.lifecycle'],
        ));
    }
}
