<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class ResearchLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'genealogy-research-livewire');
        Livewire::component('genealogy-research-list', ResearchProjectList::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Research Livewire adapter is available.';
    }
}
