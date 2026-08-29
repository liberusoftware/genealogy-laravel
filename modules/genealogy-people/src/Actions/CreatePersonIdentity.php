<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\PersonIdentity;
use Liberu\Genealogy\People\Support\PersonReference;

final class CreatePersonIdentity
{
    public function execute(array $attributes): PersonIdentity
    {
        $values = Arr::only($attributes, ['person_id', 'type', 'value', 'label', 'is_verified', 'metadata']);
        if (empty($values['type']) || trim((string) ($values['value'] ?? '')) === '') {
            throw new InvalidArgumentException('An identity requires a person, type, and value.');
        }
        $values['person_id'] = app(PersonReference::class)->require($values['person_id'] ?? null);
        $values['type'] = trim((string) $values['type']);
        $values['value'] = trim((string) $values['value']);
        $values['team_id'] = app(TeamContext::class)->require();

        return PersonIdentity::query()->create($values);
    }
}
