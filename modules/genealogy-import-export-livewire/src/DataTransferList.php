<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Livewire;

use Liberu\Genealogy\ImportExport\Models\DataTransfer;
use Livewire\Component;

final class DataTransferList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        return view('genealogy-import-export-livewire::list', [
            'records' => DataTransfer::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
