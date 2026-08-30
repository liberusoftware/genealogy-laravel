<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Livewire;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Liberu\Genealogy\Collaboration\Models\CollaborationSpace;
use Livewire\Component;

final class CollaborationSpaceList extends Component
{
    public string $status = '';

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(CollaborationSpace::STATUSES)],
        ];
    }

    public function updatedStatus(): void
    {
        Validator::validate(['status' => $this->status], $this->rules());
    }

    public function render(): mixed
    {
        abort_unless(auth()->check(), 403);

        return view('genealogy-collaboration-livewire::list', [
            'records' => CollaborationSpace::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
