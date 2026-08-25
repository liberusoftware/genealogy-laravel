<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Listeners;

use Liberu\Genealogy\Evidence\Models\CitationLink;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;
use Liberu\Genealogy\People\Events\PersonMerged;

final class ReconcilePersonMerge
{
    public function handle(PersonMerged $event): void
    {
        EvidenceRecord::query()
            ->where('team_id', $event->primary->team_id)
            ->where('subject_person_id', $event->duplicateId)
            ->update(['subject_person_id' => $event->primary->getKey()]);
        CitationLink::query()
            ->where('team_id', $event->primary->team_id)
            ->where('subject_person_id', $event->duplicateId)
            ->update(['subject_person_id' => $event->primary->getKey()]);
    }
}
