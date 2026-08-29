<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Livewire;

use Illuminate\Validation\Rule;
use Liberu\Genealogy\Research\Models\ResearchProject;
use Livewire\Component;

final class ResearchProjectList extends Component
{
    public string $status = '';

    protected function rules(): array
    {
        return ['status' => ['nullable', Rule::in(ResearchProject::STATUSES)]];
    }

    public function updatedStatus(): void
    {
        $this->validateOnly('status');
    }

    public function render(): mixed
    {
        abort_unless(auth()->check(), 403);

        return view('genealogy-research-livewire::list', [
            'records' => ResearchProject::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
