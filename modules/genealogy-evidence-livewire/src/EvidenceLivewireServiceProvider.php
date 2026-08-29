<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class EvidenceLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'genealogy-evidence-livewire');
        Livewire::component('genealogy-evidence-list', EvidenceRecordList::class);
        Livewire::component('genealogy-evidence-editor', EvidenceEditor::class);
        Livewire::component('module-genealogy-evidence::evidence-list', EvidenceRecordList::class);
        Livewire::component('module-genealogy-evidence::evidence-editor', EvidenceEditor::class);
        Livewire::component('module-genealogy-evidence::citation-links', CitationLinks::class);
        Livewire::component('module-genealogy-evidence::evidence-entity-list', EvidenceEntityList::class);
        Livewire::component('module-genealogy-evidence::evidence-entity-editor', EvidenceEntityEditor::class);
        Livewire::component('module-genealogy-evidence::source-list', EvidenceEntityList::class);
        Livewire::component('module-genealogy-evidence::repository-list', EvidenceEntityList::class);
        Livewire::component('module-genealogy-evidence::citation-list', EvidenceEntityList::class);
        Livewire::component('module-genealogy-evidence::extract-list', EvidenceEntityList::class);
        Livewire::component('module-genealogy-evidence::assertion-list', EvidenceEntityList::class);
        Livewire::component('module-genealogy-evidence::proof-conclusion-list', EvidenceEntityList::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Evidence Livewire adapter is available.';
    }
}
