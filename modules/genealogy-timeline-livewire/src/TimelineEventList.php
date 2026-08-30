<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Livewire;

use Illuminate\Validation\Rule;
use Liberu\Genealogy\Timeline\Models\TimelineEvent;
use Livewire\Component;

final class TimelineEventList extends Component
{
    public string $status = '';

    protected function rules(): array
    {
        return ['status' => ['nullable', Rule::in(TimelineEvent::STATUSES)]];
    }

    public function updatedStatus(): void
    {
        $this->validateOnly('status');
    }

    public function render(): mixed
    {
        abort_unless(auth()->check(), 403);

        return view('genealogy-timeline-livewire::list', [
            'records' => TimelineEvent::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
