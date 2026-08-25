<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource;

final class ImportExportFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class ImportExportFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'genealogy-import-export-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([DataTransferResource::class]);
    }

    public function boot(Panel $panel): void {}
}
