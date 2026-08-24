<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\Timeline\Models\TimelineEvent;

final class CreateTimelineEvent
{
    public function execute(array $attributes): TimelineEvent
    {
        return TimelineEvent::query()->create(Arr::only($attributes, ['name', 'status', 'metadata']));
    }
}
