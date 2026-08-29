<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Livewire;

use Liberu\Genealogy\Timeline\Queries\ChronologicalTimeline;
use Liberu\Genealogy\Timeline\Queries\ConflictingTimelineEvents;
use Livewire\Component;

final class TimelineBrowser extends Component
{
    public ?string $subjectPersonId = null;

    public ?string $familyKey = null;

    public ?string $from = null;

    public ?string $to = null;

    public bool $includePrivate = false;

    public function render(ChronologicalTimeline $timeline, ConflictingTimelineEvents $conflicts): mixed
    {
        $this->validate(['subjectPersonId' => ['nullable', 'uuid'], 'familyKey' => ['nullable', 'string', 'max:255'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'], 'includePrivate' => ['boolean']]);
        $includePrivate = $this->includePrivate && auth()->check();

        return view('genealogy-timeline-livewire::timeline', [
            'events' => $timeline->execute($this->subjectPersonId, $this->familyKey, $this->from, $this->to, $includePrivate),
            'conflicts' => $conflicts->execute($this->subjectPersonId, $includePrivate),
        ]);
    }
}
