<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Events\PersonAttributesUpdated;
use Liberu\Genealogy\People\Models\Person;

final class RemovePersonAttribute
{
    public function execute(Person $person, string $attribute): Person
    {
        if ((string) $person->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The person must belong to the active team.');
        }

        $attribute = trim($attribute);
        if ($attribute === '') {
            throw new InvalidArgumentException('An attribute name is required.');
        }

        $attributes = is_array($person->attributes) ? $person->attributes : [];
        unset($attributes[$attribute]);

        DB::transaction(function () use ($person, $attributes): void {
            $person->update(['attributes' => $attributes]);
        });

        $updated = $person->refresh();
        event(new PersonAttributesUpdated($updated));

        return $updated;
    }
}
