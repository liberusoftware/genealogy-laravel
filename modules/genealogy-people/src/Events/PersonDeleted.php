<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Events;

use Liberu\Genealogy\People\Models\Person;

final class PersonDeleted
{
    public bool $afterCommit = true;

    public function __construct(public Person $person) {}
}
