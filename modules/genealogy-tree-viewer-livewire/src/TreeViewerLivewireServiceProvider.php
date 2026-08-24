<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class TreeViewerLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'genealogy-tree-viewer-livewire');
        Livewire::component('genealogy-tree-viewer-list', TreeViewList::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy TreeViewer Livewire adapter is available.';
    }
}
