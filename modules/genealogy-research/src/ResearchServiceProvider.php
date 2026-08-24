<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research;

use Illuminate\Support\ServiceProvider;

final class ResearchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-research',
            'Genealogy Research',
            ['genealogy.research', 'genealogy.research.lifecycle'],
        ));
    }
}
