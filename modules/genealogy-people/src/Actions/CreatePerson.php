<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Events\PersonCreated;
use Liberu\Genealogy\People\Models\Person;

final class CreatePerson
{
    public function execute(array $attributes): Person
    {
        $values = Arr::only($attributes, [
            'given_name', 'family_name', 'display_name', 'sex', 'aliases', 'attributes',
            'birth_date', 'death_date', 'birth_place', 'death_place', 'is_public', 'metadata',
        ]);
        $schema = Person::query()->getModel()->getConnection()->getSchemaBuilder();
        $columns = $schema->getColumnListing('genealogy_people');
        $values = Arr::only($values, $columns);
        if (Person::query()->getModel()->getConnection()->getSchemaBuilder()->hasColumn('genealogy_people', 'team_id')) {
            $values['team_id'] = app(TeamContext::class)->require();
        }
        $values['given_name'] = trim((string) ($values['given_name'] ?? ''));
        if ($values['given_name'] === '') {
            throw new InvalidArgumentException('A given name is required.');
        }
        if (isset($values['birth_date'], $values['death_date']) && $values['death_date'] < $values['birth_date']) {
            throw new InvalidArgumentException('A death date cannot precede a birth date.');
        }

        $person = DB::transaction(fn (): Person => Person::query()->create($values));
        if (app()->bound('events')) {
            event(new PersonCreated($person));
        }

        return $person;
    }
}
