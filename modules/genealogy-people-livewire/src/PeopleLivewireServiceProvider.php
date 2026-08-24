<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class PeopleLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('genealogy-people-livewire-status', Status::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy People Livewire adapter is available.';
    }
}
