<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Policies\TeamOwnedPolicy;
use Liberu\Genealogy\Reports\Models\GenealogyReport;

final class ReportsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        Gate::policy(GenealogyReport::class, TeamOwnedPolicy::class);
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-reports',
            'Genealogy Reports',
            ['genealogy.reports', 'genealogy.reports.family-groups', 'genealogy.reports.pedigrees', 'genealogy.reports.descendants', 'genealogy.reports.timelines', 'genealogy.reports.research', 'genealogy.reports.sources', 'genealogy.reports.charts', 'genealogy.reports.lifecycle'],
        ));
    }
}
