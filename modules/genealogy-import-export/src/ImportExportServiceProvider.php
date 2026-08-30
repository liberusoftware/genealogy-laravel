<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Policies\TeamOwnedPolicy;
use Liberu\Genealogy\ImportExport\Exporters\GedcomExporter;
use Liberu\Genealogy\ImportExport\Exporters\GedcomXExporter;
use Liberu\Genealogy\ImportExport\Exporters\GrampsExporter;
use Liberu\Genealogy\ImportExport\Importers\GenealogyDocumentParser;
use Liberu\Genealogy\ImportExport\Importers\GenealogyImportService;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;

final class ImportExportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        Gate::policy(DataTransfer::class, TeamOwnedPolicy::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/genealogy-import-export.php', 'genealogy-import-export');
        $this->app->singleton(GenealogyDocumentParser::class);
        $this->app->singleton(GenealogyImportService::class);
        $this->app->singleton(GrampsExporter::class);
        $this->app->singleton(GedcomExporter::class);
        $this->app->singleton(GedcomXExporter::class);
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-import-export',
            'Genealogy Import Export',
            ['genealogy.import-export', 'genealogy.import-export.gedcom', 'genealogy.import-export.gramps', 'genealogy.import-export.validation', 'genealogy.import-export.dry-run', 'genealogy.import-export.duplicates', 'genealogy.import-export.reports', 'genealogy.import-export.round-trip', 'genealogy.import-export.lifecycle'],
        ));
    }
}
