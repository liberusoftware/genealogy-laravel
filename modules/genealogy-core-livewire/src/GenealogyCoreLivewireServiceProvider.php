<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Livewire;

use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Livewire\Components\TreeManager;
use Livewire\Livewire;

final class GenealogyCoreLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'genealogy-core-livewire');
        Livewire::component('module-genealogy-core::tree-manager', TreeManager::class);
    }
}
