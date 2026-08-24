<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Livewire;

use Liberu\Platform\BusinessWorkflowReconciliation\Models\ReconciliationCase;
use Livewire\Component;

final class ReconciliationCaseList extends Component
{
    public string $status = '';

    public function updatedStatus(string $status): void
    {
        if (! in_array($status, ['', 'draft', 'active', 'completed'], true)) {
            $this->status = '';
        }
    }

    public function render(): mixed
    {
        $user = auth()->user();
        $tenantId = $user?->currentTeam?->getKey() ?? $user?->getAuthIdentifier();
        $query = ReconciliationCase::query();

        if ($tenantId !== null) {
            $query->forTenant($tenantId);
        } else {
            $query->whereRaw('1 = 0');
        }

        return view('liberu-business-workflow-reconciliation-livewire::list', [
            'records' => $query
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
