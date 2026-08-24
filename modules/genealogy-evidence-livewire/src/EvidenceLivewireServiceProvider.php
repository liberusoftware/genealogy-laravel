<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class EvidenceLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'genealogy-evidence-livewire');
        Livewire::component('genealogy-evidence-list', EvidenceRecordList::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Evidence Livewire adapter is available.';
    }
}
