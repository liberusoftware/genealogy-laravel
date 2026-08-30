<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Policies\TeamOwnedPolicy;
use Liberu\Genealogy\Timeline\Models\TimelineEvent;
use Liberu\Genealogy\Timeline\Services\HistoricalEventService;

final class TimelineServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        Gate::policy(TimelineEvent::class, TeamOwnedPolicy::class);
        $this->app->singleton(HistoricalEventService::class);
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-timeline',
            'Genealogy Timeline',
            ['genealogy.timeline', 'genealogy.timeline.personal-family', 'genealogy.timeline.historical-context', 'genealogy.timeline.conflicts', 'genealogy.timeline.chronological-navigation', 'genealogy.timeline.lifecycle'],
        ));
    }
}
