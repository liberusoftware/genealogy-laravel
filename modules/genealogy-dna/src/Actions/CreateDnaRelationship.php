<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Dna\Models\DnaMatch;
use Liberu\Genealogy\Dna\Models\DnaRelationship;
use Liberu\Genealogy\People\Models\Person;

final class CreateDnaRelationship
{
    public function execute(array $attributes): DnaRelationship
    {
        $values = Arr::only($attributes, ['match_id', 'person_id', 'relationship_type', 'confidence', 'status', 'rationale', 'metadata']);
        if (! DnaMatch::query()->whereKey($values['match_id'] ?? null)->exists()) {
            throw ValidationException::withMessages(['match_id' => 'The match must belong to the active team.']);
        }
        if (! Person::query()->whereKey($values['person_id'] ?? null)->exists()) {
            throw ValidationException::withMessages(['person_id' => 'The person must belong to the active team.']);
        }
        if (isset($values['confidence']) && ($values['confidence'] < 0 || $values['confidence'] > 100)) {
            throw ValidationException::withMessages(['confidence' => 'Confidence must be between 0 and 100.']);
        }
        if (isset($values['status']) && ! in_array($values['status'], DnaRelationship::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'The relationship status is invalid.']);
        }

        return DnaRelationship::query()->create($values);
    }
}
