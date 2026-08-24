<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Livewire;

use Liberu\Genealogy\Dna\Models\DnaKit;
use Livewire\Component;

final class DnaKitList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        return view('genealogy-dna-livewire::list', [
            'records' => DnaKit::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
