<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class PlacesLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('genealogy-places-livewire-status', Status::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Places Livewire adapter is available.';
    }
}
