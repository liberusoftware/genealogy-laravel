<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Models\Relationship;
use Liberu\Genealogy\Relationships\Queries\GraphValidator;

final class RecordRelationship
{
    public function execute(array $attributes): Relationship
    {
        $personId = (string) ($attributes['person_id'] ?? '');
        $relatedPersonId = (string) ($attributes['related_person_id'] ?? '');

        if ($personId === '' || $relatedPersonId === '' || $personId === $relatedPersonId) {
            throw new InvalidArgumentException('A relationship must connect two different people.');
        }

        $type = (string) ($attributes['type'] ?? '');
        $confidence = (int) ($attributes['confidence'] ?? 100);
        if ($confidence < 0 || $confidence > 100) {
            throw new InvalidArgumentException('Relationship confidence must be between 0 and 100.');
        }

        (new GraphValidator())->assertValid($personId, $relatedPersonId, $type);

        if (! Person::query()->whereKey($personId)->exists() || ! Person::query()->whereKey($relatedPersonId)->exists()) {
            throw new InvalidArgumentException('Both people must belong to the active team.');
        }

        return Relationship::query()->create(Arr::only($attributes, [
            'person_id', 'related_person_id', 'type', 'confidence', 'metadata',
        ]));
    }
}
