<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class DiscoveryLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'genealogy-discovery-livewire');
        Livewire::component('genealogy-discovery-list', DiscoveryMatchList::class);
        Livewire::component('genealogy-discovery-search', DiscoverySearch::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Discovery Livewire adapter is available.';
    }
}
