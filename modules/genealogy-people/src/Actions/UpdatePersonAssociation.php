<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\People\Models\PersonAssociation;

final class UpdatePersonAssociation
{
    public function execute(PersonAssociation $association, array $attributes): PersonAssociation
    {
        $teamId = app(TeamContext::class)->require();
        if ((string) $association->team_id !== $teamId) {
            throw new InvalidArgumentException('The association belongs to another team.');
        }
        $values = Arr::only($attributes, ['associated_person_id', 'associated_external_id', 'relationship', 'description', 'metadata']);
        if (array_key_exists('associated_person_id', $values) && $values['associated_person_id'] !== null) {
            Person::query()->where('team_id', $teamId)->findOrFail($values['associated_person_id']);
        }
        if (array_key_exists('relationship', $values)) {
            $values['relationship'] = trim((string) $values['relationship']);
        }
        if (array_key_exists('associated_person_id', $values) && $values['associated_person_id'] !== null) {
            $values['associated_external_id'] = null;
        }
        if (array_key_exists('associated_external_id', $values) && filled($values['associated_external_id'])) {
            $values['associated_person_id'] = null;
        }
        $association->update($values);

        return $association->fresh();
    }
}
