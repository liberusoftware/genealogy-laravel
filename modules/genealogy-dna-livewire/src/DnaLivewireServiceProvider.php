<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class DnaLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'genealogy-dna-livewire');
        Livewire::component('genealogy-dna-list', DnaKitList::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Dna Livewire adapter is available.';
    }
}
