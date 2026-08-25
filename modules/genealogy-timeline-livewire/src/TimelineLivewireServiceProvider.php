<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class TimelineLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'genealogy-timeline-livewire');
        Livewire::component('genealogy-timeline-list', TimelineEventList::class);
        Livewire::component('genealogy-timeline-browser', TimelineBrowser::class);
        Livewire::component('module-genealogy-timeline::timeline-list', TimelineEventList::class);
        Livewire::component('module-genealogy-timeline::timeline-browser', TimelineBrowser::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Timeline Livewire adapter is available.';
    }
}
