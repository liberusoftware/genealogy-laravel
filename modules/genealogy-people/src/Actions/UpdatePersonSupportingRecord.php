<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\MergeCandidate;
use Liberu\Genealogy\People\Models\PersonIdentity;
use Liberu\Genealogy\People\Models\PersonLifeEvent;
use Liberu\Genealogy\People\Models\PersonName;
use Liberu\Genealogy\People\Support\PersonReference;

final class UpdatePersonSupportingRecord
{
    public function execute(Model $record, array $attributes): Model
    {
        if ((string) $record->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The record must belong to the active team.');
        }

        $values = array_intersect_key($attributes, array_flip($record->getFillable()));
        unset($values['team_id'], $values['person_id']);

        if ($record instanceof PersonName) {
            $givenName = array_key_exists('given_name', $values) ? $values['given_name'] : $record->given_name;
            $familyName = array_key_exists('family_name', $values) ? $values['family_name'] : $record->family_name;
            if (blank($givenName) && blank($familyName)) {
                throw new InvalidArgumentException('A name must contain a name part.');
            }
            $values['given_name'] = filled($givenName) ? trim((string) $givenName) : null;
            $values['family_name'] = filled($familyName) ? trim((string) $familyName) : null;
        }

        if ($record instanceof PersonIdentity) {
            $values['type'] = trim((string) ($values['type'] ?? $record->type));
            $values['value'] = trim((string) ($values['value'] ?? $record->value));
            if ($values['type'] === '' || $values['value'] === '') {
                throw new InvalidArgumentException('An identity requires a type and value.');
            }
        }

        if ($record instanceof PersonLifeEvent) {
            $values['type'] = trim((string) ($values['type'] ?? $record->type));
            if ($values['type'] === '') {
                throw new InvalidArgumentException('A life event requires a type.');
            }
        }

        if ($record instanceof MergeCandidate && (array_key_exists('candidate_person_id', $values) || array_key_exists('person_id', $attributes))) {
            $personId = app(PersonReference::class)->require($record->person_id);
            $candidateId = app(PersonReference::class)->require($values['candidate_person_id'] ?? $record->candidate_person_id);
            if ($personId === $candidateId) {
                throw new InvalidArgumentException('A merge candidate must contain two different people.');
            }
            $values['candidate_person_id'] = $candidateId;
        }

        $record->fill($values)->save();

        return $record->refresh();
    }
}
