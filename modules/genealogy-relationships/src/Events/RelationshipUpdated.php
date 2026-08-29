<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Events;

use Liberu\Genealogy\Relationships\Models\Relationship;

final class RelationshipUpdated
{
    public bool $afterCommit = true;

    public function __construct(public Relationship $relationship) {}
}
