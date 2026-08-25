<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Livewire;

use Liberu\Genealogy\Research\Actions\CreateResearchEntry;
use Liberu\Genealogy\Research\Models\ResearchEntry;
use Livewire\Component;

final class ResearchEntryEditor extends Component
{
    public string $projectId = '';

    public string $kind = 'question';

    public string $title = '';

    public string $body = '';

    public string $status = 'open';

    public string $dueDate = '';

    public function save(CreateResearchEntry $create): void
    {
        $this->validate([
            'projectId' => ['required', 'uuid'],
            'kind' => ['required', 'in:'.implode(',', ResearchEntry::KINDS)],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:50000'],
            'status' => ['required', 'string', 'max:50'],
            'dueDate' => ['nullable', 'date'],
        ]);
        $create->execute([
            'research_project_id' => $this->projectId,
            'kind' => $this->kind,
            'title' => $this->title,
            'body' => $this->body ?: null,
            'status' => $this->status,
            'due_date' => $this->dueDate ?: null,
        ]);
        $this->reset('title', 'body', 'dueDate');
        $this->dispatch('research-entry-created');
    }

    public function render(): mixed
    {
        return view('genealogy-research-livewire::entry-editor', ['kinds' => ResearchEntry::KINDS]);
    }
}
