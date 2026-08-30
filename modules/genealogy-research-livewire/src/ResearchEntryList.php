<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Livewire;

use Illuminate\Validation\Rule;
use Liberu\Genealogy\Research\Actions\UpdateResearchEntry;
use Liberu\Genealogy\Research\Models\ResearchEntry;
use Livewire\Component;

final class ResearchEntryList extends Component
{
    public string $projectId = '';

    public string $status = '';

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        return [
            'projectId' => ['nullable', 'uuid'],
            'status' => ['nullable', Rule::in(ResearchEntry::STATUSES)],
        ];
    }

    public function updatedStatus(): void
    {
        $this->validateOnly('status');
    }

    public function complete(string $id, UpdateResearchEntry $update): void
    {
        $entry = ResearchEntry::query()->findOrFail($id);
        $update->execute($entry, ['status' => 'completed', 'completed_at' => now()]);
        $this->dispatch('research-entry-completed', id: $id);
    }

    public function render(): mixed
    {
        return view('genealogy-research-livewire::entries', [
            'records' => ResearchEntry::query()
                ->when($this->projectId !== '', fn ($query) => $query->where('research_project_id', $this->projectId))
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest('due_date')
                ->limit(50)
                ->get(),
        ]);
    }
}
