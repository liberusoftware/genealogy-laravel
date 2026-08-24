<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class PlacesLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'genealogy-places-livewire');
        Livewire::component('genealogy-places-list', PlaceList::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Places Livewire adapter is available.';
    }
}
