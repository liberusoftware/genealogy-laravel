<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Events\PersonUpdated;
use Liberu\Genealogy\People\Models\Person;

final class SetPersonLifeStatus
{
    public function execute(Person $person, string $status, ?string $deathDate = null): Person
    {
        if ((string) $person->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The person must belong to the active team.');
        }
        if (! in_array($status, ['living', 'deceased'], true)) {
            throw new InvalidArgumentException('The life status must be living or deceased.');
        }

        $date = $status === 'deceased'
            ? ($deathDate !== null ? Carbon::parse($deathDate)->toDateString() : $person->death_date?->toDateString())
            : null;
        if ($status === 'deceased' && $date === null) {
            throw new InvalidArgumentException('A death date is required when marking a person deceased.');
        }
        if ($date !== null && $person->birth_date !== null && $date < $person->birth_date->toDateString()) {
            throw new InvalidArgumentException('A death date cannot precede a birth date.');
        }

        DB::transaction(fn (): bool => $person->update(['death_date' => $date]));
        $updated = $person->refresh();
        event(new PersonUpdated($updated));

        return $updated;
    }
}
