<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Events;

use Liberu\Genealogy\Relationships\Models\Relationship;

final class RelationshipDeleted
{
    public bool $afterCommit = true;

    public function __construct(public Relationship $relationship) {}
}
