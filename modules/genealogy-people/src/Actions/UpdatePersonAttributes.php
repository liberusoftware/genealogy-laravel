<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Events\PersonAttributesUpdated;
use Liberu\Genealogy\People\Models\Person;

final class UpdatePersonAttributes
{
    /** @param array<string, mixed> $attributes */
    public function execute(Person $person, array $attributes, bool $replace = false): Person
    {
        if ((string) $person->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The person must belong to the active team.');
        }

        $attributes = $this->normalise($attributes);
        $current = is_array($person->attributes) ? $person->attributes : [];
        $next = $replace ? $attributes : array_replace($current, $attributes);

        DB::transaction(function () use ($person, $next): void {
            $person->update(['attributes' => $next]);
        });

        $updated = $person->refresh();
        event(new PersonAttributesUpdated($updated));

        return $updated;
    }

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    private function normalise(array $attributes): array
    {
        if ($attributes !== [] && array_is_list($attributes)) {
            throw new InvalidArgumentException('Person attributes must be an object keyed by attribute name.');
        }

        $normalised = [];
        foreach ($attributes as $key => $value) {
            $name = trim((string) $key);
            if ($name === '' || mb_strlen($name) > 100) {
                throw new InvalidArgumentException('Person attribute names must be between 1 and 100 characters.');
            }
            $normalised[$name] = $value;
        }

        return $normalised;
    }
}
