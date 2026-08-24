<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Livewire;

use Liberu\Platform\ExecutiveInsights\Models\InsightSnapshot;
use Livewire\Component;

final class InsightSnapshotList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        return view('liberu-executive-insights-livewire::list', [
            'records' => InsightSnapshot::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
