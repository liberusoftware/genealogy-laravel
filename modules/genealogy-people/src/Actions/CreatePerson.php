<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\People\Models\Person;

final class CreatePerson
{
    public function execute(array $attributes): Person
    {
        return Person::query()->create(Arr::only($attributes, [
            'given_name', 'family_name', 'display_name', 'sex', 'aliases', 'attributes',
            'birth_date', 'death_date', 'birth_place', 'death_place', 'is_public', 'metadata',
        ]));
    }
}
