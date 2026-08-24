<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration\Livewire;

use Liberu\Platform\PlatformOrchestration\Models\PlatformWorkflow;
use Livewire\Component;

final class PlatformWorkflowList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        return view('liberu-platform-orchestration-livewire::list', [
            'records' => PlatformWorkflow::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
