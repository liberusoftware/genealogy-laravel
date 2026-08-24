<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\People\Models\Person;

final class UpdatePerson
{
    public function execute(Person $person, array $attributes): Person
    {
        $person->update(Arr::only($attributes, [
            'given_name', 'family_name', 'display_name', 'birth_date', 'death_date',
            'birth_place', 'death_place', 'metadata',
        ]));

        return $person->refresh();
    }
}
