<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class RecordRelationship
{
    public function execute(array $attributes): Relationship
    {
        if (($attributes['person_id'] ?? null) === ($attributes['related_person_id'] ?? null)) {
            throw new InvalidArgumentException('A relationship must connect two different people.');
        }

        $confidence = (int) ($attributes['confidence'] ?? 100);
        if ($confidence < 0 || $confidence > 100) {
            throw new InvalidArgumentException('Relationship confidence must be between 0 and 100.');
        }

        return Relationship::query()->create(Arr::only($attributes, [
            'person_id', 'related_person_id', 'type', 'confidence', 'metadata',
        ]));
    }
}
