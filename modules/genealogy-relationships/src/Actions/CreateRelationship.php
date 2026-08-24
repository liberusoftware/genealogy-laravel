<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Actions;

use Liberu\Genealogy\Relationships\Models\Relationship;

final class CreateRelationship
{
    public function execute(array $attributes): Relationship
    {
        return (new RecordRelationship())->execute($attributes);
    }
}
