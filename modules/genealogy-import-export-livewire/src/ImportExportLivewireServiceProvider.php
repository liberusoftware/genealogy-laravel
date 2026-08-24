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
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy ImportExport Livewire adapter is available.';
    }
}
