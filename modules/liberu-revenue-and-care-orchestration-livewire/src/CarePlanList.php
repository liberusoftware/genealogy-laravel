<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Livewire;

use Liberu\Platform\RevenueAndCareOrchestration\Models\CarePlan;
use Livewire\Component;

final class CarePlanList extends Component
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
        $query = CarePlan::query();

        if ($tenantId !== null) {
            $query->forTenant($tenantId);
        } else {
            $query->whereRaw('1 = 0');
        }

        return view('liberu-revenue-and-care-orchestration-livewire::list', [
            'records' => $query
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
