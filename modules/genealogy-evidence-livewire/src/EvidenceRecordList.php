<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Livewire;

use Liberu\Genealogy\Evidence\Models\EvidenceRecord;
use Livewire\Component;

final class EvidenceRecordList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        return view('genealogy-evidence-livewire::list', [
            'records' => EvidenceRecord::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
