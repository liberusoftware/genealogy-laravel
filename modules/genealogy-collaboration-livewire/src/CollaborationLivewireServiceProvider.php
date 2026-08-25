<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class CollaborationLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'genealogy-collaboration-livewire');
        Livewire::component('genealogy-collaboration-list', CollaborationSpaceList::class);
        Livewire::component('module-genealogy-collaboration::collaboration-list', CollaborationSpaceList::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Collaboration Livewire adapter is available.';
    }
}
