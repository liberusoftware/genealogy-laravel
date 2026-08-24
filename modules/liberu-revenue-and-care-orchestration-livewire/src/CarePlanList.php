<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Livewire;

use Liberu\Platform\RevenueAndCareOrchestration\Models\CarePlan;
use Livewire\Component;

final class CarePlanList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        return view('liberu-revenue-and-care-orchestration-livewire::list', [
            'records' => CarePlan::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
