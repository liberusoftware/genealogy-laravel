<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Listeners;

use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\People\Events\PersonMerged;

final class ReconcilePersonMerge
{
    public function handle(PersonMerged $event): void
    {
        DnaKit::query()
            ->where('team_id', $event->primary->team_id)
            ->where('person_id', $event->duplicateId)
            ->update(['person_id' => $event->primary->getKey()]);
    }
}
