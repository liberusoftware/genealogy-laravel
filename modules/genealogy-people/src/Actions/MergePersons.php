<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Events\PersonMerged;
use Liberu\Genealogy\People\Models\MergeCandidate;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\People\Models\PersonAssociation;

final class MergePersons
{
    public function execute(Person $primary, Person $duplicate): Person
    {
        $teamId = app(TeamContext::class)->require();

        if ((string) $primary->team_id !== $teamId || (string) $duplicate->team_id !== $teamId) {
            throw new InvalidArgumentException('Both people must belong to the active team.');
        }
        if ((string) $primary->getKey() === (string) $duplicate->getKey()) {
            throw new InvalidArgumentException('A person cannot be merged with itself.');
        }

        $duplicateId = (string) $duplicate->getKey();
        $merged = DB::transaction(function () use ($primary, $duplicate, $teamId): Person {
            $primary = Person::query()->where('team_id', $teamId)->lockForUpdate()->findOrFail($primary->getKey());
            $duplicate = Person::query()->where('team_id', $teamId)->lockForUpdate()->findOrFail($duplicate->getKey());

            $this->mergePersonFields($primary, $duplicate);
            $this->moveNames($primary, $duplicate);
            $this->moveIdentities($primary, $duplicate);
            $this->moveLifeEvents($primary, $duplicate);
            $this->moveMergeCandidates($primary, $duplicate);
            $this->moveAssociations($primary, $duplicate);
            $duplicate->forceFill([
                'metadata' => array_merge($duplicate->metadata ?? [], [
                    'merged_into' => (string) $primary->getKey(),
                    'merged_at' => now()->toISOString(),
                ]),
            ])->save();
            $duplicate->delete();

            return $primary->fresh();
        });

        event(new PersonMerged($merged, $duplicateId));

        return $merged;
    }

    private function mergePersonFields(Person $primary, Person $duplicate): void
    {
        $fallbackFields = [
            'given_name', 'family_name', 'display_name', 'sex', 'birth_date', 'death_date',
            'birth_place', 'death_place', 'is_public',
        ];

        foreach ($fallbackFields as $field) {
            $current = $primary->getAttribute($field);
            $fallback = $duplicate->getAttribute($field);
            if (($current === null || $current === '') && $fallback !== null && $fallback !== '') {
                $primary->setAttribute($field, $fallback);
            }
        }
        foreach (['aliases', 'attributes', 'metadata'] as $field) {
            $primary->setAttribute($field, array_replace($duplicate->getAttribute($field) ?? [], $primary->getAttribute($field) ?? []));
        }
        $primary->save();
    }

    private function moveNames(Person $primary, Person $duplicate): void
    {
        foreach ($duplicate->names()->get() as $name) {
            $exists = $primary->names()->where('type', $name->type)->where('given_name', $name->given_name)->where('family_name', $name->family_name)->exists();
            $exists ? $name->delete() : $name->update(['person_id' => $primary->getKey()]);
        }
    }

    private function moveIdentities(Person $primary, Person $duplicate): void
    {
        foreach ($duplicate->identities()->get() as $identity) {
            $exists = $primary->identities()->where('type', $identity->type)->where('value', $identity->value)->exists();
            $exists ? $identity->delete() : $identity->update(['person_id' => $primary->getKey()]);
        }
    }

    private function moveLifeEvents(Person $primary, Person $duplicate): void
    {
        foreach ($duplicate->lifeEvents()->get() as $lifeEvent) {
            $exists = $primary->lifeEvents()->where('type', $lifeEvent->type)->whereDate('date', $lifeEvent->date)->where('place', $lifeEvent->place)->exists();
            $exists ? $lifeEvent->delete() : $lifeEvent->update(['person_id' => $primary->getKey()]);
        }
    }

    private function moveMergeCandidates(Person $primary, Person $duplicate): void
    {
        MergeCandidate::query()->where('team_id', $primary->team_id)->where('status', '!=', 'accepted')->where(function ($query) use ($duplicate): void {
            $query->where('person_id', $duplicate->getKey())->orWhere('candidate_person_id', $duplicate->getKey());
        })->delete();
    }

    private function moveAssociations(Person $primary, Person $duplicate): void
    {
        foreach ($duplicate->associations()->get() as $association) {
            $alreadyExists = $primary->associations()
                ->where('relationship', $association->relationship)
                ->where('associated_person_id', $association->associated_person_id)
                ->where('associated_external_id', $association->associated_external_id)
                ->exists();

            $alreadyExists ? $association->delete() : $association->update(['person_id' => $primary->getKey()]);
        }

        PersonAssociation::query()
            ->where('team_id', $primary->team_id)
            ->where('associated_person_id', $duplicate->getKey())
            ->get()
            ->each(function (PersonAssociation $association) use ($primary): void {
                if ((string) $association->person_id === (string) $primary->getKey()) {
                    $association->delete();

                    return;
                }

                $association->update(['associated_person_id' => $primary->getKey()]);
            });
    }
}
