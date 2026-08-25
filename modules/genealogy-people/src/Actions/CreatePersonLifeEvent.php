<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\PersonLifeEvent;
use Liberu\Genealogy\People\Support\PersonReference;

final class CreatePersonLifeEvent
{
    public function execute(array $attributes): PersonLifeEvent
    {
        $values = Arr::only($attributes, ['person_id', 'type', 'date', 'place', 'description', 'metadata']);
        if (empty($values['type'])) {
            throw new InvalidArgumentException('A life event requires a person and type.');
        }
        $values['person_id'] = app(PersonReference::class)->require($values['person_id'] ?? null);
        $values['type'] = trim((string) $values['type']);
        $values['team_id'] = app(TeamContext::class)->require();

        return PersonLifeEvent::query()->create($values);
    }
}
