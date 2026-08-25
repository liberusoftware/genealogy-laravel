<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Events;

use Liberu\Genealogy\Timeline\Models\TimelineEvent;

final class TimelineEventDeleted
{
    public bool $afterCommit = true;

    public function __construct(public TimelineEvent $event) {}
}
