<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class CollaborationLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('genealogy-collaboration-livewire-status', Status::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Collaboration Livewire adapter is available.';
    }
}
