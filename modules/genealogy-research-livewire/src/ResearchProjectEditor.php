<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Livewire;

use Illuminate\Validation\Rule;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Research\Actions\CreateResearchProject;
use Liberu\Genealogy\Research\Actions\UpdateResearchProject;
use Liberu\Genealogy\Research\Models\ResearchProject;
use Livewire\Component;

final class ResearchProjectEditor extends Component
{
    public ?string $projectId = null;

    public string $name = '';

    public string $status = 'draft';

    public function mount(?string $projectId = null): void
    {
        $this->projectId = $projectId;

        if ($projectId === null) {
            return;
        }

        $project = $this->project();
        $this->name = $project->name;
        $this->status = $project->status;
    }

    public function save(CreateResearchProject $create, UpdateResearchProject $update): void
    {
        $values = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(ResearchProject::STATUSES)],
        ]);
        abort_unless(auth()->check(), 403);

        if ($this->projectId === null) {
            $project = $create->execute($values);
            $this->projectId = (string) $project->getKey();
            $this->dispatch('research-project-created');

            return;
        }

        $update->execute($this->project(), $values);
        $this->dispatch('research-project-updated');
    }

    public function render(): mixed
    {
        return view('genealogy-research-livewire::project-editor', [
            'statuses' => ResearchProject::STATUSES,
        ]);
    }

    private function project(): ResearchProject
    {
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        abort_unless($teamId !== null, 403);

        return app(TeamContext::class)->run($teamId, fn (): ResearchProject => ResearchProject::query()->findOrFail($this->projectId));
    }
}
