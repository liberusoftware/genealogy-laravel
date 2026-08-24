<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport;

use Illuminate\Support\ServiceProvider;

final class ImportExportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-import-export',
            'Genealogy Import Export',
            ['genealogy.import-export', 'genealogy.import-export.lifecycle'],
        ));
    }
}
