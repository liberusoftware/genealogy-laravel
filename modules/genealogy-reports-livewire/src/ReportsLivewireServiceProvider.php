<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class ReportsLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'genealogy-reports-livewire');
        Livewire::component('genealogy-reports-list', GenealogyReportList::class);
        Livewire::component('module-genealogy-reports::report-list', GenealogyReportList::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Reports Livewire adapter is available.';
    }
}
