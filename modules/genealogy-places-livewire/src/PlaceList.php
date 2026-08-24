<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Livewire;

use Liberu\Genealogy\Places\Models\Place;
use Livewire\Component;

final class PlaceList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        return view('genealogy-places-livewire::list', [
            'records' => Place::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
