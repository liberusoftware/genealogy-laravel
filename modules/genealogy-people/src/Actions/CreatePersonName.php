<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\PersonName;
use Liberu\Genealogy\People\Support\PersonReference;

final class CreatePersonName
{
    public function execute(array $attributes): PersonName
    {
        $values = Arr::only($attributes, ['person_id', 'type', 'given_name', 'family_name', 'prefix', 'suffix', 'is_preferred', 'metadata']);
        if ((empty($values['given_name']) && empty($values['family_name']))) {
            throw new InvalidArgumentException('A name must belong to a person and contain a name part.');
        }
        $values['person_id'] = app(PersonReference::class)->require($values['person_id'] ?? null);
        $values['given_name'] = filled($values['given_name'] ?? null) ? trim((string) $values['given_name']) : null;
        $values['family_name'] = filled($values['family_name'] ?? null) ? trim((string) $values['family_name']) : null;
        $values['team_id'] = app(TeamContext::class)->require();

        return PersonName::query()->create($values);
    }
}
