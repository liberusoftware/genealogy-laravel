<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence;

use Illuminate\Support\ServiceProvider;

final class EvidenceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-evidence',
            'Genealogy Evidence',
            ['genealogy.evidence', 'genealogy.evidence.lifecycle'],
        ));
    }
}
