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
        Livewire::component('genealogy-research-entry-editor', ResearchEntryEditor::class);
        Livewire::component('module-genealogy-research::project-list', ResearchProjectList::class);
        Livewire::component('module-genealogy-research::entry-editor', ResearchEntryEditor::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Research Livewire adapter is available.';
    }
}
