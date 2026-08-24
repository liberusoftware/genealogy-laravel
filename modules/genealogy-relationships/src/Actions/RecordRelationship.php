<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class RecordRelationship
{
    public function execute(array $attributes): Relationship
    {
        return Relationship::query()->create(Arr::only($attributes, [
            'person_id', 'related_person_id', 'type', 'confidence', 'metadata',
        ]));
    }
}
