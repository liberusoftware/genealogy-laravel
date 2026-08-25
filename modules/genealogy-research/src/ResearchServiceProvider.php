<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Policies\TeamOwnedPolicy;
use Liberu\Genealogy\Research\Models\ResearchEntry;
use Liberu\Genealogy\Research\Models\ResearchProject;

final class ResearchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        Gate::policy(ResearchProject::class, TeamOwnedPolicy::class);
        Gate::policy(ResearchEntry::class, TeamOwnedPolicy::class);
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-research',
            'Genealogy Research',
            ['genealogy.research', 'genealogy.research.questions', 'genealogy.research.plans', 'genealogy.research.tasks', 'genealogy.research.logs', 'genealogy.research.correspondence', 'genealogy.research.negative-searches', 'genealogy.research.findings', 'genealogy.research.lifecycle'],
        ));
    }
}
