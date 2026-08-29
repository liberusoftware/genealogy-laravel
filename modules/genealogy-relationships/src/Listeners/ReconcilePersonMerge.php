<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Listeners;

use Illuminate\Support\Facades\DB;
use Liberu\Genealogy\People\Events\PersonMerged;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class ReconcilePersonMerge
{
    public function handle(PersonMerged $event): void
    {
        DB::transaction(function () use ($event): void {
            $relationships = Relationship::query()
                ->where('team_id', $event->primary->team_id)
                ->where(function ($query) use ($event): void {
                    $query->where('person_id', $event->duplicateId)->orWhere('related_person_id', $event->duplicateId);
                })
                ->lockForUpdate()
                ->get();

            foreach ($relationships as $relationship) {
                $personId = (string) $relationship->person_id === $event->duplicateId
                    ? $event->primary->getKey()
                    : $relationship->person_id;
                $relatedPersonId = (string) $relationship->related_person_id === $event->duplicateId
                    ? $event->primary->getKey()
                    : $relationship->related_person_id;

                if ((string) $personId === (string) $relatedPersonId || Relationship::query()
                    ->where('team_id', $relationship->team_id)
                    ->where('person_id', $personId)
                    ->where('related_person_id', $relatedPersonId)
                    ->where('type', $relationship->type)
                    ->where($relationship->getKeyName(), '!=', $relationship->getKey())
                    ->exists()) {
                    $relationship->delete();

                    continue;
                }

                $relationship->update(['person_id' => $personId, 'related_person_id' => $relatedPersonId]);
            }
        });
    }
}
