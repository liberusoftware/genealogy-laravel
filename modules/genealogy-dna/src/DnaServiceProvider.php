<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna;

use Illuminate\Support\ServiceProvider;

final class DnaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
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
