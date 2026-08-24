<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Livewire;

use Liberu\Genealogy\Reports\Models\GenealogyReport;
use Livewire\Component;

final class GenealogyReportList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        return view('genealogy-reports-livewire::list', [
            'records' => GenealogyReport::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
