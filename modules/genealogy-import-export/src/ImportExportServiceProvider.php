<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport;

use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\ImportExport\Importers\GenealogyDocumentParser;
use Liberu\Genealogy\ImportExport\Importers\GenealogyImportService;

final class ImportExportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->singleton(GenealogyDocumentParser::class);
        $this->app->singleton(GenealogyImportService::class);
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-import-export',
            'Genealogy Import Export',
            ['genealogy.import-export', 'genealogy.import-export.lifecycle'],
        ));
    }
}
