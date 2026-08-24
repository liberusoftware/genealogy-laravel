<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class TreeViewerLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('genealogy-tree-viewer-livewire-status', Status::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy TreeViewer Livewire adapter is available.';
    }
}
