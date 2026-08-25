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
        Livewire::component('module-genealogy-dna::dna-list', DnaKitList::class);
        Livewire::component('module-genealogy-dna::match-list', DnaMatchList::class);
        Livewire::component('module-genealogy-dna::group-list', DnaGroupList::class);
        Livewire::component('module-genealogy-dna::annotation-list', DnaAnnotationList::class);
        Livewire::component('genealogy-dna-match-list', DnaMatchList::class);
        Livewire::component('genealogy-dna-group-list', DnaGroupList::class);
        Livewire::component('genealogy-dna-annotation-list', DnaAnnotationList::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Dna Livewire adapter is available.';
    }
}
