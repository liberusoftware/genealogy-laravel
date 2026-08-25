<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Timeline\Events\TimelineEventDeleted;
use Liberu\Genealogy\Timeline\Models\TimelineEvent;

final class DeleteTimelineEvent
{
    public function execute(TimelineEvent $event): void
    {
        if ((string) $event->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The timeline event must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $event->delete());
        event(new TimelineEventDeleted($event));
    }
}
