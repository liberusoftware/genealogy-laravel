<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class ImportExportLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'genealogy-import-export-livewire');
        Livewire::component('genealogy-import-export-list', DataTransferList::class);
        Livewire::component('genealogy-import-export-import', DataTransferImport::class);
        Livewire::component('genealogy-import-export-export', DataTransferExport::class);
        Livewire::component('module-genealogy-import-export::data-transfer-list', DataTransferList::class);
        Livewire::component('module-genealogy-import-export::data-transfer-import', DataTransferImport::class);
        Livewire::component('module-genealogy-import-export::data-transfer-export', DataTransferExport::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy ImportExport Livewire adapter is available.';
    }
}
