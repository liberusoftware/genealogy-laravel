<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Events\PersonUpdated;
use Liberu\Genealogy\People\Models\Person;

final class UpdatePerson
{
    public function execute(Person $person, array $attributes): Person
    {
        if ((string) $person->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The person must belong to the active team.');
        }
        $values = Arr::only($attributes, [
            'given_name', 'family_name', 'display_name', 'sex', 'aliases', 'attributes',
            'birth_date', 'death_date', 'birth_place', 'death_place', 'is_public', 'metadata',
        ]);
        if (array_key_exists('given_name', $values)) {
            $values['given_name'] = trim((string) $values['given_name']);
            if ($values['given_name'] === '') {
                throw new InvalidArgumentException('A given name is required.');
            }
        }
        if (array_key_exists('sex', $values)) {
            $values['sex'] = Person::normalizeSex($values['sex']);
        }
        if (isset($values['birth_date'], $values['death_date']) && $values['death_date'] < $values['birth_date']) {
            throw new InvalidArgumentException('A death date cannot precede a birth date.');
        }
        DB::transaction(function () use ($person, $values): void {
            $person->update($values);
        });

        event(new PersonUpdated($person->refresh()));

        return $person;
    }
}
