<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Livewire;

use Liberu\Genealogy\Timeline\Models\TimelineEvent;
use Livewire\Component;

final class TimelineEventList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        return view('genealogy-timeline-livewire::list', [
            'records' => TimelineEvent::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
