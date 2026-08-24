<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class ReportsLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('genealogy-reports-livewire-status', Status::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Reports Livewire adapter is available.';
    }
}
