<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class DiscoveryLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('genealogy-discovery-livewire-status', Status::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Discovery Livewire adapter is available.';
    }
}
