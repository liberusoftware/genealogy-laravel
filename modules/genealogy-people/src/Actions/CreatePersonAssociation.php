<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\People\Models\PersonAssociation;

final class CreatePersonAssociation
{
    public function execute(array $attributes): PersonAssociation
    {
        $teamId = app(TeamContext::class)->require();
        $values = Arr::only($attributes, ['person_id', 'associated_person_id', 'associated_external_id', 'relationship', 'description', 'metadata']);
        $person = Person::query()->where('team_id', $teamId)->findOrFail($values['person_id'] ?? '');
        if (empty($values['associated_person_id']) === empty($values['associated_external_id'])) {
            throw new InvalidArgumentException('An association must reference a person or an external identifier.');
        }
        if (isset($values['associated_person_id'])) {
            Person::query()->where('team_id', $teamId)->findOrFail($values['associated_person_id']);
        }
        $values['person_id'] = $person->getKey();
        $values['team_id'] = $teamId;
        $values['relationship'] = trim((string) ($values['relationship'] ?? ''));
        if ($values['relationship'] === '') {
            throw new InvalidArgumentException('An association relationship is required.');
        }

        return PersonAssociation::query()->create($values);
    }
}
