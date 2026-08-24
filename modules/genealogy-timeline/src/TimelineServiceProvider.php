<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline;

use Illuminate\Support\ServiceProvider;

final class TimelineServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-timeline',
            'Genealogy Timeline',
            ['genealogy.timeline', 'genealogy.timeline.lifecycle'],
        ));
    }
}
