<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports;

use Illuminate\Support\ServiceProvider;

final class ReportsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-reports',
            'Genealogy Reports',
            ['genealogy.reports', 'genealogy.reports.lifecycle'],
        ));
    }
}
