<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Livewire;

use Illuminate\Validation\Rule;
use Liberu\Genealogy\Places\Models\Place;
use Liberu\Genealogy\Places\Queries\PlaceHierarchy;
use Livewire\Component;

final class PlaceList extends Component
{
    public string $status = '';

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        return ['status' => ['nullable', Rule::in(Place::STATUSES)]];
    }

    public function updatedStatus(): void
    {
        $this->validateOnly('status');
    }

    public function render(PlaceHierarchy $hierarchy): mixed
    {
        abort_unless(auth()->check(), 403);

        return view('genealogy-places-livewire::list', [
            'records' => Place::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
            'hierarchy' => $hierarchy->execute(flat: true),
        ]);
    }
}
