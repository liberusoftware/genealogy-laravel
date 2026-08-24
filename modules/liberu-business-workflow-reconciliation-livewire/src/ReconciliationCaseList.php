<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Livewire;

use Liberu\Platform\BusinessWorkflowReconciliation\Models\ReconciliationCase;
use Livewire\Component;

final class ReconciliationCaseList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        return view('liberu-business-workflow-reconciliation-livewire::list', [
            'records' => ReconciliationCase::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
