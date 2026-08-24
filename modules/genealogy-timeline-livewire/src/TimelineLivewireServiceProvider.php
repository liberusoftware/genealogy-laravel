<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class TimelineLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('genealogy-timeline-livewire-status', Status::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Timeline Livewire adapter is available.';
    }
}
